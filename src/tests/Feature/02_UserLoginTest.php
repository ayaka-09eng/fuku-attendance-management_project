<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_メールアドレスが未入力の場合、バリデーションメッセージが表示される()
    {
        User::factory()->create([
            'email' => 'example@example.com',
            'password' => bcrypt('password'),
            'is_admin' => 0,
        ]);

        $response = $this->post('/login', [
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function test_パスワードが未入力の場合、バリデーションメッセージが表示される()
    {
        User::factory()->create([
            'email' => 'example@example.com',
            'password' => bcrypt('password'),
            'is_admin' => 0,
        ]);

        $response = $this->post('/login', [
            'email' => 'example@example.com',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    public function test_登録内容と一致しない場合、バリデーションメッセージが表示される()
    {
        User::factory()->create([
            'email' => 'example@example.com',
            'password' => bcrypt('password'),
            'is_admin' => 0,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}
