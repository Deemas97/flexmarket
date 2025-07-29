<?php $this->extend('vendors/base.html.php') ?>

<?php $this->startSection('content') ?>
<div class="container-fluid py-4">
    <!-- Хлебные крошки -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vendor/main">Главная</a></li>
            <li class="breadcrumb-item active"><?= $this->escape($product['name']) ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Основная информация о товаре -->
        <div class="col-lg-5 mb-4 mb-lg-0">
            <!-- Главное изображение -->
            <div class="card mb-3">
                <img src="<?= $product['image_main'] ? 'http://crm.flexmarket.local/uploads/images/products/main/' . $product['image_main'] : '/assets/img/image_placeholder.jpg'; ?>" 
                     class="card-img-top" 
                     alt="<?= $this->escape($product['name']) ?>"
                     id="mainProductImage"
                     style="max-height: 500px; object-fit: contain;">
            </div>
        </div>

        <!-- Информация о товаре -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h1 class="h2 mb-3"><?= $this->escape($product['name']) ?></h1>

                    <!-- Цена и наличие -->
                    <div class="mb-4">
                        <h3 class="text-primary mb-2"><?= number_format($product['price'], 2, '.', ' ') ?> ₽</h3>
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <span class="badge bg-success">В наличии</span>
                            <small class="text-muted">(<?= $product['stock_quantity'] ?> шт.)</small>
                        <?php else: ?>
                            <span class="badge bg-secondary">Нет в наличии</span>
                        <?php endif; ?>
                    </div>

                    <!-- Галерея изображений товара -->
                    <div class="mb-3">
                        <?php if (!empty($data['product']['gallery_path']) && !empty($data['images'])): ?>
                            <div class="mb-3">  
                                <!-- Слайдер галереи -->
                                <div id="gallerySlider" class="carousel slide mb-3" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        <?php $i = 0; ?>
                                        <?php foreach ($data['images'] as $filename): ?>
                                                <div class="carousel-item">
                                                    <img src="http://crm.flexmarket.local/uploads/images/products/gallery/<?= $this->escape($data['product']['gallery_path']) ?>/<?= $this->escape($filename) ?>" 
                                                         class="d-block w-100 img-thumbnail" 
                                                         style="max-height: 400px; object-fit: contain;">
                                                </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (count($data['images']) > 1): ?>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#gallerySlider" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#gallerySlider" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                                
                                <!-- Миниатюры для навигации -->
                                <div class="row g-2 mb-3" id="galleryThumbs">
                                    <?php foreach ($data['images'] as $index => $filename): ?>
                                            <div class="col-2 position-relative gallery-thumb" data-bs-target="#gallerySlider" data-bs-slide-to="<?= $index ?>">
                                                <img src="http://crm.flexmarket.local/uploads/images/products/gallery/<?= $this->escape($data['product']['gallery_path']) ?>/<?= $this->escape($filename) ?>" 
                                                     class="img-thumbnail w-100" style="height: 80px; object-fit: cover;">
                                            </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Подробное описание -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Подробное описание</h4>
                </div>
                <div class="card-body">
                    <?= $product['description'] ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Отзывы -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Отзывы</h4>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
                        <i class="fas fa-plus me-1"></i> Оставить отзыв
                    </button>
                </div>
                <div class="card-body">
                    <?php if (!empty($product['reviews'])): ?>
                        <?php foreach ($product['reviews'] as $review): ?>
                        <div class="review mb-4 pb-3 border-bottom">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <strong><?= $this->escape($review['customer_i']. ' ' . $review['customer_f']) ?></strong>
                                    <div class="rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?= $i > $review['rating'] ? '-empty' : '' ?> text-warning"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <small class="text-muted"><?= date('d.m.Y', strtotime($review['created_at'])) ?></small>
                            </div>
                            <p class="mb-2"><?= nl2br($this->escape($review['comment'])) ?></p>
                            
                            <?php if ($review['customer_id'] == $user_id): ?>
                            <div class="review-actions">
                                <button class="btn btn-sm btn-outline-primary edit-review" 
                                        data-review-id="<?= $review['id'] ?>">
                                    <i class="fas fa-edit me-1"></i>Редактировать
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-review" 
                                        data-review-id="<?= $review['id'] ?>">
                                    <i class="fas fa-trash me-1"></i>Удалить
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="far fa-comment-alt fa-3x text-muted mb-3"></i>
                            <h5>Пока нет отзывов</h5>
                            <p class="text-muted">Будьте первым, кто оставит отзыв об этом товаре</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Похожие товары -->
    <?php if (!empty($similarProducts)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <h3 class="mb-4">Похожие товары</h3>
            <div class="row">
                <?php foreach ($similarProducts as $product): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="product-card card h-100">
                        <?php if ($product['newness_score'] > 0.7): ?>
                        <span class="badge bg-danger position-absolute top-0 start-0 m-2">Новинка</span>
                        <?php endif; ?>
                        <img src="<?= $product['image_main'] ? 'http://crm.flexmarket.local/uploads/images/products/main/' . $product['image_main'] : '/assets/img/image_placeholder.jpg'; ?>" 
                             class="card-img-top" 
                             alt="<?= $this->escape($product['name']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= $this->escape($product['name']) ?></h5>
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
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<style>
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

.rating {
    color: #ffc107;
}

.rating-input {
    font-size: 1.5rem;
    cursor: pointer;
}

.rating-input .rating-star {
    color: #ffc107;
}

.gallery-thumb {
    transition: transform 0.2s;
}

.gallery-thumb:hover {
    transform: scale(1.05);
    cursor: pointer;
}

.cursor-pointer {
    cursor: pointer;
}

.review-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}
</style>
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    // Инициализация рейтинга звездами
    $('.rating-star').hover(
        function() {
            const value = $(this).data('value');
            $(this).prevAll('.rating-star').addBack().removeClass('far').addClass('fas');
            $(this).nextAll('.rating-star').removeClass('fas').addClass('far');
        },
        function() {
            const currentRating = $('input[name="rating"]').val();
            $('.rating-star').each(function() {
                if ($(this).data('value') <= currentRating) {
                    $(this).removeClass('far').addClass('fas');
                } else {
                    $(this).removeClass('fas').addClass('far');
                }
            });
        }
    );

    $('.rating-star').click(function() {
        const value = $(this).data('value');
        $('input[name="rating"]').val(value);
    });
});
</script>
<?php $this->endSection() ?>