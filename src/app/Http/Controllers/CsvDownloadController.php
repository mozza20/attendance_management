<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Symfony\Component\HttpFoundation\StreamedResponse;


class CsvDownloadController extends Controller
{
    public function downloadCsv($user_id){
        $userName=User::where('id',$user_id)->value('name');
        $attendances=Attendance::where('user_id',$user_id)->get();
        $csvHeader = ['日付', '出勤', '退勤', '休憩', '合計'];
        $csvData = $attendances->map(function($attendance){
            return[
                formatJapaneseDate($attendance->date),
                formatTime($attendance->start_time),
                formatTime($attendance->finish_time),
                gmdate('G:i', $attendance->breakTimes->sum('break_total')),
                formatTotalTime($attendance->work_total),
            ];
        })->toArray();

        $response = new StreamedResponse(function () use ($csvHeader, $csvData) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");  //文字化防止 UTF-8のBOMを書き込む
            fputcsv($handle, $csvHeader);

            foreach ($csvData as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, [
            //ダウンロードできる形式？
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$userName.'_attendances.csv"',
        ]);

        return $response;
    }
}
