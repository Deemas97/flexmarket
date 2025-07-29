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

class CustomerReviewsApiController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager
    )
    {}

    public function apiSave(): ControllerResponseInterface
    {
        if (!$this->checkMethod('POST')) {
            return $this->initJsonResponse(['error' => 'Invalid request method'], 405);
        }

        $this->checkAuth();

        $data = $_POST;
        $db = $this->dbManager->getConnection();
        $userId = $this->auth->getUserId();

        try {
            if (empty($data['product_id']) || empty($data['order_id'])) {
                throw new RuntimeException('Не указан товар или заказ');
            }

            if (empty($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
                throw new RuntimeException('Оценка должна быть от 1 до 5');
            }

            if (empty($data['comment']) || strlen(trim($data['comment'])) < 5) {
                throw new RuntimeException('Комментарий должен содержать минимум 5 символов');
            }

            $orderProduct = $db->query("
                SELECT op.product_id, op.status
                FROM orders_products op
                JOIN orders o ON op.order_id = o.id
                WHERE op.order_id = {$db->escape($data['order_id'])}
                AND op.product_id = {$db->escape($data['product_id'])}
                AND o.customer_id = {$userId}
                LIMIT 1
            ");

            if (empty($orderProduct)) {
                throw new RuntimeException('Товар не найден в ваших заказах');
            }

            if ($orderProduct[0]['status'] !== 'delivered') {
                throw new RuntimeException('Можно оставить отзыв только на доставленные товары');
            }

            $existingReview = $db->query("
                SELECT id FROM reviews 
                WHERE customer_id = {$userId} 
                AND product_id = {$db->escape($data['product_id'])}
                LIMIT 1
            ");

            if (!empty($existingReview) && empty($data['review_id'])) {
                throw new RuntimeException('Вы уже оставили отзыв на этот товар');
            }

            $reviewData = [
                'customer_id' => $userId,
                'product_id' => $db->escape($data['product_id']),
                'rating'     => (int)$db->escape($data['rating']),
                'comment'    => $db->escape(trim($data['comment'])),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if (!empty($data['review_id'])) {
                $reviewId = (int)$db->escape($data['review_id']);
                $result = $db->query("
                    UPDATE reviews SET 
                    rating = {$reviewData['rating']},
                    comment = '{$reviewData['comment']}',
                    updated_at = '{$reviewData['updated_at']}'
                    WHERE id = {$reviewId} AND customer_id = {$userId}
                ");
            } else {
                $reviewData['created_at'] = $reviewData['updated_at'];
                $result = $db->query("
                    INSERT INTO reviews (customer_id, product_id, rating, comment, created_at, updated_at)
                    VALUES (
                        {$reviewData['customer_id']},
                        {$reviewData['product_id']},
                        {$reviewData['rating']},
                        '{$reviewData['comment']}',
                        '{$reviewData['created_at']}',
                        '{$reviewData['updated_at']}'
                    )
                ");
            }

            if ($result === false) {
                throw new RuntimeException('Ошибка при сохранении отзыва');
            }

            return $this->initJsonResponse(['success' => true]);
        } catch (RuntimeException $e) {
            return $this->initJsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function apiDelete(): ControllerResponseInterface
    {
        if (!$this->checkMethod('POST')) {
            return $this->initJsonResponse(['error' => 'Invalid request method'], 405);
        }

        $data = $_POST;
        $db = $this->dbManager->getConnection();
        $userId = $this->auth->getUserId();

        try {
            if (empty($data['review_id'])) {
                throw new RuntimeException('Не указан отзыв для удаления');
            }

            $reviewId = (int)$db->escape($data['review_id']);
            
            $review = $db->query("
                SELECT id FROM reviews 
                WHERE id = {$reviewId} AND customer_id = {$userId}
                LIMIT 1
            ");

            if (empty($review)) {
                throw new RuntimeException('Отзыв не найден или у вас нет прав на его удаление');
            }

            $result = $db->query("DELETE FROM reviews WHERE id = {$reviewId}");

            if ($result === false) {
                throw new RuntimeException('Ошибка при удалении отзыва');
            }

            return $this->initJsonResponse(['success' => true]);
        } catch (RuntimeException $e) {
            return $this->initJsonResponse(['error' => $e->getMessage()], 400);
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