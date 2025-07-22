<?php
use Carbon\Carbon;

function formatJapaneseDate($date){
    $carbon=Carbon::parse($date);
    $week=['日','月','火','水','木','金','土'][$carbon->dayOfWeek];
    return $carbon->format('n/j')."($week)";
}

function formatTime($time){
    return Carbon::parse($time)->format('H:i');
}