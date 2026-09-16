<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\HandlesTrash;

class Ticket extends Model
{
    use HasFactory, SoftDeletes, HandlesTrash;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'attachment_path',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Attachments stay on disk until the ticket is permanently deleted. */
    protected function trashFileMap(): array
    {
        return ['public' => 'attachment_path'];
    }
}
