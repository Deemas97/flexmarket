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

class VendorReviewsController extends ControllerRendering
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
            'title' => 'Управление отзывами',
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
        $companyId = $db->query("SELECT * FROM vendors AS v WHERE v.id = {$userId}")[0]['company_id'];
        
        $reviews = $db->query("
            SELECT r.*,
                   c.i as customer_i, c.f AS customer_f,
                   p.name AS product_name
            FROM reviews r
            LEFT JOIN customers c ON r.customer_id = c.id
            LEFT JOIN products p ON r.product_id = p.id
            WHERE p.company_id = {$companyId}
            ORDER BY r.created_at DESC
        ");
        
        $data['reviews'] = $reviews === false ? [] : $reviews;

        return $this->render('vendors/reviews.html.php', $data);
    }

    protected function checkAuth()
    {
        $this->auth->setUserTable('vendors');

        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/vendor/login');
        }
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}