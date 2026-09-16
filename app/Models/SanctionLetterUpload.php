<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\HandlesTrash;

class SanctionLetterUpload extends Model
{
    use HasFactory, SoftDeletes, HandlesTrash;

    protected $fillable = [
        'sanction_letter_id',
        'uploaded_by',
        'file_path',
        'original_name',
        'file_size',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'file_size' => 'integer',
    ];

    public function letter(): BelongsTo
    {
        return $this->belongsTo(SanctionLetter::class, 'sanction_letter_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** Signed PDFs live on the local disk and survive until a permanent delete. */
    protected function trashFileMap(): array
    {
        return ['local' => 'file_path'];
    }
}