<nav class="navbar navbar-expand-lg crm-navbar">
    <div class="container-fluid navbar-container">
        <!-- Первый ряд (центрированный) -->
        <div class="navbar-top-row w-100 d-flex justify-content-center align-items-center py-2">
            <div class="d-flex align-items-center justify-content-between navbar-top-content" style="width: 100%; max-width: 1200px;">
                <!-- Логотип -->
                <div class="d-flex align-items-center">
                    <a class="navbar-brand" href="/customer/main">
                        <img src="/assets/img/logo.png" alt="CRM Logo" height="40">
                        <span class="ms-2 brand-text d-none d-sm-inline">ФлексМаркет</span>
                    </a>
                </div>
                
                <!-- Поиск (центрированный) -->
                <div class="flex-grow-1 mx-3" style="max-width: 600px;">
                    <form class="input-group crm-search" action="/customer/search" method="GET">
                        <input type="text" class="form-control" name="q" placeholder="Поиск..." value="<?= $this->escape($_GET['q'] ?? '') ?>">
                        <button class="btn" type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                
                <!-- Профиль -->
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle user-dropdown nav-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="/<?= $this->escape($data['user_session']['avatar'] ?? 'assets/img/default-avatar.png'); ?>" class="rounded-circle" width="40" height="40" alt="User">
                        <span class="ms-2 d-none d-lg-inline"><?= $this->escape($data['user_session']['name'] ?? 'Пользователь') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><h6 class="dropdown-header"><?= $this->escape($data['user_session']['email'] ?? 'email@example.com') ?></h6></li>
                        <li><a class="dropdown-item" href="/customer/basket"><i class="fas fa-shopping-cart me-2"></i>Корзина</a></li>
                        <li><a class="dropdown-item" href="/customer/orders"><i class="fas fa-tasks me-2"></i>Заказы</a></li>
                        <li><a class="dropdown-item" href="/customer/reviews"><i class="fas fa-comment me-2"></i>Отзывы</a></li>
                        <li><a class="dropdown-item" href="/customer/profile"><i class="fas fa-user me-2"></i>Профиль</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/api/customer/logout"><i class="fas fa-sign-out-alt me-2"></i>Выход</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Второй ряд (центрированный) -->
        <div class="navbar-bottom-row w-100 py-2">
            <div class="d-flex justify-content-center">
                <div class="navbar-nav nav-links-container nav-white" style="max-width: 1200px; width: 100%;">
                    <a class="nav-link px-3 nav-white" href="/customer/main"><i class="fas fa-home me-1 d-none d-md-inline"></i> Главная</a>
                    <a class="nav-link px-3 nav-white" href="/customer/recommendations"><i class="fas fa-check-square me-1 d-none d-md-inline"></i> Рекомендации</a>
                    <a class="nav-link px-3 nav-white" href="/customer/products"><i class="fas fa-list me-1 d-none d-md-inline"></i> Каталог</a>
                    <a class="nav-link px-3 nav-white" href="/customer/categories"><i class="fas fa-bar-chart me-1 d-none d-md-inline"></i> Категории</a>
                    <a class="nav-link px-3 nav-white" href="/customer/companies"><i class="fas fa-building me-1 d-none d-md-inline"></i> Компании</a>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
.crm-navbar {
    background-color: var(--crm-primary);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1030;
    padding: 0;
    flex-direction: column;
}

.navbar-container {
    padding: 0;
    display: flex;
    flex-direction: column;
}

/* Стили для первого ряда */
.navbar-top-row {
    background-color: var(--crm-primary);
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.navbar-top-content {
    display: flex;
    align-items: center;
    padding: 0 20px;
}

/* Стили для второго ряда */
.navbar-bottom-row {
    background-color: var(--crm-dark);
    display: flex;
    justify-content: center;
}

.nav-links-container {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    padding: 0 20px;
}

.brand-text {
    color: white;
    font-weight: 600;
    font-size: 1.2rem;
}

.crm-search .form-control {
    background-color: white;
    border: none;
    color: var(--crm-dark);
    border-radius: 20px 0 0 20px;
}

.crm-search .form-control::placeholder {
    color: #aaa;
}

.crm-search .btn {
    background-color: var(--crm-accent);
    color: white;
    border-radius: 0 20px 20px 0;
}

.nav-link {
    color: rgba(255,255,255,0.8);
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
    transition: color 0.2s;
    white-space: nowrap;
}

.nav-link:hover, .nav-link:focus {
    color: white;
    text-decoration: none;
}

.nav-white {
    color: #FFFFFF !important
}

.nav-link i {
    font-size: 0.9rem;
}

.user-dropdown {
    padding: 0.25rem 0.5rem;
    display: flex;
    align-items: center;
}

.dropdown-menu {
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-top: 0.5rem;
}

.dropdown-header {
    color: var(--crm-secondary);
    font-size: 0.8rem;
    padding: 0.5rem 1rem;
}

/* Адаптивные стили */
@media (max-width: 992px) {
    .navbar-top-content {
        flex-wrap: wrap;
        justify-content: space-between;
        padding: 10px 15px;
    }
    
    .crm-search {
        order: 3;
        width: 100%;
        margin-top: 10px;
        max-width: 100% !important;
    }
    
    .nav-links-container {
        justify-content: flex-start;
        overflow-x: auto;
        white-space: nowrap;
        padding: 0 15px;
    }
    
    .nav-link {
        padding: 0.5rem;
    }
}

@media (max-width: 576px) {
    .brand-text {
        display: none !important;
    }
    
    .nav-link {
        font-size: 0.9rem;
        padding: 0.5rem;
    }
    
    .nav-link i {
        margin-right: 0 !important;
    }
}
</style>