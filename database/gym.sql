
-- Enable foreign key support in SQLite
-- PRAGMA foreign_keys = ON;

-- Core User Table (Members, Trainers, and Admins)
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    real_name TEXT NOT NULL,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    role TEXT NOT NULL CHECK(role IN ('member', 'trainer', 'admin')),
    profile_photo TEXT NULL,
    is_deactivated BOOLEAN DEFAULT FALSE
);

-- Trainer Profiles (1-to-1 relationship with users where role = 'trainer')
DROP TABLE IF EXISTS trainer_profiles;
CREATE TABLE trainer_profiles (
    user_id INTEGER PRIMARY KEY,
    bio TEXT,
    specializations TEXT,
    certifications TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Trainer certificates (multiple uploaded files per trainer)
DROP TABLE IF EXISTS trainer_certificates;
CREATE TABLE trainer_certificates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    trainer_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    original_name TEXT NOT NULL,
    file_name TEXT NOT NULL,
    file_path TEXT NOT NULL,
    mime_type TEXT NOT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Gym Class Catalog
DROP TABLE IF EXISTS gym_classes;
CREATE TABLE gym_classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL, -- more specific than "type", for example  "box, mma, kickbox"
    difficulty INTEGER CHECK(difficulty > 0 AND difficulty <= 5), -- difficulty on a scale of 1-5
    description TEXT,
    default_capacity INTEGER NOT NULL,
    type TEXT NOT NULL CHECK(type IN ('yoga', 'pilates', 'cardio', 'workout', 'martial arts')) -- generic type of the class
);

-- Class Sessions (Specific instances of a Gym Class taught by a Trainer)
DROP TABLE IF EXISTS class_sessions;
CREATE TABLE class_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    trainer_id INTEGER NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    FOREIGN KEY (class_id) REFERENCES gym_classes(id) ON DELETE CASCADE,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Enrollments M : N relationship between Users Members and Class Sessions 
DROP TABLE IF EXISTS enrollments;
CREATE TABLE enrollments (
    member_id INTEGER NOT NULL,
    session_id INTEGER NOT NULL,
    enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (member_id, session_id),
    FOREIGN KEY (member_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES class_sessions(id) ON DELETE CASCADE
);

-- Equipment (Main training area items)
DROP TABLE IF EXISTS equipment;
CREATE TABLE equipment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    status TEXT NOT NULL
);

-- Reviews (Feedback from Members on Gym class)
DROP TABLE IF EXISTS reviews;
CREATE TABLE reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id INTEGER NOT NULL,
    member_id INTEGER NOT NULL,
    rating INTEGER NOT NULL CHECK(rating BETWEEN 1 AND 5),
    comment TEXT,
    FOREIGN KEY (session_id) REFERENCES class_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES users(id) ON DELETE CASCADE
);

/**********************************************/
/*               SEEDING DATA
/**********************************************/

-- Seed data for users
-- Note: all passwords are "1234" stored as a hash
INSERT INTO users (id, real_name, username, password_hash, email, role, is_deactivated) VALUES
(1, 'Admin User', 'admin', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'admin@gym.com', 'admin', 0),
(2, 'Trainer One', 'trainer1', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 't1@gym.com', 'trainer', 0),
(3, 'Trainer Two', 'trainer2', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 't2@gym.com', 'trainer', 0),
(4, 'Trainer Three', 'trainer3', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 't3@gym.com', 'trainer', 0),
(5, 'Member One', 'member1', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm1@gym.com', 'member', 0),
(6, 'Member Two', 'member2', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm2@gym.com', 'member', 0),
(7, 'Member Three', 'member3', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm3@gym.com', 'member', 0),
(8, 'Member Four', 'member4', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm4@gym.com', 'member', 0),
(9, 'Member Five', 'member5', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm5@gym.com', 'member', 0);

-- Seed data for trainer profiles
INSERT INTO trainer_profiles (user_id, bio, specializations, certifications) VALUES
(2, 'Focused on strength fundamentals and accessible movement for all levels.', 'Strength training, Yoga', 'Certified Personal Trainer, Yoga Foundations Certificate'),
(3, 'Pilates and martial arts coach with a focus on technique and injury prevention.', 'Pilates, Martial arts', 'Pilates Mat Certification, Muay Thai Coaching Certificate'),
(4, 'High-energy cardio and conditioning trainer for members building endurance.', 'Cardio, HIIT, Conditioning', 'Group Fitness Instructor, HIIT Specialist');

-- Seed data for gym_classes
INSERT INTO gym_classes (id, name, difficulty, description, default_capacity, type) VALUES
(1, 'Vinyasa Flow', 3, 'A dynamic yoga practice connecting breath with movement.', 20, 'yoga'),
(2, 'Mat Pilates Core', 4, 'Focus on core strength, flexibility, and alignment.', 15, 'pilates'),
(3, 'HIIT Blast', 5, 'High intensity interval training to boost metabolism.', 25, 'cardio'),
(4, 'Full Body Strength', 3, 'Weightlifting and resistance training for all muscle groups.', 15, 'workout'),
(5, 'Muay Thai Basics', 4, 'Introduction to the art of eight limbs.', 20, 'martial arts');

-- Seed data for class_sessions
INSERT INTO class_sessions (id, class_id, trainer_id, start_time, end_time) VALUES
(1, 1, 2, '2026-05-01 08:00:00', '2026-05-01 09:00:00'),
(2, 2, 3, '2026-05-01 10:00:00', '2026-05-01 11:00:00'),
(3, 3, 4, '2026-05-01 18:00:00', '2026-05-01 19:00:00'),
(4, 4, 2, '2026-05-02 07:00:00', '2026-05-02 08:00:00'),
(5, 5, 3, '2026-05-02 19:00:00', '2026-05-02 20:30:00'),
(6, 1, 4, '2026-05-03 09:00:00', '2026-05-03 10:00:00');

INSERT INTO enrollments (member_id, session_id, enrolled_at) VALUES
-- Session 1: Vinyasa Flow (Trainer 2)
(5, 1, '2026-04-10 10:00:00'),
(6, 1, '2026-04-11 09:30:00'),
(7, 1, '2026-04-12 14:00:00'),

-- Session 2: Mat Pilates Core (Trainer 3)
(8, 2, '2026-04-10 11:00:00'),
(9, 2, '2026-04-11 15:45:00'),
(5, 2, '2026-04-13 08:00:00'),

-- Session 3: HIIT Blast (Trainer 4)
(6, 3, '2026-04-14 12:00:00'),
(7, 3, '2026-04-14 12:05:00'),
(8, 3, '2026-04-14 12:10:00'),
(9, 3, '2026-04-14 12:15:00'),

-- Session 4: Full Body Strength (Trainer 2)
(5, 4, '2026-04-15 17:00:00'),
(8, 4, '2026-04-15 17:30:00'),

-- Session 5: Muay Thai Basics (Trainer 3)
(6, 5, '2026-04-16 09:00:00'),
(7, 5, '2026-04-16 09:15:00'),
(9, 5, '2026-04-16 10:00:00'),

-- Session 6: Vinyasa Flow (Trainer 4)
(5, 6, '2026-04-17 08:30:00');
