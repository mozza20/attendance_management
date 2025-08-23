<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
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
            'rev_start_time' => ['required', 'date_format:H:i'],
            'rev_finish_time' => ['required', 'date_format:H:i', 'after:rev_start_time'],
            'breaks.*.rev_start_time' => ['nullable', 'date_format:H:i'],
            'breaks.*.rev_end_time' => ['nullable', 'date_format:H:i'],
            'remarks' => ['required'],
        ];
    }

    //時間の比較 (ワイルとカード対応)
    public function withValidator($validator){
        $validator->after( function($validator){
            $attendanceStart = $this->input('rev_start_time');
            $attendanceEnd   = $this->input('rev_finish_time');
            $breaks = $this->input('breaks',[]);

            foreach($breaks as $index => $break){
                $start = $break['rev_start_time'] ?? null;
                $end = $break['rev_end_time'] ?? null;

                //休憩開始<終了チェック
                if($start && $end && $start >= $end){
                    $validator->errors()->add(
                        "breaks.$index.rev_end_time",
                        "休憩時間が不適切な値です"
                    );
                }
                //出勤<休憩開始<退勤チェック
                if ($start && $attendanceStart && $attendanceEnd && ($start < $attendanceStart || $start > $attendanceEnd)) {
                    $validator->errors()->add(
                        "breaks.$index.rev_start_time",
                        "休憩時間が不適切な値です"
                    );
                }
                //休憩終了<退勤チェック
                if ($end && $attendanceEnd && $end > $attendanceEnd) {
                    $validator->errors()->add(
                        "breaks.$index.rev_end_time",
                        "休憩時間もしくは退勤時間が不適切な値です"
                    );
                }

                //休憩時間の開始or終了のどちらかが入っていないとき
                if (($start && !$end) || (!$start && $end)){
                     $validator->errors()->add(
                        "breaks.$index.rev_start_time",
                        "休憩時間が不適切な値です"
                    );
                }
            }
        });
    }

    public function messages(){
        return[
            'rev_start_time.required' => '出勤時間を入力してください',
            'rev_start_time.date_format' => '出勤時間は"00:00"の時間形式で入力してください',
            'rev_finish_time.required' => '退勤時間を入力してください',
            'rev_finish_time.date_format' => '退勤時間は"00:00"の時間形式で入力してください',
            'rev_finish_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'remarks.required' => '備考欄を記入してください',
        ];
    }

}
