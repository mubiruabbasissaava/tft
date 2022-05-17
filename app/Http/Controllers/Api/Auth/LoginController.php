<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Client;
use Illuminate\Support\Carbon;
use App\User;
use App\Profile;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
//JWT Initialisation
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Encoding\JoseEncoder;

use Illuminate\Support\Facades\Http;


class LoginController extends Controller
{


    const MESSAGE = "successfully updated";

    use IssueTokenTrait;

    private $client;

    public function __construct()
    {
        $this->client = Client::find(1);
        $this->middleware('doNotCacheResponse');

    }

    public function loginFacebook(Request $request)
    {
        $provider = "facebook"; // or $request->input('provider_name') for multiple providers
    
        // get the provider's user. (In the provider server)

        $check_banned = DB::table('banned_user_devices')->where('device_serial',$request->device_id)->count();
        if($check_banned != 0)
        {

            $code = [
                'status' => 400,
                'code' => 400,
                'message' => 'This Device has been banned from accessing '.env('APP_NAME').' services. Contact '.env('APP_NAME').' for further assistance.',
            ];
    
                return response()->json($code,$code['status']);

        }
        $providerUser = Socialite::driver($provider)->userFromToken($request->token);
        // check if access token exists etc..
        // search for a user in our server with the specified provider id and provider name
        $user = User::where('provider_name', $provider)->where('provider_id', $providerUser->id)->first();
        // if there is no record with these data, create a new user
        if($user == null){
            $user = User::create([
                'name' => $providerUser->name,
                'email' => $providerUser->email,
                'avatar' => $providerUser->avatar,
                'premuim' => false,
                'manual_premuim' => false,
                'email_verified_at' => \Carbon\Carbon::now(),
                'status'=>1,
                'provider_name' => $provider,
                'provider_id' => $providerUser->id
            ]);
        

            
        }
 $user = User::where('provider_name', $provider)->where('provider_id', $providerUser->id)->first();
        
        if($user == null)
        {
            $code = [
                'status' => 400,
                'code' => 400,
                'message' => 'Account is not connected to Facebook Login. Please login with username and password',
            ];
            
    
                return response()->json($code,$code['status']);
        }
        
        $check = $user;
        if($check->status != 1)
        {

            $code = [
                'status' => 400,
                'code' => 400,
                'message' => 'Account ('.$check->name.') has been banned from accessing '.env('APP_NAME').' services. Contact '.env('APP_NAME').' for further assistance.',
            ];
            
    
                return response()->json($code,$code['status']);

        }
            //IF PASSWORD IS MATCHING
            //Get Current user auth('api')->user();

            //Check User Device

            $check_devices = DB::table('user_devices')->where('user_id',$check->id)->count();
             
            $check_active_devices = DB::table('user_devices')->where('user_id',$check->id)->where('status',1)->count();
            
              

            //$user_plan = DB::table('plans')->where('name',$check->pack_name)->orWhere('stripe_plan_id',$check->pack_id)->first();

            if($check->premuim == 1)
            {

            }

            if($check_devices!=0)
            {
                //IF There are Registered Devices
                //Check if the device is one of the allowed devices for subsription
                $check_dev_primary = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->where('is_primary',1)->count();

                $check_dev = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->count();

                $check_dev_active = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->where('status',1)->count();


                if($check_dev!=0)
                {
                    //If The Device is allowed and active


                    if($check_dev_primary!=0 && $check_active_devices ==0)
                    {
                    
                        //If Device is primary and active
                       $device_update = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->update([
                           'status'=>1,
                           'updated_at'=> Carbon::now()
                    ]);
                        
                        //\Log::debug($this->issueToken($request, 'password'));
                        $tokenResult = $user->createToken('facebook');
            return \response()->json([
                'token_type'    =>  'Bearer',
                'expires_in'    =>  $tokenResult->token->expires_at->diffInSeconds(Carbon::now()),
                'access_token'  =>  $tokenResult->accessToken,
                'refresh_token'  =>  $tokenResult->refreshToken,
                'type'          =>  'facebook'
            ]);
                    }

                    else if($check_dev_active!=0 && $check_active_devices == 1)
                    {
                    
                        //If Device is primary and active
                       $device_update = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->update([
                           'status'=>1,
                           'updated_at'=> Carbon::now()
                    ]);
                        
                        //\Log::debug($this->issueToken($request, 'password'));
                        $tokenResult = $user->createToken('facebook');
            return \response()->json([
                'token_type'    =>  'Bearer',
                'expires_in'    =>  $tokenResult->token->expires_at->diffInSeconds(Carbon::now()),
                'access_token'  =>  $tokenResult->accessToken,
                'refresh_token'  =>  $tokenResult->refreshToken,
                'type'          =>  'facebook'
            ]);
                    }
                    else
                    {
                     // If Device is not Primary
                     
                        $device_update = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->update([
                            'status'=>1,
                            'updated_at'=> Carbon::now()
                         ]);
 
                         $tokenResult = $user->createToken('facebook');
            return \response()->json([
                'token_type'    =>  'Bearer',
                'expires_in'    =>  $tokenResult->token->expires_at->diffInSeconds(Carbon::now()),
                'access_token'  =>  $tokenResult->accessToken,
                'refresh_token'  =>  $tokenResult->refreshToken,
                'type'          =>  'facebook'
            ]);
                       
                       
 
                    if($check_active_devices == 0 && $check_dev == 0)
                  {
                    $newdevice = DB::table('user_devices')->insert([
                        'device_serial'=>$request->device_id,
                        'status'=>1,
                        'operating_system'=>$request->device_os,
                        'user_id'=>$check->id,
                        'updated_at'=>Carbon::now(),
                        'created_at'=>Carbon::now(),
            
        
                       ]);
        
                       $tokenResult = $user->createToken('facebook');
                    return \response()->json([
                        'token_type'    =>  'Bearer',
                        'expires_in'    =>  $tokenResult->token->expires_at->diffInSeconds(Carbon::now()),
                        'access_token'  =>  $tokenResult->accessToken,
                        'refresh_token'  =>  $tokenResult->refreshToken,
                        'type'          =>  'facebook'
                    ]);
        
                  }
                  elseif($check_active_devices == 0 && $check_dev == 1)
                  {
               $device_update = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->update([
                            'status'=>1,
                            'updated_at'=> Carbon::now()
                         ]);
 
                         $tokenResult = $user->createToken('facebook');
            return \response()->json([
                'token_type'    =>  'Bearer',
                'expires_in'    =>  $tokenResult->token->expires_at->diffInSeconds(Carbon::now()),
                'access_token'  =>  $tokenResult->accessToken,
                'refresh_token'  =>  $tokenResult->refreshToken,
                'type'          =>  'facebook'
            ]);
                  }
                     


                    }
                }
                else
                {

                  $check_primary_device = DB::table('user_devices')->where('user_id',$check->id)->where('is_primary',1)->count();

                    //if device Is not in database 

                    
                        $newdevice = DB::table('user_devices')->insert([
                            'device_serial'=>$request->device_id,
                            'status'=>1,
                            'operating_system'=>$request->device_os,
                            'user_id'=>$check->id,
                            'updated_at'=>Carbon::now(),
                            'created_at'=>Carbon::now(),
                            'is_primary'=>1
            
                           ]);
            
                           $tokenResult = $user->createToken('facebook');
                        return \response()->json([
                            'token_type'    =>  'Bearer',
                            'expires_in'    =>  $tokenResult->token->expires_at->diffInSeconds(Carbon::now()),
                            'access_token'  =>  $tokenResult->accessToken,
                            'refresh_token'  =>  $tokenResult->refreshToken,
                            'type'          =>  'facebook'
                        ]);
            
                     
            }
              
            
        }
       

    }




