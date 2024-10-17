<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Request;

class UserService {
    //trait customize the methods for successful , failed , authentecation responses.
    use ApiResponseTrait;
    /**
     * method to view all users with filter on role
     * @param   Request $request
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function get_all_Users($role){
        try {
            $user = User::filter($role)->get();
            return $user;
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with fetche users', 400);}
    }
    //========================================================================================================================
    /**
     * method to store a new user
     * @param   $data
     * @return /Illuminate\Http\JsonResponse ig have an error
     */
    public function create_User($data) {
        try {
            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = $data['password'];
            
            $user->save(); 
    
            return $user; 
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with create user', 400);}
    }    
    //========================================================================================================================
    /**
     * method to update user alraedy exist
     * @param  $data
     * @param  User $user
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function update_user($data,User $user){
        try {  
            $user->name = $data['name'] ?? $user->name;
            $user->email = $data['email'] ?? $user->email;
            $user->password = $data['password'] ?? $user->password;  
            $user->role = $data['role'] ?? $user->role;

            $user->save();  
            return $user;

        }catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with view user', 400);}
    }
    //========================================================================================================================
    /**
     * method to show user alraedy exist
     * @param  $user_id
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function view_User($user_id) {
        try {    
            $user = User::find($user_id);
            if(!$user){
                throw new \Exception('user not found');
            }
            return $user;
        } catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 404);
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with update user', 400);}
    }
    //========================================================================================================================
    /**
     * method to soft delete user alraedy exist
     * @param  User $user
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function delete_user($user_id)
    {
        try {  
            $user = User::find($user_id);
            if(!$user){
                throw new \Exception('user not found');
            }
             //منع الأدمن من إزالة حسابه
             if ($user->role == 'admin') {
                throw new \Exception('You cannot soft delete admin account');
            }
            $user->delete();
            return true;
        }catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 400);
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with deleting user', 400);}
    }
    //========================================================================================================================
    /**
     * method to return all soft delete users
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function all_trashed_user()
    {
        try {  
            $users = User::onlyTrashed()->get();
            return $users;
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with view trashed user', 400);}
    }
    //========================================================================================================================
    /**
     * method to restore soft delete user alraedy exist
     * @param   $user_id
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function restore_user($user_id)
    {
        try {
            $user = User::withTrashed()->find($user_id);
            if(!$user){
                throw new \Exception('user not found');
            }
            return $user->restore();
        }catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 400);   
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with restore user', 400);
        }
    }
    //========================================================================================================================
    /**
     * method to force delete on user that soft deleted before
     * @param   $user_id
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function forceDelete_user($user_id)
    {   
        try {
            $user = User::onlyTrashed()->find($user_id);
            if(!$user){
                throw new \Exception('user not found');
            }
             //منع الأدمن من إزالة حسابه
             else if ($user->role == 'admin') {
                throw new \Exception('You cannot delete admin account');
            }
            return $user->forceDelete();
        }catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 400);   
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with deleting user', 400);}
    }
    //========================================================================================================================

}
