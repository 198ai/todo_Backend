<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\User;
use App\CalendarModel;
use App\myevents;
use App\MyAlarmModel;
use DateTime;
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

   public function addevents(Request $request){
        $user = $request->user();
        $collection = collect();
        $collapsed = $collection->collapse();
        $ss="2022-02-01 00:00:00.000";
        $request->$ss;
        //return response()->json($request->$ss,200);
        $data = [];
        foreach ( $request->$ss as $value){
            $data[] =  $value;
            
        }return response()->json($data ,200);
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
            'status'=>'required',
        ]);

        //$request->alarmDate!=null? $date = Carbon::parse($request->alarmDate)->format('Y-m-d h:i:s'):$date =null;
        $data =[
            'alarmId'=>$request->alarmId,
            'eventTitle'=>$request->eventTitle,
            'eventDescp'=>$request->eventDescp,
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




/**
 * 
 */  
public function myevents(Request $request){
    $user = $request->user();
    //改成数组
    $data = $request->all();
    
    foreach($data as $rows){
        //将array变成map
        $object = (object) $rows;
        //查询日历里面有没，有就拿出ID 没有就添加日期
        if($object->calendar !=null){
            $id= self::getCalendarId($object->calendar,$user->id);
            if($id !=false){
                $calendarId = $id; 
            }
        }
        //查询myevents里面有没有 有就更新数据 没有就添加
        if($object->events !=null){
            foreach($object->events as $row){
                $object = (object) $row;
                if($object->eventTitle !=null && $object->eventDescp!=null){
                    $eventsId = myevents::getId($object->eventTitle,$object->eventDescp);
                    
                }
                if($eventsId ==null){
                    //添加user数据
                    $data =[
                        'eventTitle'=>$object->eventTitle,
                        'eventDescp'=>$object->eventDescp,
                        'alarmDate'=>$object->alarm,
                        "user_id"=>$user->id,
                        "calendarId"=>$calendarId->id,
                        "status"=>$object->status
                    ];
                   $eventsId = myevents::insert($data);
                }
            }
        }
    }
    //添加完毕以后查询一下
    $all=myevents::all()->where("user_id",$user->id);
    return response()->json($all, 201) ->header('Content-Type','application/json; charset=UTF-8');
}

  public static function getCalendarId (String $calendar, int $userId){
   
    $calendarId = DB::table('calendar')
    ->select('id')
    ->where('user_id', $userId)
    ->where('date', $calendar)
    ->where('status', 0)
    ->first();
    
    if($calendarId ==null){
        $data =[
            'date'=>$calendar,
            "user_id"=>$userId,
            "status"=>0
        ];
        $add =DB::table('calendar')->insert($data);
        $calendarId = DB::table('calendar')
        ->select('id')
        ->where('user_id', $userId)
        ->where('date', $calendar)
        ->where('status', 0)
        ->first();
    }
    return  $calendarId;
  } 

}
