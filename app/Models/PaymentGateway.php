<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PaymentGateway extends Model {
    protected $fillable=['name','slug','status','environment','credentials','configuration'];
    protected $casts=['credentials'=>'encrypted:array','configuration'=>'array','status'=>'boolean'];
}
