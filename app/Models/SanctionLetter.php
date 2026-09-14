<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SanctionLetter extends Model
{
    use HasFactory;

    public const REFERENCE_PREFIX = 'RFE';
    public const SANCTION_LETTER_PREFIX = 'V-EOM/SL';

    /** Hours an agent has to upload the signed PDF before the slot expires. */
    public const UPLOAD_WINDOW_HOURS = 48;

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

    /**
     * Derived workflow badge used across admin and agent UIs.
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

        // Already uploaded and waiting for the admin decision - cannot re-upload yet.
        if (($this->signed_pdf_upload_count ?? 0) > 0 && $this->review_status === 'under_review') {
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
}
