<nav class="navbar navbar-expand-lg crm-navbar">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <a class="navbar-brand" href="/">
                <img src="/assets/img/logo.png" alt="CRM Logo" height="40">
                <span class="ms-2 brand-text">ФлексМаркет</span>
            </a>
        </div>
        
        <div class="d-flex align-items-center">
            <!-- Поисковая строка для десктопа -->
            <form class="input-group crm-search d-none d-lg-flex me-3" action="/search" method="GET">
                <input type="text" class="form-control" name="q" placeholder="Поиск..." value="<?= $this->escape($_GET['q'] ?? '') ?>">
                <button class="btn" type="submit"><i class="fas fa-search"></i></button>
            </form>
            
            <div class="d-flex gap-2">
                <!-- Кнопка входа/регистрации для покупателя -->
                <div class="dropdown">
                    <a class="btn btn-outline-light btn-login" href="/customer/login" id="customerLogin" role="button">
                        <i class="fas fa-user me-1"></i>
                        <span class="d-none d-lg-inline">Покупатель</span>
                    </a>
                </div>
                
                <!-- Кнопка входа/регистрации для продавца -->
                <div class="dropdown">
                    <a class="btn btn-accent btn-login" href="/vendor/login" id="vendorLogin" role="button">
                        <i class="fas fa-store me-1"></i>
                        <span class="d-none d-lg-inline">Продавец</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<div class="sidebar-overlay"></div>

<style>
.crm-navbar {
    background-color: var(--crm-primary);
    height: auto;
    min-height: 80px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1030;
    padding: 0.5rem 1rem;
}

.navbar-brand {
    display: flex;
    align-items: center;
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
    min-width: 250px;
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

/* Стили для кнопок входа */
.btn-login {
    border-radius: 20px;
    padding: 0.5rem 1rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    transition: all 0.2s;
    white-space: nowrap;
}

.btn-outline-light {
    border: 1px solid rgba(255,255,255,0.5);
    color: white;
}

.btn-outline-light:hover {
    background-color: rgba(255,255,255,0.1);
    border-color: white;
}

.btn-accent {
    background-color: var(--crm-accent);
    color: white;
    border: 1px solid var(--crm-accent);
}

.btn-accent:hover {
    background-color: var(--crm-accent-dark);
    border-color: var(--crm-accent-dark);
    color: white;
}

@media (max-width: 992px) {
    .crm-navbar {
        flex-wrap: wrap;
    }
    
    .crm-search {
        width: 100%;
        margin: 0.5rem 0;
    }
    
    .crm-search .form-control {
        min-width: auto;
    }
    
    .btn-login span {
        display: none;
    }
    
    .btn-login i {
        margin-right: 0 !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработчик для кнопки переключения сайдбара
    document.querySelector('.sidebar-toggle')?.addEventListener('click', function() {
        document.querySelector('.crm-wrapper').classList.toggle('sidebar-open');
    });
    
    // Закрытие сайдбара при клике на оверлей
    document.querySelector('.sidebar-overlay')?.addEventListener('click', function() {
        document.querySelector('.crm-wrapper').classList.remove('sidebar-open');
    });
});
</script>