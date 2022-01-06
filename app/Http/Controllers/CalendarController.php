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

   public function set(){
    return "123";
   }


   public function calendar(Request $request){
        $user = $request->user();
        $calendardate = DB::table('myevents')
        ->where('user_id', $user->id)
        ->select("date")
        ->get();
        $restult = DB::table('myevents')
        ->where('myevents.user_id', $user->id)
        ->where('myalarm.user_id', $user->id)
        ->leftJoin('myalarm','myalarm.alarmId','=','myevents.alarmId')
        ->orwhereNull('myevents.alarmId')
        ->where('myalarm.status','!=',1)
        ->where('myevents.status','!=',1)
        ->select('myevents.alarmId','myalarm.alarmDate as alarm','myevents.eventTitle','myevents.eventDescp','myevents.date')->get();
        
        $restult2 = DB::table('myevents')
        ->where('myevents.user_id', $user->id)
        ->where('myevents.alarmId','=','')
        ->orwhereNull('myevents.alarmId')
        ->where('myevents.status','!=',1)
        ->select('myevents.alarmId','myevents.eventTitle','myevents.eventDescp','myevents.date')->get();
        
        $collection = collect([$restult2,$restult]);
        $collapsed = $collection->collapse();
        $array1 = array();
        foreach($calendardate as $row){
        $array1[] = $row->date;
        }
        $array2 = array();
        $array3 = array();
        foreach($array1 as $row){
        foreach($collapsed as $events){
            if($events->date == $row){
                $array3[]=$events;
            }
        }
        $array2["$row"] = $array3;
        $array3=[];
        }
        return response()->json($array2,200) ->header('Content-Type','application/json; charset=UTF-8');
    }

    public function addcalendar(Request $request){
        $user = $request->user();

        $request->validate([
            'alarmId'=>'required',
            'eventTitle'=>'required',
            'eventDescp'=>'required',
            'date'=>'required',
            'status'=>'required',
        ]);
  
        $data =[
            'alarmId'=>$request->alarmId,
            'eventTitle'=>$request->eventTitle,
            'eventDescp'=>$request->eventDescp,
            'date'=>$request->date,
            'status'=>$request->status,
            "user_id"=>$user->id,
        ];
       
      
        $add =DB::table('myevents')->insert($data);
        if($add){
            return response()->json("追加しました", 201)
            ->header('Content-Type','application/json; charset=UTF-8');
        }else{
            return response()->json("追加失敗した", 400)
            ->header('Content-Type','application/json; charset=UTF-8');
        }
    }

    public function updatecalendar(Request $request){
        $user = $request->user();

        $request->validate([
            'alarmId'=>'required',
            'eventTitle'=>'required',
            'eventDescp'=>'required',
            'date'=>'required',
            'status'=>'required',
        ]);
  
        $data =[
            'alarmId'=>$request->alarmId,
            'eventTitle'=>$request->eventTitle,
            'eventDescp'=>$request->eventDescp,
            'date'=>$request->date,
            'status'=>$request->status,
            "user_id"=>$user->id,
        ];
       
        $add =DB::table('myevents')
        ->where('id',$request->id)
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
