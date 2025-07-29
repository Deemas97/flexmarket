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

class CustomerBasketController extends ControllerRendering
{
    public function __construct(
        Renderer $renderer,
        private AuthService $auth,
        private DBConnectionManager $dbManager
    ) {
        parent::__construct($renderer);
    }

    public function index(): ControllerResponseInterface
    {
        $this->checkAuth();

        $data = [
            'title' => 'Корзина товаров',
            'company_name' => 'ФлексМаркет'
        ];

        // Добавляем данные пользователя
        $userSessionData = $this->auth->getUser();
        $data['user_session'] = [
            'role' => $userSessionData['role'],
            'name' => $userSessionData['name'],
            'email' => $userSessionData['email'],
            'avatar' => $userSessionData['avatar']
        ];

        $db = $this->dbManager->getConnection();
        $userId = $this->auth->getUserId();

        // Получаем товары в корзине с полной информацией о товарах
        $products = $db->query("
            SELECT b.*, p.id AS product_id, p.name, p.description, p.price, p.stock_quantity, p.image_main
            FROM basket b
            JOIN products p ON b.product_id = p.id
            WHERE b.customer_id = {$userId}
        ");

        $data['products'] = $products === false ? [] : $products;

        // Рассчитываем общую сумму
        $total = 0;
        foreach ($data['products'] as $product) {
            $total += $product['price'] * $product['count'];
        }
        $data['total'] = $total;

        return $this->render('customers/basket.html.php', $data);
    }

    protected function checkAuth()
    {
        $this->auth->setUserTable('customers');

        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/customer/login');
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