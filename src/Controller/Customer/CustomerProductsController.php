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

class CustomerProductsController extends ControllerRendering
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

        return $this->render('customers/products.html.php', $data);
    }

    public function product(int $id): ControllerResponseInterface
    {
        $this->checkAuth();

        $data = [
            'title' => 'Просмотр товара',
            'company_name' => 'ФлексМаркет'
        ];

        $userSessionData = $this->auth->getUser();
        $data['user_session'] = [
            'role' => $userSessionData['role'],
            'name' => $userSessionData['name'],
            'email' => $userSessionData['email'],
            'avatar' => $userSessionData['avatar']
        ];

        $data['user_id'] = $this->auth->getUserId();

        $db = $this->dbManager->getConnection();
    
        $product = $db->query("SELECT * FROM products WHERE id = {$id} LIMIT 1");
        if (empty($product)) {
            $this->redirect('/not_found');
        }
        $data['product'] = $product[0];

        // EXTERNAL API VIA SERVER
        $url = 'http://crm.flexmarket.local/api/customer/storage/get_images';

        $queryParams = [
            'directory' => 'products/gallery/' . $data['product']['gallery_path']
        ];

        $ch = curl_init();

        $requestUrl = $url . '?' . http_build_query($queryParams);
        
        $apiToken = $db->query("
            SELECT api_token
            FROM customers AS c
            WHERE c.id = {$db->escape($this->auth->getUserId())}
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
        } else {
            echo "HTTP Error: $httpCode";
            echo "Response: $responseApi";
        }

        if (isset($responseApiData['images'])) {
            $data['images'] = $responseApiData['images'];
        }
        ////

        $db = $this->dbManager->initDefaultConnection();

        $reviews = $db->query("
            SELECT r.*, c.id AS customer_id, c.i AS customer_i, c.f AS customer_f
            FROM reviews r
            LEFT JOIN customers AS c
            ON r.customer_id = c.id
            WHERE r.product_id = {$id}
            ORDER BY r.created_at DESC
        ");
        $data['product']['reviews'] = $reviews;

        $similarProducts = $db->query("
            SELECT p.*
            FROM products p
            JOIN products_categories pc ON p.id = pc.product_id
            WHERE pc.category_id IN (
                SELECT category_id 
                FROM products_categories 
                WHERE product_id = {$id}
            )
            AND p.id != {$id}
            AND p.stock_quantity > 0
            GROUP BY p.id
            ORDER BY p.newness_score DESC, p.created_at DESC
            LIMIT 4
        ");
        $data['similarProducts'] = $similarProducts === false ? [] : $similarProducts;

        return $this->render('customers/product.html.php', $data);
    }

    public function filter(): ControllerResponseInterface
    {
        $this->checkAuth();

        $filter = [
            'category' => $_GET['category'] ?? null,
            'companies' => $_GET['companies'] ?? [],
            'price_min' => $_GET['price_min'] ?? null,
            'price_max' => $_GET['price_max'] ?? null,
            'in_stock' => isset($_GET['in_stock']) ? (bool)$_GET['in_stock'] : false,
            'has_image' => isset($_GET['has_image']) ? (bool)$_GET['has_image'] : false,
        ];

        $data = [
            'title' => 'Товары',
            'company_name' => 'ФлексМаркет',
            'filter' => $filter
        ];

        // Данные пользователя
        $userSessionData = $this->auth->getUser();
        $data['user_session'] = [
            'role' => $userSessionData['role'],
            'name' => $userSessionData['name'],
            'email' => $userSessionData['email'],
            'avatar' => $userSessionData['avatar']
        ];

        $db = $this->dbManager->getConnection();

        // Получаем компании для фильтра
        $data['companies'] = $db->query("SELECT id, name FROM companies ORDER BY name ASC") ?? [];

        // Получаем категории
        $data['categories'] = $db->query("SELECT * FROM categories ORDER BY name ASC") ?? [];

        // Строим запрос для фильтрации товаров
        $query = "SELECT p.* FROM products p";
        $where = [];

        // Фильтр по категории
        if (!empty($filter['category'])) {
            $categoryId = (int)$filter['category'];
            $query .= " JOIN products_categories pc ON p.id = pc.product_id";
            $where[] = "pc.category_id = $categoryId";
        }

        // Фильтр по компаниям
        if (!empty($filter['companies'])) {
            $companyIds = array_map('intval', $filter['companies']);
            $companyIdsStr = implode(',', $companyIds);
            $where[] = "p.company_id IN ($companyIdsStr)";
        }

        // Фильтр по цене
        if (!empty($filter['price_min'])) {
            $priceMin = (float)$filter['price_min'];
            $where[] = "p.price >= $priceMin";
        }
        if (!empty($filter['price_max'])) {
            $priceMax = (float)$filter['price_max'];
            $where[] = "p.price <= $priceMax";
        }

        // Фильтр по наличию
        if ($filter['in_stock']) {
            $where[] = "p.stock_quantity > 0";
        }

        // Фильтр по наличию изображения
        if ($filter['has_image']) {
            $where[] = "p.image_main IS NOT NULL AND p.image_main != ''";
        }

        // Собираем условия WHERE
        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }

        // Добавляем сортировку
        $query .= " ORDER BY p.newness_score DESC, p.created_at DESC LIMIT 100";

        // Выполняем запрос
        $filteredProducts = $db->query($query) ?? [];

        // Заменяем стандартные списки товаров на отфильтрованные
        $data['featured_products'] = $filteredProducts;
        $data['recommended_products'] = $filteredProducts;
        $data['personal_recommended_products'] = $filteredProducts;

        return $this->render('customers/products.html.php', $data);
    }

    protected function sendGetRequest(string $url, array $data, array $headers = []): string
    {
        $options = [
            'http' => [
                'header'  => array_merge([
                    'Content-type: application/x-www-form-urlencoded',
                ], $headers),
                'method'  => 'GET',
                'content' => http_build_query($data),
                'ignore_errors' => true
            ]
        ];
        
        $context = stream_context_create($options);
        return file_get_contents($url, false, $context);
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
