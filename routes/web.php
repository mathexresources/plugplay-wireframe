<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// ---------- storefront ----------
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/auto', [CarController::class, 'select'])->name('car.select');
Route::post('/auto/zrusit', [CarController::class, 'clear'])->name('car.clear');
Route::get('/auto/{slug}', [CarController::class, 'show'])->name('car.show');

Route::get('/katalog/{category?}', [CatalogController::class, 'index'])->name('catalog');
Route::get('/hledat', [CatalogController::class, 'search'])->name('search');
Route::get('/produkt/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::post('/produkt/{product}/recenze', [ProductController::class, 'review'])->name('product.review')->middleware('throttle:10,1');

Route::get('/plan', [CartController::class, 'index'])->name('cart');
Route::post('/plan/pridat', [CartController::class, 'add'])->name('cart.add');
Route::post('/plan/sada/{bundle}', [CartController::class, 'addBundle'])->name('cart.bundle');
Route::post('/plan/naradi', [CartController::class, 'addTools'])->name('cart.tools');
Route::post('/plan/upravit', [CartController::class, 'update'])->name('cart.update');
Route::post('/plan/kupon', [CartController::class, 'coupon'])->name('cart.coupon');
Route::post('/plan/ulozit', [CartController::class, 'save'])->name('cart.save');
Route::get('/plan/sdileny/{token}', [CartController::class, 'shared'])->name('cart.shared');
Route::post('/plan/sdileny/{token}', [CartController::class, 'loadShared'])->name('cart.loadShared');

Route::get('/objednavka', [CheckoutController::class, 'show'])->name('checkout');
Route::post('/objednavka', [CheckoutController::class, 'store'])->name('checkout.store')->middleware('throttle:20,1');
Route::get('/objednavka/{order}/platba', [CheckoutController::class, 'gateway'])->name('checkout.gateway');
Route::get('/objednavka/{order}/hotovo', [CheckoutController::class, 'done'])->name('checkout.done');
Route::post('/objednavka/{order}/ucet', [CheckoutController::class, 'createAccount'])->name('checkout.account');

Route::get('/navody', [GuideController::class, 'index'])->name('guides');
Route::get('/navody/{slug}', [GuideController::class, 'show'])->name('guides.show');
Route::post('/navody/{guide}/postup', [GuideController::class, 'progress'])->name('guides.progress')->middleware('auth');

Route::get('/info/{slug}', [PageController::class, 'show'])->name('page');
Route::get('/kontakt', [ContactController::class, 'form'])->name('contact');
Route::post('/kontakt', [ContactController::class, 'send'])->name('contact.send')->middleware('throttle:5,1');
Route::get('/promeny', [ContactController::class, 'showcases'])->name('showcases');
Route::post('/promeny', [ContactController::class, 'submitShowcase'])->name('showcases.submit')->middleware('throttle:5,1');

// ---------- auth ----------
Route::middleware('guest')->group(function () {
    Route::get('/prihlaseni', [AuthController::class, 'form'])->name('login');
    Route::post('/prihlaseni', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:10,1');
    Route::post('/registrace', [AuthController::class, 'register'])->name('register')->middleware('throttle:10,1');
    Route::get('/zapomenute-heslo', [AuthController::class, 'forgotForm'])->name('password.request');
    Route::post('/zapomenute-heslo', [AuthController::class, 'forgotSend'])->name('password.email')->middleware('throttle:5,1');
    Route::get('/nove-heslo/{token}', [AuthController::class, 'resetForm'])->name('password.reset');
    Route::post('/nove-heslo', [AuthController::class, 'reset'])->name('password.update');
});
Route::post('/odhlasit', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ---------- customer account ----------
Route::middleware('auth')->prefix('ucet')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'garage'])->name('garage');
    Route::post('/auta', [AccountController::class, 'storeCar'])->name('cars.store');
    Route::put('/auta/{car}', [AccountController::class, 'updateCar'])->name('cars.update');
    Route::delete('/auta/{car}', [AccountController::class, 'deleteCar'])->name('cars.delete');
    Route::post('/auta/{car}/aktivni', [AccountController::class, 'activateCar'])->name('cars.activate');
    Route::post('/auta/{car}/upgrady/{upgrade}', [AccountController::class, 'toggleUpgrade'])->name('cars.upgrade');
    Route::get('/objednavky', [AccountController::class, 'orders'])->name('orders');
    Route::get('/objednavky/{order}', [AccountController::class, 'order'])->name('order');
    Route::get('/navody', [AccountController::class, 'guides'])->name('guides');
    Route::get('/plany', [AccountController::class, 'plans'])->name('plans');
    Route::post('/plany/{plan}/otevrit', [AccountController::class, 'openPlan'])->name('plans.open');
    Route::delete('/plany/{plan}', [AccountController::class, 'deletePlan'])->name('plans.delete');
    Route::get('/udaje', [AccountController::class, 'profile'])->name('profile');
    Route::put('/udaje', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::put('/heslo', [AccountController::class, 'updatePassword'])->name('password');
    Route::post('/adresy', [AccountController::class, 'storeAddress'])->name('addresses.store');
    Route::delete('/adresy/{address}', [AccountController::class, 'deleteAddress'])->name('addresses.delete');
    Route::delete('/zrusit-ucet', [AccountController::class, 'destroy'])->name('destroy');
});

