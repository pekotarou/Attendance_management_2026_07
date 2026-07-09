<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiTokenController extends Controller
{
    /**
     * APIトークン発行
     */
    public function store(Request $request)
    {
        // APIログイン用バリデーション
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        // ユーザーが存在しない、またはパスワード不一致なら同じエラー
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['認証情報が正しくありません。'],
            ]);
        }

        // 外部アプリ用トークンを発行
        $token = $user->createToken('external-app-token')->plainTextToken;

        return response()->json([
            'token' => $token,
        ]);
    }
}