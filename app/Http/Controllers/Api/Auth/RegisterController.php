<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RegisterController extends Controller
{
    use IssueTokenTrait;

    private $client;

    public function __construct()
    {
        $this->client = Client::find(1);
        $this->middleware('doNotCacheResponse');

    }

    public function register(Request $request)
    {

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'currency' => 'required',
            'string',
            'min:8',  
            'regex:/[a-z]/',      
            'regex:/[A-Z]/',    
            'regex:/[0-9]/',    
            'regex:/[@$!%*#?&]/']);
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

            $data = [
                'status' => 400,
                'message' => 'Email Already Registered!',
            ];
            $code = [
                'status' => 400,
                'message' => 'Email Already Registered!',
                'code' => 400,
            ];
            $check_email = User::where('email',$request->email)->count();
          if($check_email!=0)
          {
           return response()->json($code,$code['status']);
          } 
          
      if(empty($request->phone)|| $request->phone == "")
      {
        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'avatar' => $request->root() . '/api/avatars/image/avatar_default.png',
            'premuim' => false,
            'manual_premuim' => false,
            'password' => bcrypt(request('password'))


        ]);
      }
      else
      {
        $check_phone = User::where('phone',$request->phone)->count();
        $data = [
            'message' => 'Phone Number Already Registered!',
        ];
        $code = [
            'status' => 400,
            'code' => 400,
            'message' => 'Phone Number Already Registered!',
        ];
        

        if($check_phone!=0)
        {
            return response()->json($code,$code['status']);
        }
        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'phone' => request('phone'),
            'country' => request('country'),
            'email_verified_at' => \Carbon\Carbon::now(),
            'avatar' => $request->root() . '/api/avatars/image/avatar_default.png',
            'premuim' => false,
            'manual_premuim' => false,
            'password' => bcrypt(request('password'))


        ]);

        $newdevice = DB::table('user_devices')->insert([
            'device_serial'=>$request->device_id,
            'status'=>1,
            'operating_system'=>$request->device_os,
            'user_id'=>$user->id,
            'updated_at'=>Carbon::now(),
            'created_at'=>Carbon::now(),
            'is_primary'=>1

           ]);
      }
        $user->createWallet($request->currency);

        return $this->issueToken($request, 'password');

    }
     public function registerVerify(Request $request)
    {

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'currency' => 'required',
            'string',
            'min:8',  
            'regex:/[a-z]/',      
            'regex:/[A-Z]/',    
            'regex:/[0-9]/',    
            'regex:/[@$!%*#?&]/']);
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
            $data = [
                'status' => 400,
                'message' => 'Email Already Registered!',
            ];
            $code = [
                'status' => 400,
                'message' => 'Email Already Registered!',
                'code' => 400,
            ];
            $check_email = User::where('email',$request->email)->count();
          if($check_email!=0)
          {
           return response()->json($code,$code['status']);
          } 
          
      if(empty($request->phone)|| $request->phone == "")
      {
        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'avatar' => $request->root() . '/api/avatars/image/avatar_default.png',
            'premuim' => false,
            'manual_premuim' => false,
            'password' => bcrypt(request('password'))


        ]);
        $user->sendEmailVerificationNotification();
      }
      else
      {
        $check_phone = User::where('phone',$request->phone)->count();
        $data = [
            'message' => 'Phone Number Already Registered!',
        ];
        $code = [
            'status' => 400,
            'code' => 400,
            'message' => 'Phone Number Already Registered!',
        ];
        

        if($check_phone!=0)
        {
            return response()->json($code,$code['status']);
        }
        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'phone' => request('phone'),
            'country' => request('country'),
            'avatar' => $request->root() . '/api/avatars/image/avatar_default.png',
            'premuim' => false,
            'manual_premuim' => false,
            'password' => bcrypt(request('password'))


        ]);

        $newdevice = DB::table('user_devices')->insert([
            'device_serial'=>$request->device_id,
            'status'=>1,
            'operating_system'=>$request->device_os,
            'user_id'=>$user->id,
            'updated_at'=>Carbon::now(),
            'created_at'=>Carbon::now(),
            'is_primary'=>1

           ]);
           $user->sendEmailVerificationNotification();
      }
        
        $user->createWallet($request->currency);
        return $this->issueToken($request, 'password');

    }
}
