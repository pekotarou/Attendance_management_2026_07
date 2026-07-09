<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
    */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
    */
    public function boot(): void
    {
        //会員登録時に使うユーザー作成クラスを指定
        Fortify::createUsersUsing(CreateNewUser::class);

        //会員登録画面を指定
        Fortify::registerView(function () {
            return view('auth.register');
        });

        //ログイン画面を指定
        Fortify::loginView(function () {
            return view('auth.login');
        });

        //メール認証誘導画面を指定
        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        //ログイン時のバリデーションと認証処理
        Fortify::authenticateUsing(function ($request) {
            $loginRequest = LoginRequest::createFrom($request);
            $loginRequest->setContainer(app());
            $loginRequest->validateResolved();

            $user = User::where('email', $request->email)->first();

            // ユーザーが存在しない、パスワードが違う、管理者である場合は同じエラーにする
            if (! $user || ! Hash::check($request->password, $user->password) || $user->admin) {
                throw ValidationException::withMessages([
                    'email' => ['ログイン情報が登録されていません'],
                ]);
            }

            return $user;
        });

        //開発中だけログイン失敗回数の上限を増やす（不要になったら削除）
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(30)->by(
                Str::lower($email) . '|' . $request->ip()
            );
        });
        //試行回数を増やすのが不要になったら上記を消すこと
    }
}
