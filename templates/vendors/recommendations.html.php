<?php $this->extend('vendors/base.html.php') ?>

<?php $this->startSection('content') ?>
            <div class="container-fluid py-4">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Рейтинг рекомендаций</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/vendor/main">Главная</a></li>
                                <li class="breadcrumb-item active">Рекомендации</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <div class="tab-pane fade show active" id="global-tab-pane" role="tabpanel" 
                         aria-labelledby="global-tab" tabindex="0">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Глобальные рекомендации</h5>
                                <div class="input-group" style="width: 300px;">
                                    <input type="text" class="form-control global-search" placeholder="Поиск...">
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
                                                <th>Товар</th>
                                                <th>Рейтинг</th>
                                                <th>Дата создания</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['global_recommendations'] as $rec): ?>
                                            <tr>
                                                <td><?= $this->escape($rec['id']) ?></td>
                                                <td><?= $this->escape($rec['product_name']) ?></td>
                                                <td><?= number_format($rec['recommendation_score'], 5) ?></td>
                                                <td><?= date('d.m.Y', strtotime($rec['created_at'])) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<link rel="stylesheet" href="/assets/css/pages/recommendations.css">
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {    
    // Поиск в таблицах
    $('.global-search').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#global-tab-pane table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
<?php $this->endSection() ?>