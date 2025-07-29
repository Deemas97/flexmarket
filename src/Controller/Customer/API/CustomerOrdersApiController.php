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
use RuntimeException;

class CustomerOrdersApiController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager
    )
    {}

    public function apiCreate(int $id): ControllerResponseInterface
    {
        if (!$this->checkMethod('POST')) {
            return $this->initJsonResponse(['error' => 'Invalid request method'], 405);
        }

        $this->checkAuth();

        $data = $_POST;
        $db = $this->dbManager->getConnection();

        try {
            $db->beginTransaction();

            if (empty($data['status'])) {
                throw new RuntimeException('Статус заказа обязателен');
            }

            $order = $db->query("SELECT id, status FROM orders WHERE id = {$id} LIMIT 1");
            if (empty($order)) {
                throw new RuntimeException('Заказ не найден');
            }

            $currentStatus = $order[0]['status'];

            $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            if (!in_array($data['status'], $validStatuses)) {
                throw new RuntimeException('Недопустимый статус заказа');
            }

            $updateData = [
                'status' => $data['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updateParts = [];
            foreach ($updateData as $key => $value) {
                $updateParts[] = "$key = '{$db->escape($value)}'";
            }

            $result = $db->query("UPDATE orders SET " . implode(', ', $updateParts) . " WHERE id = {$id}");

            if ($result === false) {
                throw new RuntimeException('Ошибка при обновлении статуса заказа');
            }

            if ($data['status'] === 'cancelled' && $currentStatus !== 'pending') {
                $this->restoreStock($id);
            }

            $db->commit();
            $this->redirect('/customer/orders');
            return $this->initJsonResponse();
        } catch (RuntimeException $e) {
            $db->rollback();
            return $this->initJsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function apiCancel(int $id): ControllerResponseInterface
    {
        if (!$this->checkMethod('POST')) {
            return $this->initJsonResponse(['error' => 'Invalid request method'], 405);
        }

        $this->checkAuth();

        $db = $this->dbManager->getConnection();

        try {
            $db->beginTransaction();

            $order = $db->query("SELECT id, status FROM orders WHERE id = {$id} LIMIT 1");
            if (empty($order)) {
                throw new RuntimeException('Заказ не найден');
            }

            $currentStatus = $order[0]['status'];
            if ($currentStatus === 'delivered') {
                throw new RuntimeException('Нельзя отменить уже доставленный заказ');
            }

            if ($currentStatus === 'cancelled') {
                throw new RuntimeException('Заказ уже отменен');
            }

            $updateData = [
                'status' => 'cancelled',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updateParts = [];
            foreach ($updateData as $key => $value) {
                $updateParts[] = "$key = '{$db->escape($value)}'";
            }

            $result = $db->query("UPDATE orders SET " . implode(', ', $updateParts) . " WHERE id = {$id}");

            if ($result === false) {
                throw new RuntimeException('Ошибка при отмене заказа');
            }

            if ($currentStatus !== 'pending') {
                $this->restoreStock($id);
            }

            $db->commit();
            return $this->initJsonResponse(['success' => true]);
        } catch (RuntimeException $e) {
            $db->rollback();
            return $this->initJsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function apiAddProduct(int $orderId): ControllerResponseInterface
    {
        if (!$this->checkMethod('POST')) {
            return $this->initJsonResponse(['error' => 'Invalid request method'], 405);
        }

        $this->checkAuth();

        $data = $_POST;
        $db = $this->dbManager->getConnection();

        try {
            $db->beginTransaction();

            if (empty($data['product_id']) || empty($data['count'])) {
                throw new RuntimeException('ID товара и количество обязательны');
            }

            $order = $db->query("SELECT id, status FROM orders WHERE id = {$orderId} LIMIT 1");
            if (empty($order)) {
                throw new RuntimeException('Заказ не найден');
            }

            $orderStatus = $order[0]['status'];
            if ($orderStatus !== 'pending') {
                throw new RuntimeException('Можно добавлять товары только в заказы со статусом "Ожидает"');
            }

            $product = $db->query("SELECT id, price, stock_quantity FROM products WHERE id = {$db->escape($data['product_id'])} LIMIT 1");
            if (empty($product)) {
                throw new RuntimeException('Товар не найден');
            }

            $product = $product[0];
            $count = (int)$data['count'];

            if ($count <= 0) {
                throw new RuntimeException('Количество должно быть больше нуля');
            }

            if ($product['stock_quantity'] < $count) {
                throw new RuntimeException('Недостаточно товара на складе');
            }

            $existingProduct = $db->query("
                SELECT id, count 
                FROM orders_products 
                WHERE order_id = {$orderId} AND product_id = {$product['id']}
                LIMIT 1
            ");

            if (!empty($existingProduct)) {
                $newCount = $existingProduct[0]['count'] + $count;
                $result = $db->query("
                    UPDATE orders_products 
                    SET count = {$newCount} 
                    WHERE id = {$existingProduct[0]['id']}
                ");
            } else {
                $result = $db->query("
                    INSERT INTO orders_products (order_id, product_id, price, count)
                    VALUES (
                        {$orderId},
                        {$product['id']},
                        {$product['price']},
                        {$count}
                    )
                ");
            }

            if ($result === false) {
                throw new RuntimeException('Ошибка при добавлении товара в заказ');
            }

            $result = $db->query("
                UPDATE products 
                SET stock_quantity = stock_quantity - {$count} 
                WHERE id = {$product['id']}
            ");

            if ($result === false) {
                throw new RuntimeException('Ошибка при обновлении количества товара');
            }

            $this->updateOrderTotal($orderId);

            $db->commit();
            return $this->initJsonResponse(['success' => true]);
        } catch (RuntimeException $e) {
            $db->rollback();
            return $this->initJsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function apiRemoveProduct(int $orderId, int $productId): ControllerResponseInterface
    {
        if (!$this->checkMethod('POST')) {
            return $this->initJsonResponse(['error' => 'Invalid request method'], 405);
        }

        $this->checkAuth();

        $db = $this->dbManager->getConnection();

        try {
            $db->beginTransaction();

            $order = $db->query("SELECT id, status FROM orders WHERE id = {$orderId} LIMIT 1");
            if (empty($order)) {
                throw new RuntimeException('Заказ не найден');
            }

            $orderStatus = $order[0]['status'];
            if ($orderStatus !== 'pending') {
                throw new RuntimeException('Можно удалять товары только из заказов со статусом "Ожидает"');
            }

            $productInOrder = $db->query("
                SELECT id, count 
                FROM orders_products 
                WHERE order_id = {$orderId} AND product_id = {$productId}
                LIMIT 1
            ");

            if (empty($productInOrder)) {
                throw new RuntimeException('Товар не найден в заказе');
            }

            $count = $productInOrder[0]['count'];

            $result = $db->query("
                DELETE FROM orders_products 
                WHERE order_id = {$orderId} AND product_id = {$productId}
            ");

            if ($result === false) {
                throw new RuntimeException('Ошибка при удалении товара из заказа');
            }

            $result = $db->query("
                UPDATE products 
                SET stock_quantity = stock_quantity + {$count} 
                WHERE id = {$productId}
            ");

            if ($result === false) {
                throw new RuntimeException('Ошибка при возврате товара на склад');
            }

            $this->updateOrderTotal($orderId);

            $db->commit();
            return $this->initJsonResponse(['success' => true]);
        } catch (RuntimeException $e) {
            $db->rollback();
            return $this->initJsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    private function restoreStock(int $orderId): void
    {
        $db = $this->dbManager->getConnection();

        $products = $db->query("SELECT product_id, count FROM orders_products WHERE order_id = {$orderId}");

        if (!empty($products)) {
            foreach ($products as $product) {
                $result = $db->query("
                    UPDATE products 
                    SET stock_quantity = stock_quantity + {$product['count']} 
                    WHERE id = {$product['product_id']}
                ");

                if ($result === false) {
                    throw new RuntimeException('Ошибка при возврате товара на склад');
                }
            }
        }
    }

    private function updateOrderTotal(int $orderId): void
    {
        $db = $this->dbManager->getConnection();

        $total = $db->query("
            SELECT SUM(price * count) as total 
            FROM orders_products 
            WHERE order_id = {$orderId}
        ");

        if (!empty($total)) {
            $newTotal = $total[0]['total'] ?? 0;
            $result = $db->query("UPDATE orders SET sum = {$newTotal} WHERE id = {$orderId}");
            
            if ($result === false) {
                throw new RuntimeException('Ошибка при обновлении суммы заказа');
            }
        }
    }

    private function checkAuth(): void
    {
        $this->auth->setUserTable('customers');

        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/customer/login');
        }
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