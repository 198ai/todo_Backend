<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
class RegisterController extends Controller
{
    //用户注册
    // public function register(RegisterUserRequest $request){
    //     $user =User::Create([
    //         'name'=>$request->name,
    //         'email'=>$request->email,
    //         'password'=>bcrypt($request->password)
    //     ]);
    //     return response()->json([
    //         'data'=>$user
    //     ],201)->header('Content-Type','application/json; charset=UTF-8');
    // }
    public function signup(Request $request)
    {
        $data =[
            'name'=>'required|min:3|unique:users',
            'email'=>'required|email|unique:users',
            'password'=>'required|min:5'
        ];
        $validator = Validator::make($request->all(),$data);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 401)
            ->header('Content-Type','application/json; charset=UTF-8');
        }
        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $user->save();
        $credentials = request(['email', 'password']);

        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);

        $user = $request->user();

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);

        $token->save();

        return response()->json([
            'name'=>$user->name,
            'email'=>$user->email,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
            ],201)->header('Content-Type','application/json; charset=UTF-8');
        // return response()->json([
        //     'message' => 'Successfully created user!'
        // ], 201);
    }

    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function login(Request $request)
    {
        $data = [
            'email'=>'required|email',
            'password'=>'required|min:5'
        ];
        $validator = Validator::make($request->all(),$data);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 401)
            ->header('Content-Type','application/json; charset=UTF-8');
        }
        $user =User::where('email',$request->email)->first();
        if(!$user){
            return response()->json("メールアドレスが間違っています。",401)
            ->header('Content-Type','application/json; charset=UTF-8');
        }
        if(!Hash::check($request->password,$user->password)){
            return response()->json("パスワードが間違っています",401)
            ->header('Content-Type','application/json; charset=UTF-8');
        }
        
        $token = $user->createToken('Auth Token')->accessToken;
         return response()->json([
            'name'=>$user->name,
            'email'=>$user->email,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ],201)->header('Content-Type','application/json; charset=UTF-8');
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'ログアウトしました'
        ],201)->header('Content-Type','application/json; charset=UTF-8');
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json($request->user())->header('Content-Type','application/json; charset=UTF-8');
    }

}
