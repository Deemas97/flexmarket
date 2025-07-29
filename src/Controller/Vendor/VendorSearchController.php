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

class VendorSearchController extends ControllerRendering
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

        $query = trim($_GET['q'] ?? '');
        $results = [];

        if (!empty($query)) {
            $results = $this->performSearch($query);
        }

        $data = [
            'title' => 'Результаты поиска',
            'query' => $query,
            'results' => $results,
            'user_session' => $this->auth->getUser()
        ];

        return $this->render('vendors/search.html.php', $data);
    }

    private function performSearch(string $query): array
    {
        $db = $this->dbManager->getConnection();
        $results = [];

        // Поиск товаров
        $products = $db->query("
            SELECT id, name, description, price, stock_quantity, image_main
            FROM products 
            WHERE name LIKE '%{$query}%' OR description LIKE '%{$query}%'
            LIMIT 5
        ");

        foreach ($products as $product) {
            $results[] = [
                'type' => 'Товар',
                'title' => $product['name'],
                'description' => $this->shortenDescription($product['description']),
                'url' => "/vendor/product/{$product['id']}/edit",
                'image_main' => "/vendor/product/{$product['image_main']}",
                'meta' => [
                    'Цена' => number_format($product['price'], 2) . ' ₽',
                    'На складе' => $product['stock_quantity'] . ' шт.'
                ]
            ];
        }

        // Поиск категорий
        $categories = $db->query("
            SELECT c.id, c.name, c.description, c.image
            FROM categories c
            WHERE c.name LIKE '%{$query}%' OR c.description LIKE '%{$query}%'
            LIMIT 5
        ");

        foreach ($categories as $category) {
            $results[] = [
                'type' => 'Поставщик',
                'title' => "{$category['name']}",
                'description' => $this->shortenDescription($category['description']),
                'url' => "/vendor/category/{$category['id']}",
                'image' => "/vendor/category/{$category['image']}"
            ];
        }

        $companies = $db->query("
            SELECT c.id, c.name, c.description, c.image
            FROM companies c
            WHERE c.name LIKE '%{$query}%' OR c.description LIKE '%{$query}%'
            LIMIT 5
        ");

        foreach ($companies as $company) {
            $results[] = [
                'type' => 'Поставщик',
                'title' => "{$company['name']}",
                'description' => $this->shortenDescription($company['description']),
                'url' => "/vendor/company/{$company['id']}",
                'image' => "/vendor/company/{$company['image']}"
            ];
        }

        return $results;
    }

    private function shortenDescription(string $text, int $length = 100): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length) . '...';
    }

    private function checkAuth(): void
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