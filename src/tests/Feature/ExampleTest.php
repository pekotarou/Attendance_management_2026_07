<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * トップページがログイン画面へリダイレクトされることを確認
     */
    public function test_top_page_redirects_to_login()
    {
        // / は /login にリダイレクトする設計
        $response = $this->get('/');

        // 200ではなく、/login へのリダイレクトを確認
        $response->assertRedirect('/login');
    }
}