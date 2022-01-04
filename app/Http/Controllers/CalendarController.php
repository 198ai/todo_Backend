<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\User;
use App\CalendarModel;
use App\MyEventsModel;
use App\MyAlarmModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use function PHPSTORM_META\map;

class CalendarController extends Controller
{
    ///アラーム查询
   public function alarm(Request $request){
    $user = $request->user();
    $calendardate = DB::table('calendar')
    ->where('user_id', $user->id)
    ->select("date")
    ->get();
    $restult = DB::table('myevents')
    ->where('myevents.user_id', $user->id)
    ->where('myalarm.user_id', $user->id)
    ->Join('myalarm','myevents.alarmId','=','myalarm.alarmId')
    ->where('myalarm.status','!=',1)
    ->select('myalarm.alarmId','myalarm.alarmDate','myalarm.alarmSubTitle','myalarm.alarmTitle','myalarm.status')->get();
 
    return response()->json(
        $restult, 201)
    ->header('Content-Type','application/json; charset=UTF-8');
   }
   public function addalarm(Request $request){
    $user = $request->user();
    $request->validate([
        'alarmDate'=>'required',
        'alarmSubTitle'=>'required',
        'alarmTitle'=>'required',
        'status'=>'required'
    ]);
    $data =[
        'alarmDate'=>$request->alarmDate,
        'alarmSubTitle'=>$request->alarmSubTitle,
        'alarmTitle'=>$request->alarmTitle,
        'status'=>$request->status,
        "user_id"=>$user->id,
    ];
    $add =DB::table('myalarm')->insert($data);
    if($add){
        return response()->json("追加しました", 201)
        ->header('Content-Type','application/json; charset=UTF-8');
    }else{
        return response()->json("追加失敗した", 400)
        ->header('Content-Type','application/json; charset=UTF-8');
    }
   
   }
   public function updatealarm(Request $request){
    $user = $request->user();
    $request->validate([
        'alarmDate'=>'required',
        'alarmSubTitle'=>'required',
        'alarmTitle'=>'required',
        'status'=>'required'
    ]);
    $data =[
        'alarmDate'=>$request->alarmDate,
        'alarmSubTitle'=>$request->alarmSubTitle,
        'alarmTitle'=>$request->alarmTitle,
        'status'=>$request->status,
        "user_id"=>$user->id,
    ];
    $add =DB::table('myalarm')
    ->where('alarmId',$request->alarmId)
    ->update($data);
    if($add==1){
        return response()->json("更新しました", 201)
        ->header('Content-Type','application/json; charset=UTF-8');
    }else{
        return response()->json("更新失敗した", 400)
        ->header('Content-Type','application/json; charset=UTF-8');
    }
   
   }
}
