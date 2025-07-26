<?php
use Carbon\Carbon;

function formatJapaneseDate($date){
    $carbon=Carbon::parse($date);
    $week=['日','月','火','水','木','金','土'][$carbon->dayOfWeek];
    return $carbon->format('m/d')."($week)";
}

function formatTime($time){
    return Carbon::parse($time)->format('H:i');
}

function formatTotalTime($time){
    return Carbon::parse($time)->format('G:i');
}

function formatJapaneseYear($date){
    return Carbon::parse($date)->format('Y年');
}

function formatJapaneseDay($date){
    return Carbon::parse($date)->format('n月j日');
}