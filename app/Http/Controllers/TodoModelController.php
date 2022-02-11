<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TodoModel;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Requests\RegisterUserRequest;
class TodoModelController extends Controller
{
    public function todolist(Request $request){
        $user = $request->user();
        $request= DB::table('todomodel')->where('user_id', $user->id)
        ->where('status','!=','1')
        ->select('id', 'user_id','title', 'time','date','endDate','created_at','updated_at','complete')
        ->get();
        return response()->json($request, 201)
        ->header('Content-Type','application/json; charset=UTF-8');
    }
    public function addtodolist(Request $request){
        $user = $request->user();
        $request->validate([
            'title'=>'required',
            'time'=>'required',
            'date'=>'required',
            "endDate"=> 'required',
            "complete"=>'required',
            "status"=>'required'
        ]);
        
        $data =[
            'title'=>$request->title,
            'time'=>$request->time,
            'date'=>$request->date,
            "endDate"=> $request->endDate,
            "complete"=>$request->complete,
            "user_id"=>$user->id,
            "status"=>$request->status,
            "created_at"=>Carbon::now(),
            "updated_at"=>Carbon::now(),
        ];
        $todolist =DB::table('todomodel')->insert($data);
        if($todolist){
            $dataid = DB::table('todomodel')->where("user_id",$user->id)
            ->where("date",$request->date)->where("title",$request->title)
            ->where('status','!=','1')
            ->value('id');
            return response()->json($dataid, 201)
            ->header('Content-Type','application/json; charset=UTF-8');
        }else{
            return response()->json("追加失敗した", 400)
            ->header('Content-Type','application/json; charset=UTF-8');
        }

    }
    public function updatetodolist(Request $request){
        $user = $request->user();
        $request->validate([
            'title'=>'required',
            'time'=>'required',
            'date'=>'required',
            "endDate"=> 'required',
            "complete"=>'required',
            "status"=>'required'
        ]);
        $data =[
            'title'=>$request->title,
            'time'=>$request->time,
            'date'=>$request->date,
            "endDate"=> $request->endDate,
            "complete"=>$request->complete,
            "user_id"=>$user->id,
            "status"=>$request->status,
        ];
        $todolist =DB::table('todomodel')
        ->where('id',$request->id)
        ->update($data);
        if($todolist==1){
            return response()->json("更新しました", 201)
            ->header('Content-Type','application/json; charset=UTF-8');
        }else{
            return response()->json("更新失敗した", 400)
            ->header('Content-Type','application/json; charset=UTF-8');
        }
       
    }
    public function updatetime(Request $request){
        $user = $request->user();
        $request->validate([
            'time'=>'required',
            "complete"=>'required',
            "differTimes"=>'required',
            "date"=>'required'
        ]);
        $dataTodo =[
            'time'=>$request->time,
            "complete"=>$request->complete,
            "user_id"=>$user->id,
        ];
        $todotitle =DB::table('todomodel')
        ->where('id',$request->id)
        ->value('title');

        //当天日期不为空，则查找是否是已经存在的项目
        $getTimes =DB::table('graph')->where('user_id', $user->id)->where("date",$request->date)->where("events",$todotitle)->value('times');
        //没有存在项目就添加
        if($getTimes ==null){
            $data=[
                "events"=>$todotitle,
                "date"=>$request->date,
                "times"=>$request->differTimes,
                "user_id"=>$user->id,
                "status"=>0
            ];
            $add =DB::table('graph')->insert($data);
            if(!$add){
                return response()->json("統計追加失敗", 400)
                ->header('Content-Type','application/json; charset=UTF-8');
            }
        }
        if($getTimes !=null){
            $data=[
                'times'=>$request->differTimes==0? $getTimes:$request->differTimes+$getTimes
            ];
            $restult =DB::table('graph')->where('user_id', $user->id)->where("date",$request->date)->where("events",$todotitle)->update($data);
            if($restult!=1){
                return response()->json("統計追加失敗", 400) ->header('Content-Type','application/json');
            }
                }
        $todolist =DB::table('todomodel')
        ->where('id',$request->id)
        ->where('user_id', $user->id)
        ->update($dataTodo);
        if($todolist==1){
            return response()->json("更新しました", 201)
            ->header('Content-Type','application/json; charset=UTF-8');
        }else{
            return response()->json("更新失敗した", 400)
            ->header('Content-Type','application/json; charset=UTF-8');
        }
       
    }
}
