<?php
namespace App;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
class LinkedSocialAccount extends Model
{
    use Uuids;
    public $incrementing = false;
    protected $fillable = [
        'provider_name',
        'provider_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}