<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrder extends Model {
    use HasFactory;
    protected $fillable = ['po_number','supplier_id','user_id','subtotal','tax','shipping','total','status','payment_status','order_date','expected_date','received_date','notes'];
    protected $casts = ['order_date'=>'date','expected_date'=>'date','received_date'=>'date'];

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function user()     { return $this->belongsTo(User::class); }
    public function items()    { return $this->hasMany(PurchaseOrderItem::class); }

    public static function nextPoNumber(): string {
        $last = static::orderByDesc('id')->value('po_number');
        $n = $last ? ((int) substr($last, 3)) + 1 : 1;
        return 'PO-'.str_pad($n, 5, '0', STR_PAD_LEFT);
    }
}
