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

/**
 * Returns the number of days since a given date compared to current date.
 * 
 * @param string $dateString The target date string.
 * @return int The number of days elapsed.
 */
function formatTimeSinceDate(string $dateString): int
{
    $date = new DateTime($dateString);
    $now = new DateTime();
    $diff = $now->diff($date);
    return abs($diff->days);
}

/**
 * Formats session start and end times into a readable array.
 * Example: ['date' => '6th of July 2026', 'time' => '19:00 - 20:00']
 * 
 * @param string $start Start time timestamp.
 * @param string $end End time timestamp.
 * @return array Formatted parts.
 */
function formatSessionTime(string $start, string $end): array
{
    $startTime = strtotime($start);
    $endTime = strtotime($end);
    
    $datePart = date('jS \of F Y', $startTime);
    $timePart = date('G:i', $startTime) . ' - ' . date('G:i', $endTime);
    
    return [
        'date' => $datePart,
        'time' => $timePart
    ];
}