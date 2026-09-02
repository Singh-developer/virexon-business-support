<?php

namespace App\Http\Controllers;

use App\Http\Requests\CardRequest;
use App\Models\Business;
use App\Models\User;
use App\Models\VirtualCard;
use App\Services\AuditService;
use App\Services\MockVirtualCardProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | AGENT
        |--------------------------------------------------------------------------
        | Agent never sees the card listing.
        | Agent is sent directly to their one card.
        |--------------------------------------------------------------------------
        */
        if ($user->isAgent()) {
            $card = VirtualCard::query()
                ->where('agent_id', $user->id)
                ->first();

            if (! $card) {
                return view('cards.agent-empty');
            }

            return redirect()->route('cards.show', $card);
        }

        /*
        |--------------------------------------------------------------------------
        | ADMIN / SUPER ADMIN
        |--------------------------------------------------------------------------
        */
        $cards = VirtualCard::query()
            ->with(['business', 'agent'])
            ->when(
                $request->filled('q'),
                function ($query) use ($request) {
                    $search = $request->q;

                    $query->where(function ($subQuery) use ($search) {
                        $subQuery
                            ->where('reference', 'like', "%{$search}%")
                            ->orWhere(
                                'cardholder_name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'last4',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('cards.index', compact('cards'));
    }

    public function create()
    {
        $agents = User::query()
            ->with(['business', 'virtualCard'])
            ->whereHas(
                'role',
                fn ($query) => $query->where('slug', 'agent')
            )
            ->where('status', 'active')
            ->whereDoesntHave('virtualCard')
            ->orderBy('name')
            ->get();

        return view('cards.form', [
            'card' => new VirtualCard([
                'status' => 'active',
                'card_limit' => 100000,
                'daily_limit' => 20000,
                'monthly_limit' => 80000,
                'per_transaction_limit' => 20000,
            ]),
            'businesses' => Business::where('status', 'active')
                ->orderBy('name')
                ->get(),
            'agents' => $agents,
        ]);
    }

    public function store(
        CardRequest $request,
        AuditService $audit,
        MockVirtualCardProvider $provider
    ) {
        $data = $request->validated();

        $agent = User::with('business')
            ->findOrFail($data['agent_id']);

        /*
        |--------------------------------------------------------------------------
        | Final ownership safety check
        |--------------------------------------------------------------------------
        */
        abort_unless(
            $agent->isAgent(),
            422,
            'Selected user is not an Agent.'
        );

        abort_unless(
            (int) $agent->business_id === (int) $data['business_id'],
            422,
            'Agent does not belong to the selected business.'
        );

        /*
        |--------------------------------------------------------------------------
        | Extra application-level one-card check.
        | Database UNIQUE(agent_id) remains the final protection.
        |--------------------------------------------------------------------------
        */
        if (
            VirtualCard::where('agent_id', $agent->id)->exists()
        ) {
            return back()
                ->withErrors([
                    'agent_id' => 'This Agent already has a virtual card.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Create sandbox card
        |--------------------------------------------------------------------------
        */
        $providerCard = $provider->createCard(
            $agent->name
        );

        $reference =
            'VC-' .
            now()->format('ymd') .
            '-' .
            strtoupper(str()->random(8));

        $card = DB::transaction(function () use (
            $data,
            $agent,
            $providerCard,
            $reference,
            $request,
            $audit
        ) {
            $card = VirtualCard::create([
                'business_id' => $agent->business_id,

                'agent_id' => $agent->id,

                'reference' => $reference,

                /*
                 * PAN is encrypted at rest.
                 */
                'encrypted_pan' => encrypt(
                    $providerCard['pan']
                ),

                'last4' => $providerCard['last4'],

                'provider_card_id' =>
                    $providerCard['provider_card_id'],

                'cardholder_name' =>
                    $providerCard['cardholder_name'],

                'status' => $data['status'],

                'card_limit' =>
                    $data['card_limit'],

                'daily_limit' =>
                    $data['daily_limit'],

                'monthly_limit' =>
                    $data['monthly_limit'],

                'per_transaction_limit' =>
                    $data['per_transaction_limit'],

                'current_usage' => 0,

                'expiry_date' =>
                    $providerCard['expiry_date'],

                'created_by' =>
                    $request->user()->id,

                'last_transaction_at' => null,
            ]);

            $audit->record(
                $request,
                'card.created',
                $card,
                [
                    'agent_id' => $agent->id,
                    'last4' => $providerCard['last4'],
                    'provider_card_id' =>
                        $providerCard['provider_card_id'],
                ]
            );

            return $card;
        });

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        | CVV is intentionally NOT written to the database.
        |
        | We send it to the Admin one time immediately after creation.
        |--------------------------------------------------------------------------
        */
        return redirect()
            ->route('cards.show', $card)
            ->with('card_created', true)
            ->with(
                'generated_cvv',
                $providerCard['cvv']
            );
    }

    public function updateStatus(
        Request $request,
        VirtualCard $card
    ) {
        abort_unless(
            $request->user()->isAdmin(),
            403
        );

        $request->validate([
            'status' => [
                'required',
                'in:active,blocked,suspended,cancelled',
            ],
        ]);

        $card->update([
            'status' => $request->status,
        ]);

        return back()->with(
            'success',
            'Card status updated.'
        );
    }

    public function show(
        Request $request,
        VirtualCard $card
    ) {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Agent can only access their own card
        |--------------------------------------------------------------------------
        */
        if ($user->isAgent()) {
            abort_unless(
                (int) $card->agent_id === (int) $user->id,
                403
            );
        }

        $card->load([
            'business',
            'agent',
        ]);

        return view(
            'cards.show',
            compact('card')
        );
    }

    /**
     * Reveal full PAN.
     *
     * Agent can reveal ONLY their own card.
     * Admin can reveal cards because Admin manages cards.
     */
    public function revealPan(
        Request $request,
        VirtualCard $card
    ) {
        $user = $request->user();

        if ($user->isAgent()) {
            abort_unless(
                (int) $card->agent_id === (int) $user->id,
                403
            );
        }

        abort_unless(
            $user->isAdmin() || $user->isAgent(),
            403
        );

        $pan = $card->revealPan();

        return response()->json([
            'pan' => $pan,
            'formatted' => implode(
                ' ',
                str_split($pan, 4)
            ),
        ]);
    }
}