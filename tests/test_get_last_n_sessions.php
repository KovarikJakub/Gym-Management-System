<?php
require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/models/class_sessions.php';

function setupTestDb() {
    $db = new PDO('sqlite::memory:');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec('PRAGMA foreign_keys = ON;');

    // Load schema and seed data
    $sql = file_get_contents(__DIR__ . '/../database/gym.sql');
    
    $db->exec($sql);

    // Set the global connection for the model functions
    getDbConnection($db);
    
    return $db;
}

function test_getUsersPastSessions() {
    setupTestDb();

    echo "Running test_getUsersPastSessions...\n";

    // Member 5
    // Seed data: Session 6 (2026-05-03), Session 4 (2026-05-02), Session 2 (2026-05-01), Session 1 (2026-05-01)
    $user_id = 5;
    $n = 10;
    $today = "2026-06-02";
    
    $sessions = getUsersPastSessions($user_id, $today, $n);
    
    // In seed data, member 5 is enrolled in sessions 1, 2, 4, 6.
    // All are in the past for the seeded test calendar.
    
    assert(count($sessions) === 4, "Member 5 should have 4 past sessions");
    assert($sessions[0]['session_id'] == 6, "First result should be session ID 6");
    assert($sessions[1]['session_id'] == 4, "Second result should be session ID 4");
    echo "  - Test 1 passed: retrieved all past sessions in correct order.\n";

    // Test LIMIT
    $n = 1;
    $sessions = getUsersPastSessions($user_id, $today, $n);
    assert(count($sessions) === 1, "Should return only 1 session when n=1");
    assert($sessions[0]['session_id'] == 6, "Should return the most recent session (ID 6)");
    echo "  - Test 2 passed: LIMIT works correctly.\n";

    // Test review status
    // Seed data has reviews for member 5 on sessions 1, 2, 4, 6.
    assert($sessions[0]['is_reviewed'] == 1, "Session 6 should be marked as reviewed");
    echo "  - Test 3 passed: review status correctly identified.\n";

    echo "All tests for getUsersPastSessions passed!\n\n";
}

try {
    test_getUsersPastSessions();
} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
    exit(1);
}
