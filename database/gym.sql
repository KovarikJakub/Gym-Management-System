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
    posted_date DATETIME NOT NULL,
    comment TEXT NOT NULL,
    FOREIGN KEY (session_id) REFERENCES class_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES users(id) ON DELETE CASCADE
);

/**********************************************/
/*               SEEDING DATA
/**********************************************/

-- Seed data for users
-- Note: all passwords are "1234" stored as a hash
-- Admin
INSERT INTO users (id, real_name, username, password_hash, email, role, is_deactivated) VALUES
(1, 'James Smith', 'admin', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'admin@gym.com', 'admin', 0);

-- Trainers
INSERT INTO users (id, real_name, username, password_hash, email, role, profile_photo, is_deactivated) VALUES
(2, 'Sarah Johnson', 'trainer1', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 't1@gym.com', 'trainer', 'pp_1780006449_af60e5f135e13e1c.png', 0),
(3, 'Michael Chen', 'trainer2', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 't2@gym.com', 'trainer', 'pp_1780006484_f04aed6b2e5840ed.png', 0),
(4, 'Elena Rodriguez', 'trainer3', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 't3@gym.com', 'trainer', 'pp_1780006530_70a870cc2e091b7e.png', 0);

-- Members
INSERT INTO users (id, real_name, username, password_hash, email, role, is_deactivated) VALUES
(5, 'David Brown', 'member1', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm1@gym.com', 'member', 0),
(6, 'Emily Davis', 'member2', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm2@gym.com', 'member', 0),
(7, 'Chris Wilson', 'member3', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm3@gym.com', 'member', 0),
(8, 'Jessica Taylor', 'member4', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm4@gym.com', 'member', 0),
(9, 'Daniel Moore', 'member5', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm5@gym.com', 'member', 0),
(10, 'Sophia Anderson', 'member6', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm6@gym.com', 'member', 0),
(11, 'Matthew Thomas', 'member7', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm7@gym.com', 'member', 0),
(12, 'Olivia Jackson', 'member8', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm8@gym.com', 'member', 0),
(13, 'Andrew White', 'member9', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm9@gym.com', 'member', 0),
(14, 'Isabella Harris', 'member10', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm10@gym.com', 'member', 0),
(15, 'Joshua Martin', 'member11', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm11@gym.com', 'member', 0),
(16, 'Mia Thompson', 'member12', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm12@gym.com', 'member', 0),
(17, 'Ryan Garcia', 'member13', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm13@gym.com', 'member', 0),
(18, 'Charlotte Martinez', 'member14', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm14@gym.com', 'member', 0),
(19, 'Nathan Robinson', 'member15', '$2y$10$8i79MncYrZ02pFA5666uh.U7YArFnH1qYwWMyu.EZEFSzul5BT4uC', 'm15@gym.com', 'member', 0);

-- Seed data for trainer profiles
INSERT INTO trainer_profiles (user_id, bio, specializations, certifications) VALUES
(2, 'Focused on strength fundamentals and accessible movement for all levels.', 'Strength training, Yoga', 'Certified Personal Trainer, Yoga Foundations Certificate'),
(3, 'Pilates and martial arts coach with a focus on technique and injury prevention.', 'Pilates, Martial arts', 'Pilates Mat Certification, Muay Thai Coaching Certificate'),
(4, 'High-energy cardio and conditioning trainer for members building endurance.', 'Cardio, HIIT, Conditioning', 'Group Fitness Instructor, HIIT Specialist');

-- Seed data for gym_classes
INSERT INTO gym_classes (id, name, difficulty, description, default_capacity, type) VALUES
(1, 'Vinyasa Flow', 3, 'A dynamic yoga practice connecting breath with movement.', 8, 'yoga'),
(2, 'Mat Pilates Core', 4, 'Focus on core strength, flexibility, and alignment.', 5, 'pilates'),
(3, 'HIIT Blast', 5, 'High intensity interval training to boost metabolism.', 10, 'cardio'),
(4, 'Full Body Strength', 3, 'Weightlifting and resistance training for all muscle groups.', 6, 'workout'),
(5, 'Muay Thai Basics', 4, 'Introduction to the art of eight limbs.', 4, 'martial arts'),
(6, 'Yin Yoga', 2, 'Slow-paced style of yoga with postures held for longer periods.', 5, 'yoga'),
(7, 'Power Pilates', 4, 'Intense pilates session focusing on strength and stamina.', 7, 'pilates'),
(8, 'Spin Cycle', 5, 'High-energy indoor cycling workout.', 10, 'cardio'),
(9, 'Kettlebell Power', 4, 'Functional strength training using kettlebells.', 6, 'workout'),
(10, 'Brazilian Jiu-Jitsu', 5, 'Grappling-based martial art and combat sport.', 4, 'martial arts');

-- Seed data for class_sessions (June 1st - June 7th, 2026)
INSERT INTO class_sessions (id, class_id, trainer_id, start_time, end_time) VALUES
(1, 1, 2, '2026-06-01 08:00:00', '2026-06-01 09:00:00'),
(2, 3, 4, '2026-06-01 18:00:00', '2026-06-01 19:00:00'),
(3, 2, 3, '2026-06-02 10:00:00', '2026-06-02 11:00:00'),
(4, 5, 3, '2026-06-02 19:00:00', '2026-06-02 20:30:00'),
(5, 4, 2, '2026-06-03 07:00:00', '2026-06-03 08:00:00'),
(6, 8, 4, '2026-06-03 17:00:00', '2026-06-03 18:00:00'),
(7, 6, 2, '2026-06-04 09:00:00', '2026-06-04 10:00:00'),
(8, 10, 3, '2026-06-04 20:00:00', '2026-06-04 21:30:00'),
(9, 7, 3, '2026-06-05 11:00:00', '2026-06-05 12:00:00'),
(10, 9, 4, '2026-06-05 18:30:00', '2026-06-05 19:30:00'),
(11, 1, 4, '2026-06-06 10:00:00', '2026-06-06 11:00:00'),
(12, 3, 2, '2026-06-06 16:00:00', '2026-06-06 17:00:00'),
(13, 2, 3, '2026-06-07 10:00:00', '2026-06-07 11:00:00'),
(14, 5, 3, '2026-06-07 15:00:00', '2026-06-07 16:30:00');

-- Historical Sessions (May 2026)
INSERT INTO class_sessions (id, class_id, trainer_id, start_time, end_time) VALUES
(101, 1, 2, '2026-05-11 08:00:00', '2026-05-11 09:00:00'),
(102, 3, 4, '2026-05-11 18:00:00', '2026-05-11 19:00:00'),
(103, 2, 3, '2026-05-12 10:00:00', '2026-05-12 11:00:00'),
(104, 4, 2, '2026-05-13 07:00:00', '2026-05-13 08:00:00'),
(105, 5, 3, '2026-05-13 19:00:00', '2026-05-13 20:30:00'),
(106, 1, 4, '2026-05-14 09:00:00', '2026-05-14 10:00:00'),
(107, 8, 4, '2026-05-15 17:00:00', '2026-05-15 18:00:00'),
(108, 6, 2, '2026-05-16 09:00:00', '2026-05-16 10:00:00'),
(109, 10, 3, '2026-05-16 20:00:00', '2026-05-16 21:30:00'),
(110, 9, 4, '2026-05-17 18:30:00', '2026-05-17 19:30:00'),
(111, 1, 2, '2026-05-18 08:00:00', '2026-05-18 09:00:00'),
(112, 3, 4, '2026-05-18 18:00:00', '2026-05-18 19:00:00'),
(113, 2, 3, '2026-05-19 10:00:00', '2026-05-19 11:00:00'),
(114, 4, 2, '2026-05-20 07:00:00', '2026-05-20 08:00:00'),
(115, 5, 3, '2026-05-20 19:00:00', '2026-05-20 20:30:00');

-- Enrollments (Dates before June 1st, 2026)
INSERT INTO enrollments (member_id, session_id, enrolled_at) VALUES
-- Session 4: Muay Thai Basics (Trainer 3, Cap 4) - FULLY BOOKED
(5, 4, '2026-05-20 10:00:00'),
(6, 4, '2026-05-21 09:30:00'),
(7, 4, '2026-05-22 14:00:00'),
(8, 4, '2026-05-23 08:00:00'),

-- Session 2: HIIT Blast (Trainer 4, Cap 10)
(9, 2, '2026-05-24 11:00:00'),
(10, 2, '2026-05-24 15:45:00'),
(11, 2, '2026-05-25 08:00:00'),
(12, 2, '2026-05-25 12:00:00'),
(13, 2, '2026-05-25 12:05:00'),

-- Session 1: Vinyasa Flow (Trainer 2, Cap 8)
(14, 1, '2026-05-26 12:10:00'),
(15, 1, '2026-05-26 12:15:00'),
(16, 1, '2026-05-27 17:00:00'),
(17, 1, '2026-05-27 17:30:00'),
(18, 1, '2026-05-28 09:00:00'),
(19, 1, '2026-05-28 09:15:00'),

-- Past Enrollments for Member 1 (David Brown, id=5)
-- 1 current enrollment in future session 1 (from previous seed) + 1 in future session 4 (booked above).
(5, 101, '2026-05-01 10:00:00'),
(5, 102, '2026-05-01 11:00:00'),
(5, 103, '2026-05-02 08:00:00'),
(5, 104, '2026-05-03 09:00:00'),
(5, 105, '2026-05-03 10:00:00'),
(5, 106, '2026-05-04 11:00:00'),
(5, 107, '2026-05-05 12:00:00'),
(5, 108, '2026-05-06 13:00:00'),
(5, 109, '2026-05-07 14:00:00'),
(5, 110, '2026-05-08 15:00:00'),
(5, 111, '2026-05-09 16:00:00'),
(5, 112, '2026-05-10 17:00:00'),
(5, 113, '2026-05-11 08:00:00'),
(5, 114, '2026-05-12 09:00:00'),
(5, 115, '2026-05-13 10:00:00'),

-- Past Enrollments for other members (1-3 each)
(6, 101, '2026-05-01 10:30:00'),
(6, 105, '2026-05-03 10:30:00'),
(7, 102, '2026-05-01 11:30:00'),
(8, 103, '2026-05-02 08:30:00'),
(8, 107, '2026-05-05 12:30:00'),
(8, 110, '2026-05-08 15:30:00'),
(9, 104, '2026-05-03 09:30:00'),

-- Random others for future sessions (June)
(5, 1, '2026-05-28 10:00:00'),
(6, 2, '2026-05-28 10:30:00'),
(7, 3, '2026-05-29 08:30:00'),
(8, 5, '2026-05-29 09:00:00'),
(9, 6, '2026-05-29 10:00:00'),
(10, 7, '2026-05-30 11:00:00'),
(11, 8, '2026-05-30 12:00:00'),
(12, 9, '2026-05-30 13:00:00'),
(13, 10, '2026-05-30 14:00:00'),
(14, 11, '2026-05-30 15:00:00'),
(15, 12, '2026-05-30 16:00:00'),
(16, 13, '2026-05-30 17:00:00'),
(17, 14, '2026-05-30 18:00:00');

-- Reviews (Ensuring at most 1 review per user per session)
INSERT INTO reviews (session_id, member_id, rating, posted_date, comment) VALUES
(1, 5, 5, '2026-06-01 10:00:00', 'Amazing start to the week!'),
(1, 14, 4, '2026-06-01 11:30:00', 'Great pace, really enjoyed it.'),
(2, 9, 5, '2026-06-01 20:00:00', 'Insane energy! Exactly what I needed.'),
(2, 10, 5, '2026-06-01 20:15:00', 'Hardest 60 minutes of my week, but the best.'),
(4, 5, 5, '2026-06-02 21:00:00', 'Loved the technique focus. Authentic feel.'),
(4, 6, 5, '2026-06-02 21:15:00', 'Great cardio and skill building. Highly addictive!'),
(5, 8, 4, '2026-06-03 09:00:00', 'Feeling much stronger after this session.'),
(6, 9, 5, '2026-06-03 19:00:00', 'Best spin class ever.'),
(7, 10, 4, '2026-06-04 11:00:00', 'Very relaxing and mindful.'),
(8, 11, 5, '2026-06-04 22:00:00', 'BJJ is tough but rewarding.'),
(9, 12, 4, '2026-06-05 13:00:00', 'Great core workout.'),
(10, 13, 5, '2026-06-05 20:30:00', 'Kettlebells are no joke!'),
(11, 14, 5, '2026-06-06 12:00:00', 'Wonderful Saturday morning yoga.'),
(12, 15, 4, '2026-06-06 18:00:00', 'High intensity, high reward.'),
(13, 16, 5, '2026-06-07 12:00:00', 'Perfect Sunday pilates.'),
(14, 17, 5, '2026-06-07 17:00:00', 'Muay Thai is definitely my new favorite.');

-- Equipment (Main training area items)
INSERT INTO equipment (name, status) VALUES
('Treadmill 1', 'available'),
('Treadmill 2', 'in use'),
('Power Rack 1', 'available'),
('Power Rack 2', 'available'),
('Bench Press 1', 'in use'),
('Dumbbell Set 1-10kg', 'available'),
('Dumbbell Set 12-30kg', 'available'),
('Leg Press Machine', 'maintenance'),
('Stationary Bike 1', 'available'),
('Stationary Bike 2', 'maintenance');
