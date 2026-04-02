<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rest;
use App\Models\Attendance;
use Carbon\Carbon;

class RestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            $date = Carbon::parse($attendance->work_date);

            Rest::create([
                'attendance_id' => $attendance->id,
                'rest_start' => $date->copy()->setTime(12, 0),
                'rest_end'   => $date->copy()->setTime(13, 0),
            ]);
        }
    }
}
