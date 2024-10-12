<?php

namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\ApiResponseTrait;


class AuthService {
    //trait customize the methods for successful , failed , authentecation responses.
    use ApiResponseTrait;
    /**
     * function to login users
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function login($credentials){
        // This method authenticates a user with their email and password. 
        //When a user is successfully authenticated, the Auth facade attempt() method returns the JWT token. 
        //The generated token is retrieved and returned as JSON with the user object
        try {
            $token = Auth::attempt($credentials);
            if (!$token) {
                return $this->failed_Response('The email or password is not correct', 401);
            }
        return $token;

        }catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with login', 400);}
    }
//========================================================================================================================
    /**
     * function to logout users
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function logout(){
        try {
            Auth::logout();
            return true;
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with logout', 400);}
    }
//========================================================================================================================
    /**
     * function to refresh token
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function refresh(){
        try {
            return [
                'user' => Auth::user(),
                'token' => Auth::refresh(),
            ];

        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with refresh token', 400);}
    }
//========================================================================================================================
}