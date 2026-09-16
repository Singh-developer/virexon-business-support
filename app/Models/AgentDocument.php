<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\HandlesTrash;

class AgentDocument extends Model
{
    use SoftDeletes, HandlesTrash;
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

    /** Uploaded KYC files stay on disk until the document is purged. */
    protected function trashFileMap(): array
    {
        return ['public' => 'file_path'];
    }

    /**
     * Single source of truth for every required document type.
     *
     * Adding or removing a type here automatically updates:
     *  - agent document cards (label/desc/icon/color/fields/accepted formats)
     *  - admin document review rows (label/desc/emoji)
     *  - upload validation and notifications
     *
     * Keys:
     *  - name         canonical label used in notifications / flash messages
     *  - label/desc   agent-side card title + description
     *  - icon/color   agent-side card icon + color key (see colorMap in view)
     *  - fields       agent-side checklist items
     *  - accept       allowed file extensions shown in the upload modal
     *  - admin_*      admin-side row title + description
     *  - emoji        admin-side row icon
     */
    public static function types(): array
    {
        return [
            'pan_card' => [
                'name'        => 'PAN Card',
                'label'       => 'PAN Card',
                'desc'        => 'PAN Card, Address Proof, Photo',
                'icon'        => 'fa-id-card',
                'color'       => 'blue',
                'fields'      => ['PAN Card', 'Address Proof', 'Photo'],
                'accept'      => '.jpg,.jpeg,.png,.pdf',
                'admin_label' => 'PAN Card',
                'admin_desc'  => 'Clear scan of PAN card. Required for KYC.',
                'emoji'       => '🪪',
            ],
            'aadhaar' => [
                'name'        => 'Aadhaar Card',
                'label'       => 'Aadhaar Card',
                'desc'        => 'Front and back of Aadhaar',
                'icon'        => 'fa-address-card',
                'color'       => 'green',
                'fields'      => ['Front Side', 'Back Side'],
                'accept'      => '.jpg,.jpeg,.png,.pdf',
                'admin_label' => 'Aadhaar Card',
                'admin_desc'  => 'Front & back of Aadhaar. Used for identity verification.',
                'emoji'       => '📋',
            ],
            'photo' => [
                'name'        => 'Photograph',
                'label'       => 'Photograph',
                'desc'        => 'Recent passport size photograph',
                'icon'        => 'fa-image',
                'color'       => 'purple',
                'fields'      => ['Passport Size Photo'],
                'accept'      => '.jpg,.jpeg,.png',
                'admin_label' => 'Photograph',
                'admin_desc'  => 'Passport-size photo. Recent and clear.',
                'emoji'       => '📸',
            ],
            'bank_proof' => [
                'name'        => 'Bank Passbook / Cancelled Cheque',
                'label'       => 'Bank Verification',
                'desc'        => 'Bank details & account proof',
                'icon'        => 'fa-building-columns',
                'color'       => 'yellow',
                'fields'      => ['Cancelled Cheque', 'Account Details', 'IFSC Verification'],
                'accept'      => '.jpg,.jpeg,.png,.pdf',
                'admin_label' => 'Bank Passbook / Cancelled Cheque',
                'admin_desc'  => 'First page of passbook or cancelled cheque leaf.',
                'emoji'       => '🏦',
            ],
            'agent_id_proof' => [
                'name'        => 'Agent ID Proof',
                'label'       => 'Business Support Advance Agreement',
                'desc'        => 'Signed agreement document',
                'icon'        => 'fa-id-badge',
                'color'       => 'orange',
                'fields'      => ['Agreement Form', 'Firm Signature', 'Agent Signature'],
                'accept'      => '.jpg,.jpeg,.png,.pdf',
                'admin_label' => 'Agent ID Proof / Agreement',
                'admin_desc'  => 'Agent ID card, appointment letter, or signed agreement.',
                'emoji'       => '📄',
            ],
            'address_proof' => [
                'name'        => 'Address Proof',
                'label'       => 'Address Proof',
                'desc'        => 'Voter ID / Utility Bill / Aadhaar',
                'icon'        => 'fa-house-user',
                'color'       => 'red',
                'fields'      => ['Address Proof Document'],
                'accept'      => '.jpg,.jpeg,.png,.pdf',
                'admin_label' => 'Address Proof',
                'admin_desc'  => 'Voter ID, utility bill, or any government address proof.',
                'emoji'       => '🏠',
            ],
        ];
    }

    /**
     * Human-readable canonical document type names (notifications / flashes)
     */
    public static function typeLabel(string $type): string
    {
        $config = self::types()[$type] ?? null;
        return $config['name'] ?? ucfirst(str_replace('_', ' ', $type));
    }

    /**
     * All required document types
     */
    public static function requiredTypes(): array
    {
        return array_keys(self::types());
    }
}