    public function loginGoogle(Request $request)
    {



        
        $provider = "google"; 
    
        $check_banned = DB::table('banned_user_devices')->where('device_serial',$request->device_id)->count();
        if($check_banned != 0)
        {

            $code = [
                'status' => 400,
                'code' => 400,
                'message' => 'This Device has been banned from accessing '.env('APP_NAME').' services. Contact '.env('APP_NAME').' for further assistance.',
            ];
    
                return response()->json($code,$code['status']);

        }

        $access_token = Socialite::driver($provider)->getAccessTokenResponse($request->token);
        $providerUser = Socialite::driver($provider)->userFromToken($access_token['access_token']);

        $user = User::where('provider_name', $provider)->where('provider_id', $providerUser->id)->first();
        // if there is no record with these data, create a new user
        if($user == null){
            $user = User::create([
                'name' => $providerUser->name,
                'email' => $providerUser->email,
                'avatar' => $providerUser->avatar,
                'premuim' => false,
                'manual_premuim' => false,
                'status'=>1,
                'email_verified_at' => \Carbon\Carbon::now(),
                'provider_name' => $provider,
                'provider_id' => $providerUser->id
            ]);
        
        }
        if($user == null)
        {
            $code = [
                'status' => 400,
                'code' => 400,
                'message' => 'Account is not connected to Google Login. Please login with username and password',
            ];
            
    
                return response()->json($code,$code['status']);
        }
        
        $check = $user;
        
        if($check->status != 1)
        {

            $code = [
                'status' => 400,
                'code' => 400,
                'message' => 'Account ('.$check->name.') has been banned from accessing '.env('APP_NAME').' services. Contact '.env('APP_NAME').' for further assistance.',
            ];
            
    
                return response()->json($code,$code['status']);

        }
            //IF PASSWORD IS MATCHING
            //Get Current user auth('api')->user();

            //Check User Device

            $check_devices = DB::table('user_devices')->where('user_id',$check->id)->count();
             
            $check_active_devices = DB::table('user_devices')->where('user_id',$check->id)->where('status',1)->count();
            
              

            //$user_plan = DB::table('plans')->where('name',$check->pack_name)->orWhere('stripe_plan_id',$check->pack_id)->first();

            if($check->premuim == 1)
            {

            }

            if($check_devices!=0)
            {
                //IF There are Registered Devices
                //Check if the device is one of the allowed devices for subsription
                $check_dev_primary = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->where('is_primary',1)->count();

                $check_dev = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->count();

                $check_dev_active = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->where('status',1)->count();

                

                if($check_dev!=0)
                {
                    //If The Device is allowed and active


                    if($check_dev_primary!=0 && $check_active_devices == 0)
                    {
                    
                        //If Device is primary and active
                       $device_update = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->update([
                           'status'=>1,
                           'updated_at'=> Carbon::now()
                    ]);
                        
                        //\Log::debug($this->issueToken($request, 'password'));
                        $tokenResult = $user->createToken('google');
                        return \response()->json([
                            'token_type'    =>  'Bearer',
                            'expires_in'    =>  $tokenResult->token->expires_at->diffInSeconds(Carbon::now()),
                            'access_token'  =>  $tokenResult->accessToken,
                            'type'          =>  'google'
                        ]);
                    }
                    else if($check_dev_active!=0 && $check_active_devices == 1)
                    {
                    
                        //If Device is primary and active
                       $device_update = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->update([
                           'status'=>1,
                           'updated_at'=> Carbon::now()
                    ]);
                        
                        //\Log::debug($this->issueToken($request, 'password'));
                        $tokenResult = $user->createToken('google');
                        return \response()->json([
                            'token_type'    =>  'Bearer',
                            'expires_in'    =>  $tokenResult->token->expires_at->diffInSeconds(Carbon::now()),
                            'access_token'  =>  $tokenResult->accessToken,
                            'type'          =>  'google'
                        ]);
                    }
                    else
                    {
                     // If Device is not Primary
                     if($check_active_devices == 0 && $check_dev == 0)
                     {
                      $newdevice = DB::table('user_devices')->insert([
                          'device_serial'=>$request->device_id,
                          'status'=>1,
                          'operating_system'=>$request->device_os,
                          'user_id'=>$check->id,
                          'updated_at'=>Carbon::now(),
                          'created_at'=>Carbon::now(),
                          
          
                         ]);
          
                         $tokenResult = $user->createToken('google');
                         return \response()->json([
                             'token_type'    =>  'Bearer',
                             'expires_in'    =>  $tokenResult->token->expires_at->diffInSeconds(Carbon::now()),
                             'access_token'  =>  $tokenResult->accessToken,
                             'type'          =>  'google'
                         ]);
           
                     }
                        $device_update = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->update([
                            'status'=>1,
                            'updated_at'=> Carbon::now()
                         ]);
 
                         $tokenResult = $user->createToken('google');
                         return \response()->json([
                             'token_type'    =>  'Bearer',
                             'expires_in'    =>  $tokenResult->token->expires_at->diffInSeconds(Carbon::now()),
                             'access_token'  =>  $tokenResult->accessToken,
                             'type'          =>  'google'
                         ]);
                       
                       
                        }
                }
                else
                {

                $check_primary_device = DB::table('user_devices')->where('user_id',$check->id)->where('is_primary',1)->count();

                    //if device Is not in database 

                   
                        $newdevice = DB::table('user_devices')->insert([
                            'device_serial'=>$request->device_id,
                            'status'=>1,
                            'operating_system'=>$request->device_os,
                            'user_id'=>$check->id,
                            'updated_at'=>Carbon::now(),
                            'created_at'=>Carbon::now(),
                            'is_primary'=>1
            
                           ]);
            
                           $tokenResult = $user->createToken('google');
                           return \response()->json([
                               'token_type'    =>  'Bearer',
                               'expires_in'    =>  $tokenResult->token->expires_at->diffInSeconds(Carbon::now()),
                               'access_token'  =>  $tokenResult->accessToken,
                               'type'          =>  'google'
                           ]);
                      
            }
              
            
         
            }
        

    }

