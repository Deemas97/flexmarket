<?php
namespace App;

include_once '../src/Core/Container/GlobalContainerInterface.php';
include_once '../src/Core/Container/GlobalContainer.php';
include_once '../src/Core/Container/SharingContainerInterface.php';
include_once '../src/Core/Container/SharingContainer.php';
include_once '../src/Core/MessageBusInterface.php';
include_once '../src/Core/MessageBus/Request.php';
include_once '../src/Core/ModuleInterface.php';
include_once '../src/Core/Module/RequestManagerInterface.php';
include_once '../src/Core/Module/RequestManager.php';
include_once '../src/Core/Module/ActionsManagerInterface.php';
include_once '../src/Core/Module/ActionsManager.php';
include_once '../src/Core/Module/ResponseManagerInterface.php';
include_once '../src/Core/Module/ResponseManager.php';

use App\Core\Container\GlobalContainerInterface;
use App\Core\Container\GlobalContainer;
use App\Core\Container\SharingContainer;
use App\Core\Container\SharingContainerInterface;
use App\Core\MessageBusInterface;
use App\Core\MessageBus\Request;
use App\Core\ModuleInterface;
use App\Core\Module\RequestManagerInterface;
use App\Core\Module\RequestManager;
use App\Core\Module\ActionsManagerInterface;
use App\Core\Module\ActionsManager;
use App\Core\Module\ResponseManagerInterface;
use App\Core\Module\ResponseManager;
use Exception;
use RuntimeException;

class Kernel
{
    private static ?Kernel $coreBase = null;

    // BASE VARIABLES
    protected static string $dir;
    protected static array  $env;

    // CORE INFRASTRUCTURE COMPONENTS
    protected GlobalContainerInterface  $container;
    protected SharingContainerInterface $containerSharing;
    protected MessageBusInterface      $messageBus;

    // CORE INFRASTRUCTURE MODULES
    protected ModuleInterface&RequestManagerInterface  $requestManager;
    protected ModuleInterface&ActionsManagerInterface  $actionsManager;
    protected ModuleInterface&ResponseManagerInterface $responseManager;

    // INIT CORE
    public function __construct()
    {
        try {
            if (self::$coreBase) {
                throw new RuntimeException('Экземпляр ядра уже создан');
            }

            self::$coreBase = &$this;

            $this->initRootDir();
            $this->initEnv(self::$dir . '/configs/.env');
            $this->initContainer();
        } catch (RuntimeException $error) {
            throw new RuntimeException('[Ошибка инициализации ядра приложения]: ' . $error->getMessage());
        }
    }

    // RUN APP CORE
    public function run(): int
    {
        try {
            $this->messageBus = $this->initMessageBus();

            $this->messageBus = $this->requestManager->process($this->messageBus);
            $this->messageBus = $this->actionsManager->process($this->messageBus);
            $this->messageBus = $this->responseManager->process($this->messageBus);
        } catch (Exception $error) {
            throw new RuntimeException($error);
        }

        return 0;
    }

    public static function getRootDir(): string
    {
        return (self::$dir ?? '');
    }

    public static function getEnv(): array
    {
        return (self::$env ?? []);
    }

    public static function reloadEnv(): void
    {
        self::$env = [];
        self::$coreBase->initEnv(self::$dir . '/configs/.env');
    }
    
    // INIT ROOT DIR
    private function initRootDir(): void
    {
        self::$dir = dirname($_SERVER['DOCUMENT_ROOT'] ?? __DIR__);
    }

    // INIT ENV DATA FROM ENV FILE
    private function initEnv($filePath): void
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("Файл .env не найден по пути " . $filePath);
        }
    
        $lines = file($filePath, (FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        self::$env = [];
    
        foreach ($lines as $line) {
            if ((strpos(trim($line), '#') === 0) || (strpos(trim($line), ';') === 0)) {
                continue;
            }
    
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                
                $name = trim($name);
                $value = trim($value);
                
                if (preg_match('/^"(.*)"$/', $value, $matches)) {
                    $value = $matches[1];
                } elseif (preg_match('/^\'(.*)\'$/', $value, $matches)) {
                    $value = $matches[1];
                }
                
                $value = str_replace('\\n', "\n", $value);
                $value = str_replace('\\r', "\r", $value);
                $value = str_replace('\\t', "\t", $value);
                
                self::$env[$name] = $value;
            }
        }
    }

    private function initContainer()
    {
        $this->container = new GlobalContainer();

        // INIT MODULES
        $this->requestManager  = $this->container->get(RequestManager::class);
        $this->actionsManager  = $this->container->get(ActionsManager::class);
        $this->responseManager = $this->container->get(ResponseManager::class);
        ////

        // INIT KERNEL SERVICES CONTAINER FOR CONTROLLER WORKSPACE
        $this->containerSharing = $this->container->get(SharingContainer::class);
        $this->containerSharing->init($this->container);
        ////
    }

    private function initMessageBus(): MessageBusInterface
    {
        $requestDto = new Request();
    
        $uri = explode('?', $_SERVER['REQUEST_URI'])[0];
        parse_str($_SERVER['QUERY_STRING'] ?? '', $queryParams);
    
        $inputData = [];
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $rawInput = file_get_contents('php://input');
            $inputData = json_decode($rawInput, true) ?? [];
        }
    
        $requestDto->addItem('route', $uri);
        $requestDto->addItem('query', $queryParams);
        $requestDto->addItem('input', $inputData);
        $requestDto->addItem('headers', getallheaders());
        $requestDto->addItem('method', $_SERVER['REQUEST_METHOD']);

        $requestDto->addItem('sharing_services', $this->containerSharing);
    
        return $requestDto;
    }
}