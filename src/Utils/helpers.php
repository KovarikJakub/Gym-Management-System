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