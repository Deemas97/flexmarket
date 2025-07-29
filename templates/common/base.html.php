<!DOCTYPE html>
<html lang="ru,en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $this->escape($title) ?></title>
        <link rel="icon" href="/assets/img/logo.png">

        <!-- Styles from CDNs -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

        <!-- Custom styles -->
        <link rel="stylesheet" href="/assets/css/styles.css">
        <?= $this->section('styles') ?>

        <!-- Libs from CDNs -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body>
        <div class="crm-container">
            <!-- Top Navigation -->
            <?php $this->includeComponent('common/components/navbar.html.php', $data) ?>

            <div class="crm-wrapper">
                <!-- Main Content -->
                <main class="crm-main">
                    <?= $this->section('content') ?>
                </main>

                <!-- Scripts -->
                <?= $this->section('scripts') ?>
            </div>
        </div>
        <div class="cookie-consent-container">
            <div class="cookie-consent-content">
                <div class="cookie-consent-text">
                    <h5>Мы используем cookies</h5>
                    <p>Этот сайт использует файлы cookie для улучшения работы и анализа трафика. Продолжая использовать сайт, вы соглашаетесь с нашей <a href="/terms" target="_blank">Политикой конфиденциальности</a>.</p>
                </div>
                <div class="cookie-consent-buttons">
                    <button class="btn btn-sm btn-outline-secondary" id="cookie-consent-deny">Отклонить</button>
                    <button class="btn btn-sm btn-primary" id="cookie-consent-accept">Принять</button>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Проверяем, было ли уже принято решение о cookies
            if (!localStorage.getItem('cookie_consent')) {
                // Показываем окно через небольшую задержку
                setTimeout(function() {
                    document.querySelector('.cookie-consent-container').classList.add('show');
                }, 1000);
            }
        
            // Обработка кнопки "Принять"
            document.getElementById('cookie-consent-accept').addEventListener('click', function() {
                localStorage.setItem('cookie_consent', 'accepted');
                document.querySelector('.cookie-consent-container').classList.remove('show');
                // Здесь можно добавить код для загрузки аналитических cookies
            });
        
            // Обработка кнопки "Отклонить"
            document.getElementById('cookie-consent-deny').addEventListener('click', function() {
                localStorage.setItem('cookie_consent', 'denied');
                document.querySelector('.cookie-consent-container').classList.remove('show');
            });
        });
        </script>
    </body>
</html>