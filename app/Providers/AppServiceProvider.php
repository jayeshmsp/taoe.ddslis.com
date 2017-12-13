<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Setting;
use App\User;
use DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $setting = Setting::first();
        if (!empty($setting)) {
            config([
                'plivo.PLIVO_AUTH_ID' => $setting->plivo_auth_id,
                'plivo.PLIVO_AUTH_TOKEN' => $setting->plivo_auth_token,
                'plivo.APP_SRC_NUMBER' => $setting->plivo_auth_number
            ]);
            \Artisan::call('config:clear');
        }

        //Add this custom validation rule.
        Validator::extend('alpha_space', function ($attribute, $value) {
            return preg_match('/^[\pL\s]+$/u', $value); 
        });

        \Schema::defaultStringLength(191);
        
        //v validation for duplicate entry
        Validator::extend('is_user_exist', function($attribute, $value, $parameters, $validator) {
               $return  = false;
               $numOfField = count($parameters);
               $whereArr = array();
               if($numOfField %2 == 0 ) {
                   $numOfField--;
                   $id = end($parameters);
               }
               for($i = 1 ; $i < $numOfField; $i+=2) {
                    $whereArr[$parameters[$i]] = $parameters[$i+1];
               }
               if(empty($parameters[0] ) ) {
                   return $return;
               }
               $whereArr[$attribute] = $value;
               if(isset($id)) {
                   $records = DB::table($parameters[0])->where($whereArr)->where("id","<>",$id)->count();
               } else {
                    $records = DB::table($parameters[0])->where($whereArr)->count();
               }
               if($records > 0) {
                   return $return;
               }
               return true;
               //print "<pre>"; print_r($whereArr); print "</pre>";
               
               //echo  $value; exit;
        });

        Validator::extend('verify_choosen_method', function($attribute, $value, $parameters, $validator) {
               $return  = true;
               if($value == 2) {
                  if(isset($parameters[1]) && !empty($parameters[1])) {
                    $return = true; 
                  }else{
                    $return = false; 
                  }
               }
               return $return;
               
        });

        Validator::extend('email_name_username_validation', function($attribute, $value, $parameters, $validator) {
            $input = $validator->getData();
            if (!empty($input)) {
                $user =   User::where('first_name','=',$input['first_name'])
                                ->where('last_name','=',$input['last_name'])
                                ->where('email','=',$input['email'])
                                ->first();

                if (isset($user->username)) {
                    if (!empty($user->username)) {
                       return false; 
                    }else{
                        return true;
                    }
                }
            }
            return true;
        });
        
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
