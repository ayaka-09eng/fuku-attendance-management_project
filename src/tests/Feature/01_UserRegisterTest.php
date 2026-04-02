<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRegisterTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_名前が未入力の場合、バリデーションメッセージが表示される()
    {
        $data = [
            'email' => 'example@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $data);
        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    public function test_メールアドレスが未入力の場合、バリデーションメッセージが表示される()
    {
        $data = [
            'name' => '山田　太郎',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $data);
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function test_パスワードが8文字未満の場合、バリデーションメッセージが表示される()
    {
        $data = [
            'name' => '山田　太郎',
            'email' => 'example@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ];

        $response = $this->post('/register', $data);
        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    public function test_パスワードが一致しない場合、バリデーションメッセージが表示される()
    {
        $data = [
            'name' => '山田　太郎',
            'email' => 'example@example.com',
            'password' => 'pass',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $data);
        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
    }

    public function test_パスワードが未入力の場合、バリデーションメッセージが表示される()
    {
        $data = [
            'name' => '山田　太郎',
            'email' => 'example@example.com',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $data);
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    public function test_フォームに内容が入力されていた場合、データが正常に保存される()
    {
        $data = [
            'name' => '山田 太郎',
            'email' => 'example@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $data);

        $response->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'name' => '山田 太郎',
            'email' => 'example@example.com',
        ]);
    }
}
