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

class VendorApiController extends ControllerAbstract
{
    public function __construct(
        private AuthService $auth,
        private DBConnectionManager $dbManager
    )
    {}

    public function apiChangePassword(): ControllerResponseInterface
    {
        if (!$this->checkMethod('POST')) {
            return $this->initJsonResponse();
        }

        $this->checkAuth();

        $this->auth->setUserTable('vendors');

        if ($this->auth->changePassword($_POST)) {
            $this->redirect('/vendor/profile/edit');
        }

        return $this->initJsonResponse();
    }

    public function apiUpdate(): ControllerResponseInterface
    {
        if (!$this->checkMethod('POST')) {
            return $this->initJsonResponse();
        }
    
        $this->checkAuth();
    
        $userId = $this->auth->getUserId();
        $db = $this->dbManager->getConnection();
    
        // Prepare update data
        $updateData = [
            'f' => $_POST['f'] ?? '',
            'i' => $_POST['i'] ?? '',
            'o' => $_POST['o'] ?? '',
            'updated_at' => date('Y-m-d H:i:s')
        ];
    
        // Basic validation
        if (empty($updateData['f']) || empty($updateData['i'])) {
            return $this->initJsonResponse();
        }
    
        // Handle image upload
        if (!empty($_FILES['avatar']['name'])) {
            $uploadDir = 'uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
    
            $fileName = 'avatar_' . $userId . '_' . time() . '.' . pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $uploadPath = $uploadDir . $fileName;
    
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
                $updateData['avatar'] = $uploadPath;
            }
        }
    
        // Build update query
        $updateParts = [];
        foreach ($updateData as $key => $value) {
            $updateParts[] = "{$key} = '{$db->escape($value)}'";
        }

        $updateSql = "UPDATE vendors SET " . implode(', ', $updateParts) . " WHERE id = {$userId}";
        $result = $db->query($updateSql);
    
        if ($result === false) {
            return $this->initJsonResponse();
        }
    
        // Update session
        $userSession = $this->auth->getUser();
        if ($userSession) {
            $userName = $updateData['i'] . ' ' . $updateData['f'];
            $this->auth->setUserName($userName);

            if (isset($updateData['avatar']) && !empty($updateData['avatar'])) {
                $this->auth->setUserAvatar($updateData['avatar']);
            }
        }

        $this->redirect('/vendor/profile');
    
        return $this->initJsonResponse();
    }

    private function checkAuth()
    {
        $this->auth->setUserTable('vendors');

        if (!$this->auth->isAuthenticated()) {
            $this->redirect('/vendor/login');
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