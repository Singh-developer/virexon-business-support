<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $agents = User::query()
            ->with([
                'business',
                'virtualCard',
            ])
            ->whereHas(
                'role',
                fn($query) => $query->where('slug', 'agent')
            )
            ->when(
                $request->filled('q'),
                function ($query) use ($request) {
                    $search = $request->q;

                    $query->where(function ($subQuery) use ($search) {
                        $subQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
                }
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(
            'agents.index',
            compact('agents')
        );
    }

    public function create()
    {
        return view('agents.form', [
            'agent' => new User([
                'status' => 'active',
            ]),

            'businesses' => Business::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $role = Role::where(
            'slug',
            'agent'
        )->firstOrFail();

        DB::transaction(function () use (
            $data,
            $role
        ) {
            User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'role_id' => $role->id,
                'business_id' => $data['business_id'],
                'status' => $data['status'],
            ]);
        });

        return redirect()
            ->route('agents.index')
            ->with(
                'success',
                'Agent created successfully. Virtual card must be created separately by Admin.'
            );
    }

    public function edit(User $agent)
    {
        abort_unless(
            $agent->isAgent(),
            404
        );

        return view('agents.form', [
            'agent' => $agent,

            'businesses' => Business::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(
        Request $request,
        User $agent
    ) {
        abort_unless(
            $agent->isAgent(),
            404
        );

        $data = $this->validated(
            $request,
            $agent
        );

        DB::transaction(function () use (
            $agent,
            $data
        ) {
            $agent->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'business_id' => $data['business_id'],
                'status' => $data['status'],

                ...(
                    filled($data['password'] ?? null)
                    ? ['password' => $data['password']]
                    : []
                ),
            ]);

            /*
             * DO NOT CREATE A CARD HERE.
             *
             * If an existing card belongs to the Agent,
             * synchronize business/cardholder information.
             */
            if ($agent->virtualCard) {
                $agent->virtualCard->update([
                    'business_id' => $agent->business_id,
                    'cardholder_name' => $agent->name,
                ]);
            }
        });

        return redirect()
            ->route('agents.index')
            ->with(
                'success',
                'Agent details updated.'
            );
    }

    public function show(User $agent)
    {
        abort_unless(
            $agent->isAgent(),
            404
        );

        $agent->load([
            'business',
            'virtualCard',
        ]);

        return view(
            'agents.show',
            compact('agent')
        );
    }

    private function validated(
        Request $request,
        ?User $agent = null
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:180',
            ],

            'email' => [
                'required',
                'email',
                'max:180',
                Rule::unique(
                    'users',
                    'email'
                )->ignore($agent?->id),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:40',
            ],

            'business_id' => [
                'required',
                'exists:businesses,id',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],

            'password' => [
                $agent ? 'nullable' : 'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);
    }
}
