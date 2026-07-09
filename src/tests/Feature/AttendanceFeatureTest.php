<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceFeatureTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * テスト用の一般ユーザーを作成
     */
    private function createUser(): User
    {
        return User::create([
            'name' => 'テスト太郎',
            'email' => 'attendance-test-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'admin' => false,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * 勤務外の場合、ステータスが勤務外になる
     */
    public function test_status_is_off_duty_before_clock_in()
    {
        Carbon::setTestNow(Carbon::parse('2026-07-01 08:00:00'));

        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertOk();

        // 画面上に勤務外ステータスが表示されることを確認
        $response->assertSee('勤務外');

        Carbon::setTestNow();
    }

    /**
     * 出勤処理ができる
     */
    public function test_user_can_clock_in()
    {
        Carbon::setTestNow(Carbon::parse('2026-07-01 09:00:00'));

        $user = $this->createUser();

        $response = $this->actingAs($user)->post('/attendance/clock-in');

        $response->assertRedirect('/attendance');

        // attendancesテーブルに出勤時刻が保存される
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => '2026-07-01',
        ]);

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', '2026-07-01')
            ->first();

        $this->assertNotNull($attendance->clock_in_time);
        $this->assertNull($attendance->clock_out_time);

        Carbon::setTestNow();
    }

    /**
     * 出勤中の場合、ステータスが出勤中になる
     */
    public function test_status_is_working_after_clock_in()
    {
        Carbon::setTestNow(Carbon::parse('2026-07-01 09:00:00'));

        $user = $this->createUser();

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-07-01',
            'clock_in_time' => '2026-07-01 09:00:00',
            'clock_out_time' => null,
            'note' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertOk();

        // 画面上に出勤中ステータスが表示されることを確認
        $response->assertSee('出勤中');

        Carbon::setTestNow();
    }

    /**
     * 出勤は一日一回のみできる
     */
    public function test_user_cannot_clock_in_twice_in_one_day()
    {
        Carbon::setTestNow(Carbon::parse('2026-07-01 09:00:00'));

        $user = $this->createUser();

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-07-01',
            'clock_in_time' => '2026-07-01 09:00:00',
            'clock_out_time' => null,
            'note' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertOk();

        // すでに出勤済みなので「出勤する」ボタンは表示されない
        $response->assertDontSee('出勤する');

        Carbon::setTestNow();
    }

    /**
     * 休憩開始処理ができる
     */
    public function test_user_can_start_break()
    {
        Carbon::setTestNow(Carbon::parse('2026-07-01 12:00:00'));

        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-07-01',
            'clock_in_time' => '2026-07-01 09:00:00',
            'clock_out_time' => null,
            'note' => null,
        ]);

        $response = $this->actingAs($user)->post('/attendance/break-in');

        $response->assertRedirect('/attendance');

        // breaksテーブルに休憩開始時刻が保存される
        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
        ]);

        $break = AttendanceBreak::where('attendance_id', $attendance->id)->first();

        $this->assertNotNull($break->break_in_time);
        $this->assertNull($break->break_out_time);

        Carbon::setTestNow();
    }

    /**
     * 休憩中の場合、ステータスが休憩中になる
     */
    public function test_status_is_on_break()
    {
        Carbon::setTestNow(Carbon::parse('2026-07-01 12:10:00'));

        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-07-01',
            'clock_in_time' => '2026-07-01 09:00:00',
            'clock_out_time' => null,
            'note' => null,
        ]);

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_in_time' => '2026-07-01 12:00:00',
            'break_out_time' => null,
            'break_time' => 0,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertOk();

        // 画面上に休憩中ステータスが表示されることを確認
        $response->assertSee('休憩中');

        Carbon::setTestNow();
    }

    /**
     * 休憩戻り処理ができる
     */
    public function test_user_can_finish_break()
    {
        Carbon::setTestNow(Carbon::parse('2026-07-01 13:00:00'));

        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-07-01',
            'clock_in_time' => '2026-07-01 09:00:00',
            'clock_out_time' => null,
            'note' => null,
        ]);

        $break = AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_in_time' => '2026-07-01 12:00:00',
            'break_out_time' => null,
            'break_time' => 0,
        ]);

        $response = $this->actingAs($user)->post('/attendance/break-out');

        $response->assertRedirect('/attendance');

        $break->refresh();

        // 休憩終了時刻と休憩時間が保存される
        $this->assertNotNull($break->break_out_time);
        $this->assertEquals(60, $break->break_time);

        Carbon::setTestNow();
    }

    /**
     * 退勤処理ができる
     */
    public function test_user_can_clock_out()
    {
        Carbon::setTestNow(Carbon::parse('2026-07-01 18:00:00'));

        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-07-01',
            'clock_in_time' => '2026-07-01 09:00:00',
            'clock_out_time' => null,
            'note' => null,
        ]);

        $response = $this->actingAs($user)->post('/attendance/clock-out');

        $response->assertRedirect('/attendance');

        $attendance->refresh();

        // 退勤時刻が保存される
        $this->assertNotNull($attendance->clock_out_time);

        Carbon::setTestNow();
    }

    /**
     * 退勤済みの場合、ステータスが退勤済になる
     */
    public function test_status_is_finished_after_clock_out()
    {
        Carbon::setTestNow(Carbon::parse('2026-07-01 18:10:00'));

        $user = $this->createUser();

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-07-01',
            'clock_in_time' => '2026-07-01 09:00:00',
            'clock_out_time' => '2026-07-01 18:00:00',
            'note' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertOk();

        // 画面上に退勤済ステータスが表示されることを確認
        $response->assertSee('退勤済');

        Carbon::setTestNow();
    }

    /**
     * 勤怠一覧画面に自分の勤怠が表示される
     */
    public function test_user_can_see_own_attendance_list()
    {
        $user = $this->createUser();

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-07-01',
            'clock_in_time' => '2026-07-01 09:00:00',
            'clock_out_time' => '2026-07-01 18:00:00',
            'note' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-07');

        $response->assertOk();

        // 勤怠一覧に日付と時刻が表示される
        $response->assertSee('07/01');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 勤怠詳細画面に選択した勤怠情報が表示される
     */
    public function test_user_can_see_attendance_detail()
    {
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-07-01',
            'clock_in_time' => '2026-07-01 09:00:00',
            'clock_out_time' => '2026-07-01 18:00:00',
            'note' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance/' . $attendance->id);

        $response->assertOk();

        // 勤怠詳細にユーザー名・日付・時刻が表示される
        $response->assertSee('テスト太郎');
        $response->assertSee('2026年');
        $response->assertSee('7月1日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 勤怠詳細修正申請ができる
     */
    public function test_user_can_request_attendance_correction()
    {
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-07-01',
            'clock_in_time' => '2026-07-01 09:00:00',
            'clock_out_time' => '2026-07-01 18:00:00',
            'note' => null,
        ]);

        // 実際の画面操作と同じように、勤怠詳細画面からPOSTしたことにする
        $response = $this
            ->actingAs($user)
            ->from('/attendance/' . $attendance->id)
            ->post('/attendance/' . $attendance->id . '/correction', [
                // 実際のFormRequestに合わせて name を変更
                'clock_in_time' => '09:30',
                'clock_out_time' => '18:30',

                'breaks' => [
                    [
                    'id' => '',
                    'break_in' => '12:00',
                    'break_out' => '13:00',
                ],
            ],
            'note' => '電車遅延のため',
        ]);

        // バリデーションエラーがないことも確認
        $response->assertSessionHasNoErrors();

        // 勤怠詳細画面に戻ることを確認
        $response->assertRedirect('/attendance/' . $attendance->id);

        // attendance_editsテーブルに承認待ちで保存される
        $this->assertDatabaseHas('attendance_edits', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_time' => '2026-07-01 09:30:00',
            'requested_clock_out_time' => '2026-07-01 18:30:00',
            'status' => '承認待ち',
            'note' => '電車遅延のため',
        ]);
    }
}