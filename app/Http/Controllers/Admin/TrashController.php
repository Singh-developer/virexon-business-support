<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{
    Advance,
    AgentDocument,
    Commission,
    Payment,
    ReferencePerson,
    SanctionLetter,
    SanctionLetterUpload,
    Ticket,
    User,
    UserDetail,
    VirtualCard,
};
use App\Services\AuditService;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    /**
     * Registry of soft-deletable record types shown in the trash panel.
     *
     * Each entry:
     *  - label    Human-readable heading
     *  - icon     Emoji for the tab button
     *  - model    Eloquent class that uses SoftDeletes
     *  - apply    Optional closure to narrow the query (e.g. only agents)
     *  - title    Closure returning a short description for a model instance
     *  - meta     Closure returning extra meta text shown below the title
     *
     * @return array<string, array{label:string, icon:string, model:class-string, apply?:callable, title:callable, meta?:callable}>
     */
    protected function types(): array
    {
        return [
            'agent' => [
                'label' => 'Agents',
                'icon'  => '👤',
                'model' => User::class,
                'apply' => fn ($q) => $q->whereHas('role', fn ($r) => $r->where('slug', 'agent')),
                'title' => fn (User $m) => $m->name . ' — ' . $m->email,
                'meta'  => fn (User $m) => 'Agent ID: ' . ($m->detail?->agent_id_number ?? '—'),
            ],
            'sanction' => [
                'label' => 'Sanction Letters',
                'icon'  => '✉️',
                'model' => SanctionLetter::class,
                'title' => fn (SanctionLetter $m) => $m->sanction_letter_no . ' — ' . ($m->user?->name ?? '—'),
                'meta'  => fn (SanctionLetter $m) => 'Status: ' . ucfirst($m->review_status ?? $m->status),
            ],
            'card' => [
                'label' => 'Virtual Cards',
                'icon'  => '💳',
                'model' => VirtualCard::class,
                'title' => fn (VirtualCard $m) => $m->reference . ' •••• ' . ($m->last4 ?? '????'),
                'meta'  => fn (VirtualCard $m) => 'Agent: ' . ($m->agent?->name ?? '—'),
            ],
            'document' => [
                'label' => 'Agent Documents',
                'icon'  => '📎',
                'model' => AgentDocument::class,
                'title' => fn (AgentDocument $m) => \App\Models\AgentDocument::typeLabel($m->document_type),
                'meta'  => fn (AgentDocument $m) => 'Agent: ' . ($m->user?->name ?? '—'),
            ],
            'advance' => [
                'label' => 'Advances',
                'icon'  => '💰',
                'model' => Advance::class,
                'title' => fn (Advance $m) => '₹' . number_format((float) $m->total_amount, 2),
                'meta'  => fn (Advance $m) => 'Agent: ' . ($m->user?->name ?? '—'),
            ],
            'payment' => [
                'label' => 'Payments',
                'icon'  => '↗️',
                'model' => Payment::class,
                'title' => fn (Payment $m) => $m->reference,
                'meta'  => fn (Payment $m) => '₹' . number_format((float) $m->amount, 2) . ' — ' . ucfirst($m->status->value),
            ],
            'commission' => [
                'label' => 'Commissions',
                'icon'  => '✔️',
                'model' => Commission::class,
                'title' => fn (Commission $m) => '₹' . number_format((float) $m->net_amount, 2),
                'meta'  => fn (Commission $m) => 'Agent: ' . ($m->user?->name ?? '—'),
            ],
            'ticket' => [
                'label' => 'Support Tickets',
                'icon'  => '🎫',
                'model' => Ticket::class,
                'title' => fn (Ticket $m) => $m->title,
                'meta'  => fn (Ticket $m) => 'Agent: ' . ($m->user?->name ?? '—') . ' · Status: ' . ucfirst($m->status),
            ],
            'user_detail' => [
                'label' => 'Agent Details',
                'icon'  => '📋',
                'model' => UserDetail::class,
                'title' => fn (UserDetail $m) => 'ID: ' . ($m->agent_id_number ?? '—'),
                'meta'  => fn (UserDetail $m) => 'Agent: ' . ($m->user?->name ?? '—'),
            ],
            'reference' => [
                'label' => 'Reference People',
                'icon'  => '🪪',
                'model' => ReferencePerson::class,
                'title' => fn (ReferencePerson $m) => $m->person_name,
                'meta'  => fn (ReferencePerson $m) => 'Agent: ' . ($m->user?->name ?? '—'),
            ],
            'upload' => [
                'label' => 'Signed PDF Uploads',
                'icon'  => '📄',
                'model' => SanctionLetterUpload::class,
                'title' => fn (SanctionLetterUpload $m) => $m->original_name ?? basename($m->file_path),
                'meta'  => fn (SanctionLetterUpload $m) => 'Letter: ' . ($m->letter?->sanction_letter_no ?? '—'),
            ],
        ];
    }

    /** List trashed records, grouped by type. */
    public function index(Request $request)
    {
        $types    = $this->types();
        $active   = $request->get('type', 'agent');
        $selected = $types[$active] ?? reset($types);

        // Count trashed records for each type (for the tab badges).
        $counts = collect($types)->mapWithKeys(fn ($cfg, $key) => [
            $key => $this->queryTrashed($cfg)->count(),
        ]);

        $query = $this->queryTrashed($selected);
        $items = $query->paginate(20)->withQueryString();

        return view('admin.trash.index', compact('types', 'active', 'counts', 'items'));
    }

    /**
     * Restore a trashed record (and any records trashed together with it).
     *
     * Checks enforced:
     *  - User must be admin (route middleware).
     *  - Record must exist in the soft-deleted scope.
     *  - Restoring an agent automatically restores all its child records.
     */
    public function restore(Request $request, string $type, int $id, AuditService $audit)
    {
        $cfg  = $this->resolveType($type);
        $model = $cfg['model']::withTrashed()->findOrFail($id);

        abort_unless($model->trashed(), 403, 'This record is not currently trashed.');

        $model->restore();

        $label = $cfg['title']($model);

        $audit->record($request, "{$type}.restored", $model, [
            'restored_by' => $request->user()->id,
        ]);

        return back()->with('success', "Restored: {$label}");
    }

    /**
     * Permanently delete a trashed record.
     *
     * Safety checks:
     *  1. Must be admin.
     *  2. Record MUST already be soft-deleted (in the trash).
     *  3. Uploaded files are removed from disk only at this stage.
     */
    public function forceDelete(Request $request, string $type, int $id, AuditService $audit)
    {
        $cfg   = $this->resolveType($type);
        $model = $cfg['model']::withTrashed()->findOrFail($id);

        abort_unless($model->trashed(), 403, 'Only records in the trash can be permanently deleted.');

        $label = $cfg['title']($model);

        $model->forceDelete();

        $audit->record($request, "{$type}.permanently_deleted", null, [
            'deleted_by' => $request->user()->id,
            'label'      => $label,
        ]);

        return back()->with('success', "Permanently deleted: {$label}");
    }

    /**
     * Purge all records of a given type from the trash.
     *
     * Super-admin only safety gate.
     */
    public function emptyType(Request $request, string $type, AuditService $audit)
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Only super-admins can purge entire categories.');

        $cfg  = $this->resolveType($type);
        $query = $this->queryTrashed($cfg);

        $count = $query->count();
        if ($count === 0) {
            return back()->with('success', "Trash already empty for {$cfg['label']}.");
        }

        // Force-delete each record individually so model events (file cleanup) fire.
        foreach ($query->get() as $model) {
            $model->forceDelete();
        }

        $audit->record($request, "{$type}.trash_purged", null, [
            'count' => $count,
        ]);

        return back()->with('success', "Permanently deleted {$count} record(s) from {$cfg['label']}.");
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /** Resolve a type key to its registry entry, or abort 404. */
    protected function resolveType(string $key): array
    {
        $types = $this->types();

        abort_unless(isset($types[$key]), 404, "Unknown trash type: {$key}");

        return $types[$key];
    }

    /** Build an Eloquent query that returns only trashed records for a type. */
    protected function queryTrashed(array $cfg)
    {
        $query = $cfg['model']::query()->onlyTrashed();

        if (isset($cfg['apply']) && is_callable($cfg['apply'])) {
            ($cfg['apply'])($query);
        }

        return $query->latest('deleted_at');
    }
}