    public function controllogin(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required'
        ]);

        $count = DB::table('users')->where('email',$request->username)->OrWhere('phone',$request->username)->count();
      $check = DB::table('users')->where('email',$request->username)->OrWhere('phone',$request->username)->first();
      if($count!=0)
      {
          //IF USER EXISTS
        

        $check_pass = Hash::check($request->password,$check->password);
        if($check_pass)
        {
            if($check->status != 1)
        {

            $code = [
                'status' => 400,
                'code' => 400,
                'message' => 'Account ('.$check->name.') has been banned from accessing '.env('APP_NAME').' services. Contact '.env('APP_NAME').' for further assistance.',
            ];
            
    
                return response()->json($code,$code['status']);

        }

        if($check->role == "admin")
        {
            return $this->issueToken($request, 'password');
        }
        else
        {
            $code = [
                'status' => 400,
                'code' => 400,
                'message' => '',
            ];
            return response()->json($code,400);
        }
            //IF PASSWORD IS MATCHING
            //Get Current user auth('api')->user();

            //Check User Device
       }
        else
        {
          // IF PASSWORD IS WRONG
        
        $code = [
            'status' => 400,
            'code' => 400,
            'message' => 'Incorrect password!',
        ];
        

            return response()->json($code,$code['status']);
         }
      }
      else
      {
         // IF USER DOES NOT EXIST

        
        $code = [
            'status' => 400,
            'code' => 400,
            'message' => 'Invalid Usernane Or Phone!',
        ];
        

            return response()->json($code,$code['status']);

      }

       // return $this->issueToken($request, 'password');

    }
    public function login(Request $request)
    {

        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
            'device_id'=>'required',
            'device_os'=>'required'
        ]);
        
        $check_banned = DB::table('banned_user_devices')->where('device_serial',$request->device_id)->count();
        if($check_banned != 0)
        {

            $code = [
                'status' => 400,
                'code' => 400,
                'message' => 'This Device has been banned from accessing '.env('APP_NAME').' services. Contact '.env('APP_NAME').' for further assistance.',
            ];
    
                return response()->json($code,$code['status']);

        }
      $count = DB::table('users')->where('email',$request->username)->OrWhere('phone',$request->username)->count();
      $check = DB::table('users')->where('email',$request->username)->OrWhere('phone',$request->username)->first();
      if($count!=0)
      {
          //IF USER EXISTS
        

        $check_pass = Hash::check($request->password,$check->password);
        if($check_pass)
        {
            if($check->status != 1)
        {

            $code = [
                'status' => 400,
                'code' => 400,
                'message' => 'Account ('.$check->name.') has been banned from accessing '.env('APP_NAME').' services. Contact '.env('APP_NAME').' for further assistance.',
            ];
            
    
                return response()->json($code,$code['status']);

        }
            //IF PASSWORD IS MATCHING
            //Get Current user auth('api')->user();

            //Check User Device

            $check_devices = DB::table('user_devices')->where('user_id',$check->id)->count();
             
            $check_active_devices = DB::table('user_devices')->where('user_id',$check->id)->where('status',1)->count();
            
              

           // $user_plan = DB::table('plans')->where('name',$check->pack_name)->orWhere('stripe_plan_id',$check->pack_id)->first();

            if($check->premuim == 1)
            {

            }

            if($check_devices!=0)
            {
                //IF There are Registered Devices
                //Check if the device is one of the allowed devices for subsription
                $check_dev_primary = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->where('is_primary',1)->count();

                $check_dev = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->count();

                $check_dev_active = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->where('status',1)->count();


                if($check_dev!=0)
                {
                    //If The Device is allowed and active


                    if($check_dev_primary!=0 && $check_active_devices == 0)
                    {
                    
                        //If Device is primary and active
                       $device_update = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->update([
                           'status'=>1,
                           'updated_at'=> Carbon::now()
                    ]);
                        
                        //\Log::debug($this->issueToken($request, 'password'));
                        return $this->issueToken($request, 'password');

                    }
                    else if($check_dev_active!=0 && $check_active_devices == 1)
                    {
                    
                        //If Device is primary and active
                       $device_update = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->update([
                           'status'=>1,
                           'updated_at'=> Carbon::now()
                    ]);
                        
                        //\Log::debug($this->issueToken($request, 'password'));
                        return $this->issueToken($request, 'password');

                    }
                    else
                    {
                     // If Device is not Primary
                     if($check_active_devices == 0 && $check_dev == 0)
                     {
                       $newdevice = DB::table('user_devices')->insert([
                           'device_serial'=>$request->device_id,
                           'status'=>1,
                           'operating_system'=>$request->device_os,
                           'user_id'=>$check->id,
                           'updated_at'=>Carbon::now(),
                           'created_at'=>Carbon::now(),
                           
           
                          ]);
           
                          return $this->issueToken($request, 'password');
                     }
                        $device_update = DB::table('user_devices')->where('user_id',$check->id)->where('device_serial',$request->device_id)->update([
                            'status'=>1,
                            'updated_at'=> Carbon::now()
                         ]);
 
                         return $this->issueToken($request, 'password');
                       
                       
                    }
                }
                else
                {


                $check_primary_device = DB::table('user_devices')->where('user_id',$check->id)->where('is_primary',1)->count();
                    //if device Is not in database 
                        $newdevice = DB::table('user_devices')->insert([
                            'device_serial'=>$request->device_id,
                            'status'=>1,
                            'operating_system'=>$request->device_os,
                            'user_id'=>$check->id,
                            'updated_at'=>Carbon::now(),
                            'created_at'=>Carbon::now(),
                            'is_primary'=>1
            
                           ]);
            
                           return $this->issueToken($request, 'password');
                 }
            
            
        }
       }
        else
        {
          // IF PASSWORD IS WRONG
        
        $code = [
            'status' => 400,
            'code' => 400,
            'message' => 'Incorrect password!',
        ];
        

            return response()->json($code,$code['status']);
         }
      }
      else
      {
         // IF USER DOES NOT EXIST

        
        $code = [
            'status' => 400,
            'code' => 400,
            'message' => 'Invalid Usernane Or Phone!',
        ];
        

            return response()->json($code,$code['status']);

      }

       // return $this->issueToken($request, 'password');

    }



    public function createNewProfile(Request $request) {

        $user = Auth()->user();

        
        $movieVideo = new Profile();
        $movieVideo->name = $request->name;
        $movieVideo->user_id = $user->id;
        $movieVideo->image = $user->image;
        $movieVideo->fill($request->all());
        $movieVideo->save();

        
        $data = [
            'status' => 200,
            self::MESSAGE,
            'body' => $user
        ];

        return response()->json($data, $data['status']);

    }


    public function refresh(Request $request)
    {
        $this->validate($request, [
            'refresh_token' => 'required'
        ]);

        return $this->issueToken($request, 'refresh_token');


    }

    public function update(Request $request,Plan $plan)
    {

       
        $accessToken = Auth::user()->token();


        DB::table('users')
            ->where('id', $accessToken->user_id)
            ->update(

                array( 
                    "premuim" => true,
                    "pack_name" => request('pack_name'),
                    "expired_in" => Carbon::now()->addDays(request('pack_duration'))
    
   )

            );
            


        return response()->json([], 204);

    }





    public function setRazorPay(Request $request,Plan $plan)
    {


        $api = new Api("rzp_test_9Lwp5FKGNQ37SY","W22kuir9KeqWkxzjtsuXvIFX");

        $subscription  = $api->subscription->create(array('plan_id' => 'plan_HjExFJHhxXZ9oP',
         'customer_notify' => 1, 'total_count' => 6,
         'start_at' => Carbon::now(),
         'addons' => array(array(
         'item' => array('name' => 'Delivery charges',
         'amount' => 30000, 'currency' => 'INR')))));
       
        //$accessToken = Auth::user()->token();

        return response()->json($subscription, 204);

    }


    public function updatePaypal(Request $request,Plan $plan)
    {


        $this->validate($request, [
            'transaction_id' => 'required',
            'pack_id' => 'required',
            'pack_name' => 'required',
            "type" => 'required',
            "pack_duration" => 'required']);
        
       
        $accessToken = Auth::user()->token();


        DB::table('users')
            ->where('id', $accessToken->user_id)
            ->update(

                array( 
                    "premuim" => true,
                    "transaction_id" => request('transaction_id'),
                    "pack_id" => request('pack_id'),
                    "pack_name" => request('pack_name'),
                    "type" => request('type'),
                    "expired_in" => Carbon::now()->addDays(request('pack_duration'))));
            
   return response()->noContent();

    }
    public function addPlanToUserOtherAirtel(Request $request)
    {
        //$stripeToken = $request->get('stripe_token');
        //$user = Auth::user();
        //$user->newSubscription($request->get('stripe_plan_id'), $request->get('stripe_plan_price'))->create($stripeToken);

       // $accessToken = Auth::user()->token();

       $Token = $request->bearerToken();
        $tokenId = (new Parser(new JoseEncoder()))->parse($Token)->claims()
        ->all()['jti'];

        $accessToken = DB::table('oauth_access_tokens')
        ->where('id', $tokenId)
        ->first();

      
        $token = DB::table('oauth_access_tokens')
        ->where('id', $tokenId)->first();

        $liveplan = DB::table('plans')->where('stripe_plan_id', $request->get('stripe_plan_id'))->first();

        $hour_duration = (floatval($liveplan->pack_duration) * 24);
        
        //Payment server status check

        $serverresponse = Http::withHeaders([
            'X-Authorization' => env('PAYMENT_SERVER_AUTH'),
        ])->post(env('PAYMENT_SERVER')."/api/status", [
            'id' => $token->user_id
        ]);

        //return json_decode($response,true);
        $serverresponse=  json_decode($serverresponse,true);

        if($serverresponse['airtelmoney_status'] == false)
        {
            $code = [
                'status' => 400,
                'code' => 400,
                'message' => 'Airtel Money is currenty not accessible . Please try again later.',
            ];
            
    
            return response()->json($code,$code['status']);

        }

        //Payment Initiation

        $response = Http::withHeaders([
            'X-Authorization' => env('PAYMENT_SERVER_AUTH'),
        ])->post(env('PAYMENT_SERVER')."/api/airtelrequestToPay", [
            'id' => $token->user_id,
            'amount' => $liveplan->price,
            'client_phone' => $request->phone,
            'reference'=>"'.env('APP_NAME').' Subscription",
            'userid'=> $token->user_id,

        ]);

        //return json_decode($response,true);
        $response =  json_decode($response,true);

        if(!empty($response['Error']))
    {
        $code = [
            'status' => 400,
            'code' => 400,
            'message' => 'Payment Failed . ('.$response['Error'].')',
        ];
        

        return response()->json($code,$code['status']);
       
    }

    if(empty($response))
    {
        $code = [
            'status' => 400,
            'code' => 400,
            'message' => 'Payment Failed . ('."'.env('APP_NAME').' Payment Server returned no response".')',
        ];
        

        return response()->json($code,$code['status']);

    }
        if($response['data']['transaction']['status'] == "TS")
    {

            //TODO IF Payment is Successful

            DB::table('users')
            ->where('id', Auth::user()->id)
            ->update(
    
                array( 
                    "premuim" => true,
                    "pack_name" => $liveplan->name,
                    "pack_id" => $liveplan->stripe_plan_id,
                    'transaction_id' => $response['data']['transaction']['airtel_money_id'],
                    "start_at" => Carbon::now(),
                    "plan_amount" => $liveplan->price,
                    "type" => request('type'),
                    "expired_in" => Carbon::now()->addHours($hour_duration))
    
            );
    
             DB::table('subscriptions')->insert([
             
                'user_id'=>Auth::user()->id,
                'name'=>$liveplan->name,
                'stripe_id'=> $liveplan->stripe_plan_id,
                'stripe_plan' => $liveplan->stripe_plan_id,
                'quantity'=> $liveplan->pack_duration,
                'trial_ends_at' => Carbon::today(),
                'ends_at' => Carbon::now()->addHours($hour_duration),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            
            $plan_info = SubscriptionPlan::where('plan_name',$liveplan->name)->first();
            $plan_name=$plan_info->plan_name;
            $plan_days=$plan_info->plan_days;
            $plan_amount=$plan_info->plan_price;
            $payment_trans = new Transactions;

            $payment_trans->user_id = Auth::user()->id;
            $payment_trans->email = Auth::user()->email;
            $payment_trans->plan_id = $plan_info->id;
            $payment_trans->gateway = 'Airtel Money';
            $payment_trans->payment_amount = $plan_amount;
            $payment_trans->payment_id = $response['data']['transaction']['airtel_money_id'];
            $payment_trans->date = strtotime(date('m/d/Y H:i:s'));                    
            $payment_trans->save();
            
            $user_full_name=Auth::user()->name;

               $data_email = array(
                'name' => $user_full_name
                 );    
              $user = Auth::user();
               try
               {
                \Mail::send('emails.subscription_created', $data_email, function($message) use ($user,$user_full_name){
                $message->to(Auth::user()->email, $user_full_name)
                    ->from('accounts@pearlmovies.co', "'.env('APP_NAME').'") 
                    ->subject(''.env('APP_NAME').' Subscription Created');
                 });
                   
               }
               catch(\Exception $e)
               {
                   
               }

          $code = [
            'status' => 204,
            'code' => 204,
            'message' => $response['data']['transaction']['airtel_money_id'].' Payment Successful',
          ];
        
            return response()->json($code, 204);

        }
        else
        {
            $code = [
                'status' => 400,
                'code' => 400,
                'message' => 'Payment Failed . ('.$response['data']['transaction']['message'].')',
            ];
            
    
            return response()->json($code,$code['status']);

            
        }
    
    }

public function addPlanToUserOtherCard(Request $request)
    {
        //$stripeToken = $request->get('stripe_token');
        //$user = Auth::user();
        //$user->newSubscription($request->get('stripe_plan_id'), $request->get('stripe_plan_price'))->create($stripeToken);

       // $accessToken = Auth::user()->token();

       $Token = $request->bearerToken();
        $tokenId = (new Parser(new JoseEncoder()))->parse($Token)->claims()
        ->all()['jti'];

        $accessToken = DB::table('oauth_access_tokens')
        ->where('id', $tokenId)
        ->first();

      
        $token = DB::table('oauth_access_tokens')
        ->where('id', $tokenId)->first();

        $liveplan = DB::table('plans')->where('stripe_plan_id', $request->get('stripe_plan_id'))->first();

        $hour_duration = (floatval($liveplan->pack_duration) * 24);
        



        //Payment Initiation

        $response = Http::withHeaders([
            'X-Authorization' => env('PAYMENT_SERVER_AUTH'),
        ])->post(env('PAYMENT_SERVER')."/api/requestToPayCard", [
            'tid' => $request->tid,
            'userid'=> $token->user_id,

        ]);

        //return json_decode($response,true);
        $response =  json_decode($response,true);

        if(!empty($response['Error']))
    {
        $code = [
            'status' => 400,
            'code' => 400,
            'message' => 'Payment Failed . ('.$response['Error'].')',
        ];
        

        return response()->json($code,$code['status']);
       
    }

    if(empty($response))
    {
        $code = [
            'status' => 400,
            'code' => 400,
            'message' => 'Payment Failed . ('."'.env('APP_NAME').' Payment Server returned no response".')',
        ];
        

        return response()->json($code,$code['status']);

    }
    
    if($response['status'] == "error")
    {
        $code = [
            'status' => 400,
            'code' => 400,
            'message' => 'Payment Failed . ('.$response['message'].')',
        ];
        

        return response()->json($code,$code['status']);
    }
    $chargeResponsecode = $response['data']['chargecode'];
    $chargeAmount = $response['data']['amount'];
    $chargeCurrency = $response['data']['currency'];

    if (($chargeResponsecode == "00" || $chargeResponsecode == "0")) 
    {

            //TODO IF Payment is Successful
         $validate_tid = Transactions::where('payment_id',$response['data']['txid'])->where('gateway',"Card")->count();
         if($validate_tid !=0)
         {
            $code = [
                'status' => 400,
                'code' => 400,
                'message' => 'Payment Failed . (Duplicate Transaction ID. More attempts will lead to banning of your account.)',
            ];
            
    
            return response()->json($code,$code['status']);

         }
            DB::table('users')
            ->where('id', Auth::user()->id)
            ->update(
    
                array( 
                    "premuim" => true,
                    "pack_name" => $liveplan->name,
                    "pack_id" => $liveplan->stripe_plan_id,
                    'transaction_id' => $response['data']['txid'],
                    "start_at" => Carbon::now(),
                    "plan_amount" => $liveplan->price,
                    "type" => request('type'),
                    "expired_in" => Carbon::now()->addHours($hour_duration))
    
            );
    
             DB::table('subscriptions')->insert([
             
                'user_id'=>Auth::user()->id,
                'name'=>$liveplan->name,
                'stripe_id'=> $liveplan->stripe_plan_id,
                'stripe_plan' => $liveplan->stripe_plan_id,
                'quantity'=> $liveplan->pack_duration,
                'trial_ends_at' => Carbon::today(),
                'ends_at' => Carbon::now()->addHours($hour_duration),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            
            $plan_info = SubscriptionPlan::where('plan_name',$liveplan->name)->first();
            $plan_name=$plan_info->plan_name;
            $plan_days=$plan_info->plan_days;
            $plan_amount=$plan_info->plan_price;
            $payment_trans = new Transactions;

            $payment_trans->user_id = Auth::user()->id;
            $payment_trans->email = Auth::user()->email;
            $payment_trans->plan_id = $plan_info->id;
            $payment_trans->gateway = 'Card';
            $payment_trans->payment_amount = $plan_amount;
            $payment_trans->payment_id = $response['data']['txid'];
            $payment_trans->date = strtotime(date('m/d/Y H:i:s'));                    
            $payment_trans->save();
            
            $user_full_name=Auth::user()->name;

               $data_email = array(
                'name' => $user_full_name
                 );    
              $user = Auth::user();
               try
               {
                \Mail::send('emails.subscription_created', $data_email, function($message) use ($user,$user_full_name){
                $message->to(Auth::user()->email, $user_full_name)
                    ->from('accounts@pearlmovies.co', "'.env('APP_NAME').'") 
                    ->subject(''.env('APP_NAME').' Subscription Created');
                 });
                   
               }
               catch(\Exception $e)
               {
                   
               }

          $code = [
            'status' => 204,
            'code' => 204,
            'message' => $response['data']['txid'].' Payment Successful',
          ];
        
            return response()->json($code, 204);

        }
        else
        {
            $code = [
                'status' => 400,
                'code' => 400,
                'message' => 'Payment Failed . ('.$response['message'].')',
            ];
            
    
            return response()->json($code,$code['status']);

            
        }
    
    }


    public function addPlanToUserOther(Request $request)
    {
        //$stripeToken = $request->get('stripe_token');
        //$user = Auth::user();
        //$user->newSubscription($request->get('stripe_plan_id'), $request->get('stripe_plan_price'))->create($stripeToken);

       // $accessToken = Auth::user()->token();

       $Token = $request->bearerToken();
        $tokenId = (new Parser(new JoseEncoder()))->parse($Token)->claims()
        ->all()['jti'];

        $accessToken = DB::table('oauth_access_tokens')
        ->where('id', $tokenId)
        ->first();

      
        $token = DB::table('oauth_access_tokens')
        ->where('id', $tokenId)->first();

        $liveplan = DB::table('plans')->where('stripe_plan_id', $request->get('stripe_plan_id'))->first();

        $hour_duration = (floatval($liveplan->pack_duration) * 24);
        
        //Payment server status check

        $serverresponse = Http::withHeaders([
            'X-Authorization' => env('PAYMENT_SERVER_AUTH'),
        ])->post(env('PAYMENT_SERVER')."/api/status", [
            'id' => $token->user_id
        ]);

        //return json_decode($response,true);
        $serverresponse=  json_decode($serverresponse,true);

        if($serverresponse['mtnmomo_status'] == false)
        {
            $code = [
                'status' => 400,
                'code' => 400,
                'message' => 'MTN Momo is currenty not accessible . Please try again later.',
            ];
            
    
            return response()->json($code,$code['status']);

        }

        //Payment Initiation

        $response = Http::withHeaders([
            'X-Authorization' => env('PAYMENT_SERVER_AUTH'),
        ])->post(env('PAYMENT_SERVER')."/api/requestToPay", [
            'id' => $token->user_id,
            'amount' => $liveplan->price,
            'client_phone' => $request->phone,
            'userid'=> $token->user_id,

        ]);

        //return json_decode($response,true);
        $response =  json_decode($response,true);

        if(!empty($response['Error']))
    {
        $code = [
            'status' => 400,
            'code' => 400,
            'message' => 'Payment Failed . ('.$response['Error'].')',
        ];
        

        return response()->json($code,$code['status']);
       
    }

    if(empty($response))
    {
        $code = [
            'status' => 400,
            'code' => 400,
            'message' => 'Payment Failed . ('."'.env('APP_NAME').' Payment Server returned no response".')',
        ];
        

        return response()->json($code,$code['status']);

    }
        if($response['status']== "SUCCESSFUL")
        {

            //TODO IF Payment is Successful

            DB::table('users')
            ->where('id', Auth::user()->id)
            ->update(
    
                array( 
                    "premuim" => true,
                    "pack_name" => $liveplan->name,
                    "pack_id" => $liveplan->stripe_plan_id,
                    'transaction_id' => $response['financialTransactionId'],
                    "start_at" => Carbon::now(),
                    "plan_amount" => $liveplan->price,
                    "type" => request('type'),
                    "expired_in" => Carbon::now()->addHours($hour_duration))
    
            );
    
             DB::table('subscriptions')->insert([
             
                'user_id'=>Auth::user()->id,
                'name'=>$liveplan->name,
                'stripe_id'=> $liveplan->stripe_plan_id,
                'stripe_plan' => $liveplan->stripe_plan_id,
                'quantity'=> $liveplan->pack_duration,
                'trial_ends_at' => Carbon::today(),
                'ends_at' => Carbon::now()->addHours($hour_duration),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            $plan_info = SubscriptionPlan::where('plan_name',$liveplan->name)->first();
            $plan_name=$plan_info->plan_name;
            $plan_days=$plan_info->plan_days;
            $plan_amount=$plan_info->plan_price;
            $payment_trans = new Transactions;

            $payment_trans->user_id = Auth::user()->id;
            $payment_trans->email = Auth::user()->email;
            $payment_trans->plan_id = $plan_info->id;
            $payment_trans->gateway = 'MTN Momo';
            $payment_trans->payment_amount = $plan_amount;
            $payment_trans->payment_id = $response['financialTransactionId'];
            $payment_trans->date = strtotime(date('m/d/Y H:i:s'));                    
            $payment_trans->save();
            
            $user_full_name=Auth::user()->name;

               $data_email = array(
                'name' => $user_full_name
                 );    
              $user = Auth::user();
               try
               {
                \Mail::send('emails.subscription_created', $data_email, function($message) use ($user,$user_full_name){
                $message->to(Auth::user()->email, $user_full_name)
                    ->from('accounts@pearlmovies.co', "'.env('APP_NAME').'") 
                    ->subject(''.env('APP_NAME').' Subscription Created');
                 });
                   
               }
               catch(\Exception $e)
               {
                   
               }
          $code = [
            'status' => 204,
            'code' => 204,
            'message' => $response['financialTransactionId'].' Payment Successful',
          ];
        
            return response()->json($code, 204);

        }
        else if($response['status']== "FAILED")
        {
            $code = [
                'status' => 400,
                'code' => 400,
                'message' => 'Payment Failed . ('.$response['reason'].')',
            ];
            
    
            return response()->json($code,$code['status']);

            
        }
        else
        {
            $code = [
                'status' => 400,
                'code' => 400,
                'message' => 'Payment Failed . ('.$response['Error'].')',
            ];
            
    
            return response()->json($code,$code['status']);

            
        }
    




       


    }

    public function addPlanToUser(Request $request)
    {

        $stripeToken = $request->get('stripe_token');
        $user = Auth::user();
        $user->newSubscription($request->get('stripe_plan_id'), $request->get('stripe_plan_price'))->create($stripeToken);

        $accessToken = Auth::user()->token();

        $liveplan = DB::table('plans')->where('stripe_plan_id', $request->get('stripe_plan_id'))->first();

        $hour_duration = (floatval($liveplan->pack_duration ? null : request('pack_duration') ) * 24);
        DB::table('users')
        ->where('id', $accessToken->user_id)
        ->update(

            array( 
                "premuim" => true,
                "pack_name" => request('pack_name'),
                "pack_id" => request('stripe_plan_id'),
                "start_at" => Carbon::now(),
                "plan_amount" => $liveplan->price,
                "type" => request('type'),
                "expired_in" => Carbon::now()->addHours($hour_duration))

        );
           $plan_info = SubscriptionPlan::where('plan_name',$liveplan->name)->first();
            $plan_name=$plan_info->plan_name;
            $plan_days=$plan_info->plan_days;
            $plan_amount=$plan_info->plan_price;
            $payment_trans = new Transactions;

            $payment_trans->user_id = Auth::user()->id;
            $payment_trans->email = Auth::user()->email;
            $payment_trans->plan_id = $plan_info->id;
            $payment_trans->gateway = 'Card';
            $payment_trans->payment_amount = $plan_amount;
            $payment_trans->payment_id = "Nan";
            $payment_trans->date = strtotime(date('m/d/Y H:i:s'));                    
            $payment_trans->save();
            $user_full_name=Auth::user()->name;

               $data_email = array(
                'name' => $user_full_name
                 );    
              //$user = Auth::user();
               try
               {
                \Mail::send('emails.subscription_created', $data_email, function($message) use ($user,$user_full_name){
                $message->to(Auth::user()->email, $user_full_name)
                    ->from('accounts@pearlmovies.co', "'.env('APP_NAME').'") 
                    ->subject(''.env('APP_NAME').' Subscription Created');
                 });
                   
               }
               catch(\Exception $e)
               {
                   
               }

        return response()->json($user, 204);

    }



    public function cancelSubscription(Request $request)
    {

 
       $user = Auth::user();

        $accessToken = Auth::user()->token();

        $packId = Auth::user()->pack_id;
        
        if(\Carbon\Carbon::parse($user->expired_in)->gt(\Carbon\Carbon::now()))
        {
            return response()->json($user, 204);
        }
        $user->subscription($packId)->cancelNow();


        DB::table('users')
        ->where('id', $accessToken->user_id)
        ->update(

            array( 
                "premuim" => false,
                "pack_name" => "",
                "start_at" => "",
                "type" => "",
                )

        );

       /* DB::table('user_devices')->where('user_id',$accessToken->user_id)->where('status',1)->where('is_primary',0)->update([
            'status'=>0,
            'updated_at'=> Carbon::now()
         ]);
         */

         return response()->json($user, 204);

    }


    public function cancelSubscriptionPaypal(Request $request)
    {

 
       $user = Auth::user();

        $accessToken = Auth::user()->token();

        if(\Carbon\Carbon::parse($user->expired_in)->gt(\Carbon\Carbon::now()))
        {
            return response()->json($user, 204);
        }
        DB::table('users')
        ->where('id', $accessToken->user_id)
        ->update(

            array( 
                "premuim" => false,
                "pack_name" => "",
                "start_at" => "",
                "type" => "",
                "expired_in" => Carbon::now())

        );

         return response()->json([$user], 200);

    }



    public function profile(Request $request)
    {

        $user = User::find(1);
        $user->subscribedTo("1");

        return response()->json($user, 204);

    }



    public function update_avatar(Request $request){

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
        ]);

        $user = Auth::user();

        if (Storage::disk('avatars')->exists($user->avatar)) {

         Storage::delete($user->avatar);
  
        }

        $avatarName = $user->id.'_avatar.'.request()->avatar->getClientOriginalExtension();

        $request->avatar->storeAs('avatars',$avatarName);

        $user->avatar = $request->root() . '/api/avatars/image/' . $avatarName;
        $user->save();

        return response()->json([], 204);

    }


    public function user (Request $request){
        
        return $request->user();
     }

    public function userWallet (Request $request){
        
        return \Response::json(['balance'=>$request->user()->getWalletBalance(),'currency'=>$request->user()->getWalletCurrency()]);
     }

    public function logout(Request $request)
    {
        //Laravel 8 Get Token ID
        $Token = $request->bearerToken();
        $tokenId = (new Parser(new JoseEncoder()))->parse($Token)->claims()
        ->all()['jti'];

        $accessToken = DB::table('oauth_access_tokens')
        ->where('id', $tokenId)
        ->update(['revoked' => true]);

       $revokerefresh = DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $tokenId)
            ->update(['revoked' => true]);
      
        $token = DB::table('oauth_access_tokens')
        ->where('id', $tokenId)->first();

            

        $markdevice = DB::table('user_devices')->where('user_id',$token->user_id)->where('device_serial',$request->device_id)->update([
          
            'status'=> 0,
            'updated_at'=>Carbon::now()
        ]);
        
        if($markdevice)
        {
            return response()->json([], 204);
        }
        else
        {
            $code = [
                'status' => 400,
                'code' => 400,
                'message' => 'An error occured during the logout process !',
            ];
            
    
                return response()->json($code,$code['status']);
        }
        

    }




    public function getImg($filename)
    {

        $image = Storage::disk('avatars')->get($filename);

        $mime = Storage::disk('avatars')->mimeType($filename);

        return (new Response($image, 200))->header('Content-Type', $mime);
    }

//TODO 
    public function checkUserDevice(Request $request)
    {
        $request->validate([
           'jws'=>'required'
        ]);
       // \Log::debug($request->jws);
       // return;
  $components = explode('.', $request->jws);
  if (count($components) !== 3) {
    throw new MalformedSignatureException('JWS string must contain 3 dot separated component.');
  }

$header = base64_decode($components[0]);
$payload = base64_decode($components[1]);
$signature = self::base64Url_decode($components[2]);
$dataToSign = $components[0].".".$components[1];        
$headerJson = json_decode($header,true);    
$algorithm = $headerJson['alg']; 
//echo "<pre style='white-space: pre-wrap; word-break: keep-all;'>$algorithm</pre>";

$certificate = '-----BEGIN CERTIFICATE-----'.PHP_EOL;    
$certificate .= chunk_split($headerJson['x5c'][0],64,PHP_EOL);
$certificate .= '-----END CERTIFICATE-----'.PHP_EOL;

$certparsed = openssl_x509_parse($certificate,false);
//print_r($certparsed);

$cert_object = openssl_x509_read($certificate);
$pkey_object = openssl_pkey_get_public($cert_object);
$pkey_array = openssl_pkey_get_details($pkey_object);
//echo "<br></br>";
//print_r($pkey_array);
$publicKey = $pkey_array ['key'];
//echo "<pre style='white-space: pre-wrap; word-break: keep-all;'>$publicKey</pre>";

$result = openssl_verify($dataToSign,$signature,$publicKey,OPENSSL_ALGO_SHA256);

$payload = json_decode($payload);
//return $payload->nonce;
if ($result == 1) {
    //echo "good";
    if($payload->ctsProfileMatch == true && $payload->basicIntegrity == true)
    {
        $code = [
            'status' => 200,
            'code' => 200,
            'message' => 'Device passed',
          ];
        
         return response()->json($code, 200);
    }
    elseif ($payload->basicIntegrity == true && $payload->ctsProfileMatch == false)
    {
         
        $code = [
            'status' => 200,
            'code' => 200,
            'message' => 'Device passed but not CTS',
          ];
        
         return response()->json($code, 200);
    }
    elseif($payload->basicIntegrity == false && $payload->ctsProfileMatch == false)
    {
        $Token = $request->bearerToken();
        $tokenId = (new Parser(new JoseEncoder()))->parse($Token)->claims()
        ->all()['jti'];

        $accessToken = DB::table('oauth_access_tokens')
        ->where('user_id', Auth::user()->id)
        ->update(['revoked' => true]);

       $revokerefresh = DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $tokenId)
            ->update(['revoked' => true]);  

        $markdevice = DB::table('user_devices')->where('user_id',Auth::user()->id)->update([
          
            'status'=> 0,
            'updated_at'=>Carbon::now()
        ]);

        $banned = DB::table('banned_users')->insert([
            'reason' => Auth::user()->name." is using a Rooted device and has malicious apps installed on their devices.",
            'user_id' => Auth::user()->id, 
            'payload' => $payload,
            'updated_at'=>Carbon::now(),
            'created_at'=>Carbon::now()
        ]);

        $user = Auth::user();
        $user->status = 0;
        $user->save();
        $code = [
            'status' => 400,
            'code' => 400,
            'message' => 'Device failed',
          ];
        
         return response()->json($code, 400);
    }

} elseif ($result == 0) {
   // echo "bad";
        $Token = $request->bearerToken();
        $tokenId = (new Parser(new JoseEncoder()))->parse($Token)->claims()
        ->all()['jti'];

        $accessToken = DB::table('oauth_access_tokens')
        ->where('user_id', Auth::user()->id)
        ->update(['revoked' => true]);

       $revokerefresh = DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $tokenId)
            ->update(['revoked' => true]);  

        $markdevice = DB::table('user_devices')->where('user_id',Auth::user()->id)->update([
          
            'status'=> 0,
            'updated_at'=>Carbon::now()
        ]);

        $banned = DB::table('banned_users')->insert([
            'reason' => Auth::user()->name." is using a Rooted device and has malicious apps installed on their devices. and also Tampered with '.env('APP_NAME').' validation",
            'user_id' => Auth::user()->id, 
            'payload' => $payload,
            'updated_at'=>Carbon::now(),
            'created_at'=>Carbon::now()
        ]);
        
        $user = Auth::user();
        $user->status = 0;
        $user->save();
        $code = [
            'status' => 400,
            'code' => 400,
            'message' => 'Device failed',
          ];
        
         return response()->json($code, 400);

} else {

    //echo "ugly, error checking signature";
    $Token = $request->bearerToken();
    $tokenId = (new Parser(new JoseEncoder()))->parse($Token)->claims()
    ->all()['jti'];

    $accessToken = DB::table('oauth_access_tokens')
    ->where('user_id', Auth::user()->id)
    ->update(['revoked' => true]);

   $revokerefresh = DB::table('oauth_refresh_tokens')
        ->where('access_token_id', $tokenId)
        ->update(['revoked' => true]);  

    $markdevice = DB::table('user_devices')->where('user_id',Auth::user()->id)->update([
      
        'status'=> 0,
        'updated_at'=>Carbon::now()
    ]);

    $banned = DB::table('banned_users')->insert([
        'reason' => Auth::user()->name." is using a Rooted device and has malicious apps installed on their devices. and also Tampered with '.env('APP_NAME').' validation",
        'user_id' => Auth::user()->id, 
        'payload' => "Malformed",
        'updated_at'=>Carbon::now(),
        'created_at'=>Carbon::now()
    ]);
    
    $user = Auth::user();
    $user->status = 0;
    $user->save();
    $code = [
        'status' => 400,
        'code' => 400,
        'message' => 'Device failed',
      ];
    
     return response()->json($code, 400);
}
openssl_pkey_free($pkey_object);

  

    }
    private static function base64Url_decode($data)
{
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}
     
}
