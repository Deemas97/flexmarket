<?php $this->extend('customers/base.html.php') ?>

<?php $this->startSection('content') ?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Заказ #<?= $data['order']['id'] ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/customer/main">Главная</a></li>
                    <li class="breadcrumb-item"><a href="/customer/orders">Заказы</a></li>
                    <li class="breadcrumb-item active">Обзор</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- Order Info -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Информация о заказе</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Статус</label>
                            <select class="form-select" name="status" disabled>
                                <?php foreach ($data['statuses'] as $value => $label): ?>
                                    <option value="<?= $value ?>" 
                                        <?= $value == $data['order']['status'] ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Сумма заказа</label>
                        <input type="text" class="form-control" 
                               value="<?= number_format($data['order']['sum'], 2, '.', ' ') ?> ₽" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Дата создания</label>
                        <input type="text" class="form-control" 
                               value="<?= date('d.m.Y H:i', strtotime($data['order']['created_at'])) ?>" readonly>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="/customer/orders" class="btn btn-secondary me-2">Назад</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Products List -->
    <div class="row mt-4">
        <div class="col">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Товары в заказе</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Товар</th>
                                    <th>Цена</th>
                                    <th>Количество</th>
                                    <th>Сумма</th>
                                    <th>Статус</th>
                                    <th>Отзыв</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['products'] as $product): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div><a href="/customer/product/<?= $this->escape($product['product_id']) ?>"><?= $this->escape($product['product_name']) ?></a></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= number_format($product['price'], 2, '.', ' ') ?> ₽</td>
                                    <td><?= $product['count'] ?></td>
                                    <td><?= number_format($product['price'] * $product['count'], 2, '.', ' ') ?> ₽</td>
                                    <td>
                                        <span class="badge 
                                            <?= $product['status'] === 'pending'    ? 'bg-warning' : '' ?>
                                            <?= $product['status'] === 'processing' ? 'bg-info' : '' ?>
                                            <?= $product['status'] === 'shipped'    ? 'bg-primary' : '' ?>
                                            <?= $product['status'] === 'delivered'  ? 'bg-success' : '' ?>
                                            <?= $product['status'] === 'cancelled'  ? 'bg-danger' : '' ?>
                                        ">
                                            <?= $data['position_statuses'][$product['status']] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($product['status'] === 'delivered'): ?>
                                            <?php if (isset($product['review']) && $product['review']): ?>
                                                <!-- Показать существующий отзыв -->
                                                <div class="review-container" data-product-id="<?= $product['product_id'] ?>">
                                                    <div class="rating mb-1">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star<?= $i > $product['review']['rating'] ? '-empty' : '' ?> text-warning"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <div class="comment mb-2"><?= $this->escape($product['review']['comment']) ?></div>
                                                    <div class="review-actions">
                                                        <button class="btn btn-sm btn-outline-primary edit-review" 
                                                                data-review-id="<?= $product['review']['id'] ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger delete-review" 
                                                                data-review-id="<?= $product['review']['id'] ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <!-- Форма для нового отзыва -->
                                                <button class="btn btn-sm btn-outline-success add-review" 
                                                        data-product-id="<?= $product['product_id'] ?>">
                                                    <i class="fas fa-plus me-1"></i>Оставить отзыв
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="table-active">
                                    <td colspan="5" class="text-end"><strong>Итого:</strong></td>
                                    <td colspan="2"><strong><?= number_format($data['order']['sum'], 2, '.', ' ') ?> ₽</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                    <input type="hidden" id="reviewId" name="review_id" value="">
                    <input type="hidden" id="productId" name="product_id" value="">
                    <input type="hidden" id="orderId" name="order_id" value="<?= $data['order']['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Оценка</label>
                        <div class="rating-input">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="far fa-star rating-star" data-value="<?= $i ?>"></i>
                            <?php endfor; ?>
                            <input type="hidden" id="rating" name="rating" value="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comment" class="form-label">Комментарий</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="submitReview">Сохранить</button>
            </div>
        </div>
    </div>
</div>

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
            const currentRating = $('#rating').val();
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
        $('#rating').val(value);
    });

    // Обработка кнопки "Оставить отзыв"
    $('.add-review').click(function() {
        const productId = $(this).data('product-id');
        $('#productId').val(productId);
        $('#reviewId').val('');
        $('#rating').val(0);
        $('#comment').val('');
        $('.rating-star').removeClass('fas').addClass('far');
        $('#reviewModal .modal-title').text('Оставить отзыв');
        $('#reviewModal').modal('show');
    });

    // Обработка кнопки "Редактировать отзыв"
    $('.edit-review').click(function() {
        const reviewId = $(this).data('review-id');
        const reviewContainer = $(this).closest('.review-container');
        const productId = reviewContainer.data('product-id');
        const rating = reviewContainer.find('.fa-star').length;
        const comment = reviewContainer.find('.comment').text().trim();
        
        $('#reviewId').val(reviewId);
        $('#productId').val(productId);
        $('#rating').val(rating);
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
});
</script>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<style>
.rating-input {
    font-size: 1.5rem;
    cursor: pointer;
}

.rating-input .rating-star {
    color: #ffc107;
}

.review-container {
    max-width: 300px;
}

.comment {
    font-size: 0.9rem;
    white-space: pre-wrap;
    word-break: break-word;
}

.review-actions {
    display: flex;
    gap: 5px;
}
</style>
<?php $this->endSection() ?>