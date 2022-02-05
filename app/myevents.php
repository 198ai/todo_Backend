<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
class myevents extends Model
{
    
    protected $tabel ='myevents';
      /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'user_id','eventTitle','eventDescp','alarmDate','calendarId','status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
       
    ];

    public static function getId($eventTitle,$eventDescp){
        $id = myevents::select('id')
        ->where('eventTitle','=',$eventTitle)
        ->where('eventDescp','=',$eventDescp)
        ->first();
        return $id;
    }
}
