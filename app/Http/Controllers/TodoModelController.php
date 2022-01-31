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
            return response()->json("追加しました", 201)
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
   
}
