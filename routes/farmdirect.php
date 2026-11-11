<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Client\DashboardController as ClientDashboard;
use App\Http\Controllers\Farmer\FarmerController;
use App\Http\Controllers\Manager\ManagerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FarmerFollowController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\AccountVerificationController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureAccountVerified;
use App\Support\DemoData as D;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
 | FarmDirect – frontend stage routes (demo data, no database yet).
 | Include from routes/web.php:   require __DIR__.'/farmdirect.php';
 */

// ---- Public pages (real data from the database) ----
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/products', [PageController::class, 'products'])->name('products');
Route::get('/search', [PageController::class, 'search'])->name('search');
Route::get('/products/{id}', [PageController::class, 'show'])->whereNumber('id')->name('product.show');
Route::get('/shop-by-district', [PageController::class, 'districts'])->name('districts');
Route::get('/farmers', [PageController::class, 'farmers'])->name('farmers');
Route::get('/farmers/{id}', [PageController::class, 'farmer'])->whereNumber('id')->name('farmer.show');
Route::post('/farmers/{id}/follow', [FarmerFollowController::class, 'toggle'])->whereNumber('id')
    ->middleware(['auth', EnsureRole::class . ':client'])->name('farmer.follow');
Route::view('/about', 'pages.about')->name('about');
Route::view('/contact', 'pages.contact')->name('contact');
Route::post('/contact', [PageController::class, 'storeContact'])->middleware('throttle:6,1')->name('contact.store');

