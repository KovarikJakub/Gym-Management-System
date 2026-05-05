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

    getDbConnection($db);
    
    return $db;
}

function test_getFilteredSessions() {
    setupTestDb();

    echo "Running test_getFilteredSessions...\n";

    // Test 1: Get sessions for 2026-05-01
    $date = '2026-05-01';
    $sessions = getFilteredSessions($date);
    
    assert(count($sessions) === 3, "Should have 3 sessions on 2026-05-01");
    echo "  - Test 1 passed: found 3 sessions.\n";

    // Test 2: Filter by type 'yoga'
    $sessions = getFilteredSessions($date, ['type' => 'yoga']);
    assert(count($sessions) === 1, "Should have 1 yoga session on 2026-05-01");
    assert($sessions[0]['class_type'] === 'yoga', "Session type should be yoga");
    echo "  - Test 2 passed: found 1 yoga session.\n";

    // Test 3: Filter by trainer_id 2
    $sessions = getFilteredSessions($date, ['trainer_id' => 2]);
    assert(count($sessions) === 1, "Trainer 2 should have 1 session on 2026-05-01");
    assert($sessions[0]['trainer_name'] === 'Trainer One', "Trainer name should be Trainer One");
    echo "  - Test 3 passed: found 1 session for Trainer One.\n";

    // Test 4: Check spots_left calculation
    // Session 1 (Vinyasa Flow) has capacity 20 and 3 enrollments in seed data
    $sessions = getFilteredSessions($date, ['type' => 'yoga']);
    $yogaSession = $sessions[0];
    assert($yogaSession['spots_left'] === 17, "Yoga session should have 17 spots left (20 - 3)");
    echo "  - Test 4 passed: spots_left correctly calculated (17).\n";

    echo "All tests for getFilteredSessions passed!\n\n";
}

function test_enrollUserToSession(){
    $db = setupTestDb();

    echo "Running test_enrollUserToSession...\n";

    $member_id = 6;
    $session_id = 2;
    $result = enrollUserToSession($member_id, $session_id);

    assert($result === true, "Enrollment insert should succeed");

    // query enrollments and check if there is a new one:
    $stmt = $db->prepare(
        "SELECT * FROM enrollments 
        WHERE member_id = :member_id AND session_id = :session_id"
    );
    $stmt->execute(array($member_id, $session_id));
    $enrollments = $stmt->fetchAll();
    
    assert(count($enrollments) === 1, "There should be 1 new enrollment with the corresponding member and session id");
    echo "  - Test 1 passed: user enrolled in session.\n";

    echo "All tests for enrollUserToSession passed!\n\n";
}

try {
    test_getFilteredSessions();
    test_enrollUserToSession();
} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
    exit(1);
}
