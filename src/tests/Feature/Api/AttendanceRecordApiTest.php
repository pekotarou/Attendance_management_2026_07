<?php

namespace Tests\Feature\Api;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceRecordApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * テスト用ユーザーを作成
     */
    private function createUser(): User
    {
        return User::create([
            'name' => 'テスト太郎',
            'email' => 'api-test@example.com',
            'password' => Hash::make('password'),
            'admin' => false,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * テスト用勤怠を作成
     */
    private function createAttendance(User $user): Attendance
    {
        return Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-07-01',
            'clock_in_time' => '2026-07-01 09:00:00',
            'clock_out_time' => '2026-07-01 18:00:00',
            'note' => 'APIテスト用データ',
        ]);
    }

    /**
     * 勤怠一覧APIでdataとmetaが返る
     */
    public function test_attendance_records_index_returns_data_and_meta()
    {
        $user = $this->createUser();
        $this->createAttendance($user);

        $response = $this->getJson('/api/v1/attendance-records');

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'user_name',
                    'date',
                    'clock_in',
                    'clock_out',
                    'break_minutes',
                    'work_minutes',
                    'note',
                    'is_corrected',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);
    }

    /**
     * 勤怠詳細APIで1件取得できる
     */
    public function test_attendance_record_show_returns_single_record()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->getJson('/api/v1/attendance-records/' . $attendance->id);

        $response->assertOk();

        $response->assertJson([
            'data' => [
                'id' => $attendance->id,
                'user_id' => $user->id,
                'user_name' => 'テスト太郎',
                'date' => '2026-07-01',
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'break_minutes' => 0,
                'work_minutes' => 540,
                'note' => 'APIテスト用データ',
                'is_corrected' => false,
            ],
        ]);
    }

    /**
     * 存在しない勤怠IDの場合、404 JSONが返る
     */
    public function test_attendance_record_show_returns_404_when_not_found()
    {
        $response = $this->getJson('/api/v1/attendance-records/999999');

        $response->assertNotFound();

        $response->assertJson([
            'error' => '勤怠情報が見つかりませんでした。',
        ]);
    }

    /**
     * APIトークンが発行できる
     */
    public function test_api_token_can_be_created()
    {
        $this->createUser();

        $response = $this->postJson('/api/v1/tokens', [
            'email' => 'api-test@example.com',
            'password' => 'password',
        ]);

        $response->assertOk();

        $response->assertJsonStructure([
            'token',
        ]);
    }

    /**
     * 未認証では勤怠を登録できない
     */
    public function test_unauthenticated_user_cannot_store_attendance_record()
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/v1/attendance-records', [
            'user_id' => $user->id,
            'date' => '2026-07-02',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '未認証テスト',
        ]);

        $response->assertUnauthorized();

        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    /**
     * 認証済みユーザーは勤怠を登録できる
     */
    public function test_authenticated_user_can_store_attendance_record()
    {
        $user = $this->createUser();

        // Sanctum認証済みユーザーとして実行
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/attendance-records', [
            'user_id' => $user->id,
            'date' => '2026-07-02',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => 'API登録テスト',
        ]);

        $response->assertCreated();

        $response->assertJson([
            'data' => [
                'user_id' => $user->id,
                'date' => '2026-07-02',
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'work_minutes' => 540,
                'note' => 'API登録テスト',
            ],
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => '2026-07-02',
            'note' => 'API登録テスト',
        ]);
    }

    /**
     * 不正なデータでは勤怠を登録できない
     */
    public function test_store_attendance_record_returns_422_when_invalid()
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/attendance-records', [
            'user_id' => $user->id,
            'date' => '',
            'clock_in' => '',
            'clock_out' => '18:00:00',
            'comment' => '不正データテスト',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'date',
            'clock_in',
        ]);
    }

    /**
     * 認証済みユーザーは勤怠を更新できる
     */
    public function test_authenticated_user_can_update_attendance_record()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/attendance-records/' . $attendance->id, [
            'date' => '2026-07-01',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'comment' => 'API更新テスト',
        ]);

        $response->assertOk();

        $response->assertJson([
            'data' => [
                'id' => $attendance->id,
                'date' => '2026-07-01',
                'clock_in' => '10:00:00',
                'clock_out' => '19:00:00',
                'work_minutes' => 540,
                'note' => 'API更新テスト',
            ],
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'note' => 'API更新テスト',
        ]);
    }

    /**
     * 認証済みユーザーは勤怠を削除できる
     */
    public function test_authenticated_user_can_delete_attendance_record()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/attendance-records/' . $attendance->id);

        $response->assertNoContent();

        $this->assertDatabaseMissing('attendances', [
            'id' => $attendance->id,
        ]);
    }

    /**
 * 他ユーザーの勤怠は更新できない
 */
public function test_authenticated_user_cannot_update_other_users_attendance_record()
{
    $user = $this->createUser();
    $otherUser = User::create([
        'name' => '他人太郎',
        'email' => 'other-api-test-' . uniqid() . '@example.com',
        'password' => Hash::make('password'),
        'admin' => false,
        'email_verified_at' => now(),
    ]);

    $attendance = $this->createAttendance($otherUser);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/attendance-records/' . $attendance->id, [
        'date' => '2026-07-01',
        'clock_in' => '10:00:00',
        'clock_out' => '19:00:00',
        'comment' => '他人の勤怠更新テスト',
    ]);

    $response->assertForbidden();

    $response->assertJson([
        'error' => 'この操作を実行する権限がありません。',
    ]);
}

    /**
     * 他ユーザーの勤怠は削除できない
     */
    public function test_authenticated_user_cannot_delete_other_users_attendance_record()
    {
        $user = $this->createUser();
        $otherUser = User::create([
            'name' => '他人太郎',
            'email' => 'other-delete-api-test-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'admin' => false,
            'email_verified_at' => now(),
        ]);

        $attendance = $this->createAttendance($otherUser);

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/attendance-records/' . $attendance->id);

        $response->assertForbidden();

        $response->assertJson([
            'error' => 'この操作を実行する権限がありません。',
        ]);
    }

    /**
     * 認証済みユーザーはPATCHでも勤怠を更新できる
     */
    public function test_authenticated_user_can_patch_attendance_record()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/attendance-records/' . $attendance->id, [
            'date' => '2026-07-01',
            'clock_in' => '10:30:00',
            'clock_out' => '19:30:00',
            'comment' => 'API PATCH更新テスト',
        ]);

        $response->assertOk();

        $response->assertJson([
            'data' => [
                'id' => $attendance->id,
                'date' => '2026-07-01',
                'clock_in' => '10:30:00',
                'clock_out' => '19:30:00',
                'note' => 'API PATCH更新テスト',
            ],
        ]);
    }
}