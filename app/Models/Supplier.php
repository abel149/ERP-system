<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model {
    use HasFactory;
    protected $fillable = ['name','email','phone','company','address','tax_number','payment_terms','status','notes'];

    public function purchaseOrders() { return $this->hasMany(PurchaseOrder::class); }
}
