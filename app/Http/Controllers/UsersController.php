<?php

namespace App\Http\Controllers;

use App\User;
use Auth;
use Validator;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function __construct()
    {
        // ログインしていなかったらログインページに遷移する（この処理を消すとログインしなくてもページを表示する）
        $this->middleware('auth');
    }

    public function show($user_id)
    {
        $user = User::where('id', $user_id)->firstOrFail();

        return view('user/show', ['user' => $user]);
    }

    public function edit()
    {
        $user = Auth::user();

         // テンプレート「user/edit.blade.php」を表示します。
        return view('user/edit', ['user' => $user]);
    }

    public function update(Request $request)
    {
        //バリデーション（入力値チェック）
        $validator = Validator::make($request->all() , [
            'user_name' => 'required|string|max:255',
            'user_password' => 'required|string|min:6|confirmed',
        ]);

        //バリデーションの結果がエラーの場合
        if ($validator->fails())
        {
          return redirect()->back()->withErrors($validator->errors())->withInput();
          // redirect() : 特定ページへのリダイレクト
          // back() : 例えば送信されたフォーム内容にエラーがある場合など、直前のページヘユーザーをリダイレクトさせる
          // withErrors() : リクエストのバリデーションが失敗するかを確認した後、セッションにエラーメッセージをフラッシュデータとして保存する
          // withInput() : 下のURLが良い。バリデーションエラーがあった時にフォームに値を持たせておく。
          // https://qiita.com/zaburo/items/5c019d9062ddf1493d16#form%E3%81%AE%E5%80%A4%E3%82%92%E4%BF%9D%E6%8C%81old
        }

        $user = User::find($request->id);
        $user->name = $request->user_name;
        if ($request->user_profile_photo !=null) {
            $request->user_profile_photo->storeAs('public/user_images', $user->id . '.jpg');
            $user->profile_photo = $user->id . '.jpg';
        }
        $user->password = bcrypt($request->user_password);
        $user->save();

        return redirect('/users/'.$request->id);
    }
}
