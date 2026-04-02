<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::where('is_admin', false)->get();

        $start = Carbon::create(2026, 3, 1);
        $end = Carbon::yesterday();

        for ($date = $start->copy(); $date <= $end; $date->addDay()) {

            if ($date->isWeekend()) {
                continue;
            }

            foreach ($users as $user) {
                Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $date->toDateString(),
                    'clock_start' => $date->copy()->setTime(9, 0),
                    'clock_end' => $date->copy()->setTime(18, 0),
                ]);
            }
        }
    }
}
