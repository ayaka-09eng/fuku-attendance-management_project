<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class GetDateTimeTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_現在の日時情報がUIと同じ形式で出力されている()
    {
        Carbon::setTestNow(Carbon::parse('2026-04-01 09:15:00'));

        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $expectedDate = '2026年4月1日(水)';
        $expectedTime = '09:15';

        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }
}
