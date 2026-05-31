<?php
require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/models/admin_analytics.php';

function setupAdminAnalyticsTestDb()
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

function test_getAdminAnalyticsSummary()
{
    setupAdminAnalyticsTestDb();

    echo "Running test_getAdminAnalyticsSummary...\n";

    $summary = getAdminAnalyticsSummary();

    assert((int)$summary['total_members'] === 15, "There should be 15 seeded members");
    assert((int)$summary['total_trainers'] === 3, "There should be 3 seeded trainers");
    assert((int)$summary['total_classes'] === 10, "There should be 10 seeded classes");
    assert((int)$summary['total_sessions'] === 29, "There should be 29 seeded sessions");
    assert((int)$summary['total_enrollments'] === 50, "There should be 50 seeded enrollments");

    echo "  - Summary counts passed.\n";
}

function test_getMostPopularClasses()
{
    setupAdminAnalyticsTestDb();

    echo "Running test_getMostPopularClasses...\n";

    $classes = getMostPopularClasses(3);

    assert(count($classes) === 3, "Should return the requested limit");
    assert((int)$classes[0]['enrollments_count'] >= (int)$classes[1]['enrollments_count'], "Classes should be ordered by enrollments");

    echo "  - Popular class ordering passed.\n";
}

function test_getTrainerAnalytics()
{
    setupAdminAnalyticsTestDb();

    echo "Running test_getTrainerAnalytics...\n";

    $trainers = getTrainerAnalytics(2);

    assert(count($trainers) === 2, "Should return the requested limit");
    assert((int)$trainers[0]['enrollments_count'] >= (int)$trainers[1]['enrollments_count'], "Trainers should be ordered by enrollments");

    echo "  - Trainer performance ordering passed.\n";
}

try {
    test_getAdminAnalyticsSummary();
    test_getMostPopularClasses();
    test_getTrainerAnalytics();

    echo "All admin analytics tests passed!\n";
} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
    exit(1);
}
