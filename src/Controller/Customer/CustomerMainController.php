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

class CustomerMainController extends ControllerRendering
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
            'title' => 'Главная панель',
            'company_name' => 'ФлексМаркет'
        ];

        // Данные пользователя
        $userSessionData = $this->auth->getUser();
        $data['user_session'] = [
            'role' => $userSessionData['role'],
            'name' => $userSessionData['name'],
            'email' => $userSessionData['email'],
            'avatar' => $userSessionData['avatar']
        ];

        // Получаем статистику для дашборда
        $db = $this->dbManager->getConnection();

        // 1. Категории товаров
        $data['categories'] = $db->query("
            SELECT *
            FROM categories AS c
            ORDER BY c.name ASC
        ") ?? [];

        // 2. Новинки
        $data['featured_products'] = $db->query("
            SELECT *
            FROM products AS p
            WHERE newness_score > 0.7 
            ORDER BY newness_score DESC
            LIMIT 20
        ") ?? [];

        // 3 Популярное
        $data['recommended_products'] = $db->query("
            SELECT r.product_id, p.*
            FROM recommendations AS r
            LEFT JOIN products AS p
            ON r.product_id = p.id
        ") ?? [];

        // 4. Рекомендуемое
        $data['personal_recommended_products'] = $db->query("
            SELECT pr.product_id, p.*
            FROM personal_recommendations AS pr
            LEFT JOIN products AS p
            ON pr.product_id = p.id
            WHERE pr.customer_id = {$userSessionData['id']}
        ") ?? [];

        return $this->render('customers/main.html.php', $data);
    }

    private function getCount($db, $table): int
    {
        $result = $db->query("SELECT COUNT(*) as count FROM {$table}");
        return $result[0]['count'] ?? 0;
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
