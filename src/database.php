<?php

# db connection is a singleton
function getDbConnection() {
    static $db = null;

    if ($db === null)
    {
        try
        {
            $db = new PDO('sqlite:' . __DIR__ . '/../database/gym.db');
            
            //Set error mode to exceptions for debugging
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Set default fetch mode to associative arrays (easier for JSON/Ajax)
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Enable Foreign Keys in SQLite (disabled by default)
            $db->exec('PRAGMA foreign_keys = ON;');
        }
        catch (PDOException $e)
        {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    return $db;
}