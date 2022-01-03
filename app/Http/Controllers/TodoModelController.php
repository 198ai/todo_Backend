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
         return response()->json($request, 201);
    }
    public function addtodolist(Request $request){
        $user = $request->user();
        
    }
}
