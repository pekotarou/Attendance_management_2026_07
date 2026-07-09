<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRecordRequest extends FormRequest
{
    /**
     * API登録を許可
     */
    public function authorize()
    {
        return true;
    }

    /**
     * 勤怠登録APIのバリデーション
     */
    public function rules()
    {
        return [
            // 修正: 勤怠日は必須・日付形式
            'date' => ['required', 'date_format:Y-m-d'],

            // 修正: ユーザーIDは必須・usersテーブルに存在すること
            'user_id' => ['required', 'exists:users,id'],

            // 修正: 同じユーザー・同じ日付の勤怠は重複登録させない
            'clock_in' => ['required', 'date_format:H:i:s'],

            // 修正: 退勤は任意。ただし入力する場合は時刻形式
            'clock_out' => ['nullable', 'date_format:H:i:s'],

            // 修正: 備考は任意・255文字以内
            'comment' => ['nullable', 'max:255'],
        ];
    }

    /**
     * 日本語エラーメッセージ
     */
    public function messages()
    {
        return [
            'date.required' => '勤怠日は必須です。',
            'date.date_format' => '勤怠日は YYYY-MM-DD 形式で指定してください。',
            'user_id.required' => 'ユーザーIDは必須です。',
            'user_id.exists' => '指定されたユーザーが存在しません。',
            'clock_in.required' => '出勤時刻は必須です。',
            'clock_in.date_format' => '出勤時刻は HH:MM:SS 形式で指定してください。',
            'clock_out.date_format' => '退勤時刻は HH:MM:SS 形式で指定してください。',
            'comment.max' => '備考は255文字以内で入力してください。',
        ];
    }
}