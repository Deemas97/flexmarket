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

class VendorProductsController extends ControllerRendering
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
            'title' => 'Товары',
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
        
        $products = $db->query("
            SELECT p.*, cm.name AS company_name, GROUP_CONCAT(c.name SEPARATOR ', ') AS categories_names
            FROM products p
            LEFT JOIN companies cm ON p.company_id = cm.id
            LEFT JOIN products_categories pc ON p.id = pc.product_id
            LEFT JOIN categories c ON pc.category_id = c.id
            WHERE company_id = {$companyId}
            GROUP BY p.id
            ORDER BY p.name
        ");
        
        $data['products'] = $products === false ? [] : $products;

        return $this->render('vendors/products.html.php', $data);
    }

    public function createForm(): ControllerResponseInterface
    {
        $this->checkAuth();

        $data = [
            'title' => 'Создание товара',
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
        
        $categories = $db->query("SELECT id, name FROM categories ORDER BY name");
        $data['categories'] = $categories === false ? [] : $categories;
        $data['selectedCategories'] = [];

        return $this->render('vendors/product_create.html.php', $data);
    }

    public function product(int $id): ControllerResponseInterface
    {
        $this->checkAuth();

        $data = [
            'title' => 'Редактирование товара',
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
        
        $product = $db->query("SELECT * FROM products WHERE id = {$id} LIMIT 1");
        if (empty($product)) {
            $this->redirect('/not_found');
        }
        $data['product'] = $product[0];

        $categories = $db->query("SELECT id, name FROM categories ORDER BY name");
        $data['categories'] = $categories === false ? [] : $categories;

        $selectedCategories = $db->query("SELECT category_id FROM products_categories WHERE product_id = {$id}");
        $data['selectedCategories'] = $selectedCategories === false ? [] : array_column($selectedCategories, 'category_id');

        $url = 'http://crm.flexmarket.local/api/vendor/storage/get_images';

        $queryParams = [
            'directory' => 'products/gallery/' . $data['product']['gallery_path']
        ];

        $ch = curl_init();

        $requestUrl = $url . '?' . http_build_query($queryParams);

        $apiToken = $db->query("
            SELECT api_token
            FROM vendors AS v
            WHERE v.id = {$db->escape($this->auth->getUserId())}
            LIMIT 1
        ")[0]['api_token'] ?? '';

        curl_setopt_array($ch, [
            CURLOPT_URL => $requestUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiToken,
                'Accept: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30
        ]);

        $responseApi = curl_exec($ch);

        if (curl_errno($ch)) {
            $responseApi = '';
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $responseApiData = json_decode($responseApi, true);
        }

        if (isset($responseApiData['images'])) {
            $data['images'] = $responseApiData['images'];
        }        

        return $this->render('vendors/product_edit.html.php', $data);
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