<?php

require_once __DIR__ . '/../database.php';

function getTrainerProfile($trainerId)
{
    $db = getDbConnection();

    $stmt = $db->prepare(
        "SELECT
            u.id AS trainer_id,
            u.real_name,
            u.username,
            u.email,
            u.profile_photo,
            tp.bio,
            tp.specializations,
            tp.certifications
        FROM users u
        LEFT JOIN trainer_profiles tp ON tp.user_id = u.id
        WHERE u.id = ? AND u.role = 'trainer'"
    );
    $stmt->execute([$trainerId]);

    return $stmt->fetch();
}

function getAllTrainerProfiles()
{
    $db = getDbConnection();

    $stmt = $db->prepare(
        "SELECT
            u.id AS trainer_id,
            u.real_name,
            u.username,
            u.email,
            u.profile_photo,
            tp.bio,
            tp.specializations,
            tp.certifications
        FROM users u
        LEFT JOIN trainer_profiles tp ON tp.user_id = u.id
        WHERE u.role = 'trainer' AND u.is_deactivated = 0
        ORDER BY u.real_name ASC"
    );
    $stmt->execute();

    return $stmt->fetchAll();
}

function getTrainerClasses($trainerId)
{
    $db = getDbConnection();

    $stmt = $db->prepare(
        "SELECT
            c.id AS class_id,
            c.name AS class_name,
            c.type AS class_type,
            COUNT(s.id) AS session_count
        FROM class_sessions s
        JOIN gym_classes c ON c.id = s.class_id
        WHERE s.trainer_id = ?
        GROUP BY c.id, c.name, c.type
        ORDER BY c.name ASC"
    );
    $stmt->execute([$trainerId]);

    return $stmt->fetchAll();
}

function upsertTrainerProfile($trainerId, $bio, $specializations, $certifications)
{
    $db = getDbConnection();

    $trainerCheck = $db->prepare("SELECT id FROM users WHERE id = ? AND role = 'trainer'");
    $trainerCheck->execute([$trainerId]);

    if (!$trainerCheck->fetch())
        throw new PDOException("Trainer not found.");

    $stmt = $db->prepare(
        "INSERT INTO trainer_profiles (user_id, bio, specializations, certifications)
        VALUES (:user_id, :bio, :specializations, :certifications)
        ON CONFLICT(user_id) DO UPDATE SET
            bio = excluded.bio,
            specializations = excluded.specializations,
            certifications = excluded.certifications"
    );

    return $stmt->execute([
        ':user_id' => $trainerId,
        ':bio' => $bio,
        ':specializations' => $specializations,
        ':certifications' => $certifications
    ]);
}

function getTrainerCertificates($trainerId)
{
    $db = getDbConnection();

    $stmt = $db->prepare(
        "SELECT
            id,
            trainer_id,
            title,
            original_name,
            file_name,
            file_path,
            mime_type,
            uploaded_at
        FROM trainer_certificates
        WHERE trainer_id = ?
        ORDER BY uploaded_at DESC, id DESC"
    );
    $stmt->execute([$trainerId]);

    return $stmt->fetchAll();
}

function getTrainerCertificateById($trainerId, $certificateId)
{
    $db = getDbConnection();

    $stmt = $db->prepare(
        "SELECT
            id,
            trainer_id,
            title,
            original_name,
            file_name,
            file_path,
            mime_type,
            uploaded_at
        FROM trainer_certificates
        WHERE id = ? AND trainer_id = ?"
    );
    $stmt->execute([$certificateId, $trainerId]);

    return $stmt->fetch();
}

function addTrainerCertificate($trainerId, $title, $originalName, $fileName, $filePath, $mimeType)
{
    $db = getDbConnection();

    $trainerCheck = $db->prepare("SELECT id FROM users WHERE id = ? AND role = 'trainer'");
    $trainerCheck->execute([$trainerId]);

    if (!$trainerCheck->fetch())
        throw new PDOException("Trainer not found.");

    $stmt = $db->prepare(
        "INSERT INTO trainer_certificates
            (trainer_id, title, original_name, file_name, file_path, mime_type)
        VALUES
            (:trainer_id, :title, :original_name, :file_name, :file_path, :mime_type)"
    );

    return $stmt->execute([
        ':trainer_id' => $trainerId,
        ':title' => $title,
        ':original_name' => $originalName,
        ':file_name' => $fileName,
        ':file_path' => $filePath,
        ':mime_type' => $mimeType
    ]);
}

function deleteTrainerCertificate($trainerId, $certificateId)
{
    $db = getDbConnection();

    $stmt = $db->prepare(
        "DELETE FROM trainer_certificates
        WHERE id = ? AND trainer_id = ?"
    );

    return $stmt->execute([$certificateId, $trainerId]);
}

function getTrainerSessions($trainerId)
{
    $db = getDbConnection();

    $stmt = $db->prepare(
        "SELECT
            s.id AS session_id,
            c.id AS class_id,
            c.name AS class_name,
            c.type AS class_type,
            c.difficulty,
            c.description,
            c.default_capacity,
            s.start_time,
            s.end_time,
            COUNT(e.member_id) AS current_enrollment
        FROM class_sessions s
        JOIN gym_classes c ON c.id = s.class_id
        LEFT JOIN enrollments e ON e.session_id = s.id
        WHERE s.trainer_id = :trainer_id
        GROUP BY s.id
        ORDER BY s.start_time ASC"
    );
    $stmt->execute([':trainer_id' => $trainerId]);

    $sessions = $stmt->fetchAll();

    foreach ($sessions as &$session)
    {
        $session['spots_left'] = $session['default_capacity'] - $session['current_enrollment'];
        $session['is_full'] = ($session['spots_left'] <= 0);
    }

    return $sessions;
}

function getEnrolledMembersForSession($sessionId, $trainerId)
{
    $db = getDbConnection();

    $stmt = $db->prepare(
        "SELECT
            u.id AS member_id,
            u.real_name,
            u.username,
            u.email,
            u.role,
            e.enrolled_at
        FROM enrollments e
        JOIN users u ON u.id = e.member_id
        JOIN class_sessions s ON s.id = e.session_id
        WHERE e.session_id = :session_id
            AND s.trainer_id = :trainer_id
            AND u.role = 'member'
        ORDER BY u.real_name ASC"
    );
    $stmt->execute([
        ':session_id' => $sessionId,
        ':trainer_id' => $trainerId
    ]);

    return $stmt->fetchAll();
}
