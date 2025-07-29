<?php
namespace App\Core\Module;

include_once '../src/Core/ModuleInterface.php';
include_once '../src/Core/Module/RequestManagerInterface.php';
include_once '../src/Core/MessageBusInterface.php';
include_once '../src/Core/MessageBus/ActionsInterface.php';
include_once '../src/Core/MessageBus/Actions.php';
include_once '../src/Core/Service/Router.php';
include_once '../src/Core/Service/Router/RouteInterface.php';

include_once '../src/Controller/ErrorController.php';
include_once '../src/Controller/MainController.php';
include_once '../src/Controller/SearchController.php';
include_once '../src/Controller/StatusInfoController.php';
include_once '../src/Controller/TermsController.php';
include_once '../src/Controller/Customer/CustomerBasketController.php';
include_once '../src/Controller/Customer/CustomerAuthController.php';
include_once '../src/Controller/Customer/CustomerCategoriesController.php';
include_once '../src/Controller/Customer/CustomerCompaniesController.php';
include_once '../src/Controller/Customer/CustomerController.php';
include_once '../src/Controller/Customer/CustomerMainController.php';
include_once '../src/Controller/Customer/CustomerOrdersController.php';
include_once '../src/Controller/Customer/CustomerProductsController.php';
include_once '../src/Controller/Customer/CustomerRecommendationsController.php';
include_once '../src/Controller/Customer/CustomerReviewsController.php';
include_once '../src/Controller/Customer/CustomerSearchController.php';
include_once '../src/Controller/Customer/API/CustomerBasketApiController.php';
include_once '../src/Controller/Customer/API/CustomerApiController.php';
include_once '../src/Controller/Customer/API/CustomerAuthApiController.php';
include_once '../src/Controller/Customer/API/CustomerOrdersApiController.php';
include_once '../src/Controller/Customer/API/CustomerOrdersApiController.php';
include_once '../src/Controller/Customer/API/CustomerReviewsApiController.php';

include_once '../src/Controller/Vendor/VendorAcceptancesController.php';
include_once '../src/Controller/Vendor/VendorAuthController.php';
include_once '../src/Controller/Vendor/VendorController.php';
include_once '../src/Controller/Vendor/VendorCategoriesController.php';
include_once '../src/Controller/Vendor/VendorCompaniesController.php';
include_once '../src/Controller/Vendor/VendorCompanyController.php';
include_once '../src/Controller/Vendor/VendorCustomersController.php';
include_once '../src/Controller/Vendor/VendorMainController.php';
include_once '../src/Controller/Vendor/VendorOrdersPositionsController.php';
include_once '../src/Controller/Vendor/VendorProductsController.php';
include_once '../src/Controller/Vendor/VendorProductsGlobalController.php';
include_once '../src/Controller/Vendor/VendorRecommendationsController.php';
include_once '../src/Controller/Vendor/VendorReportsController.php';
include_once '../src/Controller/Vendor/VendorReviewsController.php';
include_once '../src/Controller/Vendor/VendorsController.php';
include_once '../src/Controller/Vendor/VendorSearchController.php';
include_once '../src/Controller/Vendor/API/VendorAcceptancesApiController.php';
include_once '../src/Controller/Vendor/API/VendorApiController.php';
include_once '../src/Controller/Vendor/API/VendorAuthApiController.php';
include_once '../src/Controller/Vendor/API/VendorCompanyApiController.php';
include_once '../src/Controller/Vendor/API/VendorOrdersPositionsApiController.php';
include_once '../src/Controller/Vendor/API/VendorProductsApiController.php';
include_once '../src/Controller/Vendor/API/VendorReportsApiController.php';
include_once '../src/Controller/Vendor/API/VendorsApiController.php';

use App\Core\ModuleInterface;
use App\Core\MessageBusInterface;
use App\Core\MessageBus\ActionsInterface;
use App\Core\MessageBus\Actions;
use App\Core\Service\Router;
use App\Core\Service\Router\RouteInterface;

use App\Controller\ErrorController;
use App\Controller\MainController;
use App\Controller\SearchController;
use App\Controller\StatusInfoController;
use App\Controller\TermsController;

use App\Controller\CustomerBasketController;
use App\Controller\CustomerBasketApiController;
use App\Controller\CustomerApiController;
use App\Controller\CustomerAuthApiController;
use App\Controller\CustomerAuthController;
use App\Controller\CustomerCategoriesController;
use App\Controller\CustomerCompaniesController;
use App\Controller\CustomerController;
use App\Controller\CustomerMainController;
use App\Controller\CustomerOrdersApiController;
use App\Controller\CustomerOrdersController;
use App\Controller\CustomerProductsController;
use App\Controller\CustomerRecommendationsController;
use App\Controller\CustomerReviewsApiController;
use App\Controller\CustomerReviewsController;
use App\Controller\CustomerSearchController;

