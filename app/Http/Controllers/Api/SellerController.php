<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Traits\GeneralTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class SellerController extends Controller
{
    use GeneralTraits;

    //register-post
    public function register(Request $request)
    {
        // validation
        $rule=[
            "name" => "required",
            "email" => "required|email|unique:sellers",
            "password" => "required|confirmed",
            "phone_no" => "required",
        ];
        $validate =Validator::make($request->all(),$rule);
        if($validate->fails())
        {
            $code=$this->returnCodeAccordingToInput($validate);
            return $this->returnValidationError($code,$validate);
        }
        // create data
        $seller = new Seller();
        $seller->name = $request->name;
        $seller->email = $request->email;
        $seller->phone_no = $request->phone_no;
        $seller->password = bcrypt($request->password);
        $seller->save();

        // send response
        return $this->returnSuccessMessage("Seller registerd successfully");
        
    }
    //login-post
    public function login(Request $request)
    {
        //validation
        $rule=[
            "email" => "required|email",
            "password" => "required",
            ];
        $validate =Validator::make($request->all(),$rule);
        if($validate->fails())
        {
            $code=$this->returnCodeAccordingToInput($validate);
            return $this->returnValidationError($code,$validate);
        }
        
        //validate seller data
        if(!auth()->attempt(request()->only(['email','password']))){
            return $this->returnError("Invalid Credentials");
        }

        //create token
        $token = auth()->user()->createToken("auth_token")->accessToken;

        //send response
        return $this->returnToken($token);
    }
    
    //logout-get
    public function logout(Request $request)
    {
        // get token value
        $token = $request->user()->token();

        // revoke this token value
        $token->revoke();
        
        //send response
        return $this->returnSuccessMessage("Seller logged out successfully");
    }
}
