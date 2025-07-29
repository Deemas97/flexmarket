<?php $this->extend('vendors/base.html.php') ?>

<?php $this->startSection('content') ?>
<div class="container-fluid py-4">
    <!-- Categories -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="section-title mb-4">Компании</h2>
            <div class="row">
                <?php foreach ($companies as $company): ?>
                <div class="col-md-3 mb-4">
                    <div class="category-card card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= $this->escape($company['name']) ?></h5>
                            <p class="card-text"><?= $this->escape($company['description']) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection() ?>