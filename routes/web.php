<?php

use App\Http\Controllers\{
    BillController,
    DeveloperController,
    HomeController,
    PaymentController,
    SudoController,
    SudoWebhookController,
    SudoCardController,
    UserController, SupportController,
    InvoiceController
    
};
use App\Http\Controllers\Auth\{
    LoginController,
    RegisterController
};
use App\Http\Controllers\Back\AdminController;
use Illuminate\Support\Facades\Route;

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

/* Sudo Africa Routes */
Route::middleware(['auth'])->group(function () {
    Route::get('/user/create-card', [SudoCardController::class, 'create'])->name('sudo.card.create');
Route::get('/user/card-transactions', [SudoController::class, 'indexCardTransactions'])->middleware('auth') ->name('sudo.card.transactions.index');
Route::get('/user/card-transactions/{card_id}', [SudoController::class, 'fetchCardTransactions'])->name('sudo.cardTransactions.user');


});

Route::prefix('sudo')->middleware('auth')->group(function () {
    Route::get('/card/create', [SudoCardController::class, 'create'])->name('sudo.card.create');
});

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/reports/swap', [AdminController::class, 'swapReport'])->name('admin.reports.swap');
    // Other admin routes...
    Route::get('/users', [AdminController::class, 'indexUsers'])->name('admin.users.index');
     Route::get('/users/{user}', [AdminController::class, 'viewUser'])->name('admin.users.view');


});


// Route::get('/user/create-card', [SudoController::class, 'createCardForm'])->name('sudo.createCardForm');
Route::post('/user/create-card', [SudoController::class, 'createCard'])->name('sudo.createCard');
Route::post('/sudo/create-card', [SudoController::class, 'createCard'])->name('sudo.createCard.admin');
Route::post('/sudo/card/{id}/{action}', [SudoController::class, 'toggleCardStatus'])->name('sudo.toggleCard');
Route::delete('/sudo/card/{id}', [SudoController::class, 'terminateCard'])->name('sudo.terminateCard');
Route::get('/sudo/card/{id}/transactions', [SudoController::class, 'fetchCardTransactions'])->name('sudo.cardTransactions.sudo');
Route::post('/sudo/complete-kyc', [SudoController::class, 'completeKyc'])->name('sudo.kyc');
Route::post('/sudo/webhook', [SudoWebhookController::class, 'handle'])->name('sudo.webhook');
Route::get('/admin/cards', [SudoController::class, 'viewCards'])->name('sudo.admin.viewCards');
Route::post('/admin/card/{id}/{action}', [SudoController::class, 'toggleCardStatus'])->name('sudo.admin.toggleCard');
Route::delete('/admin/card/{id}', [SudoController::class, 'terminateCard'])->name('sudo.admin.terminateCard');


use App\Http\Controllers\CryptoController;
use App\Http\Controllers\AdminTransactionController;

Route::get('/buy-crypto', [CryptoController::class, 'showBuyForm'])->name('crypto.buy.form'); // Viewing the buy form

Route::post('/buy-crypto', [CryptoController::class, 'processPurchase'])->name('crypto.purchase'); // Processing the purchase

Route::get('/user/transactions', [CryptoController::class, 'userTransactions'])->name('crypto.user.transactions')->middleware('auth'); // User transactions page

Route::get('/wallet-address/{crypto}', [CryptoController::class, 'getWalletAddress'])->name('crypto.wallet.address'); // Wallet address

Route::middleware(['auth', 'admin'])->group(function () {
    // Admin routes for managing transactions
    Route::get('/admin/transactions', [AdminTransactionController::class, 'index'])->name('admin.transactions');
    Route::post('/admin/transactions/{id}/approve', [CryptoController::class, 'approveTransaction'])->name('crypto.admin.transactions.approve');
    Route::post('/admin/transactions/{id}/reject', [CryptoController::class, 'rejectTransaction'])->name('crypto.admin.transactions.reject');
});

use App\Http\Controllers\AdminWalletController;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/crypto/wallets', [AdminWalletController::class, 'index'])->name('admin.crypto.wallets');
});


// Admin Routes (Auth and Admin role required)
Route::middleware(['auth', 'admin'])->group(function () {
    // Admin route to view all crypto transactions
    Route::get('/admin/crypto-transactions', [CryptoController::class, 'adminTransactions'])->name('admin.crypto.transactions');
    
    // Admin route to approve crypto transaction
    Route::post('/admin/approve-crypto/{id}', [CryptoController::class, 'approveTransaction'])->name('admin.crypto.approve');
    
    // Admin route to reject crypto transaction
    Route::post('/admin/reject-crypto/{id}', [CryptoController::class, 'rejectTransaction'])->name('admin.crypto.reject');
    
    // Admin route to manage wallet addresses for different cryptocurrencies
    Route::get('/admin/wallets', [AdminWalletController::class, 'index'])->name('admin.wallets');
    
    // Admin route to update wallet address for a specific cryptocurrency
    Route::post('/admin/wallets/update', [AdminWalletController::class, 'update'])->name('admin.wallets.update');
    
Route::get('/admin/crypto/history', [CryptoController::class, 'history'])->name('admin.crypto.history');

});