// ---------- administration ----------
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('produkty', Admin\ProductController::class)->parameters(['produkty' => 'product'])->names('products')->except('show');
    Route::resource('kategorie', Admin\CategoryController::class)->parameters(['kategorie' => 'category'])->names('categories')->except('show');
    Route::resource('auta', Admin\CarModelController::class)->parameters(['auta' => 'carModel'])->names('cars')->except('show');
    Route::resource('zony', Admin\ZoneController::class)->parameters(['zony' => 'zone'])->names('zones')->except('show');
    Route::resource('sady', Admin\BundleController::class)->parameters(['sady' => 'bundle'])->names('bundles')->except('show');
    Route::resource('navody', Admin\GuideController::class)->parameters(['navody' => 'guide'])->names('guides')->except('show');
    Route::resource('naradi', Admin\ToolController::class)->parameters(['naradi' => 'tool'])->names('tools')->except('show');
    Route::resource('kupony', Admin\CouponController::class)->parameters(['kupony' => 'coupon'])->names('coupons')->except('show');
    Route::resource('stranky', Admin\PageController::class)->parameters(['stranky' => 'page'])->names('pages')->except('show');
    Route::resource('doprava', Admin\ShippingController::class)->parameters(['doprava' => 'shippingMethod'])->names('shipping')->except('show');
    Route::resource('platby', Admin\PaymentController::class)->parameters(['platby' => 'paymentMethod'])->names('payments')->except(['show', 'create', 'store', 'destroy']);

    Route::get('/objednavky', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/objednavky/export', [Admin\OrderController::class, 'export'])->name('orders.export');
    Route::get('/objednavky/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::put('/objednavky/{order}', [Admin\OrderController::class, 'update'])->name('orders.update');
    Route::delete('/objednavky/{order}', [Admin\OrderController::class, 'destroy'])->name('orders.destroy');

    Route::get('/zakaznici', [Admin\CustomerController::class, 'index'])->name('customers.index');
    Route::get('/zakaznici/{user}', [Admin\CustomerController::class, 'show'])->name('customers.show');
    Route::post('/zakaznici/{user}/admin', [Admin\CustomerController::class, 'toggleAdmin'])->name('customers.admin');
    Route::delete('/zakaznici/{user}', [Admin\CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('/recenze', [Admin\ModerationController::class, 'reviews'])->name('reviews.index');
    Route::post('/recenze/{review}', [Admin\ModerationController::class, 'toggleReview'])->name('reviews.toggle');
    Route::delete('/recenze/{review}', [Admin\ModerationController::class, 'deleteReview'])->name('reviews.destroy');
    Route::get('/promeny', [Admin\ModerationController::class, 'showcases'])->name('showcases.index');
    Route::post('/promeny/{showcase}', [Admin\ModerationController::class, 'toggleShowcase'])->name('showcases.toggle');
    Route::delete('/promeny/{showcase}', [Admin\ModerationController::class, 'deleteShowcase'])->name('showcases.destroy');
    Route::get('/zpravy', [Admin\ModerationController::class, 'messages'])->name('messages.index');
    Route::get('/zpravy/{message}', [Admin\ModerationController::class, 'message'])->name('messages.show');
    Route::delete('/zpravy/{message}', [Admin\ModerationController::class, 'deleteMessage'])->name('messages.destroy');

    Route::get('/nastaveni', [Admin\SettingController::class, 'edit'])->name('settings');
    Route::put('/nastaveni', [Admin\SettingController::class, 'update'])->name('settings.update');

    // Heureka-style XML feed preview (also linked publicly below)
});
Route::get('/feed/heureka.xml', [Admin\FeedController::class, 'heureka'])->name('feed.heureka');
