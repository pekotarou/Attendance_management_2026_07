<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFeatureTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * テスト用の一般ユーザーを作成
     */
    private function createUser(bool $emailVerified = true): User
    {
        return User::create([
            'name' => 'テスト太郎',
            'email' => 'auth-test-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'admin' => false,
            'email_verified_at' => $emailVerified ? now() : null,
        ]);
    }

    /**
     * テスト用の管理者を作成
     */
    private function createAdmin(): User
    {
        return User::create([
            'name' => '管理者太郎',
            'email' => 'admin-auth-test-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'admin' => true,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * 会員登録できる
     */
    public function test_user_can_register()
    {
        $response = $this->post('/register', [
            'name' => '新規ユーザー',
            'email' => 'new-user-' . uniqid() . '@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Fortify登録後はログイン状態になる
        $this->assertAuthenticated();

        // 登録後の遷移先は実装により /email/verify または /attendance になる可能性がある
        $response->assertStatus(302);
    }

    /**
     * 一般ユーザーはログインできる
     */
    public function test_user_can_login()
    {
        $user = $this->createUser();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/attendance');

        $this->assertAuthenticatedAs($user);
    }

    /**
     * ログアウトできる
     */
    public function test_user_can_logout()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post('/logout');

        // ログアウト後は未認証になる
        $this->assertGuest();

        // ログアウト後はログイン画面などへリダイレクトする
        $response->assertStatus(302);
    }

    /**
     * 未ログインでは勤怠画面に入れない
     */
    public function test_guest_cannot_access_attendance_page()
    {
        $response = $this->get('/attendance');

        // 未ログインなのでログイン画面へリダイレクト
        $response->assertRedirect('/login');
    }

    /**
     * メール未認証ユーザーは勤怠画面に入れない
     */
    public function test_unverified_user_cannot_access_attendance_page()
    {
        $user = $this->createUser(false);

        $response = $this->actingAs($user)->get('/attendance');

        // verifiedミドルウェアによりメール認証画面へリダイレクト
        $response->assertRedirect('/email/verify');
    }

    /**
     * メール認証済みユーザーは勤怠画面に入れる
     */
    public function test_verified_user_can_access_attendance_page()
    {
        $user = $this->createUser(true);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertOk();

        // 勤怠登録画面が表示されることを確認
        $response->assertSee('勤務外');
    }

    /**
     * 管理者は一般ログインできない
     */
    public function test_admin_cannot_login_from_user_login_page()
    {
        $admin = $this->createAdmin();

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        // 管理者は一般ログイン不可
        $this->assertGuest();

        // ログイン画面に戻される
        $response->assertRedirect();
    }
}