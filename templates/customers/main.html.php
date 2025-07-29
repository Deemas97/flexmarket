<?php $this->extend('customers/base.html.php') ?>

<?php $this->startSection('content') ?>
    <div class="container-fluid py-4">
        <!-- Hero Section -->
        <div class="row mb-5">
            <div class="col-12 hero-section">
                <div class="hero-content">
                    <h1 class="hero-title">Добро пожаловать в наш магазин</h1>
                    <p class="hero-subtitle">Лучшие товары по выгодным ценам</p>
                    <a href="#featured-products" class="btn btn-primary btn-lg">Смотреть товары</a>
                </div>
            </div>
        </div>
                
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
<style>
.hero-section {
    background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/assets/img/header.jpg');
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
.quantity-selector {
    max-width: 150px;
}

.quantity-selector .btn {
    width: 40px;
}

.quantity-selector .form-control {
    text-align: center;
}

.add-to-basket {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}

/* Стили для toast-уведомлений */
.toast {
    z-index: 1100;
}
</style>
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    // Обработчики изменения количества
    $('.decrease-qty').click(function() {
        const input = $(this).siblings('.qty-input');
        let value = parseInt(input.val());
        if (value > 1) {
            input.val(value - 1);
        }
    });

    $('.increase-qty').click(function() {
        const input = $(this).siblings('.qty-input');
        const max = parseInt(input.attr('max'));
        let value = parseInt(input.val());
        if (value < max) {
            input.val(value + 1);
        } else {
            alert('Максимальное количество товара в наличии: ' + max);
        }
    });

    // Добавление товара в корзину
    $('.add-to-basket').click(function() {
        const productId = $(this).data('product-id');
        const quantity  = $(this).closest('.product-actions').find('.qty-input').val();

        $.post('/api/customer/basket/add', {
            product_id: productId,
            count: quantity
        }, function(response) {
            if (response.success) {
                updateBasketCounter();
                showToast('Товар добавлен в корзину', 'success');
            } else {
                showToast(response.error || 'Ошибка при добавлении в корзину', 'danger');
            }
        }).fail(function() {
            showToast('Ошибка соединения с сервером', 'danger');
        });
    });

    function updateBasketCounter() {
        $.get('/api/customer/basket/count', function(response) {
            if (response.success) {
                $('.basket-counter').text(response.count).removeClass('d-none');
            }
        });
    }

    function showToast(message, type) {
        const toast = $(`
            <div class="toast align-items-center text-white bg-${type} border-0 position-fixed bottom-0 end-0 m-3" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        
        $('body').append(toast);
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();
        
        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
});
</script>
<?php $this->endSection() ?>