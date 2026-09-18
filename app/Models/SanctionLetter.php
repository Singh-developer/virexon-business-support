<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Models\Concerns\HandlesTrash;

class SanctionLetter extends Model
{
    use HasFactory, SoftDeletes, HandlesTrash;

    public const REFERENCE_PREFIX = 'RFE';
    public const SANCTION_LETTER_PREFIX = 'V-EOM/SL';

    /** Hours an agent has to upload the signed PDF before the slot expires. */
    public const UPLOAD_WINDOW_HOURS = 48;

    public const PROCESS_FEE_UNPAID = 'unpaid';
    public const PROCESS_FEE_PENDING = 'pending';
    public const PROCESS_FEE_PAID = 'paid';

    protected $table = 'sanction_letters';

    protected $fillable = [
        'sanction_number',
        'user_id',
        'business_id',
        'title',
        'subject',
        'greeting',
        'body',
        'dynamic_fields',
        'closing',
        'notes',
        'signature_name',
        'signature_designation',
        'signature_company',
        'signature_image',
        'upload_deadline_at',
        'pdf_path',
        'status',
        'process_fee',
        'process_fee_status',
        'process_fee_reference',
        'process_fee_payment_id',
        'process_fee_paid_at',
        'process_fee_gateway',
        'process_fee_response',
        'sent_at',
        'downloaded_at',
        'signed_pdf_path',
        'signed_pdf_uploaded_at',
        'signed_pdf_upload_count',
        'review_status',
        'reviewed_at',
        'reviewed_by',
        'review_comment',
    ];

