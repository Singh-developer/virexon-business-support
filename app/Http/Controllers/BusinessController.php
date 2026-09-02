<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusinessRequest;
use App\Models\Business;
use App\Services\AuditService;
use Illuminate\Http\Request;

class BusinessController
{
    /* public function index(Request $r)
    {
        $u = $r->user();
        $q = Business::query()->withCount(['users', 'cards', 'payments'])->when($u->isAgent(), fn($x) => $x->whereKey($u->business_id))->when($r->filled('q'), fn($x) => $x->where('name', 'like', '%' . $r->q . '%'))->latest();
        return view('businesses.index', ['businesses' => $q->paginate(12)->withQueryString()]);
    } */
    public function index(Request $request)
    {
        $user = $request->user();

        $businesses = Business::query()
            ->withCount([
                'users',
                'cards',
                'payments',
            ])
            ->when(
                $user->isAgent(),
                fn($query) =>
                $query->whereKey(
                    $user->business_id
                )
            )
            ->when(
                $request->filled('q'),
                fn($query) =>
                $query->where(
                    'name',
                    'like',
                    '%' . $request->q . '%'
                )
            )
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view(
            'businesses.index',
            compact('businesses')
        );
    }
    /* public function create(Request $r)
    {
        abort_unless($r->user()->isAdmin(), 403);
        return view('businesses.form', ['business' => new Business(['status' => 'active'])]);
    } */
    public function create(Request $request)
    {
        abort_unless(
            $request->user()->isAdmin(),
            403
        );

        return view(
            'businesses.form',
            [
                'business' => new Business([
                    'status' => 'active',
                ]),
            ]
        );
    }
    public function store(BusinessRequest $r, AuditService $audit)
    {
        abort_unless($r->user()->isAdmin(), 403);
        $b = Business::create($r->validated() + ['created_by' => $r->user()->id]);
        $audit->record($r, 'business.created', $b);
        return redirect()->route('businesses.index')->with('success', 'Business created successfully.');
    }
    public function edit(Request $r, Business $business)
    {
        abort_unless($r->user()->isAdmin(), 403);
        return view('businesses.form', compact('business'));
    }
    public function update(BusinessRequest $r, Business $business, AuditService $audit)
    {
        abort_unless($r->user()->isAdmin(), 403);
        $business->update($r->validated());
        $audit->record($r, 'business.updated', $business);
        return redirect()->route('businesses.index')->with('success', 'Business updated successfully.');
    }
    public function show(Request $r, Business $business)
    {
        if ($r->user()->isAgent() && $business->id !== $r->user()->business_id) abort(403);
        $business->load(['cards' => fn($q) => $q->when($r->user()->isAgent(), fn($x) => $x->where('agent_id', $r->user()->id)), 'payments' => fn($q) => $q->when($r->user()->isAgent(), fn($x) => $x->where('user_id', $r->user()->id))->latest()->limit(10)]);
        return view('businesses.show', compact('business'));
    }
}
