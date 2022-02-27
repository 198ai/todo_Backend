$data = [
            'name'=>'required|min:3',
            'email'=>'required|email',
            'password'=>'required|min:5'
        ];
        $validator = Validator::make($request->all(),$data);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 401)
            ->header('Content-Type','application/json; charset=UTF-8');
        }
        $resetPassword = DB::table('users')->where('email',$request->email)
        ->where('name',$request->name)
        ->value('password');
        if($resetPassword!=null){
            if(decrypt($resetPassword)==$request->password){
               $data= User::where('email',$request->email)
                ->where('name',$request->name)->select('id')->first();
                // $token=$data->id
                // return response()->json(
                //     $data,201)->header('Content-Type','application/json; charset=UTF-8');
            }else{
                return response()->json(
                    "パスワードが間違っています",401)->header('Content-Type','application/json; charset=UTF-8');
            }
        }else{
            return response()->json(
                "ユーザが登録されていません",401)->header('Content-Type','application/json; charset=UTF-8');
        }