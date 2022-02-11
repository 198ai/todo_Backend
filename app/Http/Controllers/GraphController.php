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


class GraphController extends Controller
{
    public function postgraph(Request $request){
        $user = $request->user();
        $contents =(object)$request->contents;
        //拿到更新的数据，先查一遍是不是今天已经更新的数据，如果是，就加上已经知道的数据，不是就不添加
        
        //当天日期不为空，则查找是否是已经存在的项目
        $getTimes =DB::table('graph')->where('user_id', $user->id)->where("date",$request->date)->where("events",$contents->events)->value('times');
        //没有存在项目就添加
        if($getTimes ==null){
            $data=[
                "events"=>$contents->events,
                "date"=>$request->date,
                "times"=>$contents->times,
                "user_id"=>$user->id,
                "status"=>0
            ];
            $add =DB::table('graph')->insert($data);
            if(!$add){
                return response()->json("追加失敗した", 400)
                ->header('Content-Type','application/json; charset=UTF-8');
            }
        }
        if($getTimes !=null){
            $data=[
                'times'=>$getTimes +$contents->times
            ];
            $restult =DB::table('graph')->where('user_id', $user->id)->where("date",$request->date)->where("events",$contents->events)->update($data);
            if($restult!=1){
                return response()->json("統計追加失敗", 400) ->header('Content-Type','application/json');
            }
        }
        return response()->json("統計更新成功", 201) ->header('Content-Type','application/json; charset=UTF-8');
    }


    public function getgraph(Request $request){
        $user = $request->user();
        $restult = DB::table('graph')->where('user_id', 57)->where('status',0)->get();
        if($restult ==null){
            return response()->json("データがありません", 400) ->header('Content-Type','application/json; charset=UTF-8');
        }
        
        $arry2=array();
        $data2=[];
        $date="";
        foreach($restult as $row){
           
           if($date==$row->date){
            $contents =[
                "events"=>$row->events,
                "times"=>$row->times];
            $array[]=$contents;
            $data2=[
                "date"=>$date,
                "contents"=>$array];
            }
            //如果是空就先赋值
            if($date=="" || $date!=$row->date){
                if($date!=""){
                    $arry2[]=$data2;
                }
                $data2=[];
                $array=[];
                $date=$row->date;
                $contents =[
                    "events"=>$row->events,
                    "times"=>$row->times];
                $array[]=$contents;
                $data2=[
                    "date"=>$date,
                    "contents"=>$array];
            }
        }
        $arry2[]=$data2;
        //return response()->json($array, 201) ->header('Content-Type','application/json; charset=UTF-8');
        return response()->json($arry2, 201) ->header('Content-Type','application/json; charset=UTF-8');
    }
}