use App\Controller\VendorAcceptancesApiController;
use App\Controller\VendorAcceptancesController;
use App\Controller\VendorApiController;
use App\Controller\VendorAuthApiController;
use App\Controller\VendorController;
use App\Controller\VendorAuthController;
use App\Controller\VendorCategoriesController;
use App\Controller\VendorCompaniesController;
use App\Controller\VendorCompanyApiController;
use App\Controller\VendorCompanyController;
use App\Controller\VendorCustomersController;
use App\Controller\VendorMainController;
use App\Controller\VendorOrdersPositionsApiController;
use App\Controller\VendorOrdersPositionsController;
use App\Controller\VendorProductsApiController;
use App\Controller\VendorProductsController;
use App\Controller\VendorProductsGlobalController;
use App\Controller\VendorRecommendationsController;
use App\Controller\VendorReportsApiController;
use App\Controller\VendorReportsController;
use App\Controller\VendorReviewsController;
use App\Controller\VendorsApiController;
use App\Controller\VendorsController;
use App\Controller\VendorSearchController;

class RequestManager implements ModuleInterface, RequestManagerInterface
{
    public function __construct(
        protected Router $router
    )
    {
        $this->initRouter();
    }

    public function process(MessageBusInterface $messageBus): ActionsInterface
    {
        $actions = new Actions();

        $requestRoute = $messageBus->get('route');
        $route = $this->router->resolve($requestRoute);

        if ($route instanceof RouteInterface) {
            $actions->addItem('controller', $route->getControllerName());
            $actions->addItem('method', $route->getMethodName());
            $actions->addItem('shared_services', $messageBus->get('sharing_services'));
        }

        return $actions;
    }

