<?php $this->extend('customers/base.html.php') ?>

<?php $this->startSection('content') ?>
<div class="container-fluid py-4">
    <!-- Categories -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="section-title mb-4">Категории товаров</h2>
            <div class="row">
                <?php foreach ($categories as $category): ?>
                <div class="col-md-3 mb-4">
                    <div class="category-card card h-100">
                    <div class="category-image" style="background-image: url('<?= $category['image'] ? 'http://crm.flexmarket.local/uploads/images/categories/' . $category['image'] : '/assets/img/image_placeholder.jpg'; ?>');"></div>
                        <div class="card-body">
                            <h5 class="card-title"><?= $this->escape($category['name']) ?></h5>
                            <p class="card-text"><?= $this->escape($category['description']) ?></p>
                            <a href="/customer/category/<?= $category['id'] ?>" class="btn btn-outline-primary">Смотреть товары</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<style>
.section-title {
    font-size: 2rem;
    font-weight: bold;
    position: relative;
    padding-bottom: 10px;
}

.section-title:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 3px;
    background-color: #4e73df;
}

.category-card {
    transition: transform 0.3s;
    border: none;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
}

.category-card:hover {
    transform: translateY(-5px);
}

.category-image {
    height: 150px;
    background-size: cover;
    background-position: center;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}
</style>
<?php $this->endSection() ?>