    protected $casts = [
        'sanction_number' => 'integer',
        'sent_at' => 'datetime',
        'upload_deadline_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'downloaded_at' => 'datetime',
        'signed_pdf_uploaded_at' => 'datetime',
        'signed_pdf_upload_count' => 'integer',
        'reviewed_at' => 'datetime',
        'review_status' => 'string',
        'dynamic_fields' => 'array',
        'process_fee' => 'decimal:2',
        'process_fee_paid_at' => 'datetime',
        'process_fee_gateway' => 'string',
        'process_fee_response' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function uploads(): HasMany
    {
        return $this->hasMany(SanctionLetterUpload::class);
    }

    public function latestUpload(): HasOne
    {
        return $this->hasOne(SanctionLetterUpload::class)->latestOfMany();
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** The agent who this letter was issued to. */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getFullNameAttribute(): string
    {
        return $this->user->name ?? '';
    }

    /** Signed PDF uploads are trashed / purged together with the letter. */
    protected function trashRelations(): array
    {
        return ['uploads'];
    }

    /**
     * Uploaded files are kept in the trash and removed only on permanent delete.
     * A shared signature image is retained while any other letter still uses it.
     */
    protected function deleteTrashFiles(): void
    {
        if ($this->pdf_path) {
            Storage::disk('public')->delete($this->pdf_path);
        }

        if ($this->signed_pdf_path) {
            Storage::disk('local')->delete($this->signed_pdf_path);
        }

        if ($this->signature_image) {
            $stillUsed = static::withTrashed()
                ->where('id', '!=', $this->id)
                ->where('signature_image', $this->signature_image)
                ->exists();

            if (! $stillUsed) {
                Storage::disk('public')->delete($this->signature_image);
            }
        }
    }

    /**
     * Derived workflow badge used across admin and agent UIs.
     * The explicit admin-set review_status always wins, so changing
     * pending <-> under_review is reflected immediately on both sides.
     * Priority: approved -> re-upload required -> under review -> pending.
     */
    public function workflowStatus(): array
    {
        if ($this->review_status === 'approved') {
            return ['key' => 'approved', 'label' => 'Approved'];
        }

        if ($this->review_status === 'reupload_required') {
            return ['key' => 'reupload_required', 'label' => 'Re-upload Required'];
        }

        if ($this->review_status === 'under_review') {
            return ['key' => 'under_review', 'label' => 'Under Review'];
        }

        if ($this->review_status === 'pending') {
            return ['key' => 'pending', 'label' => 'Pending'];
        }

        // Legacy fallback for rows without an explicit review_status:
        // an uploaded signed copy implies it is waiting for admin review.
        if (($this->signed_pdf_upload_count ?? 0) > 0) {
            return ['key' => 'under_review', 'label' => 'Under Review'];
        }

        return ['key' => 'pending', 'label' => 'Pending'];
    }

    /** Whether the agent may currently upload a signed PDF for this letter. */
    public function canUpload(): bool
    {
        if ($this->review_status === 'approved') {
            return false;
        }

        // One signed copy at a time: once uploaded, the agent must wait for
        // the admin decision. Another upload is allowed only after the admin
        // explicitly requests a re-upload (which opens a fresh 48h slot).
        if (($this->signed_pdf_upload_count ?? 0) > 0 && $this->review_status !== 'reupload_required') {
            return false;
        }

        // The upload slot has expired - only an admin reset can open a new one.
        if ($this->uploadDeadlinePassed()) {
            return false;
        }

        return true;
    }

    /**
     * The deadline by which the agent must upload the signed PDF.
     * Falls back to sent_at/created_at + the upload window for older rows.
     */
    public function uploadDeadline(): \Illuminate\Support\Carbon
    {
        return $this->upload_deadline_at
            ?? ($this->sent_at ?: $this->created_at)->addHours(self::UPLOAD_WINDOW_HOURS);
    }

    /** Whether the upload window has already expired. */
    public function uploadDeadlinePassed(): bool
    {
        return now()->greaterThan($this->uploadDeadline());
    }

    /** Human friendly remaining time, or null once expired. */
    public function uploadTimeRemaining(): ?string
    {
        if ($this->uploadDeadlinePassed()) {
            return null;
        }
        return now()->diffForHumans($this->uploadDeadline(), ['parts' => 2]);
    }

    /**
     * Get a dynamic field value by key.
     */
    public function getDynamicValue(string $key, ?string $default = null): ?string
    {
        return $this->dynamic_fields[$key] ?? $default;
    }

    /**
     * Get the public URL of the signature image.
     */
    public function getSignatureImageUrlAttribute(): ?string
    {
        if (!$this->signature_image) {
            return null;
        }
        return asset('storage/' . $this->signature_image);
    }

    /**
     * Format a numeric sequence into the reference number pattern,
     * e.g. RFE/AF/2026/000999.
     */
    public static function formatReferenceNo(int|string $sequence, ?int $year = null): string
    {
        $year ??= (int) now()->format('Y');
        return self::REFERENCE_PREFIX . '/AF/' . $year . '/' . str_pad((string) (int) $sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Format a numeric sequence into the sanction letter number pattern,
     * e.g. V-EOM/SL/AF/2026/000999.
     */
    public static function formatSanctionLetterNo(int|string $sequence, ?int $year = null): string
    {
        $year ??= (int) now()->format('Y');
        return self::SANCTION_LETTER_PREFIX . '/AF/' . $year . '/' . str_pad((string) (int) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public function getReferenceNoAttribute(): string
    {
        return self::formatReferenceNo($this->sanction_number ?? $this->id, $this->created_at?->year);
    }

    public function getSanctionLetterNoAttribute(): string
    {
        return self::formatSanctionLetterNo($this->sanction_number ?? $this->id, $this->created_at?->year);
    }

    /**
     * Processing fee in rupees. Prefers the dedicated column and falls back
     * to the legacy `process_fee` value stored inside dynamic_fields.
     */
    public function processFeeAmount(): float
    {
        $fee = (float) $this->process_fee;

        if ($fee > 0) {
            return $fee;
        }

        $fields = is_array($this->dynamic_fields) ? $this->dynamic_fields : [];

        return (float) ($fields['process_fee'] ?? 0);
    }

    /** Whether a processing fee applies to this letter. */
    public function hasProcessFee(): bool
    {
        return $this->processFeeAmount() > 0;
    }

    /**
     * Whether the agent has satisfied the processing fee requirement.
     * Letters without a fee are considered paid so the upload stays open.
     */
    public function processFeePaid(): bool
    {
        if (! $this->hasProcessFee()) {
            return true;
        }

        return $this->process_fee_status === self::PROCESS_FEE_PAID;
    }

    /** Whether a fee is still owed and no payment is currently in flight. */
    public function processFeeDue(): bool
    {
        return ! $this->processFeePaid()
            && $this->process_fee_status !== self::PROCESS_FEE_PENDING;
    }

    /** Whether a fee payment was initiated and is awaiting confirmation. */
    public function processFeePending(): bool
    {
        return $this->process_fee_status === self::PROCESS_FEE_PENDING;
    }
}
