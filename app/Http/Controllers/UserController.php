<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvatarRequest;
use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\PasswordAppRequest;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequestStore;
use App\Http\Requests\UserUpdateRequest;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Client;
use App\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str; 
class UserController extends Controller
{

    const MESSAGE = "successfully updated";




    public function create(Request $request)
    {

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'premuim' => false,
            'password' => bcrypt(request('password'))


        ]);

        return $this->issueToken($request, 'password');

    }





    // returns the authenticated user for admin panel

    public function data()
    {

    
        $user = Auth()->user();
        return response()
        ->json( $user, 200);

    
    }




    public function allusers()
    {

        return response()->json(User::orderByDesc('created_at')
        ->paginate(12), 200);
    }


    // return the logo checking the format
    public function showAvatar()

    {
        if (Storage::disk('public')->exists('users/users.jpg')) {
            $image = Storage::disk('public')->get('users/users.jpg');
            $mime = Storage::disk('public')->mimeType('/users/users.jpg');
            $type = 'jpg';
        } else {
            $image = Storage::disk('public')->get('users/users.png');
            $mime = Storage::disk('public')->mimeType('users/users.png');
            $type = 'png';
        }
        return (new Response($image, 200))
            ->header('Content-Type', $mime)->header('type', $type);
    }


    public function updateAvatar(AvatarRequest $request)
    {
        if ($request->hasFile('image')) {
            Storage::disk('public')->deleteDirectory('users');
            $extension = $request->image->getClientOriginalExtension();
            $filename = Storage::disk('public')->putFileAs('users', $request->image, "users.$extension");
            $data = [
                'status' => 200,
                'image_path' => $request->root() . '/api/image/users?' . time(),
            ];
        } else {
            $data = [
                'status' => 400,
            ];
        }

        return response()->json($data, $data['status']);
    }


   public function devices()
   {
       $devices = DB::table('user_devices')->where('user_id',Auth()->user()->id)->get();
       $userdevices = array();
       $userdeivce = array();
        foreach($devices as $device)
        {
            $userdevice['device_serial'] = $device->device_serial;
            $userdevice['status'] = $device->status;
            $userdevice['operating_system'] = $device->operating_system;
            $userdevice['created_at'] = $device->created_at;
            $userdevice['updated_at'] = \Carbon\Carbon::parse($device->updated_at)->diffForHumans();
            if($device->is_primary == 1)
            {
             $userdevice['is_primary'] = true;
            }
            else
            {
                $userdevice['is_primary'] = false;
            }
             
             $userdevices[]= $userdevice;
        }
       
       return \Response::json(['devices'=>$userdevices],200);
   }

   public function device_logout(Request $request)
   {
       $request->validate([
           'device_id'=>'required'
           ]);

           if(Str::contains($request->device_id,"-"))
           {
            $response = Http::withHeaders([
                'X-Authorization' => env('PAYMENT_SERVER_AUTH'),
            ])->post(env('PEARLWEB_SERVER')."/api/v3/device/status", [
                'uid' => Auth()->user()->id,
                'did' => $request->device_id,
                'status'=>'deactivate'
            ]);
    
            //return json_decode($response,true);
            $response=  json_decode($response,true);
    
            if($response['status'] == true)
            {
                $data = [
                    'status' => 200,
                ];
            }
            else
            {
                $data = [
                    'status' => 400,
                    'message'=> "An error occured , couldn't logout device ".$request->device_id
                ];
            }
            return response()->json($data, $data['status']);

           }
       $deviceLogout = DB::table('user_devices')->where('user_id',Auth()->user()->id)->where('device_serial',$request->device_id)->update([
           'status'=> 0,
           'updated_at'=> \Carbon\Carbon::now()
           ]);
           
           if($deviceLogout)
           {
               $data = [
                'status' => 200,
            ];
           }
           else
           {
               $data = [
                'status' => 400,
                'message'=> "An error occured , couldn't logout device ".$request->device_id
            ];
               
           }
           
           return response()->json($data, $data['status']);
           
   }
   
   public function device_check(Request $request)
   {
        
       $deviceLogout = DB::table('user_devices')->where('user_id',Auth()->user()->id)->where('device_serial',$request->device_id)->where('status',1)->count();
           
           if($deviceLogout != 0 )
           {
               $data = [
                'status' => 200,
            ];
           }
           else
           {
               $data = [
                'status' => 400,
                'message'=> "Device session is logged out"
            ];
               
           }
           
           return response()->json($data, $data['status']);
   }
    // update user data in the database
    public function update(UserRequest $request)
    {
        $user = Auth()->user();
        
        $user->fill($request->all());
        $user->save();

        
        $data = [
            'status' => 200,
            self::MESSAGE,
            'body' => $user
        ];

        return response()->json($data, $data['status']);
    }


    public function isSubscribed(Request $request)
    {

     $user = Auth()->user();

     
        
      return response()->json($data = [
        'active' => $user->subscriptions()->active()->count()]);



    }



    public function store(Request $request){


        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'premuim' => false,
            'password' => bcrypt(request('password'))


        ]);

    }


    public function addUser(Request $request)
    {

        //validation for inputs
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email'
        ]);
        $return['message'] = 'Please enter valid inputs';
        if ($validator->fails()) {
            return $return;
        }

        //Add user
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->password = Hash::make(rand(10000, 99999));
        $user->save();
        return $user;
    }


    public function updateUser(UserUpdateRequest $request, User $user)


    {

        if ($user != null) {

            $user->fill($request->user);
            $user->save();

            $data = [
                'status' => 200,
                self::MESSAGE,
            ];
        } else {
            $data = [
                'status' => 400,
                'message' => 'Error',
            ];
        }

        return response()->json($data, $data['status']);
    }


    public function destroy(User $user)
    {
        if ($user != null) {
            $user->delete();

            $data = [
                'status' => 200,
                'message' => 'successfully removed',
            ];
        } else {
            $data = [
                'status' => 400,
                'message' => 'could not be deleted',
            ];
        }

        return response()->json($data, $data['status']);
    }

    // update user password in the database
    public function passwordUpdate(PasswordUpdateRequest $request)
    {
        $user = Auth()->user();
        $user->password = bcrypt($request->password);
        $user->save();
        $data = [
            'status' => 200,
            self::MESSAGE,
        ];

        return response()->json($data, $data['status']);
    }




    public function passwordUpdateApp(PasswordAppRequest $request)
    {

        $settings = Setting::first();
        $settings->password = bcrypt($request->password);
        $settings->save();
        $data = [
            'status' => 200,
            self::MESSAGE,
        ];

        return response()->json($data, $data['status']);
    }


 // update user password in the database
 public function updateUserPassword (PasswordUpdateRequest $request)
 {
     $user = Auth()->user();
     $user->password = bcrypt($request->password);
     $user->save();
     $data = [
         'status' => 200,
         self::MESSAGE,
     ];

     return response()->json($data, $data['status']);
 }




 public function show($filename)
 {

    $image = Storage::disk('avatars')->get($filename);

    $mime = Storage::disk('avatars')->mimeType($filename);

    return (new Response($image, 200))->header('Content-Type', $mime);
 }

 public function media_access(Request $request)
 {
     //return $request->all();
     $user = User::where('id',$request->user_id)->first();
     $device = DB::table('user_devices')->where('device_serial',$request->device_id)->where('status',1)->first();
     $is_subscribed = false;
     $is_device_active = false;
     if ($user != null)
     {
         if($user->premium == 1)
         {
        if(\Carbon\Carbon::parse($user->expired_in)->gt(\Carbon\Carbon::now()) && $user->premuim == 1)
        {

        }
         else
        {          
            //\Session::flash('flash_message', 'Login status reset!');
            $user->premuim = false;
            $user->save();
        }
       }
        
        if($user->premuim == 1)
        {
            $is_subscribed = true;
        } 
     }
     if($device != null)
     {
        $is_device_active = true;    
     }
           
     $response = [

        'is_subscribed'=>$is_subscribed,
        'is_device_active' => $is_device_active
     ];
     if($user == null && $device == null)
     {
        $response = [

            'messsage'=>"UNAUTHORIZED"
         ];
     }
     
     return \Response::json($response);
 }
    
}
