<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WebhookEvent extends Model {
    protected $fillable=['gateway','event_id','event_type','payload_hash','payload','processed_at'];
    protected $casts=['payload'=>'array','processed_at'=>'datetime'];
}
