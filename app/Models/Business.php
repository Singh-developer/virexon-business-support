<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Business extends Model {
    use SoftDeletes;
    protected $fillable=['name','contact_person','email','phone','address','tax_number','status','created_by'];
    public function creator(){return $this->belongsTo(User::class,'created_by');}
    public function users(){return $this->hasMany(User::class);}
    public function cards(){return $this->hasMany(VirtualCard::class);}
    public function payments(){return $this->hasMany(Payment::class);}
    public function transactions(){return $this->hasMany(Transaction::class);}
}