// ---- Login, sign up, logout (real accounts) ----
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.attempt');
    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
    Route::get('/signup', [RegisterController::class, 'createClient'])->name('signup');
    Route::post('/signup', [RegisterController::class, 'store'])->name('signup.store');
    Route::get('/register-as-farmer', fn () => redirect()->route('signup', ['type' => 'farmer']))->name('register.farmer');
});
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::middleware(['auth', EnsureRole::class . ':client,farmer'])->group(function () {
    Route::get('/account/verification', [AccountVerificationController::class, 'show'])->name('account.verification');
    Route::post('/account/verification', [AccountVerificationController::class, 'submit'])->middleware('throttle:5,10')->name('account.verification.submit');
    Route::post('/account/verification/withdraw', [AccountVerificationController::class, 'withdraw'])->name('account.verification.withdraw');
});
Route::middleware(['auth', EnsureRole::class . ':client,farmer,manager,admin'])->prefix('messages')->group(function () {
    Route::get('/', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/unread-count', [MessageController::class, 'unreadCount'])->name('messages.unread-count');
    Route::get('/{id}/updates', [MessageController::class, 'updates'])->whereNumber('id')->name('messages.updates');
    Route::get('/{id}', [MessageController::class, 'show'])->whereNumber('id')->name('messages.show');
    Route::post('/{id}', [MessageController::class, 'send'])->whereNumber('id')->middleware('throttle:30,1')->name('messages.send');
});
Route::post('/messages/farmer/{id}', [MessageController::class, 'startFarmer'])->whereNumber('id')
    ->middleware(['auth', EnsureRole::class . ':client', 'throttle:10,1'])->name('messages.farmer');
Route::post('/messages/support', [MessageController::class, 'startSupport'])
    ->middleware(['auth', EnsureRole::class . ':client,farmer', 'throttle:10,1'])->name('messages.support');
Route::middleware('auth')->prefix('notifications')->group(function () {
    Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all');
    Route::post('/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
});

// ---- Cart and checkout ----
Route::get('/cart', [CartController::class, 'show'])->name('cart');
// Guests who press "Place Order" are sent here, asked to log in, then returned to the cart
Route::get('/checkout', fn () => redirect()->route('cart'))->middleware('auth')->name('checkout');
Route::post('/orders', [OrderController::class, 'store'])->middleware(['auth', EnsureRole::class . ':client', EnsureAccountVerified::class, 'throttle:20,1'])->name('orders.store');

// ---- Client area ----
Route::middleware(['auth', EnsureRole::class . ':client'])->group(function () {
    Route::get('/orders/{no}/success', [OrderController::class, 'success'])->name('order.success');
    Route::get('/review', [ReviewController::class, 'create'])->name('review.create');
    Route::post('/review', [ReviewController::class, 'store'])->name('review.store');
});

Route::prefix('client')->middleware(['auth', EnsureRole::class . ':client'])->group(function () {
    Route::get('/dashboard', [ClientDashboard::class, 'dashboard'])->name('client.dashboard');
    Route::get('/profile', [ClientDashboard::class, 'profile'])->name('client.profile');
    Route::post('/profile', [ClientDashboard::class, 'updateProfile'])->name('client.profile.update');
    Route::post('/password', [ClientDashboard::class, 'updatePassword'])->name('client.password.update');
    Route::get('/orders', [ClientDashboard::class, 'orders'])->name('client.orders');
    Route::get('/track/{no?}', [ClientDashboard::class, 'track'])->name('client.track');
    Route::post('/orders/{no}/cancel', [ClientDashboard::class, 'cancel'])->name('client.orders.cancel');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('client.notifications');
});

// ---- Farmer area (real data) ----
Route::prefix('farmer')->middleware(['auth', EnsureRole::class . ':farmer'])->group(function () {
    Route::get('/dashboard', [FarmerController::class, 'dashboard'])->name('farmer.dashboard');
    Route::get('/products', [FarmerController::class, 'products'])->name('farmer.products');
    Route::get('/products/create', [FarmerController::class, 'create'])->middleware(EnsureAccountVerified::class)->name('farmer.products.create');
    Route::post('/products', [FarmerController::class, 'store'])->middleware(EnsureAccountVerified::class)->name('farmer.products.store');
    Route::get('/products/{id}/edit', [FarmerController::class, 'edit'])->whereNumber('id')->middleware(EnsureAccountVerified::class)->name('farmer.products.edit');
    Route::post('/products/{id}', [FarmerController::class, 'update'])->whereNumber('id')->middleware(EnsureAccountVerified::class)->name('farmer.products.update');
    Route::post('/products/{id}/stock', [FarmerController::class, 'stock'])->whereNumber('id')->name('farmer.products.stock');
    Route::post('/products/{id}/delete', [FarmerController::class, 'destroy'])->whereNumber('id')->name('farmer.products.delete');
    Route::get('/orders', [FarmerController::class, 'orders'])->name('farmer.orders');
    Route::get('/tracking/{id?}', [FarmerController::class, 'tracking'])->whereNumber('id')->name('farmer.tracking');
    Route::get('/earnings', [FarmerController::class, 'earnings'])->name('farmer.earnings');
    Route::get('/location', [FarmerController::class, 'location'])->name('farmer.location');
    Route::post('/location', [FarmerController::class, 'saveLocation'])->name('farmer.location.save');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('farmer.notifications');
    Route::get('/profile', [FarmerController::class, 'profile'])->name('farmer.profile');
    Route::post('/profile', [FarmerController::class, 'saveProfile'])->name('farmer.profile.save');
    Route::post('/password', [FarmerController::class, 'savePassword'])->name('farmer.password.save');
});

// ---- Manager area (real data) ----
Route::prefix('manager')->middleware(['auth', EnsureRole::class . ':manager'])->group(function () {
    Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('manager.dashboard');
    Route::get('/approvals', [ManagerController::class, 'approvals'])->name('manager.approvals');
    Route::post('/approvals/{id}/approve', [ManagerController::class, 'approve'])->whereNumber('id')->name('manager.approvals.approve');
    Route::post('/approvals/{id}/reject', [ManagerController::class, 'reject'])->whereNumber('id')->name('manager.approvals.reject');
    Route::get('/inventory', [ManagerController::class, 'inventory'])->name('manager.inventory');
    Route::post('/inventory/products', [ManagerController::class, 'storeProduct'])->name('manager.inventory.store');
    Route::post('/inventory/{id}/stock', [ManagerController::class, 'stock'])->whereNumber('id')->name('manager.inventory.stock');
    Route::get('/orders', [ManagerController::class, 'orders'])->name('manager.orders');
    Route::get('/orders/{no}', [ManagerController::class, 'orderShow'])->name('manager.orders.show');
    Route::post('/orders/{no}/status', [ManagerController::class, 'orderStatus'])->name('manager.orders.status');
    Route::get('/deliveries', [ManagerController::class, 'deliveries'])->name('manager.deliveries');
    Route::post('/deliveries/assign', [ManagerController::class, 'assign'])->name('manager.deliveries.assign');
    Route::post('/deliveries/men', [ManagerController::class, 'addDeliveryMan'])->name('manager.deliveries.men');
    Route::get('/warehouse', [ManagerController::class, 'warehouse'])->name('manager.warehouse');
    Route::post('/warehouse/receive', [ManagerController::class, 'receive'])->name('manager.warehouse.receive');
    Route::post('/warehouse/transfer', [ManagerController::class, 'transfer'])->name('manager.warehouse.transfer');
    Route::post('/warehouse', [ManagerController::class, 'addWarehouse'])->name('manager.warehouse.store');
    Route::get('/reports', [ManagerController::class, 'reports'])->name('manager.reports');
    Route::get('/reports/inventory.csv', [ManagerController::class, 'exportCsv'])->name('manager.reports.export');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('manager.notifications');
    Route::get('/profile', [ManagerController::class, 'profile'])->name('manager.profile');
    Route::post('/profile', [ManagerController::class, 'saveProfile'])->name('manager.profile.save');
    Route::post('/password', [ManagerController::class, 'savePassword'])->name('manager.password.save');
});

// ---- Admin dashboard ----
Route::prefix('admin')->middleware(['auth', EnsureRole::class . ':admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users/{id}/status', [AdminController::class, 'toggleUser'])->whereNumber('id')->name('admin.users.status');
    Route::post('/users/{id}/role', [AdminController::class, 'updateRole'])->whereNumber('id')->name('admin.users.role');
    Route::get('/users/{id}/verification/{kind}', [AccountVerificationController::class, 'document'])->whereNumber('id')->name('admin.users.verification.document');
    Route::post('/users/{id}/verification/approve', [AdminController::class, 'approveVerification'])->whereNumber('id')->name('admin.users.verification.approve');
    Route::post('/users/{id}/verification/reject', [AdminController::class, 'rejectVerification'])->whereNumber('id')->name('admin.users.verification.reject');
    Route::post('/farmers/{id}/approve', [AdminController::class, 'approveFarmer'])->whereNumber('id')->name('admin.farmers.approve');
    Route::post('/farmers/{id}/reject', [AdminController::class, 'rejectFarmer'])->whereNumber('id')->name('admin.farmers.reject');
    Route::get('/messages', [AdminController::class, 'messages'])->name('admin.messages');
    Route::post('/messages/{id}/read', [AdminController::class, 'markMessageRead'])->whereNumber('id')->name('admin.messages.read');
    Route::post('/messages/{id}/reply', [AdminController::class, 'replyToMessage'])->whereNumber('id')->name('admin.messages.reply');
    Route::view('/roles', 'admin.roles')->name('admin.roles');
    Route::get('/categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('admin.categories.store');
    Route::post('/categories/{id}', [AdminController::class, 'updateCategory'])->whereNumber('id')->name('admin.categories.update');
    Route::post('/categories/{id}/status', [AdminController::class, 'toggleCategory'])->whereNumber('id')->name('admin.categories.status');
    Route::get('/transactions', [AdminController::class, 'transactions'])->name('admin.transactions');
    Route::post('/transactions/{id}/approve', [AdminController::class, 'approvePayment'])->whereNumber('id')->name('admin.transactions.approve');
    Route::post('/transactions/{id}/reject', [AdminController::class, 'rejectPayment'])->whereNumber('id')->name('admin.transactions.reject');
    Route::get('/transactions/export.csv', [AdminController::class, 'exportTransactions'])->name('admin.transactions.export');
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/reports/export.csv', [AdminController::class, 'exportReport'])->name('admin.reports.export');
    Route::get('/backup', [AdminController::class, 'backup'])->name('admin.backup');
    Route::get('/backup/export.json', [AdminController::class, 'exportData'])->name('admin.backup.export');
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/settings', [AdminController::class, 'saveSettings'])->name('admin.settings.save');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('admin.notifications');
    Route::get('/profile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::post('/profile', [AdminController::class, 'saveProfile'])->name('admin.profile.save');
    Route::post('/password', [AdminController::class, 'savePassword'])->name('admin.password.save');
});
