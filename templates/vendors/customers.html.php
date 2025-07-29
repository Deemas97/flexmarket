<?php $this->extend('vendors/base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Customers Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Список покупателей</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/vendor/main">Главная</a></li>
                                <li class="breadcrumb-item active">Покупатели</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-plus me-2"></i>Добавить покупателя
                        </button>
                    </div>
                </div>
                
                <!-- Users Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Список покупателей</h5>
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control search" placeholder="Поиск пользователей...">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>ФИО</th>
                                        <th>Роль</th>
                                        <th>Email</th>
                                        <th>Статус</th>
                                        <th>Дата регистрации</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['customers'] as $customer): ?>
                                    <tr>
                                        <td><?= $this->escape($customer['id']) ?></td>
                                        <td>
                                            <?= $this->escape($customer['f']) ?> 
                                            <?= $this->escape($customer['i']) ?>
                                            <?= isset($customer['o']) ? $this->escape($customer['o']) : '' ?>
                                        </td>
                                        <td>
                                            <?php if ($customer['role_id'] === 'simple'): ?>
                                                <span class="badge bg-danger">Базовый</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">Подписчик</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $this->escape($customer['email']) ?></td>
                                        <td>
                                            <?php if ($customer['status'] === 'active'): ?>
                                                <span class="badge bg-success">Активен</span>
                                            <?php elseif ($customer['status'] === 'banned'): ?>
                                                <span class="badge bg-dark">Заблокирован</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">На модерации</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d.m.Y', strtotime($customer['created_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<link rel="stylesheet" href="/assets/css/pages/customers.css">
<?php $this->endSection() ?>