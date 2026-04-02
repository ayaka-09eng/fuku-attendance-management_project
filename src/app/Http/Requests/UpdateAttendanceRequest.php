<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'requested_clock_start' => ['required'],
            'requested_clock_end' => ['required'],
            'note' => ['required'],
        ];
    }

    public function messages() {
        return [
            'requested_clock_start.required' => '出勤時間を入力してください',
            'requested_clock_end.required' => '退勤時間を入力してください',
            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator) {
        $validator->after(function ($validator) {
            $clockStart = $this->input('requested_clock_start');
            $clockEnd   = $this->input('requested_clock_end');

            if ($clockStart && $clockEnd && $clockStart > $clockEnd) {
                $validator->errors()->add('time_range', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $parsed = [];

            foreach ($this->input('rest_corrections', []) as $key => $rest) {
                $start = $rest['requested_rest_start'] ?? null;
                $end   = $rest['requested_rest_end'] ?? null;

                if ($start && !$end) {
                    $validator->errors()->add("rest_corrections.$key.requested_rest_end", '休憩終了時間を入力してください');
                }
                if (!$start && $end) {
                    $validator->errors()->add("rest_corrections.$key.requested_rest_start", '休憩開始時間を入力してください');
                }

                if ($start && $end && $start > $end) {
                    $validator->errors()->add("rest_corrections.$key.requested_rest_start", '休憩時間が不適切な値です');
                }

                if ($start) {
                    if ($clockStart && $start < $clockStart) {
                        $validator->errors()->add("rest_corrections.$key.requested_rest_start", '休憩時間が不適切な値です');
                    }
                    if ($clockEnd && $start > $clockEnd) {
                        $validator->errors()->add("rest_corrections.$key.requested_rest_start", '休憩時間が不適切な値です');
                    }
                }

                if ($end && $clockEnd && $end > $clockEnd) {
                    $validator->errors()->add("rest_corrections.$key.requested_rest_end", '休憩時間もしくは退勤時間が不適切な値です');
                }

                if ($start && $end) {
                    $parsed[] = [
                        'index' => $key,
                        'start' => Carbon::parse($start),
                        'end'   => Carbon::parse($end),
                    ];
                }
            }

            for ($i = 0; $i < count($parsed); $i++) {
                for ($j = $i + 1; $j < count($parsed); $j++) {

                    $a = $parsed[$i];
                    $b = $parsed[$j];

                    if ($a['start']->lt($b['end']) && $b['start']->lt($a['end'])) {
                        $validator->errors()->add(
                            "rest_corrections.{$a['index']}.requested_rest_start",
                            '休憩時間が他の休憩時間と重複しています。'
                        );
                    }
                }
            }
        });
    }
}
