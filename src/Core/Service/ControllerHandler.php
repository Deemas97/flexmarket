<?php
namespace App\Core\Service;

include_once '../src/Core/KernelServiceInterface.php';
include_once '../src/Core/Container/SharingContainer.php';
include_once '../src/Core/Service/ControllerHandler/ControllersContainer.php';
include_once '../src/Core/Service/ControllerHandler/ControllerHandlerResponseInterface.php';
include_once '../src/Core/Service/ControllerHandler/ControllerHandlerResponse.php';
include_once '../src/Service/RouterMediator.php';

use App\Core\KernelServiceInterface;
use App\Core\Container\SharingContainer;
use App\Core\Service\ControllerHandler\ControllersContainer;
use App\Core\Service\ControllerHandler\ControllerHandlerResponseInterface;
use App\Core\Service\ControllerHandler\ControllerHandlerResponse;
use App\Service\RouterMediator;
use ErrorException;
use RuntimeException;

class ControllerHandler implements KernelServiceInterface
{
    public function __construct(
        private ControllersContainer $container
    )
    {}

    public function run(string $controllerName, string $methodName, ?SharingContainer $servicesContainer = null): ControllerHandlerResponseInterface
    {
        $response = new ControllerHandlerResponse();

        if (!class_exists($controllerName)) {
            throw new RuntimeException("Controller {$controllerName} not found");
        }
    
        if (!method_exists($controllerName, $methodName)) {
            throw new RuntimeException("Method {$methodName} not found in {$controllerName}");
        }

        $this->container->set(SharingContainer::class, $servicesContainer);
        
        $routeParameters = $servicesContainer->get(RouterMediator::class)->getCurrentRoute()->getParameters();

        $controller = $this->container->get($controllerName);
        
        try {
            $controllerResponse = $controller->$methodName(...array_values($routeParameters));
        } catch (ErrorException $error) {
            throw $error;
        }
    
        $controllerResponseDump = $controllerResponse->getAll();

        $responseType = (isset($controllerResponseDump['is_json']) && ($controllerResponseDump['is_json'] === true)) ? 'api_json' : 'view';
        
        $response->addItem('type',                $responseType);
        $response->addItem('controller_response', $controllerResponseDump);
        $response->addItem('status',              $controllerResponse->getStatusCode());
        
        return $response;
    }
}
