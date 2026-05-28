<?php

require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/models/trainers.php';
require_once __DIR__ . '/../src/Utils/upload.php';

function setupTrainerTestDb()
{
    $db = new PDO('sqlite::memory:');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec('PRAGMA foreign_keys = ON;');

    $sql = file_get_contents(__DIR__ . '/../database/gym.sql');
    $db->exec($sql);

    getDbConnection($db);

    return $db;
}

function test_getTrainerProfile()
{
    setupTrainerTestDb();

    echo "Running test_getTrainerProfile...\n";

    $profile = getTrainerProfile(2);

    assert($profile !== false, "Trainer profile should exist for trainer 2");
    assert($profile['username'] === 'trainer1', "Profile should belong to trainer1");
    assert($profile['bio'] !== null, "Trainer profile should include a bio");

    $memberProfile = getTrainerProfile(5);
    assert($memberProfile === false, "Members should not be returned as trainer profiles");

    echo "  - Trainer profile lookup passed.\n";
}

function test_getAllTrainerProfiles()
{
    setupTrainerTestDb();

    echo "Running test_getAllTrainerProfiles...\n";

    $trainers = getAllTrainerProfiles();

    assert(count($trainers) === 3, "There should be 3 active trainers");
    assert($trainers[0]['real_name'] === 'Trainer One', "Trainers should be ordered alphabetically");

    echo "  - Trainer directory lookup passed.\n";
}

function test_upsertTrainerProfile()
{
    setupTrainerTestDb();

    echo "Running test_upsertTrainerProfile...\n";

    $result = upsertTrainerProfile(
        2,
        'Updated bio',
        'Strength, Mobility',
        'Updated Certificate'
    );

    assert($result === true, "Trainer profile update should succeed");

    $profile = getTrainerProfile(2);
    assert($profile['bio'] === 'Updated bio', "Trainer bio should be updated");
    assert($profile['specializations'] === 'Strength, Mobility', "Trainer specializations should be updated");
    assert($profile['certifications'] === 'Updated Certificate', "Trainer certifications should be updated");

    echo "  - Trainer profile upsert passed.\n";
}

function test_getTrainerSessions()
{
    setupTrainerTestDb();

    echo "Running test_getTrainerSessions...\n";

    $sessions = getTrainerSessions(2);

    assert(count($sessions) === 2, "Trainer 2 should have 2 sessions");
    assert($sessions[0]['class_name'] === 'Vinyasa Flow', "First trainer 2 session should be Vinyasa Flow");
    assert($sessions[0]['current_enrollment'] === 3, "First trainer 2 session should have 3 enrollments");
    assert($sessions[0]['spots_left'] === 17, "First trainer 2 session should have 17 spots left");

    echo "  - Trainer sessions lookup passed.\n";
}

function test_getTrainerClasses()
{
    setupTrainerTestDb();

    echo "Running test_getTrainerClasses...\n";

    $classes = getTrainerClasses(2);

    assert(count($classes) === 2, "Trainer 2 should teach 2 classes");
    assert($classes[0]['class_name'] === 'Full Body Strength' || $classes[0]['class_name'] === 'Vinyasa Flow', "Trainer classes should be returned");

    echo "  - Trainer classes lookup passed.\n";
}

function test_getEnrolledMembersForSession()
{
    setupTrainerTestDb();

    echo "Running test_getEnrolledMembersForSession...\n";

    $members = getEnrolledMembersForSession(1, 2);

    assert(count($members) === 3, "Trainer 2 should see 3 enrolled members for session 1");
    assert($members[0]['role'] === 'member', "Only member rows should be returned");

    $wrongTrainerMembers = getEnrolledMembersForSession(1, 3);
    assert(count($wrongTrainerMembers) === 0, "A trainer should not see members for another trainer's session");

    echo "  - Enrolled members lookup passed.\n";
}

function test_trainerCertificateLifecycle()
{
    $db = setupTrainerTestDb();

    echo "Running test_trainerCertificateLifecycle...\n";

    $result = addTrainerCertificate(
        2,
        'Yoga Certificate',
        'yoga_certificate.pdf',
        'cert_123.pdf',
        'uploads/certifications/cert_123.pdf',
        'application/pdf'
    );

    assert($result === true, "Certificate insert should succeed");

    $certificates = getTrainerCertificates(2);
    assert(count($certificates) === 1, "Trainer 2 should have one certificate");
    assert($certificates[0]['title'] === 'Yoga Certificate', "Certificate title should match");
    assert($certificates[0]['mime_type'] === 'application/pdf', "Certificate mime type should match");

    $certificate = getTrainerCertificateById(2, $certificates[0]['id']);
    assert($certificate !== false, "Certificate should be retrievable by id");

    $deleted = deleteTrainerCertificate(2, $certificates[0]['id']);
    assert($deleted === true, "Certificate delete should succeed");

    $remaining = getTrainerCertificates(2);
    assert(count($remaining) === 0, "Trainer 2 should have no certificates after delete");

    echo "  - Trainer certificate lifecycle passed.\n";
}

try {
    test_getTrainerProfile();
    test_getAllTrainerProfiles();
    test_upsertTrainerProfile();
    test_getTrainerSessions();
    test_getTrainerClasses();
    test_getEnrolledMembersForSession();
    test_trainerCertificateLifecycle();

    echo "All trainer model tests passed!\n";
} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
    exit(1);
}
