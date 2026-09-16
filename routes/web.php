<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AuthController,
    DashboardController,
    AgentDashboardController,
    BusinessController,
    AgentController,
    CardController,
    PaymentController,
    TransactionController,
    WebhookController,
    SettingsController,
    RegistrationController,
    SanctionLetterController,
    SanctionReviewController,
    AgentSanctionLetterController,
    PaytmPaymentController,
};

use App\Http\Controllers\Admin\TrashController;

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
        return app(AgentDashboardController::class)();
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
        Route::get('/admin/documents', [\App\Http\Controllers\DocumentController::class, 'adminOverview'])->name('admin.documents.overview');
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
        | Trash (soft delete) a Virtual Card
        */
        Route::delete('/cards/{card}', [
            CardController::class,
            'destroy'
        ])->name('cards.destroy');


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
        
        Route::patch('agents/{agent}/limit-commission', [App\Http\Controllers\AgentController::class, 'updateLimitCommission'])->name('agents.update-limit-commission');

        Route::patch('agents/{agent}/details', [App\Http\Controllers\AgentController::class, 'updateDetails'])->name('agents.update-details');
        
        // Advances & Commissions
        Route::post('agents/{agent}/advances', [App\Http\Controllers\Admin\AdvanceController::class, 'store'])->name('admin.advances.store');
        Route::post('agents/{agent}/commissions', [App\Http\Controllers\Admin\CommissionController::class, 'store'])->name('admin.commissions.store');

        Route::resource(
            'agents',
            AgentController::class
        )->except([
            'destroy'
        ]);

        Route::post('agents/bulk', [AgentController::class, 'bulk'])->name('agents.bulk');
        Route::delete('agents/{agent}', [AgentController::class, 'destroy'])->name('agents.destroy');

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

        /*
        |--------------------------------------------------------------------------
        | Trash (Admin)
        |--------------------------------------------------------------------------
        |
        | Browse / restore / permanently delete soft-deleted records.
        | Permanent delete is guarded: only trashed records can be purged,
        | and uploaded files are removed from disk at that point only.
        |
        */
        Route::get('/admin/trash', [TrashController::class, 'index'])->name('admin.trash.index');
        Route::post('/admin/trash/{type}/{id}/restore', [TrashController::class, 'restore'])->name('admin.trash.restore');
        Route::delete('/admin/trash/{type}/{id}', [TrashController::class, 'forceDelete'])->name('admin.trash.force-delete');
        Route::delete('/admin/trash/{type}/purge', [TrashController::class, 'emptyType'])->name('admin.trash.purge');
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

    Route::post('/cards/{card}/reveal-cvv', [
        CardController::class,
        'revealCvv'
    ])->name('cards.reveal-cvv');


    /*
    |--------------------------------------------------------------------------
    | Payments (Admin / Super Admin only)
    |--------------------------------------------------------------------------
    |
    | Blocked for agents. Restriction applied in routes/web.php via the
    | `role:super-admin,admin` middleware group below.
    |
    */

    Route::middleware('role:super-admin,admin')->group(function () {

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
    });


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
    | Sanction Letters (Admin / Super Admin)
    |--------------------------------------------------------------------------
    |
    | Live preview endpoint works for any authenticated user so the form's
    | on-page JS can refresh while typing. Actual send / list / show / download
    | are restricted to admin roles.
    |
    */

    Route::post('/sanctions/preview', [
        SanctionLetterController::class,
        'preview'
    ])->name('sanctions.preview');

    Route::post('/sanctions/download-pdf', [
        SanctionLetterController::class,
        'downloadPdf'
    ])->name('sanctions.download-pdf');

    /*
    |--------------------------------------------------------------------------
    | Agent Sanction Letters (Authenticated Agents)
    |--------------------------------------------------------------------------
    | Agent-facing workflow: view, preview, download and upload the signed PDF.
    | Ownership is enforced inside AgentSanctionLetterController.
    */

    Route::prefix('agent')->name('agent.')->group(function () {

        Route::get('/sanction-letters', [
            AgentSanctionLetterController::class,
            'index'
        ])->name('sanctions.index');

        Route::get('/sanction-letters/{sanction}', [
            AgentSanctionLetterController::class,
            'show'
        ])->name('sanctions.show');

        Route::get('/sanction-letters/{sanction}/pdf', [
            AgentSanctionLetterController::class,
            'pdf'
        ])->name('sanctions.pdf');

        Route::get('/sanction-letters/{sanction}/download', [
            AgentSanctionLetterController::class,
            'download'
        ])->name('sanctions.download');

        Route::get('/sanction-letters/{sanction}/signed-pdf', [
            AgentSanctionLetterController::class,
            'signedPdf'
        ])->name('sanctions.signed-pdf');

        Route::post('/sanction-letters/{sanction}/pay-fee', [
            AgentSanctionLetterController::class,
            'payFee'
        ])->name('sanctions.pay-fee');

        Route::post('/sanction-letters/{sanction}/upload', [
            AgentSanctionLetterController::class,
            'upload'
        ])->name('sanctions.upload');

    });

    Route::middleware('role:super-admin,admin')->group(function () {

        Route::get('/sanctions', [
            SanctionLetterController::class,
            'index'
        ])->name('sanctions.index');

        Route::get('/sanctions/create', [
            SanctionLetterController::class,
            'create'
        ])->name('sanctions.create');

        Route::post('/sanctions', [
            SanctionLetterController::class,
            'store'
        ])->name('sanctions.store');

        Route::get('/sanctions/{sanction}', [
            SanctionLetterController::class,
            'show'
        ])->name('sanctions.show');

        Route::delete('/sanctions/{sanction}', [
            SanctionLetterController::class,
            'destroy'
        ])->name('sanctions.destroy');

        Route::get('/sanctions/{sanction}/download', [
            SanctionLetterController::class,
            'download'
        ])->name('sanctions.download');

        /*
        |--------------------------------------------------------------------------
        | Admin Review of Signed Sanction Letter PDFs
        |--------------------------------------------------------------------------
        */

        Route::get('/sanctions/{sanction}/review', [
            SanctionReviewController::class,
            'show'
        ])->name('sanctions.review');

        Route::post('/sanctions/{sanction}/approve', [
            SanctionReviewController::class,
            'approve'
        ])->name('sanctions.approve');

        Route::post('/sanctions/{sanction}/reupload-required', [
            SanctionReviewController::class,
            'requestReupload'
        ])->name('sanctions.reupload-required');

        Route::get('/sanctions/{sanction}/signed/{upload}', [
            SanctionReviewController::class,
            'signedPdf'
        ])->name('sanctions.signed-pdf');

        Route::get('/sanctions/{sanction}/signed/{upload}/download', [
            SanctionReviewController::class,
            'signedDownload'
        ])->name('sanctions.signed-download');

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

Route::match(['get', 'post'], '/payments/paytm/callback', [
    PaymentController::class,
    'paytmCallback'
])->name('payments.paytm.callback');

/*
|--------------------------------------------------------------------------
| Agent Sanction Letter Processing-Fee Paytm Callback
|--------------------------------------------------------------------------
| Intentionally OUTSIDE the auth middleware: Paytm posts the agent's
| browser here cross-site after the payment, and a Lax session cookie is
| not sent on cross-site POSTs. The controller silently logs the agent
| back in using a short-lived signed verifier baked into the callback URL.
*/

Route::match(['get', 'post'], '/agent/sanction-letters/paytm-callback', [
    App\Http\Controllers\AgentSanctionLetterController::class,
    'paytmCallback'
])->name('agent.sanctions.fee-callback');

/*
|--------------------------------------------------------------------------
| Paytm Payment Routes
|--------------------------------------------------------------------------
*/

Route::get('/paytm-checkout', [PaytmPaymentController::class, 'showCheckoutForm'])->name('paytm.checkout');
Route::post('/paytm-pay', [PaytmPaymentController::class, 'initiatePayment'])->name('paytm.pay');
Route::post('/paytm-callback', [PaytmPaymentController::class, 'handleCallback'])->name('paytm.callback');

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
