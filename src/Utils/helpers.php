<?php

/**
 * Checks if the given string is null or empty.
 * 
 * @param string $str The string variable to check.
 * @return bool True if the string is null or empty, false otherwise.
 */
function isNullOrEmptyString(string|null $str)
{
    return $str === null || trim($str) === '';
}

function removeFileIfExists($filepath)
{
    if (file_exists($filepath))
    {
        @unlink($filepath);
    }
}

/**
 * Calculates the dates for a week (Monday to Sunday) based on a reference date.
 * 
 * @param string $today The reference date in 'Y-m-d' format.
 * @return array An array of days, each with 'date', 'day_name', and 'day_num'.
 */
function getWeekDays(string $today): array
{
    $monday = date('Y-m-d', strtotime('monday this week', strtotime($today)));
    $week_days = [];
    for ($i = 0; $i < 7; $i++) {
        $timestamp = strtotime("$monday +$i days");
        $week_days[] = [
            'date' => date('Y-m-d', $timestamp),
            'day_name' => date('D', $timestamp),
            'day_num' => date('j', $timestamp)
        ];
    }
    return $week_days;
}