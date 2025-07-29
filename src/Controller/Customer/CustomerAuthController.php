<?php
namespace App\Controller;

include_once '../src/Core/Controller/ControllerRendering.php';
include_once '../src/Core/Controller/ControllerResponseInterface.php';
include_once '../src/Core/Service/Renderer.php';
include_once '../src/Service/AuthService.php';
include_once '../src/Service/DBConnectionManager.php';

use App\Core\Controller\ControllerRendering;
use App\Core\Controller\ControllerResponseInterface;
use App\Core\Service\Renderer;
use App\Service\AuthService;
use App\Service\DBConnectionManager;

class CustomerAuthController extends ControllerRendering
{
    public function __construct(
        Renderer $renderer,
        private AuthService $auth,
        private DBConnectionManager $dbManager
    )
    {
        parent::__construct($renderer);
    }

    public function authForm(): ControllerResponseInterface
    {
        $this->checkAuthWithRedirection('/customer/main');

        $this->renderer->enableCaching(true);

        $data = [
            'title' => 'Вход в систему',
            'company_name' => 'ФлексМаркет'
        ];

        return $this->render('auth/customer/auth_form.html.php', $data);
    }

    public function registerForm(): ControllerResponseInterface
    {
        $this->checkAuthWithRedirection('/customer/main');

        $this->renderer->enableCaching(true);

        $data = [
            'title' => 'Регистрация',
            'company_name' => 'ФлексМаркет'
        ];

        return $this->render('auth/customer/registration_form.html.php', $data);
    }

    public function resetPassForm(): ControllerResponseInterface
    {
        $this->checkAuthWithRedirection('/customer/main');

        $this->renderer->enableCaching(true);

        $data = [
            'title' => 'Сброс пароля',
            'company_name' => 'ФлексМаркет'
        ];

        return $this->render('auth/customer/password_reset_form.html.php', $data);
    }

    public function confirmPassForm(): ControllerResponseInterface
    {
        $this->checkAuthWithRedirection('/customer/main');

        $data = [
            'title' => 'Подтверждение сброса',
            'company_name' => 'ФлексМаркет'
        ];

        return $this->render('auth/customer/password_confirm_form.html.php', $data);
    }

    protected function checkAuthWithRedirection(string $path): void
    {
        $this->auth->setUserTable('customers');

        if ($this->auth->isAuthenticated()) {
            $this->redirect($path);
        }
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}