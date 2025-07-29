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

class VendorAuthController extends ControllerRendering
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
        $this->checkAuthWithRedirection('/vendor/main');

        $this->renderer->enableCaching(true);

        $data = [
            'title' => 'Вход в систему',
            'company_name' => 'ФлексМаркет'
        ];

        return $this->render('auth/vendor/auth_form.html.php', $data);
    }

    public function registerForm(): ControllerResponseInterface
    {
        $this->checkAuthWithRedirection('/vendor/main');

        $this->renderer->enableCaching(true);

        $data = [
            'title' => 'Регистрация',
            'company_name' => 'ФлексМаркет'
        ];

        $dbConnection = $this->dbManager->getConnection();
        $result = $dbConnection->query("SELECT id, name FROM companies");
        $data['companies'] = $result;

        return $this->render('auth/vendor/registration_form.html.php', $data);
    }

    public function resetPassForm(): ControllerResponseInterface
    {
        $this->checkAuthWithRedirection('/vendor/main');

        $this->renderer->enableCaching(true);

        $data = [
            'title' => 'Сброс пароля',
            'company_name' => 'ФлексМаркет'
        ];

        return $this->render('auth/vendor/password_reset_form.html.php', $data);
    }

    public function confirmPassForm(): ControllerResponseInterface
    {
        $this->checkAuthWithRedirection('/vendor/main');

        $data = [
            'title' => 'Подтверждение сброса',
            'company_name' => 'ФлексМаркет'
        ];

        return $this->render('auth/vendor/password_confirm_form.html.php', $data);
    }

    protected function checkAuthWithRedirection(string $path): void
    {
        $this->auth->setUserTable('vendors');

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