    protected function initRouter(): void
    {
        $this->router
            ->register('/',                   MainController::class,   'index')
            ->register('/search',             SearchController::class, 'index')

            ->register('/premoderation_info', StatusInfoController::class, 'premoderationInfo')
            ->register('/ban_info',           StatusInfoController::class, 'banInfo')
            ->register('/terms',              TermsController::class, 'termsFolder')

            ->register('/access_denied',      ErrorController::class, 'error403')
            ->register('/not_found',          ErrorController::class, 'error404')
            ->register('/crash',              ErrorController::class, 'error500')
     


            ->register('/customer/main',                          CustomerMainController::class, 'index')

            ->register('/customer/login',                         CustomerAuthController::class, 'authForm')
            ->register('/customer/signup',                        CustomerAuthController::class, 'registerForm')
            ->register('/customer/password/reset',                CustomerAuthController::class, 'resetPassForm')
            ->register('/customer/password/reset/confirm',        CustomerAuthController::class, 'confirmPassForm')

            ->register('/customer/profile',                       CustomerController::class, 'index')
            ->register('/customer/profile/edit',                  CustomerController::class, 'edit')

            ->register('/customer/search',                        CustomerSearchController::class, 'index')

            ->register('/customer/basket',                        CustomerBasketController::class, 'index')

            ->register('/customer/orders',                        CustomerOrdersController::class, 'index')
            ->register('/customer/order/{id}',                    CustomerOrdersController::class, 'order')

            ->register('/customer/products',                      CustomerProductsController::class, 'index')
            ->register('/customer/products/filter',               CustomerProductsController::class, 'filter')
            ->register('/customer/product/{id}',                  CustomerProductsController::class, 'product')

            ->register('/customer/companies',                     CustomerCompaniesController::class, 'index')
            ->register('/customer/company/{id}',                  CustomerCompaniesController::class, 'company')

            ->register('/customer/categories',                    CustomerCategoriesController::class, 'index')
            ->register('/customer/category/{id}',                 CustomerCategoriesController::class, 'category')

            ->register('/customer/reviews',                       CustomerReviewsController::class, 'index')
            ->register('/customer/review/{id}/edit',              CustomerReviewsController::class, 'review')

            ->register('/customer/recommendations',               CustomerRecommendationsController::class, 'index')


            
            ->register('/api/customer/basket/add',                CustomerBasketApiController::class, 'apiAdd')
            ->register('/api/customer/basket/update',             CustomerBasketApiController::class, 'apiUpdate')
            ->register('/api/customer/basket/delete',             CustomerBasketApiController::class, 'apiRemove')
            ->register('/api/customer/basket/count',              CustomerBasketApiController::class, 'apiCount')
            ->register('/api/customer/basket/get_total',          CustomerBasketApiController::class, 'apiGetTotal')
            ->register('/api/customer/basket/checkout',           CustomerBasketApiController::class, 'apiCheckout')

            ->register('/api/customer/order/create',              CustomerOrdersApiController::class, 'apiCreate')
            ->register('/api/customer/order/{id}/add_product',    CustomerOrdersApiController::class, 'apiAddProduct')
            ->register('/api/customer/order/{id}/remove_product', CustomerOrdersApiController::class, 'apiRemoveProduct')
            ->register('/api/customer/order/{id}/delete',         CustomerOrdersApiController::class, 'apiCancel')
            
            ->register('/api/customer/reviews/save',              CustomerReviewsApiController::class, 'apiSave')
            ->register('/api/customer/reviews/delete',            CustomerReviewsApiController::class, 'apiDelete')
            
            ->register('/api/customer/login',                     CustomerAuthApiController::class, 'apiAuth')
            ->register('/api/customer/signup',                    CustomerAuthApiController::class, 'apiRegister')
            ->register('/api/customer/logout',                    CustomerAuthApiController::class, 'apiLogout')

            ->register('/api/customer/profile/change_password',   CustomerApiController::class, 'apiChangePassword')
            ->register('/api/customer/profile/edit',              CustomerApiController::class, 'apiUpdate')



            ->register('/vendor/main',                              VendorMainController::class,   'index')

            ->register('/vendor/profile',                           VendorController::class,   'index')
            ->register('/vendor/profile/edit',                      VendorController::class,   'edit')
            ->register('/vendor/edit',                              VendorController::class,   'vendorEdit')

            ->register('/vendor/company/edit',                      VendorCompanyController::class, 'companyEdit')

            ->register('/vendor/vendors',                           VendorsController::class, 'index')
            ->register('/vendor/vendor/{id}/edit',                  VendorsController::class, 'vendor')

            ->register('/vendor/acceptances',                       VendorAcceptancesController::class, 'index')
            ->register('/vendor/acceptance/{id}/edit',              VendorAcceptancesController::class, 'acceptance')

            ->register('/vendor/companies',                         VendorCompaniesController::class, 'index')

            ->register('/vendor/products_global',                   VendorProductsGlobalController::class, 'index')
            ->register('/vendor/product_global/{id}',               VendorProductsGlobalController::class, 'product')

            ->register('/vendor/products',                          VendorProductsController::class, 'index')
            ->register('/vendor/product/create',                    VendorProductsController::class, 'createForm')
            ->register('/vendor/product/{id}/edit',                 VendorProductsController::class, 'product')

            ->register('/vendor/categories',                        VendorCategoriesController::class, 'index')

            ->register('/vendor/customers',                         VendorCustomersController::class, 'index')
            ->register('/vendor/customer/{id}',                     VendorCustomersController::class, 'customer')

            ->register('/vendor/orders',                            VendorOrdersPositionsController::class, 'index')

            ->register('/vendor/reviews',                           VendorReviewsController::class, 'index')
            ->register('/vendor/review/{id}',                       VendorReviewsController::class, 'review')

            ->register('/vendor/recommendations',                   VendorRecommendationsController::class, 'index')

            ->register('/vendor/reports',                           VendorReportsController::class, 'index')
            ->register('/vendor/report/{id}',                       VendorReportsController::class, 'report')

            ->register('/vendor/login',                             VendorAuthController::class, 'authForm')
            ->register('/vendor/signup',                            VendorAuthController::class, 'registerForm')
            ->register('/vendor/password/reset',                    VendorAuthController::class, 'resetPassForm')
            ->register('/vendor/password/reset/confirm',            VendorAuthController::class, 'confirmPassForm')

            ->register('/vendor/search',                            VendorSearchController::class, 'index')

            ->register('/api/vendor/company/edit',                  VendorCompanyApiController::class, 'apiUpdate')

            ->register('/api/vendor/create',                        VendorsApiController::class, 'apiCreate')
            ->register('/api/vendor/{id}/edit',                     VendorsApiController::class, 'apiUpdate')
            ->register('/api/vendor/{id}/change_password',          VendorsApiController::class, 'apiChangePassword')
            ->register('/api/vendor/{id}/delete',                   VendorsApiController::class, 'apiDelete')

            ->register('/api/vendor/acceptance/create',             VendorAcceptancesApiController::class, 'apiCreate')
            ->register('/api/vendor/acceptance/{id}/edit',          VendorAcceptancesApiController::class, 'apiUpdate')
            ->register('/api/vendor/acceptance/{id}/delete',        VendorAcceptancesApiController::class, 'apiDelete')

            ->register('/api/vendor/product/create',                    VendorProductsApiController::class, 'apiCreate')
            ->register('/api/vendor/product/{id}/edit',                 VendorProductsApiController::class, 'apiUpdate')
            ->register('/api/vendor/product/{id}/delete',               VendorProductsApiController::class, 'apiDelete')
            ->register('/api/vendor/product/{id}/remove_gallery_image', VendorProductsApiController::class, 'apiRemoveGalleryImage')

            ->register('/api/vendor/order/{id}/update_status',      VendorOrdersPositionsApiController::class, 'apiUpdatePositionStatus')

            ->register('/api/vendor/reports/get_list',              VendorReportsApiController::class, 'apiListReports')
            ->register('/api/vendor/report/create',                 VendorReportsApiController::class, 'apiGenerateReport')
            ->register('/api/vendor/report/{id}/download',          VendorReportsApiController::class, 'apiDownloadReport')
            ->register('/api/vendor/report/{id}/delete',            VendorReportsApiController::class, 'apiDeleteReport')

            ->register('/api/vendor/login',                         VendorAuthApiController::class, 'apiAuth')
            ->register('/api/vendor/signup',                        VendorAuthApiController::class, 'apiRegister')
            ->register('/api/vendor/logout',                        VendorAuthApiController::class, 'apiLogout')

            ->register('/api/vendor/profile/change_password',       VendorApiController::class, 'apiChangePassword')
            ->register('/api/vendor/profile/edit',                  VendorApiController::class, 'apiUpdate');
    }
}