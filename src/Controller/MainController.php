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

class MainController extends ControllerRendering
{
    public function __construct(
        Renderer $renderer,
        private DBConnectionManager $dbManager
    ) {
        parent::__construct($renderer);
    }

    public function index(): ControllerResponseInterface
    {
        $data = [
            'title' => 'ФлексМаркет',
            'company_name' => 'ФлексМаркет'
        ];

        // Получаем товары
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

        return $this->render('common/main.html.php', $data);
    }

    private function getCount($db, $table): int
    {
        $result = $db->query("SELECT COUNT(*) as count FROM {$table}");
        return $result[0]['count'] ?? 0;
    }
}
