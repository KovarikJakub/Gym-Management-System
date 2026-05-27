<?php
require_once __DIR__ . '/../database.php';

// Fetches all upcoming sessions with class details and real-time availability.
function getAllSessions($user_id = null) {
    $db = getDbConnection();

    $sql = "SELECT 
                s.id AS session_id,
                c.name AS class_name,
                c.type AS class_type,
                u.real_name AS trainer_name,
                s.start_time,
                s.end_time,
                c.default_capacity,
                -- Subquery to count currently enrolled members
                (SELECT COUNT(*) FROM enrollments e 
                 WHERE e.session_id = s.id) AS current_enrollment,
                -- Subquery to check if current user is enrolled
                (SELECT COUNT(*) FROM enrollments e 
                 WHERE e.session_id = s.id AND e.member_id = :user_id) > 0 AS is_enrolled
            FROM class_sessions s
            JOIN gym_classes c ON s.class_id = c.id
            JOIN users u ON s.trainer_id = u.id
            ORDER BY s.start_time ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    
    $sessions = $stmt->fetchAll();

    // Add a calculated 'spots_left' field to each session object
    foreach ($sessions as &$session)
    {
        $session['spots_left'] = $session['default_capacity'] - $session['current_enrollment'];
        $session['is_full'] = ($session['spots_left'] <= 0);
    }

    return $sessions;
}

function getFilteredSessions($date, $user_id,  $filters = [])
{
    $db = getDbConnection();
    
    // Base query joining the necessary tables
    $sql = "SELECT 
                s.id AS session_id,
                c.name AS class_name,
                c.type AS class_type,
                c.id AS gym_class_id,
                u.real_name AS trainer_name,
                u.username AS trainer_username,
                u.profile_photo AS trainer_profile_photo,
                u.id AS trainer_id,
                s.start_time,
                s.end_time,
                c.default_capacity,
                -- Subquery to count currently enrolled members
                (SELECT COUNT(*) FROM enrollments e WHERE e.session_id = s.id) AS current_enrollment,
                -- Subquery to check if current user is enrolled
                (SELECT COUNT(*) FROM enrollments e 
                 WHERE e.session_id = s.id AND e.member_id = :user_id) > 0 AS is_enrolled
            FROM class_sessions s
            JOIN gym_classes c ON s.class_id = c.id
            JOIN users u ON s.trainer_id = u.id
            WHERE DATE(s.start_time) = :date"; // Base condition

    $params = [
        ':date' => $date,
        ':user_id' => $user_id
    ];

    // Dynamically append filters

    // if type is provided, append condition for type
    if (!empty($filters['type']))
    {
        $sql .= " AND c.type = :type";
        $params[':type'] = $filters['type'];
    }
    
    // if type is trainer, append condition for trainer
    if (!empty($filters['trainer_id']))
    {
        $sql .= " AND s.trainer_id = :trainer_id";
        $params[':trainer_id'] = $filters['trainer_id'];
    }

    // we want them in ascending order:
    $sql .= " ORDER BY s.start_time ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $sessions = $stmt->fetchAll();
    
    // Calculate spots left
    foreach ($sessions as &$session)
    {
        $session['spots_left'] = $session['default_capacity'] - $session['current_enrollment'];
        $session['is_full'] = ($session['spots_left'] <= 0);
    }

    return $sessions;
}

function enrollUserToSession($user_id, $session_id)
{
    # create m:n relationship between a user and chosen session
    $db = getDbConnection();

    // check if target session still has capcity:    
    $sql = "SELECT c.default_capacity, 
            (SELECT COUNT(*) FROM enrollments WHERE session_id = s.id) as current_enrollment
            FROM class_sessions s
            JOIN gym_classes c ON s.class_id = c.id
            WHERE s.id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$session_id]);
    $session = $stmt->fetch();

    if (!$session || ($session['default_capacity'] - $session['current_enrollment']) <= 0)
    {
        throw new PDOException("No spots left in this session.");
    }
    

    $sql = "INSERT INTO enrollments (member_id, session_id, enrolled_at) 
            VALUES (:member_id, :session_id, :enrolled_at)";

    $params = [
        ':member_id' => $user_id,
        ':session_id' => $session_id,
        ':enrolled_at' => date('Y-m-d H:i:s')
    ];
    
    $stmt = $db->prepare($sql);

    return $stmt->execute($params);
}

function cancelEnrollment($user_id, $session_id)
{
    $db = getDbConnection();

    $sql = "DELETE FROM enrollments WHERE member_id = :member_id AND session_id = :session_id";
    $params = [ 
        ":member_id" => $user_id,
        ":session_id" => $session_id
    ];
    $stmt = $db->prepare($sql);

    // return true on success of the query(note: true doesnt mean rows were affected!)
    return $stmt->execute($params); 
}

function getUsersPastSessions($user_id, $n)
{
    $db = getDbConnection();

    $sql = "SELECT 
                s.id AS session_id,
                c.name AS class_name,
                c.type AS class_type,
                u.real_name AS trainer_name,
                s.start_time,
                s.end_time,
                (SELECT COUNT(*) FROM reviews r WHERE r.session_id = s.id AND r.member_id = :user_id) > 0 AS is_reviewed
            FROM enrollments e
            JOIN class_sessions s ON e.session_id = s.id
            JOIN gym_classes c ON s.class_id = c.id
            JOIN users u ON s.trainer_id = u.id
            WHERE e.member_id = :user_id 
              AND s.end_time < CURRENT_TIMESTAMP
            ORDER BY s.start_time DESC
            LIMIT :limit";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $n, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}