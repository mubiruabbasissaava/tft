<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
class SocialAccount extends Model
{
    use Uuids;
    public $incrementing = false;
    const SERVICE_FACEBOOK = 'facebook';
    const SERVICE_GOOGLE = 'google';

    public function user() {
        return $this->belongsTo(User::class);
    }
}
