<?php

namespace App\Http\Controllers;
use App\Http\Controllers\BaseController;
use App\Mail\SendEmailCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class ForgotPasswordController extends Controller
{
    public static $email;
    public function forgotpassword(Request $request)
    {
        
        $validator = Validator::make($request->all(), ['email'=>'required|email']);
        if ($validator->fails()) {
            return response()->json("正しくメールを入力してください。", 400)
            ->header('Content-Type','application/json; charset=UTF-8');
        }
        self::$email = DB::table('users')->where('email',$request->email)->value('email');
        if (self::$email ==null) {
            return response()->json("このメールアドレスは登録されていません。", 400)
            ->header('Content-Type','application/json; charset=UTF-8');
        }
        //随机生成验证码
        $strs="QWERTYUIOPASDFGHJKLZXCVBNM1234567890qwertyuiopasdfghjklzxcvbnm";
        $key = "";
        for( $i=0; $i<6; $i++ ) {
        $key .= $strs[mt_rand(0, 61)]; // 从索引0-62区间寻随机数
        }
        //检查是否在五分以内
        $checked = DB::table('password_resets')->where('email',self::$email)->latest('created_at')->value('created_at');
       
        if ($checked != null) {
            $times =Carbon::now()->diffInMinutes($checked)>1;
            //小于5分钟的就返回请等待五分钟
            if(!$times){
                return response()->json("60秒ごとに一回認証コードの再発行ができます。", 400)
                ->header('Content-Type','application/json; charset=UTF-8');
            }
        }
        //保存在重置中
        $data=[
            'email'=>self::$email,
            'token'=>encrypt($key),
            'created_at'=>Carbon::now()
        ];
        $saved = DB::table('password_resets')->insert($data);
        if (!$saved) {
            return response()->json("DBへ書き込みを失敗しました。", 400)
            ->header('Content-Type','application/json; charset=UTF-8');
        }
        Mail::send('emails.test',['key'=>$key],function($message){
            $to = self::$email;
            $message ->to($to)->subject('パスワードリセットの認証コード');
        });
        // 返回的一个错误数组，利用此可以判断是否发送成功
        if(count(Mail::failures()) < 1){
            return response()->json("認証コードを発送しました。", 201)
        ->header('Content-Type','application/json; charset=UTF-8');
        }else{
            return response()->json("メールが失敗した", 400)
        ->header('Content-Type','application/json; charset=UTF-8');
        }
        
    }


    public function resetPassword(Request $request){
        $error=[
            'token.required'=>"認証コードを入力してください。",
            'email.required'=>"アカウントのメールアドレスを入力してください。",
            'password.required'=>"パスワードを入力してください。",
        ];
        $validator = Validator::make($request->all(), ['token'=>'required','email'=>'required|email','password'=>'required|min:6'],$error);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400)
            ->header('Content-Type','application/json; charset=UTF-8');
        }
        //先检查是不是超过五分钟
        $checkedTime = DB::table('password_resets')->where('email',$request->email)->latest('created_at')->value('created_at');
        $checkedToken = DB::table('password_resets')->where('email',$request->email)->latest('created_at')->value('token');
        if ($checkedTime != null) {
            if($checkedToken!=null){
                //入力されたTOKENと判断
                if (decrypt($checkedToken)!=$request->token) {
                  //認証コードは違う時
                  return response()->json("認証コードが間違っています", 400)
                  ->header('Content-Type','application/json; charset=UTF-8');
                }
            }
            $times =Carbon::now()->diffInMinutes($checkedTime)>5;
            //大于5分钟的就返回请重新申请
            if($times){
                return response()->json("認証コードは5分以内にご利用いただけます。", 400)
                ->header('Content-Type','application/json; charset=UTF-8');
            }
        }else if($checkedTime == null){
            return response()->json("メールアドレスの認証コードが発行していません。", 400)
            ->header('Content-Type','application/json; charset=UTF-8');
        }
       
        //パスワードリセット
        $resetPassword = DB::table('users')->where('email',$request->email)->update(['password'=>Hash::make($request->password)]);
        if($resetPassword ==-1){
            return response()->json("パスワードリセットに失敗しました", 400)
            ->header('Content-Type','application/json; charset=UTF-8');
        }
        $user =User::where('email',$request->email)->first();
        $token = $user->createToken('Auth Token')->accessToken;
          return response()->json([
             'name'=>$user->name,
             'email'=>$user->email,
             'access_token' => $token,
             'token_type' => 'Bearer',
         ],201)->header('Content-Type','application/json; charset=UTF-8');
    }

    public function changePassword(Request $request){
        $data = [
            'email'=>'required|email',
            'password'=>'required|min:5',
            'newpassword'=>'required|min:5'
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
            return response()->json("旧パスワードが間違っています",401)
            ->header('Content-Type','application/json; charset=UTF-8');
        }
          //パスワードリセット
          $resetPassword = DB::table('users')->where('email',$request->email)->update(['password'=>Hash::make($request->newpassword)]);
          if($resetPassword ==-1){
              return response()->json("パスワードリセットに失敗しました", 400)
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
}