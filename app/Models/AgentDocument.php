<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentDocument extends Model
{
    protected $fillable = [
        'user_id',
        'document_type',
        'file_path',
        'original_name',
        'status',
        'admin_note',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Human-readable document type names
     */
    public static function typeLabel(string $type): string
    {
        return match($type) {
            'pan_card'       => 'PAN Card',
            'aadhaar'        => 'Aadhaar Card',
            'photo'          => 'Photograph',
            'bank_proof'     => 'Bank Passbook / Cancelled Cheque',
            'agent_id_proof' => 'Agent ID Proof',
            'address_proof'  => 'Address Proof',
            default          => ucfirst(str_replace('_', ' ', $type)),
        };
    }

    /**
     * All required document types
     */
    public static function requiredTypes(): array
    {
        return ['pan_card', 'aadhaar', 'photo', 'bank_proof', 'agent_id_proof', 'address_proof'];
    }
}

