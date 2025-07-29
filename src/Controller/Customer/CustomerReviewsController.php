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

class CustomerReviewsController extends ControllerRendering
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
        
        // Получаем список отзывов
        $userId = $this->auth->getUserId();
        $reviews = $db->query("
            SELECT r.*, p.id AS product_id, p.name AS product_name, p.*, c.f, c.i
            FROM reviews r
            LEFT JOIN products AS p ON r.product_id = p.id
            LEFT JOIN customers AS c ON r.customer_id = c.id
            WHERE r.customer_id = {$userId}
            ORDER BY r.created_at DESC
        ");
        
        $data['reviews'] = $reviews === false ? [] : $reviews;

        return $this->render('customers/reviews.html.php', $data);
    }

    public function review(int $id): ControllerResponseInterface
    {
        $this->checkAuth();

        $data = [
            'title' => 'Отзывы',
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
        
        // Получаем информацию о заказе
        $userId = $this->auth->getUserId();
        $review = $db->query("
            SELECT r.*, p.id AS product_id
            FROM reviews r
            LEFT JOIN products AS p
            ON r.product_id = p.id
            WHERE r.id = {$id} AND r.customer_id = {$userId}
            LIMIT 1
        ");
        
        if (empty($review)) {
            $this->redirect('/error_404');
        }
        $data['review'] = $review[0];

        return $this->render('customers/review.html.php', $data);
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
