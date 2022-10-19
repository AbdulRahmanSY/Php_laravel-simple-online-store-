<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Comment;
use App\Models\Like;
use Laravel\Passport\HasApiTokens;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticateContract;

class Seller extends Model implements AuthenticateContract
{
    use HasFactory,HasApiTokens,Authenticatable;
    protected $fillable=["name","email","password","phone_no"];
    public $timestamps=false;
    public function products()
    {
        return $this->hasMany(Product::class,'seller_id','id');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class,'seller_id','id');
    }
    public function likes()
    {
        return $this->hasMany(Like::class,'seller_id','id');
    }
}
