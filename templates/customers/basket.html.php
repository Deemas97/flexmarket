<?php $this->extend('customers/base.html.php') ?>

<?php $this->startSection('content') ?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Корзина товаров</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Корзина</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- Basket Content -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Товары в корзине</h5>
                    <span class="badge bg-primary"><?= count($data['products']) ?> товаров</span>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['products'])): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Товар</th>
                                        <th>Цена</th>
                                        <th>Количество</th>
                                        <th>Сумма</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['products'] as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($product['image_main']): ?>
                                                    <img src="http://crm.flexmarket.local/uploads/images/products/main/<?= $this->escape($product['image_main']) ?>" 
                                                         class="rounded me-3" width="60" height="60" alt="<?= $this->escape($product['name']) ?>">
                                                <?php else: ?>
                                                    <img src="/assets/img/product-placeholder.jpg" class="rounded me-3" width="60" height="60" alt="No image">
                                                <?php endif; ?>
                                                <div>
                                                    <div><a href="/customer/product/<?= $this->escape($product['product_id']) ?>"><?= $this->escape($product['name']) ?></a></div>
                                                    <small class="text-muted"><?= mb_substr($product['description'], 0, 50) ?>...</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= number_format($product['price'], 2, '.', ' ') ?> ₽</td>
                                        <td>
                                            <div class="input-group quantity-selector" style="max-width: 120px;">
                                                <button class="btn btn-outline-secondary decrease-quantity" 
                                                        type="button" 
                                                        data-product-id="<?= $product['product_id'] ?>">-</button>
                                                <input type="text" class="form-control text-center quantity-input" 
                                                       value="<?= $product['count'] ?>" 
                                                       data-product-id="<?= $product['product_id'] ?>">
                                                <button class="btn btn-outline-secondary increase-quantity" 
                                                        type="button" 
                                                        data-product-id="<?= $product['product_id'] ?>">+</button>
                                            </div>
                                        </td>
                                        <td><?= number_format($product['price'] * $product['count'], 2, '.', ' ') ?> ₽</td>
                                        <td>
                                            <button class="btn btn-outline-danger btn-sm remove-from-basket" 
                                                    data-product-id="<?= $product['product_id'] ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                            <h4>Ваша корзина пуста</h4>
                            <p class="text-muted">Добавьте товары, чтобы продолжить покупки</p>
                            <a href="/customer/main" class="btn btn-primary mt-3">Перейти к покупкам</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Сумма заказа</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Товары (<?= count($data['products']) ?>):</span>
                        <span><?= number_format($data['total'], 2, '.', ' ') ?> ₽</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Доставка:</span>
                        <span>Бесплатно</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Итого:</strong>
                        <strong><?= number_format($data['total'], 2, '.', ' ') ?> ₽</strong>
                    </div>
                    
                    <?php if (!empty($data['products'])): ?>
                        <button class="btn btn-primary w-100 mt-2 checkout-btn">
                            Оформить заказ
                        </button>
                        <a href="/customer/main" class="btn btn-outline-primary w-100 mt-2">
                            Продолжить покупки
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно обработки заказа -->
<div class="modal fade" id="orderProcessingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
                <h5>Обработка вашего заказа</h5>
                <p class="text-muted">Пожалуйста, подождите...</p>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно успешного оформления -->
<div class="modal fade" id="orderSuccessModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                </div>
                <h5>Заказ успешно оформлен!</h5>
                <p class="text-muted">На вашу почту отправлено письмо с квитанцией и QR-кодом для оплаты.</p>
                <p class="text-muted">Через несколько секунд вы будете перенаправлены на страницу заказа.</p>
                <div class="progress mt-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection() ?>

