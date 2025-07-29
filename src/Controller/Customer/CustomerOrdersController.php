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

class CustomerOrdersController extends ControllerRendering
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
            'title' => 'Заказы',
            'company_name' => 'ФлексМаркет'
        ];

        $userSessionData = $this->auth->getUser();
        $data['user_session'] = [
            'role' => $userSessionData['role'],
            'name' => $userSessionData['name'],
            'email' => $userSessionData['email'],
            'avatar' => $userSessionData['avatar']
        ];

        $db = $this->dbManager->getConnection();
        
        // Получаем список заказов с информацией о клиенте
        $userId = $this->auth->getUserId();
        $orders = $db->query("
            SELECT o.*
            FROM orders o
            WHERE o.customer_id = {$userId}
            ORDER BY o.created_at DESC
        ");
        
        $data['orders'] = $orders === false ? [] : $orders;

        // Получаем статусы для фильтрации
        $data['statuses'] = [
            'pending' => 'Ожидает',
            'processing' => 'В обработке',
            'completed' => 'Готово',
            'cancelled' => 'Отменен'
        ];

        return $this->render('customers/orders.html.php', $data);
    }

    public function order(int $id): ControllerResponseInterface
    {
        $this->checkAuth();
    
        $data = [
            'title' => 'Редактирование заказа',
            'company_name' => 'ФлексМаркет'
        ];
    
        $userSessionData = $this->auth->getUser();
        $data['user_session'] = [
            'role' => $userSessionData['role'],
            'name' => $userSessionData['name'],
            'email' => $userSessionData['email'],
            'avatar' => $userSessionData['avatar']
        ];
    
        $db = $this->dbManager->getConnection();
        $userId = $this->auth->getUserId();
        
        // Получаем информацию о заказе
        $order = $db->query("
            SELECT o.* 
            FROM orders o
            WHERE o.id = {$id} AND o.customer_id = {$userId}
            LIMIT 1
        ");
        
        if (empty($order)) {
            $this->redirect('/error_404');
        }
        $data['order'] = $order[0];
    
        // Получаем товары в заказе
        $products = $db->query("
            SELECT op.*, p.name as product_name, p.price as product_price, p.image_main as product_image
            FROM orders_products op
            LEFT JOIN products p ON op.product_id = p.id
            WHERE op.order_id = {$id}
        ");
        $data['products'] = $products === false ? [] : $products;
    
        // Получаем отзывы для товаров в заказе
        if (!empty($data['products'])) {
            $productIds = array_column($data['products'], 'product_id');
            $reviews = $db->query("
                SELECT id, product_id, rating, comment
                FROM reviews
                WHERE customer_id = {$userId}
                AND product_id IN (" . implode(',', $productIds) . ")
            ");
            
            $reviewsMap = [];
            if ($reviews !== false) {
                foreach ($reviews as $review) {
                    $reviewsMap[$review['product_id']] = $review;
                }
            }
            
            // Добавляем отзывы к товарам
            foreach ($data['products'] as &$product) {
                if (isset($reviewsMap[$product['product_id']])) {
                    $product['review'] = $reviewsMap[$product['product_id']];
                }
            }
        }
    
        // Статусы заказа
        $data['statuses'] = [
            'pending' => 'Ожидает',
            'processing' => 'В обработке',
            'completed' => 'Готов',
            'cancelled' => 'Отменен'
        ];
    
        // Статусы позиций заказа
        $data['position_statuses'] = [
            'pending' => 'Ожидает',
            'processing' => 'В обработке',
            'shipped' => 'Отправлен',
            'delivered' => 'Доставлен',
            'cancelled' => 'Отменен'
        ];
    
        return $this->render('customers/order.html.php', $data);
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
