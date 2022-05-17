<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Cashier\Billable;
use App\Notifications\PasswordReset;
use Laravel\Passport\HasApiTokens;
use BeyondCode\Comments\Contracts\Commentator;
use ChristianKuri\LaravelFavorite\Traits\Favoriteability;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Uuids;
use Illuminate\Support\Facades\Storage;



class User extends Authenticatable implements Commentator ,MustVerifyEmail
{
    use Notifiable, HasApiTokens,Billable,HasFactory,Favoriteability,Uuids;


    protected $appends = ['favoriteTours','favoritePlaces','favoriteHotels','favoriteMeals'];


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email','phone', 'wallet_currency','wallet_balance', 'country','password','avatar', 'premuim','manual_premuim','pack_name','pack_id','start_at','expired_in','role','email_verified_at'
        ,'type', 'provider_name', 'provider_id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','wallet_currency','wallet_balance'
    ];


    protected $casts = [
        'premuim' => 'int'
    
    ];


    protected $dates = [
        'email_verified_at' => 'datetime', 'trial_ends_at', 'subscription_ends_at',
    ];

    public function findForPassport($username) {
        return $this->where('email', $username)->orWhere('phone',$username)->first();
    }

    public function findFacebookUserForPassport($token) {
        // Your logic here using Socialite to push user data from Facebook generated token.
    }
    public function createWallet($currency)
    {
        $cipher = "aes-256-cbc"; 
        $usermail = $this->id;
        $amount = 0;
//Generate a 256-bit encryption key 
        $encryption_key = openssl_random_pseudo_bytes(32); 

// Generate an initialization vector 
        $iv_size = openssl_cipher_iv_length($cipher); 
        $iv = openssl_random_pseudo_bytes($iv_size); 

//Data to encrypt 
        $wallet_currency = openssl_encrypt($currency, $cipher, $encryption_key, 0, $iv); 
       
        $encrypted_data = openssl_encrypt($amount, $cipher, $encryption_key, 0, $iv); 
        $amount = $encrypted_data;
        $iv =base64_encode($iv);
        $encryption_key =base64_encode($encryption_key);

        $current_path = base_path();
		$ivpath = 'iv_'.$usermail.'.lic';
        $keypath ='encryption_key_'.$usermail.'.lic';

        Storage::disk('local')->put($ivpath, $iv);
        Storage::disk('local')->put($keypath, $encryption_key);
        $this->wallet_balance = $amount;
        $this->wallet_currency = $wallet_currency;
        $this->save();
        $respons = array("ResponseCode"=>"200","Result"=>"true","ResponseMsg"=>"Wallet Created Successfully!");
        return $respons;
    }
    public function updateWallet($amount)
    {
        $cipher = "aes-256-cbc"; 
        $usermail = $this->id;
        $ivpath = 'iv_'.$usermail.'.lic';
        $keypath ='encryption_key_'.$usermail.'.lic';
        
        $cipher = "aes-256-cbc"; 
        $encrypted_data = $this->wallet_balance;
        if(!Storage::disk('local')->exists($keypath) Or !Storage::disk('local')->exists($ivpath))
        {
            $respons = array("ResponseCode"=>"200","Result"=>"false","ResponseMsg"=>"Wallet Decryption Failed!");
            return $respons;
        }
        else
        {
            $encryption_key = base64_decode(Storage::disk('local')->get($keypath));
            $iv = base64_decode(Storage::disk('local')->get($ivpath));
            
        }
        $decrypted_data = openssl_decrypt($encrypted_data, $cipher, $encryption_key, 0, $iv); 
        $wallet_balance = (floatval($decrypted_data) + floatval($amount));

        
        //Data to encrypt 
        $wallet_balance = openssl_encrypt($wallet_balance, $cipher, $encryption_key, 0, $iv); 

        $this->wallet_balance = $wallet_balance;
        $this->save();

        $respons = array("ResponseCode"=>"200","Result"=>"true","ResponseMsg"=>"Wallet Updated Successfully!");
        return $respons;
    

    }
    public function getWalletBalance()
    {
        $cipher = "aes-256-cbc"; 
        $usermail = $this->id;
        $ivpath = 'iv_'.$usermail.'.lic';
        $keypath ='encryption_key_'.$usermail.'.lic';
        
        $cipher = "aes-256-cbc"; 
        $encrypted_data = $this->wallet_balance;
        if(!Storage::disk('local')->exists($keypath) Or !Storage::disk('local')->exists($ivpath))
        {
            $respons = array("ResponseCode"=>"200","Result"=>"false","ResponseMsg"=>"Wallet Decryption Failed!");
            return $respons;
        }
        else
        {
            $encryption_key = base64_decode(Storage::disk('local')->get($keypath));
            $iv = base64_decode(Storage::disk('local')->get($ivpath));
            
        }
        $decrypted_data = openssl_decrypt($encrypted_data, $cipher, $encryption_key, 0, $iv); 
        $wallet_balance = $decrypted_data;
        return $wallet_balance;
    }
    public function getWalletCurrency()
    {
       
        $cipher = "aes-256-cbc"; 
        $usermail = $this->id;
        $ivpath = 'iv_'.$usermail.'.lic';
        $keypath ='encryption_key_'.$usermail.'.lic';
        
        $cipher = "aes-256-cbc"; 
        $encrypted_data = $this->wallet_currency;
        if(!Storage::disk('local')->exists($keypath) Or !Storage::disk('local')->exists($ivpath))
        {
            $respons = array("ResponseCode"=>"200","Result"=>"false","ResponseMsg"=>"Wallet Decryption Failed!");
            return $respons;
        }
        else
        {
            $encryption_key = base64_decode(Storage::disk('local')->get($keypath));
            $iv = base64_decode(Storage::disk('local')->get($ivpath));
            
        }
        $decrypted_data = openssl_decrypt($encrypted_data, $cipher, $encryption_key, 0, $iv); 
        $wallet_currency = $decrypted_data;
        return $wallet_currency;
    
    }
    public function getFavoriteToursAttribute()
    {

        $movies = $this->favorite(Movie::class);

        $newEpisodes = [];
        foreach ($movies as $item) {

            array_push($newEpisodes, $item->makeHidden(['videos',
            'casterslist','casters','downloads','networks','networkslist','substitles']));
        }

        return $newEpisodes;
    }


    public function getFavoritePlacesAttribute()
    {


        $movies = $this->favorite(Serie::class);

        $newEpisodes = [];
        foreach ($movies as $item) {

              array_push($newEpisodes, $item->makeHidden(['videos','casterslist','casters','downloads','networks','networkslist','substitles']));
        }

        return $newEpisodes;
    }



    public function getFavoriteHotelsAttribute()
    {

      
        $movies = $this->favorite(Anime::class);

        $newEpisodes = [];
        foreach ($movies as $item) {
            array_push($newEpisodes, $item->makeHidden(['seasons','videos','casterslist','casters','downloads','networks','networkslist','substitles']));
        }
        return $newEpisodes;
    }


    public function getFavoriteMealsAttribute()
    {

        $livetv = $this->favorite(Livetv::class);

        $newEpisodes = [];
        foreach ($livetv as $item) {
            array_push($newEpisodes, $item->makeHidden(['videos']));
        }
        return $newEpisodes;
    }


    public function sendPasswordResetNotification($token)
{
    $this->notify(new PasswordReset($token));
}


    public function needsCommentApproval($model): bool
    {
        return false;    
    }
}
