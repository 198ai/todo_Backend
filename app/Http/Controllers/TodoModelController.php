<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TodoModel;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterUserRequest;
class TodoModelController extends Controller
{
    public function todolist(Request $request){
        $user = $request->user();
        $request= DB::table('todomodel')->where('user_id', $user->id)->get();
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
            "complete"=>'required'
        ]);
        $data =[
            'title'=>$request->title,
            'time'=>$request->time,
            'date'=>$request->date,
            "endDate"=> $request->endDate,
            "complete"=>$request->complete,
            "user_id"=>$user->id,
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
            "complete"=>'required'
        ]);
        $data =[
            'title'=>$request->title,
            'time'=>$request->time,
            'date'=>$request->date,
            "endDate"=> $request->endDate,
            "complete"=>$request->complete,
            "user_id"=>$user->id,
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