Auth::routes(['verify' => true]);
Route::get('/password-reset-successful', function () {
    return view('auth.resetsuccess');
})->name('password.reset.success');


Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('invoice/{id}', [InvoiceController::class, 'show'])->name('invoice.show');
Route::post('invoice', [InvoiceController::class, 'store'])->name('invoice.store');

Auth::routes(['verify' => true]);
Route::get('admin/login', [AdminController::class, 'login'])->name('admin.login')->middleware('guest');
// Auth pages
Route::controller(LoginController::class)->middleware('maintenance')->group(function(){
    Route::get('/login', 'user_login')->name('login');
    Route::get('/user/login', 'user_login')->name('user.login');
    Route::post('/user/login', 'submit_login')->name('user.login');
});
// Register
Route::controller(RegisterController::class)->middleware('maintenance')->group(function(){
    Route::get('/register', 'user_register')->name('register');
    Route::get('/user/register', 'user_register')->name('user.register');
    Route::post('/user/register', 'register')->name('user.register');
});
Route::controller(HomeController::class)->middleware('maintenance')->group(function(){
    Route::get('/', 'index')->name('index');
    Route::get('/home', 'home')->name('home');
    Route::get('/logout', 'logout')->name('logout');
    Route::get('/policy', 'policy')->name('policy');
      Route::get('/about', 'about')->name('about');
    Route::get('/terms', 'terms')->name('terms');
    Route::get('/page/{slug}', 'pages')->name('page');
    Route::get('/contact', 'contact_us')->name('contact');
    Route::post('/contact', 'send_contact')->name('contact');
    Route::get('/services', 'services')->middleware('user')->name('services');
});
// Bills Payment
Route::prefix('bills')->controller(BillController::class)->as('bills.')->middleware('auth','suspend','verified','maintenance')->group(function(){
    Route::get('/', 'bills')->name('index');
    // data
    Route::get('/data', 'data')->name('data');
    Route::get('/data/{slug}', 'data_plan')->name('data.plan');
    Route::post('/buydata', 'buy_dataplan')->name('data.buyplan');
    // datacard
    Route::get('/datacard', 'datacard')->name('datacard');
    Route::get('/datacard/{slug}', 'datacard_plan')->name('datacard.plan');
    Route::post('/buydatacard', 'buy_datacard')->name('datacard.buyplan');
    // airtime
    Route::get('/airtime', 'airtime')->name('airtime');
    Route::post('/airtime', 'buy_airtime')->name('airtime.buy');
    // cable tv
    Route::get('/cable', 'cabletv')->name('cable');
    Route::post('/cable', 'buy_cabletv')->name('cable.buy');
    Route::get('/cable/{slug}', 'cabletv_packages')->name('cable.plan');
    Route::post('/cable-validation', 'cabletv_validation')->name('cable.validation');
    // electricity
    Route::get('/electricity', 'electricity')->name('electricity');
    Route::post('/electricity', 'buy_electricity')->name('buypower');
    Route::post('/meter-validation', 'electricity_validation')->name('power.validation');
    // educationn
    Route::get('/education', 'education')->name('education');
    Route::post('/education', 'buy_education')->name('education');
    // bulksms
    Route::post('/bulksms', 'send_bulksms')->name('bulksms');
    Route::get('/bulksms', 'bulksms')->name('bulksms');
    // recharge pins
    Route::get('/recharge-pins', 'recharge_pins')->name('airtimepin');
    Route::post('/recgarge-pins', 'generate_recharge_pins')->name('cardpin');
    // airtime swap
    Route::get('/airtime-cash', 'airtime_cash')->name('a2c');
    Route::post('/airtime-swap', 'airtime_swap')->name('airtime_swap');
    // betting
    Route::get('/betting', 'betting')->name('betting');
    Route::post('/betting', 'buy_betting')->name('betting.buy');
    Route::post('/bet-validation', 'bet_validation')->name('bet.validation');
    // Utility
    Route::get('/utility-payment', 'utilityBills')->name('utility');
    Route::get('/utility/billers', 'utilityBillers')->name('utility.billers');
    Route::get('/utility/plans', 'utilityPlans')->name('utility.plans');
    Route::post('/utility-payment', 'utilityPay')->name('utility.pay');

    // intl airtime
    Route::get('/topup', 'topup')->name('topup');
    Route::post('/topup', 'buy_topup')->name('topup.buy');
    Route::get('/topup-validate', 'validateNumber')->name('topup.validate');
    // Intl-data
    Route::get('/intl-data', 'globalData')->name('global-data');
    Route::post('/global/buy', 'buyGlobal')->name('global.buy');
    Route::post('/intl-data', 'buyGlobalData')->name('global-data.buy');
    Route::get('/intl/get-operators', 'getGlobalOperators')->name('global.operators');
    Route::get('/intl/get-plans', 'getGlobalPlans')->name('global.plans');
    // Giftcard
    Route::get('/giftcard', 'giftcard')->name('giftcard');
    Route::post('/giftcard', 'buy_giftcard')->name('giftcard.buy');
  
});
// User Routes
Route::middleware('user','verified','maintenance')->as('user.')->controller(UserController::class)->group(function(){
    Route::get('/user', 'dashboard')->name('index');
    Route::get('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/setting', 'setting')->name('setting');
    Route::get('/profile', 'profile')->name('profile');
    Route::post('change-pin','change_pin')->name('change_pin');
    Route::post('profile','update_profile')->name('profile.update');
     Route::post('ref','update_ref')->name('ref.update');
    Route::post('password','update_password')->name('password.update');
    Route::get('/upgrade_acct', 'upgrade_account')->name('upgrade');
    Route::get('/verify-kyc', 'verify_account')->name('verify');
    Route::post('/verify-kyc', 'verify_kyc')->name('verify');
    // self service
    Route::get('/self-service', 'self_service')->name('self.service');
    Route::get('/transactions', 'transactions')->name('transactions');
    Route::get('/pricing', 'pricing')->name('pricing');
    Route::get('/referral', 'referrals')->name('referral');
    Route::post('referral','referral_withdraw')->name('referral.withdraw');
    Route::get('/wallet', 'wallet')->name('wallet');
    Route::post('/wallet/fund', 'fund_wallet')->name('wallet.fund');
    Route::post('/wallet/manual', 'manual_payment')->name('wallet.bank');
    Route::get('/deposits', 'deposits')->name('deposit');
    Route::get('/bank-accounts', 'bank_accounts')->name('accounts');
    Route::get('/bank', 'generate_bank')->name('generate.account');
    Route::get('/generate-9pbs', 'generate_vessel_bank')->name('generate.payvessel');
    Route::post('/generate-wema', 'generateWema')->name('generate.wema');
    Route::get('/wallet-account/otp', 'wemaOtpPage')->name('wema.otp');
    Route::post('/wallet-account/otp', 'validateNinOtp')->name('wema.otp');

    // Transaction logs
    Route::get('/airtime-swap/logs', 'swap_logs')->name('swap.logs');
    Route::get('/vouchers', 'printed_cards')->name('vouchers.logs');
    Route::get('/cards/{id}', 'view_voucher')->name('voucher.view');
    Route::get('/airtime/logs', 'airtime_logs')->name('airtime.logs');
    Route::get('/data/logs', 'data_logs')->name('data.logs');
    Route::get('/power/logs', 'power_logs')->name('power.logs');
    Route::get('/cable/logs', 'decoder_logs')->name('cable.logs');
    Route::get('/education/logs', 'education_logs')->name('education.logs');
    Route::get('/bulk-sms', 'transactions')->name('sms.logs');
    Route::get('/bet/logs', 'bet_logs')->name('bet.logs');
    Route::get('/datacards/logs', 'datapin_logs')->name('datacard.logs');
    Route::get('/datacard/{id}', 'view_datacard')->name('datacard.view');
    Route::get('/utility/logs', 'utility_logs')->name('utility.logs');
    Route::get('/topup/logs', 'topup_logs')->name('topup.logs');
    Route::get('/intl-data/logs', 'globaldata_logs')->name('global-data.logs');
    Route::get('/giftcard/logs', 'giftcard_logs')->name('giftcard.logs');
    

});
// support system
Route::controller(SupportController::class)->middleware('user','verified')->as('user.')->prefix('user/tickets')->group(function(){
    Route::get('/', 'user_tickets')->name('tickets');
    Route::get('/new', 'new_ticket')->name('ticket.new');
    Route::post('/create', 'create_ticket')->name('ticket.create');
    Route::get('/{id}/{slug}', 'ticket_detail')->name('ticket.detail');
    Route::post('/comment/{id}', 'user_comment')->name('ticket.comment');
    Route::post('/close/{id}', 'close_ticket')->name('ticket.close');
});
// Developers API
Route::prefix('developer')->controller(DeveloperController::class)->as('developer.')->middleware('auth','verified')->group(function(){
    Route::get('/', 'bills')->name('index');
    Route::get('/data', 'data')->name('data');
    Route::get('/data/{slug}', 'data_plan')->name('data.plan');
    Route::get('/airtime', 'airtime')->name('airtime');
    Route::post('/airtime', 'buy_airtime')->name('airtime.buy');
    Route::get('/cable', 'cabletv')->name('cable');
    Route::get('/electricity', 'electricity')->name('electricity');
    Route::get('/education', 'education')->name('education');
    Route::get('/bulksms', 'bulksms')->name('bulksms');
    Route::get('/card-print', 'print_card')->name('airtimepin');
    Route::get('/airtime-cash', 'airtime_cash')->name('a2c');
    Route::get('/generate-key', 'generate_apikey')->name('generate.apikey');
});

// Payment Callback
Route::controller(PaymentController::class)->group(function(){
    Route::any('/paystack/success/', 'paystack_success')->name('paystack.success');
    Route::any('/flutter/success/', 'flutter_success')->name('flutter.success');
    Route::any('/monnify/success/', 'monnify_success')->name('monnify.success');
});

Route::get('/maintenance', [HomeController::class, 'maintenance'])->name('maintenance');
// Route::get('/sw.js', [HomeController::class, 'service_worker'])->name('sw');
