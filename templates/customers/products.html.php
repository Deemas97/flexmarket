<?php $this->extend('customers/base.html.php') ?>

<?php $this->startSection('content') ?>
    <div class="container-fluid py-4">                
        <!-- Добавить после Categories section и перед Personal Recommendations -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Фильтры товаров</h5>
                        <form id="products-filter" method="get" action="/customer/products/filter">
                            <div class="row">
                                <!-- Категория -->
                                <div class="col-md-3 mb-3">
                                    <label for="category" class="form-label">Категория</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="">Все категории</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>" <?= isset($filter['category']) && $filter['category'] == $category['id'] ? 'selected' : '' ?>>
                                                <?= $this->escape($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                        
                                <!-- Компании -->
                                <div class="col-md-3 mb-3">
                                    <label for="companies" class="form-label">Компании</label>
                                    <select class="form-select" id="companies" name="companies[]" multiple>
                                        <?php foreach ($companies as $company): ?>
                                            <option value="<?= $company['id'] ?>" <?= isset($filter['companies']) && in_array($company['id'], $filter['companies']) ? 'selected' : '' ?>>
                                                <?= $this->escape($company['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                        
                                <!-- Цена -->
                                <div class="col-md-2 mb-3">
                                    <label for="price_min" class="form-label">Цена от</label>
                                    <input type="number" class="form-control" id="price_min" name="price_min" 
                                           placeholder="мин" value="<?= $filter['price_min'] ?? '' ?>">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="price_max" class="form-label">Цена до</label>
                                    <input type="number" class="form-control" id="price_max" name="price_max" 
                                           placeholder="макс" value="<?= $filter['price_max'] ?? '' ?>">
                                </div>
                                        
                                <!-- Наличие и изображение -->
                                <div class="col-md-2 mb-3">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="in_stock" name="in_stock" 
                                               value="1" <?= isset($filter['in_stock']) && $filter['in_stock'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="in_stock">В наличии</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="has_image" name="has_image" 
                                               value="1" <?= isset($filter['has_image']) && $filter['has_image'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="has_image">С изображением</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary me-2">Применить</button>
                                    <a href="/customer/products" class="btn btn-outline-secondary">Сбросить</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Recommendations -->
         <?php if (!empty($personal_recommended_products)): ?>
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="section-title mb-4">Товары</h2>
                <div class="row">
                    <?php foreach ($personal_recommended_products as $product): ?>
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
        <?php endif; ?>

        <!-- Featured Products -->
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

        <!-- New Arrivals -->
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
<link rel="stylesheet" href="/assets/css/pages/store.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
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

<?php $this->startSection('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#companies').select2({
        placeholder: "Выберите компании",
        allowClear: true,
        width: '100%'
    });

    // Валидация цены
    $('#price_min, #price_max').on('change', function() {
        const min = parseFloat($('#price_min').val());
        const max = parseFloat($('#price_max').val());
        
        if (min && max && min > max) {
            alert('Минимальная цена не может быть больше максимальной');
            $(this).val('');
        }
    });
});
</script>
<?php $this->endSection() ?>