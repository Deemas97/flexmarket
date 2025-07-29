<?php $this->extend('auth/auth_base.html.php') ?>

<?php $this->startSection('auth_form') ?>
<form id="registerForm" class="auth-form" method="POST" action="/api/customer/signup">
            <div class="mb-3 form-floating">
                <input type="text" class="form-control" id="i" name="i" 
                       placeholder="Имя" required>
                <label for="i">Имя</label>
            </div>

            <div class="mb-3 form-floating">
                <input type="text" class="form-control" id="f" name="f" 
                       placeholder="Фамилия" required>
                <label for="f">Фамилия</label>
            </div>
            
            <div class="mb-3 form-floating">
                <input type="email" class="form-control" id="email" name="email" 
                       placeholder="Email" required>
                <label for="email">Email</label>
            </div>

            <div class="mb-3 form-floating">
                <input type="text" class="form-control" id="address" name="address" 
                       placeholder="Адрес" required>
                <label for="address">Адрес</label>
            </div>
            
            <div class="mb-3 form-floating">
                <input type="password" class="form-control" id="password" name="password" 
                       placeholder="Пароль" required>
                <label for="password">Пароль</label>
            </div>
            
            <div class="mb-3 form-floating">
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" 
                       placeholder="Подтверждение пароля" required>
                <label for="password_confirmation">Подтверждение пароля</label>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="agree_terms" name="agree_terms" required>
                <label class="form-check-label" for="agree_terms">
                    Я согласен с <a href="/terms">условиями использования</a>
                </label>
            </div>
            
            <button type="submit" class="btn btn-success w-100">Зарегистрироваться</button>
        </form>

<script>
$(document).ready(function() {
    $('#registerForm').validate({
        rules: {
            i: {
                required: true,
                minlength: 2
            },
            f: {
                required: true,
                minlength: 2
            },
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 8
            },
            password_confirmation: {
                required: true,
                equalTo: "#password"
            },
            agree_terms: {
                required: true
            }
        },
        messages: {
            i: {
                required: "Пожалуйста, введите ваше имя",
                minlength: "Имя должно быть не короче 2 символов"
            },
            f: {
                required: "Пожалуйста, введите вашу фамилию",
                minlength: "Фамилия должна быть не короче 2 символов"
            },
            email: "Пожалуйста, введите корректный email",
            password: {
                required: "Пожалуйста, введите пароль",
                minlength: "Пароль должен быть не менее 8 символов"
            },
            password_confirmation: {
                required: "Пожалуйста, подтвердите пароль",
                equalTo: "Пароли не совпадают"
            },
            agree_terms: "Вы должны принять условия использования"
        },
        errorElement: 'div',
        errorClass: 'invalid-feedback',
        highlight: function(element) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        },
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        }
    });
});
</script>
<?php $this->endSection() ?>