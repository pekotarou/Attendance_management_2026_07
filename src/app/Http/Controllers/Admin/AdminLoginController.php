<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminLoginController extends Controller
{
    /**
     * 管理者ログイン画面を表示
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * 管理者ログイン処理
     */
    public function login(Request $request)
    {
        // 修正: 管理者ログイン用バリデーション
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレスは「ユーザー名@ドメイン」形式で入力してください',
            'password.required' => 'パスワードを入力してください',
        ]);

        // 修正: 入力されたメールアドレスのユーザーを取得
        $user = User::where('email', $request->email)->first();

        // 修正: ユーザーが存在しない、パスワード不一致、管理者ではない場合はエラー
        if (! $user || ! Hash::check($request->password, $user->password) || ! $user->admin) {
            return back()
                ->withErrors([
                    'email' => '管理者情報が登録されていません',
                ])
                ->withInput();
        }

        // 修正: 管理者としてログイン
        Auth::login($user);

        return redirect('/admin/attendance/list');
    }
}