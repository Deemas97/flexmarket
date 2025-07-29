<?php $this->extend('vendors/base.html.php') ?>

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
                                <a href="/vendor/products" class="btn btn-outline-primary">Смотреть товары</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- New Arrivals -->
        <div class="row mb-5" id="featured-products">
            <div class="col-12">
                <h2 class="section-title mb-4">Новинки</h2>
                <div class="row">
                    <?php foreach ($featured_products as $product): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="product-card card h-100">
                            <?php if ($product['newness_score'] > 0.7): ?>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">Новинка</span>
                            <?php endif; ?>
                            <img src="<?= $product['image_main'] ? 'http://crm.flexmarket.local/uploads/images/products/main/' . $product['image_main'] : '/assets/img/image_placeholder.jpg'; ?>" class="card-img-top" alt="<?= $this->escape($product['name']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= $this->escape($product['name']) ?></h5>
                                <p class="card-text text-muted"><?= mb_substr($product['description'], 0, 100) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price"><?= number_format($product['price'], 2) ?> ₽</span>
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                        <span class="badge bg-success">В наличии</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Нет в наличии</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="/vendor/product_global/<?= $product['id'] ?>" class="btn btn-primary w-100">Подробнее</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Featured Products -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="section-title mb-4">Популярные товары</h2>
                <div class="row">
                    <?php foreach ($recommended_products as $product): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="product-card card h-100">
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">Новинка</span>
                            <img src="<?= $product['image_main'] ? 'http://crm.flexmarket.local/uploads/images/products/main/' . $product['image_main'] : '/assets/img/image_placeholder.jpg'; ?>" class="card-img-top" alt="<?= $this->escape($product['name']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= $this->escape($product['name']) ?></h5>
                                <p class="card-text text-muted"><?= mb_substr($product['description'], 0, 100) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price"><?= number_format($product['price'], 2) ?> ₽</span>
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                        <span class="badge bg-success">В наличии</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Нет в наличии</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="/vendor/product_global/<?= $product['id'] ?>" class="btn btn-primary w-100">Подробнее</a>
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
.hero-section {
    background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/assets/img/hero-bg.jpg');
    background-size: cover;
    background-position: center;
    color: white;
    padding: 100px 0;
    border-radius: 8px;
    text-align: center;
}

.hero-title {
    font-size: 3rem;
    font-weight: bold;
    margin-bottom: 20px;
}

.hero-subtitle {
    font-size: 1.5rem;
    margin-bottom: 30px;
}

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

.product-card {
    transition: transform 0.3s;
    border: none;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
}

.product-card:hover {
    transform: translateY(-5px);
}

.product-card .card-img-top {
    height: 200px;
    object-fit: cover;
}

.price {
    font-size: 1.2rem;
    font-weight: bold;
    color: #4e73df;
}
</style>
<?php $this->endSection() ?>