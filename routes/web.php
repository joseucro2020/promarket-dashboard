<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AppsController;
use App\Http\Controllers\UserInterfaceController;
use App\Http\Controllers\CardsController;
use App\Http\Controllers\ComponentsController;
use App\Http\Controllers\ExtensionController;
use App\Http\Controllers\PageLayoutController;
use App\Http\Controllers\FormsController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\MiscellaneousController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ShippingFeeController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\WebServicesController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CategoryBlocksController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SpecialCategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\Admin\BuyOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\StatesMunicipalitiesController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TermsConditionsController;
use App\Http\Controllers\PaymentGatewayController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// PRUEBA DE CONCEPTO: Ruta simple para verificar Nginx + Laravel
Route::get('/panel/test', function () {
    return 'Hola Mundo desde el Dashboard en Docker!';
});

// Main Page Route
// Route::get('/', [DashboardController::class,'dashboardEcommerce'])->name('dashboard-ecommerce')->middleware('verified');
Route::get('/panel', [DashboardController::class,'dashboardEcommerce'])->name('dashboard-home');

// Routes para el módulo de Tasa de cambio
Route::group(['prefix' => 'panel'], function () {
  Route::get('tasa-de-cambios', [ExchangeRateController::class, 'index'])->name('exchange-rates.index');
  Route::get('tasa-de-cambios/nuevo', [ExchangeRateController::class, 'create'])->name('exchange-rates.create');
  Route::post('tasa-de-cambios', [ExchangeRateController::class, 'store'])->name('exchange-rates.store');
  Route::get('tasa-de-cambios/{id}/editar', [ExchangeRateController::class, 'edit'])->name('exchange-rates.edit');
  Route::put('tasa-de-cambios/{id}', [ExchangeRateController::class, 'update'])->name('exchange-rates.update');
  Route::delete('tasa-de-cambios/{id}', [ExchangeRateController::class, 'destroy'])->name('exchange-rates.destroy');
  // Trigger fetch-now from UI
  Route::post('tasa-de-cambios/fetch-now', [ExchangeRateController::class, 'fetchBcvNow'])->name('exchange-rates.fetch-now');

  // Routes para el módulo de Productos
  Route::get('productos', [ProductController::class, 'index'])->name('products.index');
  Route::get('productos/nuevo', [ProductController::class, 'create'])->name('products.create');
  Route::post('productos', [ProductController::class, 'store'])->name('products.store');
  Route::get('productos/{id}/editar', [ProductController::class, 'edit'])->name('products.edit');
  Route::put('productos/{id}', [ProductController::class, 'update'])->name('products.update');
  Route::delete('productos/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
  // AJAX: actualizar utilidad/price de una presentación
  Route::post('product-amounts/{id}/utilidad', [ProductController::class, 'updateUtilidad'])->name('product-amounts.utilidad');

  Route::get('impuestos', [TaxController::class, 'index'])->name('taxes.index');
  Route::get('impuestos/nuevo', [TaxController::class, 'create'])->name('taxes.create');
  Route::post('impuestos', [TaxController::class, 'store'])->name('taxes.store');
  Route::get('impuestos/{id}/editar', [TaxController::class, 'edit'])->name('taxes.edit');
  Route::put('impuestos/{id}', [TaxController::class, 'update'])->name('taxes.update');
  Route::delete('impuestos/{id}', [TaxController::class, 'destroy'])->name('taxes.destroy');
  Route::post('impuestos/{id}/status', [TaxController::class, 'status'])->name('taxes.status');

  Route::get('cupones', [CouponController::class, 'index'])->name('coupons.index');
  Route::get('cupones/nuevo', [CouponController::class, 'create'])->name('coupons.create');
  Route::post('cupones', [CouponController::class, 'store'])->name('coupons.store');
  Route::get('cupones/{coupon}/editar', [CouponController::class, 'edit'])->name('coupons.edit');
  Route::put('cupones/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
  Route::delete('cupones/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');
  Route::post('cupones/{coupon}/status', [CouponController::class, 'status'])->name('coupons.status');

  Route::get('promociones', [PromotionController::class, 'index'])->name('promotions.index');
  
  // Shipping Fees
  Route::get('tasa-envio', [ShippingFeeController::class, 'index'])->name('shipping-fees.index');
  Route::put('tasa-envio/{id}', [ShippingFeeController::class, 'update'])->name('shipping-fees.update');
  Route::get('tasa-envio/all', [ShippingFeeController::class, 'getAll'])->name('shipping-fees.getAll');
  Route::post('tasa-envio/minimum', [ShippingFeeController::class, 'updateMinimum'])->name('shipping-fees.minimum');

  // SMS Sending
  Route::get('envio-sms', [SmsController::class, 'index'])->name('sms.index');
  Route::post('envio-sms/enviar', [SmsController::class, 'enviar'])->name('sms.enviar');

  // Kromi Market
  Route::get('kromi-market', [WebServicesController::class, 'index'])->name('kromi.index');
  Route::get('kromi-market/kromimarket', [WebServicesController::class, 'kromimarket'])->name('kromi.kromimarket');
  Route::post('kromi-market/import_csv', [WebServicesController::class, 'import_csv'])->name('kromi.import_csv');
  Route::post('kromi-market/register', [WebServicesController::class, 'registerKromi'])->name('kromi.register');
  Route::get('kromi-market/products', [WebServicesController::class, 'products'])->name('kromi.products');
  Route::get('promociones/nuevo', [PromotionController::class, 'create'])->name('promotions.create');
  Route::post('promociones', [PromotionController::class, 'store'])->name('promotions.store');
  Route::get('promociones/{promotion}/editar', [PromotionController::class, 'edit'])->name('promotions.edit');
  Route::put('promociones/{promotion}', [PromotionController::class, 'update'])->name('promotions.update');
  Route::delete('promociones/{promotion}', [PromotionController::class, 'destroy'])->name('promotions.destroy');
  Route::post('promociones/{promotion}/status', [PromotionController::class, 'status'])->name('promotions.status');
  Route::post('promociones/{promotion}/orden', [PromotionController::class, 'updateOrder'])->name('promotions.order');
  Route::get('promociones/{id}/subcategorias', [PromotionController::class, 'getsubcategory'])->name('promotions.subcategories');
  Route::get('promociones/productos', [PromotionController::class, 'getproducts'])->name('promotions.products');
  
  // Suppliers (Proveedores)
  Route::get('proveedores', [SupplierController::class, 'index'])->name('suppliers.index');
  Route::get('proveedores/nuevo', [SupplierController::class, 'create'])->name('suppliers.create');
  Route::post('proveedores', [SupplierController::class, 'store'])->name('suppliers.store');
  Route::get('proveedores/{id}/editar', [SupplierController::class, 'edit'])->name('suppliers.edit');
  Route::put('proveedores/{id}', [SupplierController::class, 'update'])->name('suppliers.update');
  Route::post('proveedores/{id}/status', [SupplierController::class, 'status'])->name('suppliers.status');
  // API: cargar estados y municipios dinámicamente
  Route::get('paises/{id}/estados', [SupplierController::class, 'getStates'])->name('paises.estados');
  Route::get('estados/{id}/municipios', [SupplierController::class, 'getMunicipalities'])->name('estados.municipios');
  Route::delete('proveedores/{id}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

  // Purchase Orders (Órdenes de Compra)
  Route::get('ordenes-compra', [BuyOrderController::class, 'index'])->name('buyorders.index');
  Route::get('ordenes-compra/nuevo', [BuyOrderController::class, 'create'])->name('buyorders.create');
  Route::post('ordenes-compra', [BuyOrderController::class, 'store'])->name('buyorders.store');
  Route::get('ordenes-compra/{id}/editar', [BuyOrderController::class, 'edit'])->name('buyorders.edit');
  Route::put('ordenes-compra/{id}', [BuyOrderController::class, 'update'])->name('buyorders.update');
  Route::delete('ordenes-compra/{id}', [BuyOrderController::class, 'destroy'])->name('buyorders.destroy');
  Route::post('ordenes-compra/{id}/aprobar/{row}', [BuyOrderController::class, 'aprobar'])->name('buyorders.aprobar');
  Route::post('ordenes-compra/{id}/anular', [BuyOrderController::class, 'anular'])->name('buyorders.anular');
  Route::post('ordenes-compra/export', [BuyOrderController::class, 'exportExcel'])->name('buyorders.export');
  Route::post('ordenes-compra/date', [BuyOrderController::class, 'date'])->name('buyorders.date');
  Route::post('ordenes-compra/details', [BuyOrderController::class, 'getDetails'])->name('buyorders.getDetails');

  // Orders (site purchases)
  Route::get('pedidos/{id}/print', function($id){
      $purchase = App\Models\Purchase::with(['details','user','delivery'])->find($id);
      return view('panel.purchases.print', compact('purchase'));
  })->name('purchases.print');
  Route::get('pedidos', [App\Http\Controllers\PurchaseController::class, 'index'])->name('purchases.index');
  Route::post('pedidos/date', [App\Http\Controllers\PurchaseController::class, 'date'])->name('purchases.date');
  Route::post('pedidos/details', [App\Http\Controllers\PurchaseController::class, 'getDetails'])->name('purchases.getDetails');
  Route::post('pedidos/details-company', [App\Http\Controllers\PurchaseController::class, 'getDetailsCompany'])->name('purchases.getDetailsCompany');
  Route::post('pedidos/export', [App\Http\Controllers\PurchaseController::class, 'exportExcel'])->name('purchases.export');
  Route::post('pedidos/{id}/approve', [App\Http\Controllers\PurchaseController::class, 'approve'])->name('purchases.approve');
  Route::post('pedidos/{id}/reject', [App\Http\Controllers\PurchaseController::class, 'reject'])->name('purchases.reject');

  // Clients (Customers)
  Route::get('clientes', [App\Http\Controllers\ClientController::class, 'index'])->name('clients.index');
  Route::get('clientes/all', [App\Http\Controllers\ClientController::class, 'getAll'])->name('clients.getAll');
  Route::post('clientes/{id}/status', [App\Http\Controllers\ClientController::class, 'changeStatus'])->name('clients.changeStatus');
  Route::post('clientes/{id}/delete', [App\Http\Controllers\ClientController::class, 'delete'])->name('clients.delete');
  Route::post('clientes/{id}/convert-to-pro', [App\Http\Controllers\ClientController::class, 'convertToPro'])->name('clients.convertToPro');
  Route::post('clientes/update', [App\Http\Controllers\ClientController::class, 'update'])->name('clients.update');
  Route::post('clientes/export', [App\Http\Controllers\ClientController::class, 'exportExcel'])->name('clients.export');

  // Pro Sellers (Vendedores PRO)
  Route::get('pro-sellers', [App\Http\Controllers\ProSellerController::class, 'index'])->name('pro-sellers.index');
  Route::get('pro-sellers/all', [App\Http\Controllers\ProSellerController::class, 'getAll'])->name('pro-sellers.getAll');
  Route::post('pro-sellers/{id}/status', [App\Http\Controllers\ProSellerController::class, 'changeStatus'])->name('pro-sellers.changeStatus');
  Route::post('pro-sellers/{id}/delete', [App\Http\Controllers\ProSellerController::class, 'delete'])->name('pro-sellers.delete');
  Route::get('pro-sellers/{id}/balance', [App\Http\Controllers\ProSellerController::class, 'getBalance'])->name('pro-sellers.balance');
  Route::post('pro-sellers/referral/{id}/delete', [App\Http\Controllers\ProSellerController::class, 'deleteReferral'])->name('pro-sellers.deleteReferral');

  // Bank Accounts (Cuentas Bancarias)
  Route::get('cuentas-bancarias', [BankController::class, 'index'])->name('bank-accounts.index');
  Route::post('cuentas-bancarias', [BankController::class, 'store'])->name('bank-accounts.store');
  Route::put('cuentas-bancarias/{id}', [BankController::class, 'update'])->name('bank-accounts.update');
  Route::delete('cuentas-bancarias/{id}', [BankController::class, 'destroy'])->name('bank-accounts.destroy');
  Route::post('cuentas-bancarias/{id}/status', [BankController::class, 'status'])->name('bank-accounts.status');

  // States & Municipalities (Estados y Municipios)
  Route::get('estados-municipios', [StatesMunicipalitiesController::class, 'index'])->name('states-municipalities.index');
  Route::get('estados-municipios/{id}', [StatesMunicipalitiesController::class, 'show'])->name('states-municipalities.show');
  Route::put('estados-municipios/{id}', [StatesMunicipalitiesController::class, 'update'])->name('states-municipalities.update');
  Route::post('estados-municipios/{id}/status', [StatesMunicipalitiesController::class, 'status'])->name('states-municipalities.status');
  Route::put('estados-municipios/municipios/{id}', [StatesMunicipalitiesController::class, 'updateMunicipality'])->name('states-municipalities.municipalities.update');
  Route::post('estados-municipios/municipios/{id}/status', [StatesMunicipalitiesController::class, 'municipalityStatus'])->name('states-municipalities.municipalities.status');

  // Inventory Replenishment
  Route::get('reposicion-de-inventario', [App\Http\Controllers\Admin\InventoryReplenishmentController::class, 'index'])->name('inventory.index');
  Route::get('reposicion-de-inventario/nuevo', [App\Http\Controllers\Admin\InventoryReplenishmentController::class, 'create'])->name('inventory.create');
  Route::post('reposicion-de-inventario', [App\Http\Controllers\Admin\InventoryReplenishmentController::class, 'store'])->name('inventory.store');

  // Batch Category Change (sin Admin)
  Route::get('cambios-productos-lotes', [CategoryBlocksController::class, 'index'])->name('categoryblocks.index');
  Route::post('cambios-productos-lotes/search', [CategoryBlocksController::class, 'search'])->name('categoryblocks.search');
  Route::post('cambios-productos-lotes/update', [CategoryBlocksController::class, 'update'])->name('categoryblocks.update');

  // Categories (sin Admin)
  Route::get('categorias', [CategoryController::class, 'index'])->name('categories.index');
  Route::get('categorias/nuevo', [CategoryController::class, 'create'])->name('categories.create');
  Route::post('categorias', [CategoryController::class, 'store'])->name('categories.store');
  Route::get('categorias/{id}', [CategoryController::class, 'show'])->name('categories.show');
  Route::get('categorias/{id}/editar', [CategoryController::class, 'edit'])->name('categories.edit');
  Route::put('categorias/{id}', [CategoryController::class, 'update'])->name('categories.update');
  Route::post('categorias/{id}/status', [CategoryController::class, 'status'])->name('categories.status');
  Route::delete('categorias/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

  // Special Categories (sin Admin)
  Route::get('categorias-especiales', [SpecialCategoryController::class, 'index'])->name('special-categories.index');
  Route::post('categorias-especiales', [SpecialCategoryController::class, 'store'])->name('special-categories.store');
  Route::put('categorias-especiales/{id}', [SpecialCategoryController::class, 'update'])->name('special-categories.update');
  Route::delete('categorias-especiales/{id}', [SpecialCategoryController::class, 'destroy'])->name('special-categories.destroy');
  Route::post('categorias-especiales/{id}/status', [SpecialCategoryController::class, 'status'])->name('special-categories.status');
  Route::get('categorias-especiales/{id}/detail', [SpecialCategoryController::class, 'detail'])->name('special-categories.detail');

  // Tags (sin Admin)
  Route::get('etiquetas', [TagController::class, 'index'])->name('tags.index');
  Route::post('etiquetas', [TagController::class, 'store'])->name('tags.store');
  Route::put('etiquetas/{id}', [TagController::class, 'update'])->name('tags.update');
  Route::post('etiquetas/{id}/status', [TagController::class, 'status'])->name('tags.status');
  Route::delete('etiquetas/{id}', [TagController::class, 'destroy'])->name('tags.destroy');
  // Reports - Sales (sin Admin)
  Route::get('reports/sales', [ReportController::class, 'index'])->defaults('report', 'sales')->name('reports.sales.index');
  Route::get('reports/sales/data/{type}/{from}/{to}', [ReportController::class, 'purchases'])->name('reports.sales.data');

  // Reports - Orders (sin Admin)
  Route::get('reports/orders', [ReportController::class, 'index'])->defaults('report', 'orders')->name('reports.orders.index');
  Route::get('reports/orders/data/{from}/{to}', [ReportController::class, 'orders'])->name('reports.orders.data');

  // Reports - Top Selling Products (sin Admin)
  Route::get('reports/top-products', [ReportController::class, 'index'])->defaults('report', 'top-products')->name('reports.top-products.index');
  Route::get('reports/top-products/data/{from}/{to}/{idProd?}', [ReportController::class, 'products'])->name('reports.top-products.data');

  // Reports - Top Selling Categories (sin Admin)
  Route::get('reports/top-categories', [ReportController::class, 'index'])->defaults('report', 'top-categories')->name('reports.top-categories.index');
  Route::get('reports/top-categories/data/{from}/{to}', [ReportController::class, 'categories'])->name('reports.top-categories.data');

  // Reports - Users Registered by Date (sin Admin)
  Route::get('reports/users-registered', [ReportController::class, 'index'])->defaults('report', 'users-registered')->name('reports.users-registered.index');
  Route::get('reports/users-registered/data/{from}/{to}', [ReportController::class, 'users'])->name('reports.users-registered.data');

  // Reports - Affiliates (sin Admin)
  Route::get('reports/affiliates', [ReportController::class, 'index'])->defaults('report', 'affiliates')->name('reports.affiliates.index');
  Route::get('reports/affiliates/data', [ReportController::class, 'referrals'])->name('reports.affiliates.data');

  // Reports - Waste History (sin Admin)
  Route::get('reports/waste-history', [ReportController::class, 'index'])->defaults('report', 'waste-history')->name('reports.waste-history.index');
  Route::get('reports/waste-history/data', [ReportController::class, 'mermafilter'])->name('reports.waste-history.data');

  // Reports - Profit & Share (sin Admin)
  Route::get('reports/profit-share', [ReportController::class, 'lucro'])->name('reports.profit-share.index');
  Route::get('reports/profit-share/data', [ReportController::class, 'lucrofilter'])->name('reports.profit-share.data');
  
  // Discounts
  Route::get('descuentos', [DiscountController::class, 'index'])->name('discounts.index');
  Route::get('descuentos/nuevo', [DiscountController::class, 'create'])->name('discounts.create');
  Route::post('descuentos', [DiscountController::class, 'store'])->name('discounts.store');
  Route::get('descuentos/{discount}/editar', [DiscountController::class, 'edit'])->name('discounts.edit');
  Route::get('descuentos/productos-data', [DiscountController::class, 'productsData'])->name('discounts.products.data');
  Route::put('descuentos/{discount}', [DiscountController::class, 'update'])->name('discounts.update');
  Route::delete('descuentos/{discount}', [DiscountController::class, 'destroy'])->name('discounts.destroy');
  Route::post('descuentos/{discount}/status', [DiscountController::class, 'status'])->name('discounts.status');
  
  // Offers
  Route::get('ofertas', [OfferController::class, 'index'])->name('offers.index');
  Route::get('ofertas/nuevo', [OfferController::class, 'create'])->name('offers.create');
  Route::get('ofertas/productos-data', [OfferController::class, 'productsData'])->name('offers.products.data');
  Route::post('ofertas', [OfferController::class, 'store'])->name('offers.store');
  Route::get('ofertas/{offer}/editar', [OfferController::class, 'edit'])->name('offers.edit');
  Route::put('ofertas/{offer}', [OfferController::class, 'update'])->name('offers.update');
  Route::delete('ofertas/{offer}', [OfferController::class, 'destroy'])->name('offers.destroy');
  Route::post('ofertas/{offer}/status', [OfferController::class, 'status'])->name('offers.status');

  // Banners
  Route::get('banners', [BannerController::class, 'index'])->name('banners.index');
  Route::post('banners/upload', [BannerController::class, 'upload'])->name('banners.upload');
  Route::delete('banners/{id}', [BannerController::class, 'destroy'])->name('banners.destroy');

  // About Us
  Route::get('about-us', [AboutUsController::class, 'index'])->name('about-us.index');
  Route::post('about-us', [AboutUsController::class, 'store'])->name('about-us.store');
  Route::put('about-us/{id}', [AboutUsController::class, 'update'])->name('about-us.update');

  // Contact
  Route::get('contact', [ContactController::class, 'index'])->name('contact.index');
  Route::get('contact/{id}/edit', [ContactController::class, 'edit'])->name('contact.edit');
  Route::put('contact/{id}', [ContactController::class, 'update'])->name('contact.update');

  // Terms & Conditions
  Route::get('terms-conditions', [TermsConditionsController::class, 'index'])->name('terms-conditions.index');
  Route::post('terms-conditions', [TermsConditionsController::class, 'store'])->name('terms-conditions.store');

  // Payment Gateway
  Route::get('payment-gateway', [PaymentGatewayController::class, 'index'])->name('payment-gateway.index');
  Route::get('payment-gateway/nuevo', [PaymentGatewayController::class, 'create'])->name('payment-gateway.create');
  Route::post('payment-gateway', [PaymentGatewayController::class, 'store'])->name('payment-gateway.store');
  Route::get('payment-gateway/{id}/edit', [PaymentGatewayController::class, 'edit'])->name('payment-gateway.edit');
  Route::put('payment-gateway/{id}', [PaymentGatewayController::class, 'update'])->name('payment-gateway.update');
  Route::delete('payment-gateway/{id}', [PaymentGatewayController::class, 'destroy'])->name('payment-gateway.destroy');
  Route::post('payment-gateway/{id}/status', [PaymentGatewayController::class, 'status'])->name('payment-gateway.status');
});

Auth::routes(['verify' => true]);

/* Route Dashboards */
Route::group(['prefix' => 'dashboard'], function () {
  Route::get('analytics', [DashboardController::class,'dashboardAnalytics'])->name('dashboard-analytics');
  Route::get('ecommerce', [DashboardController::class,'dashboardEcommerce'])->name('dashboard-ecommerce');
});
/* Route Dashboards */

/* Route Apps */
Route::group(['prefix' => 'app'], function () {
  Route::get('email', [AppsController::class,'emailApp'])->name('app-email');
  Route::get('chat', [AppsController::class,'chatApp'])->name('app-chat');
  Route::get('todo', [AppsController::class,'todoApp'])->name('app-todo');
  Route::get('calendar', [AppsController::class,'calendarApp'])->name('app-calendar');
  Route::get('kanban', [AppsController::class,'kanbanApp'])->name('app-kanban');
  Route::get('invoice/list', [AppsController::class,'invoice_list'])->name('app-invoice-list');
  Route::get('invoice/preview', [AppsController::class,'invoice_preview'])->name('app-invoice-preview');
  Route::get('invoice/edit', [AppsController::class,'invoice_edit'])->name('app-invoice-edit');
  Route::get('invoice/add', [AppsController::class,'invoice_add'])->name('app-invoice-add');
  Route::get('invoice/print', [AppsController::class,'invoice_print'])->name('app-invoice-print');
  Route::get('ecommerce/shop', [AppsController::class,'ecommerce_shop'])->name('app-ecommerce-shop');
  Route::get('ecommerce/details', [AppsController::class,'ecommerce_details'])->name('app-ecommerce-details');
  Route::get('ecommerce/wishlist', [AppsController::class,'ecommerce_wishlist'])->name('app-ecommerce-wishlist');
  Route::get('ecommerce/checkout', [AppsController::class,'ecommerce_checkout'])->name('app-ecommerce-checkout');
  Route::get('file-manager', [AppsController::class,'file_manager'])->name('app-file-manager');
  Route::get('user/list', [AppsController::class,'user_list'])->name('app-user-list');
  Route::get('user/view', [AppsController::class,'user_view'])->name('app-user-view');
  Route::get('user/edit', [AppsController::class,'user_edit'])->name('app-user-edit');
});
/* Route Apps */

/* Route UI */
Route::group(['prefix' => 'ui'], function () {
  Route::get('typography', [UserInterfaceController::class,'typography'])->name('ui-typography');
  Route::get('colors', [UserInterfaceController::class,'colors'])->name('ui-colors');
});
/* Route UI */

/* Route Icons */
Route::group(['prefix' => 'icons'], function () {
  Route::get('feather', [UserInterfaceController::class,'icons_feather'])->name('icons-feather');
});
/* Route Icons */

/* Route Cards */
Route::group(['prefix' => 'card'], function () {
  Route::get('basic', [CardsController::class,'card_basic'])->name('card-basic');
  Route::get('advance', [CardsController::class,'card_advance'])->name('card-advance');
  Route::get('statistics', [CardsController::class,'card_statistics'])->name('card-statistics');
  Route::get('analytics', [CardsController::class,'card_analytics'])->name('card-analytics');
  Route::get('actions', [CardsController::class,'card_actions'])->name('card-actions');
});
/* Route Cards */

/* Route Components */
Route::group(['prefix' => 'component'], function () {
  Route::get('alert', [ComponentsController::class,'alert'])->name('component-alert');
  Route::get('avatar', [ComponentsController::class,'avatar'])->name('component-avatar');
  Route::get('badges', [ComponentsController::class,'badges'])->name('component-badges');
  Route::get('breadcrumbs', [ComponentsController::class,'breadcrumbs'])->name('component-breadcrumbs');
  Route::get('buttons', [ComponentsController::class,'buttons'])->name('component-buttons');
  Route::get('carousel', [ComponentsController::class,'carousel'])->name('component-carousel');
  Route::get('collapse', [ComponentsController::class,'collapse'])->name('component-collapse');
  Route::get('divider', [ComponentsController::class,'divider'])->name('component-divider');
  Route::get('dropdowns', [ComponentsController::class,'dropdowns'])->name('component-dropdowns');
  Route::get('list-group', [ComponentsController::class,'list_group'])->name('component-list-group');
  Route::get('modals', [ComponentsController::class,'modals'])->name('component-modals');
  Route::get('pagination', [ComponentsController::class,'pagination'])->name('component-pagination');
  Route::get('navs', [ComponentsController::class,'navs'])->name('component-navs');
  Route::get('tabs', [ComponentsController::class,'tabs'])->name('component-tabs');
  Route::get('timeline', [ComponentsController::class,'timeline'])->name('component-timeline');
  Route::get('pills', [ComponentsController::class,'pills'])->name('component-pills');
  Route::get('tooltips', [ComponentsController::class,'tooltips'])->name('component-tooltips');
  Route::get('popovers', [ComponentsController::class,'popovers'])->name('component-popovers');
  Route::get('pill-badges', [ComponentsController::class,'pill_badges'])->name('component-pill-badges');
  Route::get('progress', [ComponentsController::class,'progress'])->name('component-progress');
  Route::get('media-objects', [ComponentsController::class,'media_objects'])->name('component-media-objects');
  Route::get('spinner', [ComponentsController::class,'spinner'])->name('component-spinner');
  Route::get('toast', [ComponentsController::class,'toast'])->name('component-toast');
});
/* Route Components */

/* Route Extensions */
Route::group(['prefix' => 'ext-component'], function () {
  Route::get('sweet-alerts', [ExtensionController::class,'sweet_alert'])->name('ext-component-sweet-alerts');
  Route::get('block-ui', [ExtensionController::class,'block_ui'])->name('ext-component-block-ui');
  Route::get('toastr', [ExtensionController::class,'toastr'])->name('ext-component-toastr');
  Route::get('slider', [ExtensionController::class,'slider'])->name('ext-component-slider');
  Route::get('drag-drop', [ExtensionController::class,'drag_drop'])->name('ext-component-drag-drop');
  Route::get('tour', [ExtensionController::class,'tour'])->name('ext-component-tour');
  Route::get('clipboard', [ExtensionController::class,'clipboard'])->name('ext-component-clipboard');
  Route::get('plyr', [ExtensionController::class,'plyr'])->name('ext-component-plyr');
  Route::get('context-menu', [ExtensionController::class,'context_menu'])->name('ext-component-context-menu');
  Route::get('swiper', [ExtensionController::class,'swiper'])->name('ext-component-swiper');
  Route::get('tree', [ExtensionController::class,'tree'])->name('ext-component-tree');
  Route::get('ratings', [ExtensionController::class,'ratings'])->name('ext-component-ratings');
  Route::get('locale', [ExtensionController::class,'locale'])->name('ext-component-locale');
});
/* Route Extensions */

/* Route Page Layouts */
Route::group(['prefix' => 'page-layouts'], function () {
  Route::get('collapsed-menu', [PageLayoutController::class,'layout_collapsed_menu'])->name('layout-collapsed-menu');
  Route::get('boxed', [PageLayoutController::class,'layout_boxed'])->name('layout-boxed');
  Route::get('without-menu', [PageLayoutController::class,'layout_without_menu'])->name('layout-without-menu');
  Route::get('empty', [PageLayoutController::class,'layout_empty'])->name('layout-empty');
  Route::get('blank', [PageLayoutController::class,'layout_blank'])->name('layout-blank');
});
/* Route Page Layouts */

/* Route Forms */
Route::group(['prefix' => 'form'], function () {
  Route::get('input', [FormsController::class,'input'])->name('form-input');
  Route::get('input-groups', [FormsController::class,'input_groups'])->name('form-input-groups');
  Route::get('input-mask', [FormsController::class,'input_mask'])->name('form-input-mask');
  Route::get('textarea', [FormsController::class,'textarea'])->name('form-textarea');
  Route::get('checkbox', [FormsController::class,'checkbox'])->name('form-checkbox');
  Route::get('radio', [FormsController::class,'radio'])->name('form-radio');
  Route::get('switch', [FormsController::class,'switch'])->name('form-switch');
  Route::get('select', [FormsController::class,'select'])->name('form-select');
  Route::get('number-input', [FormsController::class,'number_input'])->name('form-number-input');
  Route::get('file-uploader', [FormsController::class,'file_uploader'])->name('form-file-uploader');
  Route::get('quill-editor', [FormsController::class,'quill_editor'])->name('form-quill-editor');
  Route::get('date-time-picker', [FormsController::class,'date_time_picker'])->name('form-date-time-picker');
  Route::get('layout', [FormsController::class,'layouts'])->name('form-layout');
  Route::get('wizard', [FormsController::class,'wizard'])->name('form-wizard');
  Route::get('validation', [FormsController::class,'validation'])->name('form-validation');
  Route::get('repeater', [FormsController::class,'form_repeater'])->name('form-repeater');
});
/* Route Forms */

/* Route Tables */
Route::group(['prefix' => 'table'], function () {
  Route::get('', [TableController::class,'table'])->name('table');
  Route::get('datatable/basic', [TableController::class,'datatable_basic'])->name('datatable-basic');
  Route::get('datatable/advance', [TableController::class,'datatable_advance'])->name('datatable-advance');
  Route::get('ag-grid', [TableController::class,'ag_grid'])->name('ag-grid');
});
/* Route Tables */

/* Route Pages */
Route::group(['prefix' => 'page'], function () {
  Route::get('account-settings', [PagesController::class,'account_settings'])->name('page-account-settings');
  Route::get('profile', [PagesController::class,'profile'])->name('page-profile');
  Route::get('faq', [PagesController::class,'faq'])->name('page-faq');
  Route::get('knowledge-base', [PagesController::class,'knowledge_base'])->name('page-knowledge-base');
  Route::get('knowledge-base/category', [PagesController::class,'kb_category'])->name('page-knowledge-base-category');
  Route::get('knowledge-base/category/question', [PagesController::class,'kb_question'])->name('page-knowledge-base-question');
  Route::get('pricing', [PagesController::class,'pricing'])->name('page-pricing');
  Route::get('blog/list', [PagesController::class,'blog_list'])->name('page-blog-list');
  Route::get('blog/detail', [PagesController::class,'blog_detail'])->name('page-blog-detail');
  Route::get('blog/edit', [PagesController::class,'blog_edit'])->name('page-blog-edit');

  // Miscellaneous Pages With Page Prefix
  Route::get('coming-soon', [MiscellaneousController::class,'coming_soon'])->name('misc-coming-soon');
  Route::get('not-authorized', [MiscellaneousController::class,'not_authorized'])->name('misc-not-authorized');
  Route::get('maintenance', [MiscellaneousController::class,'maintenance'])->name('misc-maintenance');
});
/* Route Pages */
Route::get('/error', [MiscellaneousController::class,'error'])->name('error');

/* Route Authentication Pages */
Route::group(['prefix' => 'auth'], function () {
  Route::get('login-v1', [AuthenticationController::class,'login_v1'])->name('auth-login-v1');
  Route::get('login-v2', [AuthenticationController::class,'login_v2'])->name('auth-login-v2');
  Route::get('register-v1', [AuthenticationController::class,'register_v1'])->name('auth-register-v1');
  Route::get('register-v2', [AuthenticationController::class,'register_v2'])->name('auth-register-v2');
  Route::get('forgot-password-v1', [AuthenticationController::class,'forgot_password_v1'])->name('auth-forgot-password-v1');
  Route::get('forgot-password-v2', [AuthenticationController::class,'forgot_password_v2'])->name('auth-forgot-password-v2');
  Route::get('reset-password-v1', [AuthenticationController::class,'reset_password_v1'])->name('auth-reset-password-v1');
  Route::get('reset-password-v2', [AuthenticationController::class,'reset_password_v2'])->name('auth-reset-password-v2');
  Route::get('lock-screen', [AuthenticationController::class,'lock_screen'])->name('auth-lock_screen');
});
/* Route Authentication Pages */

/* Route Charts */
Route::group(['prefix' => 'chart'], function () {
  Route::get('apex', [ChartsController::class,'apex'])->name('chart-apex');
  Route::get('chartjs', [ChartsController::class,'chartjs'])->name('chart-chartjs');
  Route::get('echarts', [ChartsController::class,'echarts'])->name('chart-echarts');
});
/* Route Charts */

// map leaflet
Route::get('/maps/leaflet', [ChartsController::class,'maps_leaflet'])->name('map-leaflet');

// locale Route
Route::get('lang/{locale}', [LanguageController::class, 'swap']);


