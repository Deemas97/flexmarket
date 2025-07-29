<?php
namespace App\Controller;

include_once '../src/Core/Controller/ControllerAbstract.php';
include_once '../src/Core/Controller/ControllerResponseInterface.php';
include_once '../src/Service/AuthService.php';

use App\Core\Controller\ControllerAbstract;
use App\Core\Controller\ControllerResponseInterface;
use App\Service\AuthService;
use Exception;

class VendorAuthApiController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth
    )
    {}

    public function apiAuth(): ControllerResponseInterface
    {
        if (!$this->checkMethod('POST') && !isset($_POST['email'])) {
            $response = $this->initJsonResponse();
            $response->addItem('error', 'Invalid request method');
            return $response;
        }

        $login    = $_POST['email'];
        $password = $_POST['password'];
        $remember = $_POST['remember'] ?? false;
        
        $this->auth->setUserTable('vendors');

        if ($this->auth->login($login, $password, $remember)) {
            switch ($this->auth->getUserStatus()) {
                case 'active':
                    $this->redirect('/vendor/main');
                case 'premoderation':
                    $this->redirect('/premoderation_info');
                case 'banned':
                    $this->redirect('/ban_info');
                default:
                    $this->redirect('/crash');
            }
        } else {
            $this->redirect('/vendor/login');
        }

        return $this->initJsonResponse();
    }

    public function apiRegister(): ControllerResponseInterface
    {
        $response = $this->initJsonResponse();

        if (!$this->checkMethod('POST')) {
            $response->addItem('error', 'Invalid request method');
            return $response;
        }
        
        // Validate required fields
        $requiredFields = ['email', 'password', 'f', 'i', 'password_confirmation', 'agree_terms'];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field])) {
                $response->addItem('error', "Missing required field: {$field}");
                return $response;
            }
        }

        $this->auth->setUserTable('vendors');
        
        $additionalData = [
            'f' => $_POST['f'], // Фамилия
            'i' => $_POST['i'], // Имя
            'address' => $_POST['address'],
            'status' => 'premoderation',
            'company_id' => $_POST['company_id'],
            'role_id' => 'manager'
        ];
    
        if (isset($_POST['o'])) {
            $additionalData['o'] = $_POST['o']; // Отчество
        }
    
        try {
            $success = $this->auth->register(
                $_POST['email'],
                $_POST['password'],
                $additionalData
            );
            
            if ($success) {
                $response->addItem('success', 'Registration successful. Your account is pending approval.');
            } else {
                $response->addItem('error', 'Registration failed. Email may already be in use.');
            }
        } catch (Exception $e) {
            $response->addItem('error', 'Registration error: ' . $e->getMessage());
        }

        $this->redirect('/premoderation_info');

        return $response;
    }

    public function apiLogout(): ControllerResponseInterface
    {
        $this->auth->setUserTable('vendors');

        $this->auth->logout();
        $this->redirect('/');

        return $this->initJsonResponse();
    }

    private function checkMethod(string $method): bool
    {
        return ($_SERVER['REQUEST_METHOD'] === $method);
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}