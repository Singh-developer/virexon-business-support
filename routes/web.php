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
    RegistrationController,
    AssertionLetterController
};

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/


Route::get('/terms', function () {
    return view('terms');
})->name('terms');

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
    | Agent Registration Form
    |--------------------------------------------------------------------------
    */
    Route::get('/register-agent', [RegistrationController::class, 'create_new'])->name('register.agent');
    Route::post('/register-agent', [RegistrationController::class, 'store_new'])->name('register.agent.store');

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
    | Support Tickets (Agent)
    |--------------------------------------------------------------------------
    */
    Route::resource('tickets', \App\Http\Controllers\TicketController::class)->only(['index', 'create', 'store', 'show']);

    /*
    |--------------------------------------------------------------------------
    | Agent Documents (Upload)
    |--------------------------------------------------------------------------
    */
    Route::get('documents', [App\Http\Controllers\DocumentController::class, 'index'])->name('documents.index');
    Route::post('documents', [App\Http\Controllers\DocumentController::class, 'store'])->name('documents.store');

    // Advances & Balance (Agent)
    Route::get('advances', [App\Http\Controllers\AdvanceController::class, 'index'])->name('advances.index');
    Route::post('advances/repayment', [App\Http\Controllers\AdvanceController::class, 'updateRepaymentMethod'])->name('advances.repayment');



    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    Route::post('/notifications/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.mark-read');
    Route::get('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read');


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
        | Support Tickets (Admin)
        */
        Route::get('/admin/tickets', [\App\Http\Controllers\TicketController::class, 'adminIndex'])->name('admin.tickets.index');
        Route::get('/admin/tickets/{ticket}', [\App\Http\Controllers\TicketController::class, 'adminShow'])->name('admin.tickets.show');
        Route::patch('/admin/tickets/{ticket}/status', [\App\Http\Controllers\TicketController::class, 'adminUpdate'])->name('admin.tickets.update');

        /*
        | Agent Documents (Admin Review)
        */
        Route::get('/admin/agents/{userId}/documents', [\App\Http\Controllers\DocumentController::class, 'adminIndex'])->name('admin.documents.index');
        Route::patch('/admin/agents/{userId}/documents/{docId}', [\App\Http\Controllers\DocumentController::class, 'adminReview'])->name('admin.documents.review');


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
        Route::get('agents/import-sample', [AgentController::class, 'downloadSample'])->name('agents.import.sample');
        Route::post('agents/import', [AgentController::class, 'import'])->name('agents.import');

        Route::patch(
            'agents/{agent}/toggle-status',
            [AgentController::class, 'toggleStatus']
        )->name('agents.toggle-status');

        Route::patch('agents/{agent}/application-status', [App\Http\Controllers\AgentController::class, 'updateApplicationStatus'])->name('agents.application-status');
        
        // Advances & Commissions
        Route::post('agents/{agent}/advances', [App\Http\Controllers\Admin\AdvanceController::class, 'store'])->name('admin.advances.store');
        Route::post('agents/{agent}/commissions', [App\Http\Controllers\Admin\CommissionController::class, 'store'])->name('admin.commissions.store');

        Route::resource(
            'agents',
            AgentController::class
        )->except([
            'destroy'
        ]);

        /*
        |--------------------------------------------------------------------------
        | Role & User Management
        |--------------------------------------------------------------------------
        */

        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class)
            ->names('admin.roles')
            ->parameters(['roles' => 'role']);

        Route::get('users', [\App\Http\Controllers\Admin\RoleController::class, 'users'])
            ->name('admin.users.index');

        Route::patch('users/{user}/role', [\App\Http\Controllers\Admin\RoleController::class, 'assignRole'])
            ->name('admin.users.role');
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
    | Assertion Letters (Admin / Super Admin)
    |--------------------------------------------------------------------------
    |
    | Live preview endpoint works for any authenticated user so the form's
    | on-page JS can refresh while typing. Actual send / list / show / download
    | are restricted to admin roles.
    |
    */

    Route::post('/assertions/preview', [
        AssertionLetterController::class,
        'preview'
    ])->name('assertions.preview');

    Route::post('/assertions/download-pdf', [
        AssertionLetterController::class,
        'downloadPdf'
    ])->name('assertions.download-pdf');

    Route::middleware('role:super-admin,admin')->group(function () {

        Route::get('/assertions', [
            AssertionLetterController::class,
            'index'
        ])->name('assertions.index');

        Route::get('/assertions/create', [
            AssertionLetterController::class,
            'create'
        ])->name('assertions.create');

        Route::post('/assertions', [
            AssertionLetterController::class,
            'store'
        ])->name('assertions.store');

        Route::get('/assertions/{assertion}', [
            AssertionLetterController::class,
            'show'
        ])->name('assertions.show');

        Route::get('/assertions/{assertion}/download', [
            AssertionLetterController::class,
            'download'
        ])->name('assertions.download');

    });


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
        
        Route::delete('/settings/gateways/{gateway}', [
            SettingsController::class,
            'destroyGateway'
        ])->name('settings.gateways.destroy');
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
    $request->validate(['email' => 'required|email']);
    $email = $request->email;

    $otp = rand(100000, 999999);
    session(['loan_form_otp' => $otp]);

    Mail::raw("Your verification OTP for the application is: $otp", function ($message) use ($email) {
        $message->to($email)
            ->subject('Loan Application - Verification OTP');
    });

    return response()->json(['success' => true]);
})->name('agent.send-otp');
