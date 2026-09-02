<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\TransactionType;
class Transaction extends Model {
    use SoftDeletes;
    protected $fillable=['business_id','user_id','card_id','payment_id','gateway','gateway_transaction_id','transaction_type','amount','fee','net_amount','status','reference','description','metadata'];
    protected $casts=['transaction_type'=>TransactionType::class,'amount'=>'decimal:2','fee'=>'decimal:2','net_amount'=>'decimal:2','metadata'=>'array'];
    public function business(){return $this->belongsTo(Business::class);}
    public function user(){return $this->belongsTo(User::class);}
    public function card(){return $this->belongsTo(VirtualCard::class,'card_id');}
    public function payment(){return $this->belongsTo(Payment::class);}
}
