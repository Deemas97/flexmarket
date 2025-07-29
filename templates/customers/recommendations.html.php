<?php $this->extend('customers/base.html.php') ?>

<?php $this->startSection('content') ?>
    <div class="container-fluid py-4">
        <!-- Personal Recommendations -->
        <?php if (!empty($personal_recommended_products)): ?>
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="section-title mb-4">Рекомендации</h2>
                <div class="row">
                    <?php foreach ($personal_recommended_products as $product): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="product-card card h-100">
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">Новинка</span>
                            <img src="<?= isset($product['image_main']) ? 'http://crm.flexmarket.local/uploads/images/products/main/' . $product['image_main'] : '/assets/img/image_placeholder.jpg'; ?>" class="card-img-top" alt="<?= $this->escape($product['name']) ?>">
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
                                <a href="/customer/product/<?= $product['id'] ?>" class="btn btn-primary w-100">Подробнее</a>
                                <div class="product-actions mt-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <div class="input-group quantity-selector">
                                                <button class="btn btn-outline-secondary decrease-qty" type="button">-</button>
                                                <input type="text" class="form-control text-center qty-input" value="1" min="1" 
                                                       max="<?= $product['stock_quantity'] ?>">
                                                <button class="btn btn-outline-secondary increase-qty" type="button">+</button>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <button class="btn btn-primary btn-lg w-100 add-to-basket" 
                                                    data-product-id="<?= $product['id'] ?>">
                                                <i class="fas fa-shopping-cart me-2"></i>Добавить в корзину
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Popular products -->
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
                                <a href="/customer/product/<?= $product['id'] ?>" class="btn btn-primary w-100">Подробнее</a>
                                <div class="product-actions mt-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <div class="input-group quantity-selector">
                                                <button class="btn btn-outline-secondary decrease-qty" type="button">-</button>
                                                <input type="text" class="form-control text-center qty-input" value="1" min="1" 
                                                       max="<?= $product['stock_quantity'] ?>">
                                                <button class="btn btn-outline-secondary increase-qty" type="button">+</button>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <button class="btn btn-primary btn-lg w-100 add-to-basket" 
                                                    data-product-id="<?= $product['id'] ?>">
                                                <i class="fas fa-shopping-cart me-2"></i>Добавить в корзину
                                            </button>
                                        </div>
                                    </div>
                                </div>
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