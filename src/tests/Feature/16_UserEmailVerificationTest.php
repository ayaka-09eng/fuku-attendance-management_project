<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class UserEmailVerificationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_会員登録後、認証メールが送信される()
    {
        Notification::fake();

        // 会員登録処理
        $data = [
            'name' => '山田　太郎',
            'email' => 'example@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $data);

        $response->assertRedirect(route('user.attendance.create'));

        $user = User::where('email', 'example@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する()
    {
        $user = User::factory()->create(['is_admin' => 0]);

        $this->actingAs($user);

        $response = $this->get(route('verification.notice'));
        $response->assertStatus(200);
        $response->assertViewIs('user.auth.verify_email');

        $response->assertSee("http://localhost:8025/", false);
    }

    public function test_メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する()
    {
        $user = User::factory()->create(['is_admin' => 0]);

        $this->actingAs($user);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->get($verificationUrl);

        $response->assertRedirect(route('user.attendance.create'));

        $response = $this->followRedirects($response);

        $response->assertStatus(200);
        $response->assertViewIs('user.attendance.create');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
