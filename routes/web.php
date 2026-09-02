<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AuthController,
    DashboardController,
    BusinessController,
    AgentController,
    CardController,
    PaymentController,
    TransactionController,
    WebhookController,
    SettingsController,
    RegistrationController
};

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get('/register-agent', [RegistrationController::class, 'create'])->name('register.agent');
Route::post('/register-agent', [RegistrationController::class, 'store'])->name('register.agent.store');

Route::get('/login', [
    AuthController::class,
    'showLogin'
])->name('login');

Route::post('/login', [
    AuthController::class,
    'login'
])->name('login.attempt');

Route::post('/logout', [
    AuthController::class,
    'logout'
])->middleware('auth')->name('logout');


/*
|--------------------------------------------------------------------------
| Authenticated Application
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('home');
})->name('home');

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    /* Route::get('/', function () {
        return redirect()->route('dashboard');
    }); */

    Route::get('/dashboard', function () {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return view('dashboard.dashboard');
    })->name('dashboard');

    Route::get('/admin/dashboard', DashboardController::class)
        ->name('admin.dashboard')
        ->middleware('role:super-admin,admin');


    /*
    |--------------------------------------------------------------------------
    | Business Management
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'businesses',
        BusinessController::class
    )->except([
        'destroy'
    ]);


    /*
    |--------------------------------------------------------------------------
    | Virtual Cards
    |--------------------------------------------------------------------------
    |
    | Agent:
    |   /cards
    |   -> CardController@index
    |   -> automatically redirects to their own card
    |
    | Admin:
    |   /cards
    |   -> card listing
    |
    |--------------------------------------------------------------------------
    */

    Route::get('/cards', [
        CardController::class,
        'index'
    ])->name('cards.index');


    /*
    |--------------------------------------------------------------------------
    | Admin / Super Admin Card + Agent Management
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:super-admin,admin')->group(function () {

        /*
        | Create Virtual Card
        */
        Route::get('/cards/create', [
            CardController::class,
            'create'
        ])->name('cards.create');

        Route::post('/cards', [
            CardController::class,
            'store'
        ])->name('cards.store');


        /*
        | Change Card Status
        */
        Route::post('/cards/{card}/status', [
            CardController::class,
            'updateStatus'
        ])->name('cards.status');


        /*
        | Agent Management
        */
        Route::patch(
        'agents/{agent}/toggle-status',
        [AgentController::class, 'toggleStatus']
    )->name('agents.toggle-status');

        Route::resource(
            'agents',
            AgentController::class
        )->except([
            'destroy'
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | Card Details
    |--------------------------------------------------------------------------
    |
    | Agent:
    |   Can only open their own card.
    |
    | Admin:
    |   Can open any card.
    |
    |--------------------------------------------------------------------------
    */

    Route::get('/cards/{card}', [
        CardController::class,
        'show'
    ])->name('cards.show');


    /*
    |--------------------------------------------------------------------------
    | Reveal Full PAN
    |--------------------------------------------------------------------------
    |
    | Agent:
    |   Can reveal only their own card PAN.
    |
    | Admin:
    |   Can reveal cards they manage.
    |
    |--------------------------------------------------------------------------
    */

    Route::post('/cards/{card}/reveal-pan', [
        CardController::class,
        'revealPan'
    ])->name('cards.reveal-pan');


    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'payments',
        PaymentController::class
    )->only([
        'index',
        'create',
        'store',
        'show'
    ]);


    /*
    |--------------------------------------------------------------------------
    | Sandbox Payment Completion
    |--------------------------------------------------------------------------
    */

    Route::post('/payments/{payment}/mock-complete', [
        PaymentController::class,
        'mockComplete'
    ])->name('payments.mock-complete');


    /*
    |--------------------------------------------------------------------------
    | Transactions
    |--------------------------------------------------------------------------
    */

    Route::get('/transactions', [
        TransactionController::class,
        'index'
    ])->name('transactions.index');


    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Settings
    |--------------------------------------------------------------------------
    */

    /*
     * Agent + Admin own profile.
     */
    Route::get('/settings/profile', [
        SettingsController::class,
        'profile',
    ])->name('settings.profile');

    Route::put('/settings/profile', [
        SettingsController::class,
        'updateProfile',
    ])->name('settings.profile.update');

    Route::middleware('role:super-admin,admin')->group(function () {

        Route::get('/settings/gateways', [
            SettingsController::class,
            'gateways'
        ])->name('settings.gateways');

        Route::post('/settings/payment-mode', [
            SettingsController::class,
            'updatePaymentMode',
        ])->name('settings.payment-mode.update');

        Route::post('/settings/gateways', [
            SettingsController::class,
            'storeGateway'
        ])->name('settings.gateways.store');

        Route::put('/settings/gateways/{gateway}', [
            SettingsController::class,
            'updateGateway'
        ])->name('settings.gateways.update');
    });
});


/*
|--------------------------------------------------------------------------
| Webhooks
|--------------------------------------------------------------------------
|
| These endpoints are intentionally outside the auth middleware because
| payment providers call them directly.
|
|--------------------------------------------------------------------------
*/

Route::post('/webhooks/{gateway}', [
    WebhookController::class,
    'handle'
])->name('webhooks.handle');


/*
|--------------------------------------------------------------------------
| Paytm Callback
|--------------------------------------------------------------------------
*/

Route::post('/payments/paytm/callback', [
    PaymentController::class,
    'paytmCallback'
])->name('payments.paytm.callback');

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
*/




/* Step form mail OTP */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

Route::post('/register/agent/send-otp', function (Request $request) {
    $user = auth()->user();

    if (!$user) {
        return response()->json(['error' => 'Unauthenticated'], 401);
    }

    $otp = rand(100000, 999999);
    session(['loan_form_otp' => $otp]);

    Mail::raw("Your verification OTP for the application is: $otp", function ($message) use ($user) {
        $message->to($user->email)
            ->subject('Loan Application - Verification OTP');
    });

    return response()->json(['success' => true]);
})->name('agent.send-otp')->middleware('auth');
