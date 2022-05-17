<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
class UserProfile extends Model
{
    use Uuids;

    protected $fillable = ['name'];

    public $timestamps = false;
    public $incrementing = false;


    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
