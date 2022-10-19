<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Comment;
use App\Models\Like;

class Product extends Model
{
    use HasFactory;
    protected $fillable=["name","image","seller_id","expiretion_date","category",
                        "phone_no","quantity","price","discount_1",
                        "discount_period_1","discount_2","discount_period_2",
                        "discount_3","discount_period_3","views","likes_counts"];
    public $timestamps=false;
    public function comments()
    {
        return $this->hasMany(Comment::class,'seller_id','id');
    }
    public function likes()
    {
        return $this->hasMany(Like::class,'seller_id','id');
    }
    public function sellers(){
        return $this->belongsTo(Seller::class,'seller_id');
    }
}
