<?php $this->extend('customers/base.html.php') ?>

<?php $this->startSection('content') ?>
<div class="container-fluid py-4">
    <!-- Хлебные крошки -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/customer/main">Главная</a></li>
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

                    <!-- Кнопки добавления в корзину -->
                    <div class="product-actions mb-4">
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
                                        data-product-id="<?= $product['id'] ?>"
                                        <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                                    <i class="fas fa-shopping-cart me-2"></i>Добавить в корзину
                                </button>
                            </div>
                        </div>
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
</div>

<!-- Модальное окно для отзыва -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Оставить отзыв</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reviewForm">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Оценка</label>
                        <div class="rating-input">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="far fa-star rating-star" data-value="<?= $i ?>"></i>
                            <?php endfor; ?>
                            <input type="hidden" name="rating" value="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comment" class="form-label">Комментарий</label>
                        <textarea class="form-control" id="comment" name="comment" rows="5" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="submitReview">Отправить</button>
            </div>
        </div>
    </div>
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
        const quantity = $(this).closest('.product-actions').find('.qty-input').val();

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

    // Отправка отзыва
    $('#submitReview').click(function() {
        const formData = $('#reviewForm').serialize();
        
        $.post('/api/customer/reviews/save', formData, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.error || 'Ошибка при сохранении отзыва');
            }
        }).fail(function() {
            alert('Ошибка соединения с сервером');
        });
    });

    // Редактирование отзыва
    $('.edit-review').click(function() {
        const reviewId = $(this).data('review-id');
        const review = $(this).closest('.review');
        const rating = review.find('.fa-star').length;
        const comment = review.find('p').text().trim();
        
        $('input[name="rating"]').val(rating);
        $('#comment').val(comment);
        
        $('.rating-star').each(function() {
            if ($(this).data('value') <= rating) {
                $(this).removeClass('far').addClass('fas');
            } else {
                $(this).removeClass('fas').addClass('far');
            }
        });
        
        $('#reviewModal .modal-title').text('Редактировать отзыв');
        $('#reviewModal').modal('show');
    });

    // Удаление отзыва
    $('.delete-review').click(function() {
        if (confirm('Вы уверены, что хотите удалить этот отзыв?')) {
            const reviewId = $(this).data('review-id');
            
            $.post('/api/customer/reviews/delete', { review_id: reviewId }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.error || 'Ошибка при удалении отзыва');
                }
            }).fail(function() {
                alert('Ошибка соединения с сервером');
            });
        }
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