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

class CustomerBasketApiController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager
    )
    {}

    public function apiAdd(): ControllerResponseInterface
    {
        if (!$this->checkMethod('POST')) {
            return $this->initJsonResponse(['error' => 'Invalid request method'], 405);
        }

        $this->checkAuth();

        $data = $_POST;
        $db = $this->dbManager->getConnection();
        $customerId = $this->auth->getUser()['id'];

        try {
            $db->beginTransaction();

            if (empty($data['product_id']) || empty($data['count'])) {
                throw new RuntimeException('ID товара и количество обязательны');
            }

            $productId = (int)$data['product_id'];
            $count = (int)$data['count'];

            $product = $db->query("SELECT id, price, stock_quantity FROM products WHERE id = {$productId} LIMIT 1");
            if (empty($product)) {
                throw new RuntimeException('Товар не найден');
            }

            $product = $product[0];

            if ($count <= 0) {
                throw new RuntimeException('Количество должно быть больше нуля');
            }

            if ($product['stock_quantity'] < $count) {
                throw new RuntimeException('Недостаточно товара на складе');
            }

            $existingProduct = $db->query("
                SELECT product_id, count 
                FROM basket 
                WHERE customer_id = {$customerId} AND product_id = {$productId}
                LIMIT 1
            ");

            $dateCurrent = date('Y-m-d H:i:s');

            if (!empty($existingProduct)) {
                $newCount = $existingProduct[0]['count'] + $count;
                
                if ($product['stock_quantity'] < $newCount) {
                    throw new RuntimeException('Недостаточно товара на складе');
                }

                $result = $db->query("
                    UPDATE basket 
                    SET count = {$newCount}, created_at = '{$dateCurrent}'
                    WHERE id = {$existingProduct[0]['id']}
                ");
            } else {
                $result = $db->query("
                    INSERT INTO basket (customer_id, product_id, count, created_at)
                    VALUES (
                        {$customerId},
                        {$productId},
                        {$count},
                        '{$dateCurrent}'
                    )
                ");
            }

            if ($result === false) {
                throw new RuntimeException('Ошибка при добавлении товара в корзину');
            }

            $db->commit();
            return $this->initJsonResponse(['success' => true]);
        } catch (RuntimeException $e) {
            error_log($e->getMessage());
            $db->rollback();
            return $this->initJsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function apiRemove(): ControllerResponseInterface
    {
        if (!$this->checkMethod('POST')) {
            return $this->initJsonResponse(['error' => 'Invalid request method'], 405);
        }

        $this->checkAuth();

        $data = $_POST;
        $db = $this->dbManager->getConnection();
        $customerId = $this->auth->getUser()['id'];

        try {
            $db->beginTransaction();

            // Validate required fields
            if (empty($data['product_id'])) {
                throw new RuntimeException('ID товара обязателен');
            }

            $productId = (int)$data['product_id'];

            // Check if product exists in basket
            $productInBasket = $db->query("
                SELECT product_id 
                FROM basket 
                WHERE customer_id = {$customerId} AND product_id = {$productId}
                LIMIT 1
            ");

            if (empty($productInBasket)) {
                throw new RuntimeException('Товар не найден в корзине');
            }

            // Remove product from basket
            $result = $db->query("
                DELETE FROM basket 
                WHERE customer_id = {$customerId} AND product_id = {$productId}
            ");

            if ($result === false) {
                throw new RuntimeException('Ошибка при удалении товара из корзины');
            }

            $db->commit();
            return $this->initJsonResponse(['success' => true]);
        } catch (RuntimeException $e) {
            $db->rollback();
            return $this->initJsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function apiUpdate(): ControllerResponseInterface
    {
        if (!$this->checkMethod('POST')) {
            return $this->initJsonResponse(['error' => 'Invalid request method'], 405);
        }

        $this->checkAuth();

        $data = $_POST;
        $db = $this->dbManager->getConnection();
        $customerId = $this->auth->getUser()['id'];

        try {
            $db->beginTransaction();

            // Validate required fields
            if (empty($data['product_id']) || empty($data['count'])) {
                throw new RuntimeException('ID товара и количество обязательны');
            }

            $productId = (int)$data['product_id'];
            $count = (int)$data['count'];

            // Check if product exists and has enough stock
            $product = $db->query("SELECT id, stock_quantity FROM products WHERE id = {$productId} LIMIT 1");
            if (empty($product)) {
                throw new RuntimeException('Товар не найден');
            }

            $product = $product[0];

            if ($count <= 0) {
                // If count is 0 or negative, remove product from basket
                $result = $db->query("
                    DELETE FROM basket 
                    WHERE customer_id = {$customerId} AND product_id = {$productId}
                ");
            } else {
                if ($product['stock_quantity'] < $count) {
                    throw new RuntimeException('Недостаточно товара на складе');
                }

                // Check if product exists in basket
                $existingProduct = $db->query("
                    SELECT product_id 
                    FROM basket 
                    WHERE customer_id = {$customerId} AND product_id = {$productId}
                    LIMIT 1
                ");

                if (empty($existingProduct)) {
                    throw new RuntimeException('Товар не найден в корзине');
                }

                // Update product count
                $result = $db->query("
                    UPDATE basket 
                    SET count = {$count} 
                    WHERE customer_id = {$customerId} AND product_id = {$productId}
                ");
            }

            if ($result === false) {
                throw new RuntimeException('Ошибка при обновлении корзины');
            }

            $db->commit();
            return $this->initJsonResponse(['success' => true]);
        } catch (RuntimeException $e) {
            $db->rollback();
            return $this->initJsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function apiCheckout(): ControllerResponseInterface
    {
        if (!$this->checkMethod('POST')) {
            return $this->initJsonResponse(['error' => 'Invalid request method'], 405);
        }

        $this->checkAuth();

        $db = $this->dbManager->getConnection();
        $customerId = $this->auth->getUser()['id'];

        try {
            $db->beginTransaction();

            // Get basket items
            $basketItems = $db->query("
                SELECT b.product_id, b.count, p.price, p.stock_quantity 
                FROM basket b
                JOIN products p ON b.product_id = p.id
                WHERE b.customer_id = {$customerId}
            ");

            if (empty($basketItems)) {
                throw new RuntimeException('Корзина пуста');
            }

            // Validate stock and calculate total
            $total = 0;
            foreach ($basketItems as $item) {
                if ($item['stock_quantity'] < $item['count']) {
                    throw new RuntimeException('Недостаточно товара "' . $item['name'] . '" на складе');
                }
                $total += $item['price'] * $item['count'];
            }

            // Create order
            $orderData = [
                'customer_id' => $customerId,
                'sum' => $total,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'status' => 'pending'
            ];

            $orderFields = [];
            $orderValues = [];
            foreach ($orderData as $key => $value) {
                $orderFields[] = $key;
                $orderValues[] = "'" . $db->escape($value) . "'";
            }

            $result = $db->query("
                INSERT INTO orders (" . implode(', ', $orderFields) . ")
                VALUES (" . implode(', ', $orderValues) . ")
            ");

            if ($result === false) {
                throw new RuntimeException('Ошибка при создании заказа');
            }

            $orderId = $db->getLastInsertId();

            // Add products to order
            foreach ($basketItems as $item) {
                $result = $db->query("
                    INSERT INTO orders_products (order_id, product_id, price, count)
                    VALUES (
                        {$orderId},
                        {$item['product_id']},
                        {$item['price']},
                        {$item['count']}
                    )
                ");

                if ($result === false) {
                    throw new RuntimeException('Ошибка при добавлении товаров в заказ');
                }

                // Update product stock
                $result = $db->query("
                    UPDATE products 
                    SET stock_quantity = stock_quantity - {$item['count']} 
                    WHERE id = {$item['product_id']}
                ");

                if ($result === false) {
                    throw new RuntimeException('Ошибка при обновлении количества товара');
                }
            }

            // Clear basket
            $result = $db->query("DELETE FROM basket WHERE customer_id = {$customerId}");

            if ($result === false) {
                throw new RuntimeException('Ошибка при очистке корзины');
            }

            $db->commit();
            return $this->initJsonResponse([
                'success' => true,
                'order_id' => $orderId
            ]);
        } catch (RuntimeException $e) {
            $db->rollback();
            return $this->initJsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function apiCount(): ControllerResponseInterface
    {
        $this->checkAuth();

        $db = $this->dbManager->getConnection();
        $customerId = $this->auth->getUser()['id'];

        $count = $db->query("
            SELECT SUM(count) AS total 
            FROM basket 
            WHERE customer_id = {$customerId}
        ");

        return $this->initJsonResponse([
            'success' => true,
            'count' => $count[0]['total'] ?? 0
        ]);
    }

    public function apiGetTotal(): ControllerResponseInterface
    {
        $this->checkAuth();

        $db = $this->dbManager->getConnection();
        $customerId = $this->auth->getUser()['id'];

        $data = [];

        $db = $this->dbManager->getConnection();
        $userId = $this->auth->getUserId();

        // Получаем товары в корзине с полной информацией о товарах
        $result = $db->query("
            SELECT b.*, p.price
            FROM basket b
            JOIN products p ON b.product_id = p.id
            WHERE b.customer_id = {$userId}
        ");

        $data['products'] = $result === false ? [] : $result;

        $count = 0;
        $sum = 0;

        foreach ($data['products'] as $product) {
            $sum += $product['price'] * $product['count'];
            $count += $product['count'];
        }
        $data['sum'] = $sum;

        return $this->initJsonResponse([
            'success' => true,
            'count' => $count,
            'total' => $sum
        ]);
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