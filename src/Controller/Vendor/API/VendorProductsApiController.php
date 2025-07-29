<?php
namespace App\Controller;

include_once '../src/Core/Controller/ControllerAbstract.php';
include_once '../src/Core/Controller/ControllerResponseInterface.php';
include_once '../src/Service/AuthService.php';
include_once '../src/Service/DBConnectionManager.php';
include_once '../src/Service/FileUploader.php';

use App\Core\Controller\ControllerAbstract;
use App\Core\Controller\ControllerResponseInterface;
use App\Service\AuthService;
use App\Service\DBConnectionManager;
use App\Service\FileUploader;

class VendorProductsApiController extends ControllerAbstract
{
    private string $storageApiUrl = 'http://crm.flexmarket.local/api/vendor/storage';

    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager,
        private FileUploader $fileUploader
    ) {
        $this->checkAdminAccess();
        $this->fileUploader->setAllowedMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
        $this->fileUploader->setMaxFileSize(10 * 1024 * 1024);
    }

    public function apiCreate(): ControllerResponseInterface
    {
        if (!$this->checkMethod('POST')) {
            return $this->initJsonResponse(['error' => 'Invalid request method'], 405);
        }
    
        $data = $_POST;
        $db = $this->dbManager->getConnection();
    
        // Валидация
        if (empty($data['name'])) {
            return $this->initJsonResponse(['error' => 'Название товара обязательно'], 400);
        }
        
        if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] <= 0) {
            return $this->initJsonResponse(['error' => 'Укажите корректную цену товара'], 400);
        }

        $imageName = null;
        $galleryDir = null;
        $uploadedFiles = [];

        // Обработка главного изображения
        if (!empty($_FILES['image_main']['name'])) {
            try {
                $imageName = $this->sendUploadToStorage($_FILES['image_main'], 'main');
            } catch (\RuntimeException $e) {
                return $this->initJsonResponse(['error' => $e->getMessage()], 400);
            }
        }

        // Загрузка новых изображений в галерею
        if (!empty($_FILES['gallery']['name'][0])) {
            try {
                $galleryDir = uniqid('gallery_', true);
                $uploadedFiles = $this->sendUploadMultipleToStorage($_FILES['gallery'], 'gallery/' . $galleryDir);
                
                if (empty($uploadedFiles)) {
                    $galleryDir = null;
                }
            } catch (\RuntimeException $e) {
                if ($imageName) {
                    $this->sendDeleteToStorage('main/' . $imageName);
                }
                return $this->initJsonResponse(['error' => $e->getMessage()], 400);
            }
        }
    
        // Создание товара
        $insertData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => (float)$data['price'],
            'stock_quantity' => (int)($data['stock_quantity'] ?? 0),
            'image_main' => $imageName,
            'gallery_path' => $galleryDir,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    
        // Экранирование данных
        $updateParts = [];
        foreach ($insertData as $key => $value) {
            $updateParts[$key] = $value === null ? 'NULL' : "'{$db->escape($value)}'";
        }
    
        $columns = implode(', ', array_keys($updateParts));
        $values = implode(', ', array_values($updateParts));
    
        $result = $db->query("INSERT INTO products ($columns) VALUES ($values)");
    
        if ($result === false) {
            if ($imageName) {
                $this->sendDeleteToStorage('main/' . $imageName);
            }
            if ($galleryDir) {
                $this->sendDeleteGalleryToStorage($galleryDir);
            }
            return $this->initJsonResponse(['error' => 'Ошибка при создании товара'], 500);
        }
        
        $productId = $db->getLastInsertId();
        
        if (!empty($data['categories']) && is_array($data['categories'])) {
            $this->updateProductCategories($productId, $data['categories']);
        }
    
        return $this->initJsonResponse(['success' => true, 'product_id' => $productId]);
    }

    public function apiUpdate(int $id): ControllerResponseInterface
    {
        if (!$this->checkMethod('POST')) {
            return $this->initJsonResponse(['error' => 'Invalid request method'], 405);
        }

        $data = $_POST;
        $db = $this->dbManager->getConnection();

        $product = $db->query("SELECT * FROM products WHERE id = {$id} LIMIT 1");
        if (empty($product)) {
            return $this->initJsonResponse(['error' => 'Товар не найден'], 404);
        }
        $product = $product[0];

        if (empty($data['name'])) {
            return $this->initJsonResponse(['error' => 'Название товара обязательно'], 400);
        }
        
        if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] <= 0) {
            return $this->initJsonResponse(['error' => 'Укажите корректную цену товара'], 400);
        }

        $imageName = $product['image_main'];
        $oldImageName = null;

        if (!empty($data['remove_image']) && $data['remove_image'] === 'on') {
            if ($imageName) {
                $this->sendDeleteToStorage('main/' . $imageName);
                $imageName = null;
            }
        } elseif (!empty($_FILES['image_main']['name'])) {
            try {
                $newImageName = $this->sendUploadToStorage($_FILES['image_main'], 'main');
                $oldImageName = $imageName;
                $imageName = $newImageName;
            } catch (\RuntimeException $e) {
                return $this->initJsonResponse(['error' => $e->getMessage()], 400);
            }
        }

        $galleryDir = $product['gallery_path'];

        if (!empty($data['remove_gallery']) && $data['remove_gallery'] === 'on' && $galleryDir) {
            $this->sendDeleteGalleryToStorage($galleryDir);
            $galleryDir = null;
        }

        if (!empty($_FILES['gallery']['name'][0])) {
            try {
                if (!$galleryDir) {
                    $galleryDir = uniqid('gallery_', true);
                }

                $uploadedFiles = $this->sendUploadMultipleToStorage($_FILES['gallery'], 'products/gallery/' . $galleryDir);
                
                if (empty($uploadedFiles)) {
                    return $this->initJsonResponse(['error' => 'Не удалось загрузить файлы в галерею'], 400);
                    throw new \RuntimeException('');
                }
            } catch (\RuntimeException $e) {
                return $this->initJsonResponse(['error' => $e->getMessage()], 400);
            }
        }

        $updateData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => (float)$data['price'],
            'image_main' => $imageName,
            'gallery_path' => $galleryDir,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $updateParts = [];
        foreach ($updateData as $key => $value) {
            $updateParts[] = "$key = " . ($value === null ? 'NULL' : "'{$db->escape($value)}'");
        }

        $result = $db->query("UPDATE products SET " . implode(', ', $updateParts) . " WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse(['error' => 'Ошибка при обновлении товара'], 500);
        }
        
        if ($oldImageName) {
            $this->sendDeleteToStorage('main/' . $oldImageName);
        }
        
        if (isset($data['categories']) && is_array($data['categories'])) {
            $this->updateProductCategories($id, $data['categories']);
        }

        $this->redirect('/vendor/products');
        return $this->initJsonResponse();
    }

    public function apiDelete(int $id): ControllerResponseInterface
    {
        if (!$this->checkMethod('DELETE')) {
            return $this->initJsonResponse(['error' => 'Invalid request method'], 405);
        }

        $db = $this->dbManager->getConnection();

        $product = $db->query("SELECT image_main, gallery_path FROM products WHERE id = {$id} LIMIT 1");
        if (!empty($product)) {
            $product = $product[0];
            
            if ($product['image_main']) {
                $this->sendDeleteToStorage('main/' . $product['image_main']);
            }
            
            if ($product['gallery_path']) {
                $this->sendDeleteGalleryToStorage($product['gallery_path']);
            }
        }

        $db->query("DELETE FROM products_categories WHERE product_id = {$id}");

        $result = $db->query("DELETE FROM products WHERE id = {$id}");

        if ($result === false) {
            return $this->initJsonResponse(['error' => 'Ошибка при удалении товара'], 500);
        }

        return $this->initJsonResponse(['success' => true]);
    }

    private function sendUploadToStorage(array $file, string $directory): string
    {
        $url = $this->storageApiUrl . '/upload_product_image';

        $cfile = new \CURLFile($file['tmp_name'], $file['type'], $file['name']);

        $postData = [
            'image_main' => $cfile,
            'directory' => $directory
        ];

        $db = $this->dbManager->getConnection();
        $apiToken = $db->query("
            SELECT api_token
            FROM vendors AS v
            WHERE v.id = {$db->escape($this->auth->getUserId())}
            LIMIT 1
        ")[0]['api_token'] ?? '';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiToken,
                'Accept: application/json'
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 201) {
            $error = json_decode($response, true)['error'] ?? $response ?? $error;
            throw new \RuntimeException('Ошибка загрузки файла: ' . $error);
        }

        $responseData = json_decode($response, true);
        return $responseData['image_main'] ?? '';
    }

    private function sendUploadMultipleToStorage(array $files, string $directory): array
    {
        $url = $this->storageApiUrl . '/upload_gallery';

        $db = $this->dbManager->getConnection();
        $apiToken = $db->query("
            SELECT api_token
            FROM vendors AS v
            WHERE v.id = {$db->escape($this->auth->getUserId())}
            LIMIT 1
        ")[0]['api_token'] ?? '';

        $postData = [
            'gallery_dir' => $directory,
        ];

        foreach ($files['name'] as $index => $name) {
            if ($files['error'][$index] !== UPLOAD_ERR_OK) continue;

            $postData["files[$index]"] = new \CURLFile(
                $files['tmp_name'][$index],
                $files['type'][$index],
                $files['name'][$index]
            );
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiToken,
                'Accept: application/json'
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 201) {
            $error = json_decode($response, true)['error'] ?? $response ?? $error;
            throw new \RuntimeException('Ошибка загрузки файлов: ' . $error);
        }

        $responseData = json_decode($response, true);
        return $responseData['uploaded_files'] ?? [];
    }

    private function sendDeleteToStorage(string $filePath): bool
    {
        $url = $this->storageApiUrl . '/delete_gallery_files';
        
        $postData = [
            'gallery_dir' => dirname($filePath),
            'filenames' => [basename($filePath)]
        ];

        $db = $this->dbManager->getConnection();
        $apiToken = $db->query("
            SELECT api_token
            FROM vendors AS v
            WHERE v.id = {$db->escape($this->auth->getUserId())}
            LIMIT 1
        ")[0]['api_token'] ?? '';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiToken,
                'Content-Type: application/x-www-form-urlencoded'
            ],
        ]);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    private function sendDeleteGalleryToStorage(string $galleryDir): bool
    {
        $url = $this->storageApiUrl . '/delete_gallery';
        
        $postData = [
            'gallery_dir' => $galleryDir
        ];

        $db = $this->dbManager->getConnection();
        $apiToken = $db->query("
            SELECT api_token
            FROM vendors AS v
            WHERE v.id = {$db->escape($this->auth->getUserId())}
            LIMIT 1
        ")[0]['api_token'] ?? '';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiToken,
                'Content-Type: application/json'
            ],
        ]);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    private function updateProductCategories(int $productId, array $categoryIds): void
    {
        $db = $this->dbManager->getConnection();
        
        $db->query("DELETE FROM products_categories WHERE product_id = {$productId}");
        
        foreach ($categoryIds as $categoryId) {
            $categoryId = (int)$categoryId;
            if ($categoryId > 0) {
                $db->query("INSERT INTO products_categories (product_id, category_id) VALUES ({$productId}, {$categoryId})");
            }
        }
    }

    private function checkAdminAccess(): void
    {
        $this->auth->setUserTable('vendors');

        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/vendor/login');
        }

        $user = $this->auth->getUser();
        if ($user['role'] !== 'admin') {
            $this->redirect('/vendor/main');
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