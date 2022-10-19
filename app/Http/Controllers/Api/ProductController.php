<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Like;
use Carbon\Carbon;
use App\Traits\GeneralTraits;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class ProductController extends Controller
{
    use GeneralTraits;

    //createProduct-post
    public function createProduct(Request $request)
    {
        //validation
        $rule=[
            "name"=>"required|max:25|min:2|",
            "expiretion_date"=>"required|Date",
            "category"=>"required",
            "quantity"=>"required",
            "price"=>"required|numeric",
            "discount_1"=>"required|numeric",
            "discount_period_1"=>"required|numeric",
            "discount_2"=>"required|numeric",
            "discount_period_2"=>"required|numeric",
            "discount_3"=>"required|numeric",
            "discount_period_3"=>"required|numeric"
        ];
        $validate=Validator::make($request->all(),$rule);
        if($validate->fails())
        {
            //send response
            $code=$this->returnCodeAccordingToInput($validate);
            return $this->returnValidationError($code,$validate);
        }

        //creat product data
            //create image
            if ($request->has("image")) {
                $file_extention=$request->image; 
                $file_name=time().'.'.$file_extention->extension();
                $path='product';
                $request->image->move($path,$file_name);
            }
            
            //create data
            $product = new Product();
            ($request->has("image"))?$product->image=$file_name:0;
            $product->name=$request->name;
            $product->seller_id=auth()->user()->id;
            $product->expiretion_date=$request->expiretion_date;
            $product->category=$request->category;
            $product->phone_no=auth()->user()->phone_no;
            $product->quantity=$request->quantity;
            $product->price=$request->price;
            $product->discount_1=$request->discount_1;
            $product->discount_period_1=$request->discount_period_1;
            $product->discount_2=$request->discount_2;
            $product->discount_period_2=$request->discount_period_2;
            $product->discount_3=$request->discount_3;
            $product->discount_period_3=$request->discount_period_3;
            $product->save();
            
            //send response
        return $this->returnSuccessMessage("Product created successfully");
    }

    //listProducts-get
    public function listProducts()
    {
        //get seller id
        $seller_id=auth()->user()->id;
        
        //check empty table
        if(!$products=Product::exists())return $this->returnData(GeneralTraits::$data,"Products is empty","",false);
        
        //get data
        $products=Product::select("id","name","image","expiretion_date","price","views","likes_counts")->get();
        $path='/product/';
        $now=Carbon::now();
        foreach ($products as $key) {
            //check finish expiretion date
            if($now->gte($key->expiretion_date)){
                $key->delete();
                continue;
            }

            //check discounts
            $pricePro=$this->priceProduct($key->id);
            $key->pricePro=$pricePro;

            //check user already liked this product
            $is_like= Like::where(['seller_id'=>$seller_id,"product_id"=>$key->id])->exists();
            $key->is_like=$is_like;

            //get image
            $image=$key->image==null?
            $image="https://semantic-ui.com/images/wireframe/image.png":
            asset($path.$key->image);
            $key->image=$image;
            
            //get product data
            $product[]=$key;
        }
        
        //check empty table after check finish expiretion date
        if(!$products=Product::exists())return $this->returnData(GeneralTraits::$data,"Products is empty","",false);        

        //send response list all products
        return $this->returnData($product,"All Products");
    }
    
    //profile+listMyProducts-get
    public function profile()
    {
        //get seller id
        $seller_id=auth()->user()->id;
        
        //get profile data
        $user_data=auth()->user();
        $user_data->makeHidden(["password","created_at","updated_at"]);
        
        //check if user don't has any products
        if(!Product::where(["seller_id"=>$seller_id])->exists())
        return $this->returnData(GeneralTraits::$data,"You don't have any products","",false,1,$user_data);
        
        //get data
        $products=Product::where(["seller_id"=>$seller_id]);
        $products=$products->select("id","name","image","expiretion_date","price","views","likes_counts")->get();
        $path='/product/';
        $now=Carbon::now();
        foreach ($products as $key) {
            //check finish expiretion date
            if($now->gte($key->expiretion_date)){
                $key->delete();
                continue;
            }

            //check discounts
            $pricePro=$this->priceProduct($key->id);
            $key->pricePro=$pricePro;

            //check user already liked this product
            $is_like= Like::where(['seller_id'=>$seller_id,"product_id"=>$key->id])->exists();
            $key->is_like=$is_like;
            
            //get image
            $image=$key->image==null?
            $image="https://semantic-ui.com/images/wireframe/image.png":
            asset($path.$key->image);
            $key->image=$image;

            //get product data
            $product[]=$key;
        }
            
        //check empty table after check finish expiretion date
        if(!$products=Product::exists())return $this->returnData(GeneralTraits::$data,"Products is empty","",false); 
        
        //send response profile and list my products
        return $this->returnData($product,"Profile with my products","S000",true,1,$value1=$user_data);
    }
  
       //searchProduct-get
       public function searchProduct(Request $request)
       {
           //get seller id
           $now = Carbon::now();
           $seller_id = auth()->user()->id;
   
           //search by category
           if (isset($request->category)) {
               //check if not exists
               if(!Product::where('category', 'like', '%' . $request->category . '%')->where('expiretion_date','>=',$now)->exists())
               return $this->returnData(GeneralTraits::$data,"Product not found","",false);
               
               //get data
               $result = Product::where("category", "like", "%" . $request->category . "%")->orderby("category","asc")->where('expiretion_date','>=',$now);
               $result=$result->select("id","name","image","views","likes_counts","expiretion_date","price")->get();
   
               $path = '/product/';
               foreach ($result as $key) {
                   //check finish expiretion date
                   if ($now->gte($key->expiretion_date)) {
                       $key->delete();
                       continue;
                   }
                   
                   //check discounts
                   $pricePro=$this->priceProduct($key->id);
                   $key->pricePro=$pricePro;
                   
                   //check user already liked this product
                   $is_like = Like::where(['seller_id' => $seller_id, "product_id" => $key->id])->exists();
                   $key->is_like=$is_like;
                   
                   //get image
                   $image_name=$key->image;
                   $image=asset($path.$image_name);
                   $key->image=$image;
                   
                   //get product data
                   $product[] = $key;
               }
               //send response
               return $this->returnData($product);
               
               //search by expiretion date
           } else if (isset($request->expiretion_date)) {
               //check if not exists
               if (!Product::where('expiretion_date','>=',$now)->exists()) {
                   //send response
                   return $this->returnData(GeneralTraits::$data,"Product not found","",false);
               }
   
               //get data
               $result = Product::where('expiretion_date','<=',$request->expiretion_date )->orderBy('expiretion_date','desc')->where('expiretion_date','>=',$now);
               $result=$result->select("id","name","image","views","likes_counts","expiretion_date","price")->get();
   
               $path = '/product/';
               $now = Carbon::now();
               foreach ($result as $key) {
                   //check finish expiretion date
                   if ($now->gte($key->expiretion_date)) {
                       $key->delete();
                       continue;
                   }
   
                   //check discounts
                   $pricePro=$this->priceProduct($key->id);
                   $key->pricePro=$pricePro;
   
                   //check user already liked this product
                   $is_like = Like::where(['seller_id' => $seller_id, "product_id" => $key->id])->exists();
                   $key->is_like=$is_like;
   
                   //get image
                   $image_name = $key->image;
                   $image = asset($path . $image_name);
                   $key->image=$image;
   
                   //get product data
                   $product[] = $key;
               }
               //send response
               return $this->returnData($product);
   
               //search by name
           } else {
               //check if not exists
               if (!Product::where("name", "like", "%" . $request->name . "%")->where('expiretion_date','>=',$now)->exists()) {
   
                   //send response
                   return $this->returnData(GeneralTraits::$data,"Product not found","",false);
               } else {
   
                   //get data
                   $result = Product::where("name", "like", "%" . $request->name . "%")->orderby("name","asc")->where('expiretion_date','>=',$now);
                   $result=$result->select("id","name","image","views","likes_counts","expiretion_date","price")->get();
   
                   $path = '/product/';
                   $now = Carbon::now();
                   foreach ($result as $key) {
   
                       //check finish expiretion date
                       if ($now->gte($key->expiretion_date)) {
                           $key->delete();
                           continue;
                       }
   
                       //check discounts
                       $pricePro=$this->priceProduct($key->id);
                       $key->pricePro=$pricePro;
                       
                       //check user already liked this product
                       $is_like = Like::where(['seller_id' => $seller_id, "product_id" => $key->id])->exists();
                       $key->is_like=$is_like;
   
                       //get image
                       $image_name = $key->image;
                       $image = asset($path . $image_name);
                       $key->image=$image;
   
                       //get product data
                       $product[] = $key;
                   }
                   //send response
                   return $this->returnData($product);
               }
           }
       }
    //priceProduct-get
    public function priceProduct($product_id)
    {
        //get product data
        $product=Product::find($product_id);

        //check if not exists
        if(!$product)return $this->returnError("not found",404);
        //لعما شو تافه طيب فتحه من لابتوبك شو دخلني انا طيب @ aldomani
        //calc new price
        $price=$product->price;
        $Dis_P_F=$product->discount_period_1;
        $Dis_P_S=$product->discount_period_2;
        $Dis_P_Th=$product->discount_period_3;
        $Dis_F=$product->discount_1;
        $Dis_S=$product->discount_2;
        $Dis_Th=$product->discount_3;
        $exp_date=$product->expiretion_date;
        $now=Carbon::now();
        $dif=$now->diffInDays($exp_date);

        //first discount clac
        if($Dis_P_F>= $dif &&  $Dis_P_S <$dif)
        {
            $pricePro= $price- ($price*$Dis_F/100);
            return  $pricePro;
        }

        //seconde discount clac
        else if($Dis_P_S>= $dif &&  $Dis_P_Th <$dif)
        {
            $pricePro= $price- ($price*$Dis_S/100);
            return  $pricePro;
        }

        //third discount clac
        elseif($Dis_P_Th >= $dif)
        {
            $pricePro= $price- ($price*$Dis_Th/100);
            return $pricePro;
        }

        //price withot discount
        return $price;
    }

    //singleProductById-get
    public function singleProductById($product_id)
    {
        //check if not exists
        if(!product::where(["id"=>$product_id])->exists())
        return $this->returnError('not found',404);
        
        //get product data
        $product=Product::with("sellers")->find($product_id);
        
        //check finish expiretion date
        $now=Carbon::now();
        if($now->gte($product->expiretion_date)){
            $product->delete();
            return $this->returnData(GeneralTraits::$data,'Product deleted due to expiration',"",false);
        }

        //views increment for all the users except the seller
        if($product->sellers->id!=auth()->user()->id)
        $product->increment("views");

        //check discounts
        $pricePro=$this->priceProduct($product_id);
        $product->pricePro=$pricePro;

        //get data
        $product->makeHidden(["phone_no","seller_id"]);
        $product->sellers->makeHidden(["password"]);
        
        //check user already liked this product
        $is_like= Like::where(['seller_id'=>auth()->user()->id,"product_id"=>$product->id])->exists();
        $product->is_like=$is_like;
        
        //get image
        $path='/product/';
        $image=$product->image==null?
        $image="https://semantic-ui.com/images/wireframe/image.png":
        asset($path.$product->image);
        $product->image=$image;
        
        //send response single product
        return $this->returnData($product,"product data with seller data");
    }
    
    //deleteProductById-get
    public function deleteProductById($product_id)
    {
        //get seller id
        $seller_id=auth()->user()->id;

        //check user owner this product or not found
        if(!Product::where([
            "seller_id"=>$seller_id,
            "id"=>$product_id
        ])->exists())
        return $this->returnError("not found",404);

        //delete product
        $product=Product::find($product_id)->delete();
        
        //send response
        return $this->returnSuccessMessage("product deleted successfully");
    }
    
    //editProductById-post
        //editProductById-post
        public function editProductById(Request $request)
        {
            //get seller id
            $seller_id=auth()->user()->id;
    
            //check user owner this product
            if(!Product::where([
                "seller_id"=>$seller_id,
                "id"=>$request->id
            ])->exists())
            return $this->returnError("not found",404);
    
            //update product data
            $product=Product::find($request->id);
            isset($request->name)==true?($product->name=$request->name):0;
            isset($request->category)==true?($product->category=$request->category):0;
            isset($request->quantity)==true?($product->quantity=$request->quantity):0;
            isset($request->price)==true?($product->price=$request->price):0;
            isset($request->discount_1)==true?($product->discount_1=$request->discount_1):0;
            isset($request->discount_period_1)==true?($product->discount_period_1=$request->discount_period_1):0;
            isset($request->discount_2)==true?($product->discount_2=$request->discount_2):0;
            isset($request->discount_period_2)==true?($product->discount_period_2=$request->discount_period_2):0;
            isset($request->discount_3)==true?($product->discount_3=$request->discount_3):0;
            isset($request->discount_period_3)==true?($product->discount_period_3=$request->discount_period_3):0;
            //image update
            if ($request->has("image")) {
                $file_extention=$request->image; 
                $file_name=time().'.'.$file_extention->extension();
                $path='product';
                $request->image->move($path,$file_name);
                $product->image=$file_name;
            }
            
            //save data
            $product->save();
    
            //send response
            return $this->returnSuccessMessage("Product updeted successfully");
        }
    
    //likeProductById-get
    public function likeProductById($product_id)
    {
        //get seller id
        $seller_id=auth()->user()->id;

        //check user already is liked this product
        if(Like::where([
            "seller_id"=>$seller_id,
            "product_id"=>$product_id
            ])->exists()){

                //remove like
                Like::where([
                    "seller_id"=>$seller_id,
                    "product_id"=>$product_id])->delete();
                Product::where("id",$product_id)->decrement("likes_counts");

                //send response
                $product=Product::find($product_id);
                return $this->returnData($product->likes_counts,"Dislike done");
            }

            //user dont have a like

                //like
                Product::where("id",$product_id)->increment("likes_counts");
                $like=new Like();
                $like->product_id=$product_id;
                $like->seller_id=$seller_id;
                $like->save();

                //send respones
                $product=Product::find($product_id);
                return $this->returnData($product->likes_counts,"Like done");
    }

    //createCommentById-post
    public function createCommentById(Request $request)
    {
        //get user id
        $seller_id=auth()->user()->id;

        //check product not exists
        if(!Product::where([
            "id"=>$request->product_id
        ])->exists())
        return $this->returnError("not found",404);

        //creare comment
        $comment=new Comment();
        $comment->comment=$request->comment;
        $comment->seller_id=$seller_id;
        $comment->product_id=$request->product_id;
        $comment->save();

        //send response
        return $this->returnData([
            "comment"=>$comment->comment,
            "seller name"=>auth()->user()->name
        ],"comment created successfully");
    }

    //listCommentsById-get
    public function listCommentsById($product_id)
    {
        //check product not exists
        if(!Product::where([
            "id"=>$product_id
        ])->exists())
        return $this->returnError("not found",404);

        //list all comments
        $comments=Comment::select("seller_id","comment")->where("product_id",$product_id)->get();
        foreach ($comments as $key) {
            
            //get comment owner's name
            $seller_name=Seller::select("name")->where("id",$key->seller_id)->get();
            $product[]=["comment"=>$key->comment,"seller name"=>$seller_name];
        }

        //send response
        return $this->returnData($comments,"All Comments");
    }
}
