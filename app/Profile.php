<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
class Profile extends Model
{
    use Uuids;
    public $incrementing = false;
    protected $fillable = ['name','image'];


    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
