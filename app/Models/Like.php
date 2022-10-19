<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;
    protected $fillable=["seller_id","product_id"];
    public $timestamps=false;

    public function sellers()
    {
        return $this->belongsTo(Seller::class,'seller_id');
    }
    public function products()
    {
        return $this->belongsTo(Product::class,'product_id');
    }
}
