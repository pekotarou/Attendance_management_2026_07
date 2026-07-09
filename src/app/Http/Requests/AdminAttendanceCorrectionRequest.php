<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceCorrectionRequest extends FormRequest
{
    /**
     * 管理者勤怠修正を許可
     */
    public function authorize()
    {
        return true;
    }

    /**
     * バリデーションルール
     */
    public function rules()
    {
        return [
            'clock_in_time' => ['required', 'date_format:H:i'],
            'clock_out_time' => ['required', 'date_format:H:i', 'after:clock_in_time'],
            'break_id' => ['nullable', 'array'],
            'break_in_time' => ['nullable', 'array'],
            'break_in_time.*' => ['nullable', 'date_format:H:i'],
            'break_out_time' => ['nullable', 'array'],
            'break_out_time.*' => ['nullable', 'date_format:H:i'],
            'note' => ['required', 'max:255'],
        ];
    }

    /**
     * エラーメッセージ
     */
    public function messages()
    {
        return [
            'clock_in_time.required' => '出勤時間を入力してください',
            'clock_in_time.date_format' => '出勤時間は「09:00」の形式で入力してください',
            'clock_out_time.required' => '退勤時間を入力してください',
            'clock_out_time.date_format' => '退勤時間は「18:00」の形式で入力してください',
            'clock_out_time.after' => '退勤時間は出勤時間より後にしてください',
            'break_in_time.*.date_format' => '休憩開始時間は「12:00」の形式で入力してください',
            'break_out_time.*.date_format' => '休憩終了時間は「13:00」の形式で入力してください',
            'note.required' => '備考を入力してください',
            'note.max' => '備考は255文字以内で入力してください',
        ];
    }
}