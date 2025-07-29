<?php
namespace App\Controller;

include_once '../src/Core/Controller/ControllerAbstract.php';
include_once '../src/Core/Controller/ControllerResponseInterface.php';
include_once '../src/Service/AuthService.php';
include_once '../src/Service/DBConnectionManager.php';

use App\Core\Controller\ControllerAbstract;
use App\Core\Controller\ControllerResponseInterface;
use App\Service\AuthService;
use App\Service\DBConnectionManager;

class VendorCompanyApiController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager
    )
    {}

    public function apiUpdate(int $id): ControllerResponseInterface
    {
        if (!$this->checkMethod('POST')) {
            return $this->initJsonResponse(['error' => 'Неправильный HTTP-метод']);
        }

        $this->checkAuth();

        $data = $_POST;
        $db = $this->dbManager->getConnection();

        // Валидация обязательных полей
        if (empty($data['name']) || empty($data['full_name']) || 
            empty($data['INN']) || empty($data['OGRN']) || 
            empty($data['address'])) {
            return $this->initJsonResponse(['error' => 'Все обязательные поля должны быть заполнены']);
        }

        // Проверка уникальности полей для других компаний
        $uniqueCheck = $db->query(
            "SELECT id FROM companies 
            WHERE id != {$id} 
            AND (name = '{$db->escape($data['name'])}' 
            OR full_name = '{$db->escape($data['full_name'])}' 
            OR INN = '{$db->escape($data['INN'])}' 
            OR OGRN = '{$db->escape($data['OGRN'])}') 
            LIMIT 1"
        );

        if (!empty($uniqueCheck)) {
            return $this->initJsonResponse(['error' => 'Компания с такими данными уже существует']);
        }

        // Обновление данных
        $updateData = [
            'name' => $data['name'],
            'full_name' => $data['full_name'],
            'INN' => $data['INN'],
            'OGRN' => $data['OGRN'],
            'address' => $data['address'],
            'description' => $data['description'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $updateParts = [];
        foreach ($updateData as $key => $value) {
            $updateParts[] = "$key = '{$db->escape($value)}'";
        }

        $result = $db->query("UPDATE companies SET " . implode(', ', $updateParts) . " WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse(['error' => 'Ошибка при обновлении компании']);
        }

        $this->redirect('/vendor/company');
        return $this->initJsonResponse();
    }

    private function checkMethod(string $method): bool
    {
        return ($_SERVER['REQUEST_METHOD'] === $method);
    }

    private function checkAuth()
    {
        $this->auth->setUserTable('vendors');

        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/vendor/login');
        }

        $this->checkStatus();
    }

    private function checkStatus()
    {
        $userStatus = $this->auth->getUserStatus();
        switch ($userStatus) {
            case 'premoderation':
                $this->redirect('/premoderation_info');
            case 'banned':
                $this->redirect('/ban_info');
            case null:
            case "":
                $this->redirect('/crash');
        }
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}