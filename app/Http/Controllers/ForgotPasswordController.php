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
            return response()->json("アカウントが存在しません。", 400)
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
            $times =Carbon::now()->diffInMinutes($checked)>5;
            //小于5分钟的就返回请等待五分钟
            if(!$times){
                return response()->json("検証コードは5分以内にご利用いただけます。", 400)
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
            return response()->json("メールが発送しました、ご確認よろしくお願い致します", 201)
        ->header('Content-Type','application/json; charset=UTF-8');
        }else{
            return response()->json("メールが失敗した", 400)
        ->header('Content-Type','application/json; charset=UTF-8');
        }
        
    }
}