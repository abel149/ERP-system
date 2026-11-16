<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionItem extends Model
{
    protected $fillable = ['production_id','issue_id'];

    public function production(): BelongsTo { return $this->belongsTo(Production::class); }
    public function issue(): BelongsTo      { return $this->belongsTo(Issue::class); }
}