<?php $this->startSection('scripts') ?>
<script>
$(document).ready(function() {
    // Удаление товара из корзины
    $('.remove-from-basket').click(function() {
        const productId = $(this).data('product-id');
        if (confirm('Вы уверены, что хотите удалить этот товар из корзины?')) {
            $.post('/api/customer/basket/delete', { product_id: productId }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    showAlert('Ошибка', response.error || 'Ошибка при удалении товара', 'danger');
                }
            }).fail(function() {
                showAlert('Ошибка', 'Ошибка соединения с сервером', 'danger');
            });
        }
    });
    
    // Увеличение количества
    $('.increase-quantity').click(function() {
        const productId = $(this).data('product-id');
        const input = $(`.quantity-input[data-product-id="${productId}"]`);
        const newValue = parseInt(input.val()) + 1;
        input.val(newValue);
        updateQuantity(productId, newValue);
    });
    
    // Уменьшение количества
    $('.decrease-quantity').click(function() {
        const productId = $(this).data('product-id');
        const input = $(`.quantity-input[data-product-id="${productId}"]`);
        let newValue = parseInt(input.val()) - 1;
        
        if (newValue < 1) newValue = 1;
        
        input.val(newValue);
        updateQuantity(productId, newValue);
    });
    
    // Ручное изменение количества
    $('.quantity-input').change(function() {
        const productId = $(this).data('product-id');
        let newValue = parseInt($(this).val()) || 1;

        if (newValue < 1) newValue = 1;
        
        $(this).val(newValue);
        updateQuantity(productId, newValue);
    });
    
    // Оформление заказа
    $('.checkout-btn').click(function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Оформление...');
        
        // Показываем модальное окно ожидания
        $('#orderProcessingModal').modal('show');
        
        $.post('/api/customer/basket/checkout', function(response) {
            if (response.success) {
                // Скрываем модальное окно ожидания
                $('#orderProcessingModal').modal('hide');
                
                // Показываем модальное окно успешного оформления
                $('#orderSuccessModal').modal('show');
                
                // Перенаправляем на страницу заказа через 5 секунд
                setTimeout(function() {
                    window.location.href = '/customer/order/' + response.order_id;
                }, 5000);
            } else {
                showAlert('Ошибка', response.error || 'Ошибка при оформлении заказа', 'danger');
                btn.prop('disabled', false).text('Оформить заказ');
            }
        }).fail(function() {
            showAlert('Ошибка', 'Ошибка соединения с сервером', 'danger');
            btn.prop('disabled', false).text('Оформить заказ');
        });
    });
    
    function updateQuantity(productId, quantity) {
        $.post('/api/customer/basket/update', { 
            product_id: productId, 
            count: quantity 
        }, function(response) {
            if (response.success) {
                // Обновляем итоговые значения
                updateOrderSummary();
            } else {
                showAlert('Ошибка', response.error || 'Ошибка при обновлении количества', 'danger');
                location.reload();
            }
        }).fail(function() {
            showAlert('Ошибка', 'Ошибка соединения с сервером', 'danger');
            location.reload();
        });
    }

    function updateOrderSummary() {
        $.get('/api/customer/basket/get_total', function(response) {
            if (response.success) {
                // Обновляем количество товаров в таблице
                $('.card-header .badge').text(response.count + ' товар' + (response.count % 10 == 1 && response.count % 100 != 11 ? '' : (response.count % 10 >= 2 && response.count % 10 <= 4 && (response.count % 100 < 10 || response.count % 100 >= 20) ? 'а' : 'ов')));
                
                // Обновляем суммы в таблице
                $('tbody tr').each(function() {
                    const productId = $(this).find('.quantity-input').data('product-id');
                    const quantity = parseInt($(this).find('.quantity-input').val());
                    const price = parseFloat($(this).find('td:nth-child(2)').text().replace(/[^\d.]/g, ''));
                    const total = (price * quantity).toFixed(2);
                    $(this).find('td:nth-child(4)').text(total.replace('.', ',') + ' ₽');
                });
                
                // Обновляем блок с итоговой суммой
                const formattedTotal = response.total.toFixed(2).replace('.', ',');
                
                // Обновляем строку с товарами
                $('.card-body .d-flex.justify-content-between.mb-2 span:first').text('Товары (' + response.count + '):');
                $('.card-body .d-flex.justify-content-between.mb-2 span:last').text(formattedTotal + ' ₽');
                
                // Обновляем итоговую сумму
                $('.card-body .d-flex.justify-content-between.mb-3 strong:contains("Итого")').next().text(formattedTotal + ' ₽');

                $('.card-body .d-flex.justify-content-between.mb-2 span:last, .card-body .d-flex.justify-content-between.mb-3 strong:contains("Итого")').next()
                    .addClass('animated-update')
                    .on('animationend', function() {
                        $(this).removeClass('animated-update');
                });
            } else {
                showAlert('Ошибка', response.error || 'Ошибка при обновлении суммы', 'danger');
            }
        }).fail(function() {
            showAlert('Ошибка', 'Ошибка соединения с сервером', 'danger');
        });
    }
    
    function showAlert(title, message, type) {
        const alert = $(`
            <div class="alert alert-${type} alert-dismissible fade show position-fixed bottom-0 end-0 m-3" role="alert">
                <strong>${title}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(alert);
        setTimeout(() => alert.alert('close'), 5000);
    }
});
</script>
<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<style>
.quantity-selector .btn {
    width: 35px;
}

.quantity-input {
    max-width: 50px;
}

.card {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.table th {
    font-weight: 500;
    color: #6c757d;
    border-top: none;
}

.table td {
    vertical-align: middle;
}

.checkout-btn {
    padding: 10px;
    font-size: 1.1rem;
    font-weight: 500;
}

.animated-update {
    animation: pulse 0.5s ease-in-out;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>
<?php $this->endSection() ?>