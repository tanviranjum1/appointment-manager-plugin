-- =================================================================
--  STEP 1: CLEAN UP PREVIOUS DEMO DATA
-- =================================================================
-- Delete specific users created by this script (IDs >= 1001)
DELETE FROM `wp_users` WHERE `ID` >= 1001;
DELETE FROM `wp_usermeta` WHERE `user_id` >= 1001;
-- Clear custom tables for a fresh start
TRUNCATE TABLE `wp_am_appointments`;
TRUNCATE TABLE `wp_am_availability`;

-- =================================================================
--  STEP 2: CREATE NEW USERS
--  Password for all users is: password
-- =================================================================
-- CONTEXT: Hospital
INSERT INTO `wp_users` (`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `display_name`) VALUES (1001, 'dreed', '$P$BqAVN4yCjgbqG8jZUWSCQ5s2pD.3b1/', 'dreed', 'e.reed@example.com', 'Dr. Evelyn Reed');
INSERT INTO `wp_usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES
(1001, 'wp_capabilities', 'a:1:{s:12:"tan_approver";b:1;}'), (1001, 'tan_status', 'active'), (1001, 'tan_context', 'Hospital'),
(1001, 'tan_designation', 'Cardiologist'), (1001, 'tan_institute', 'City General Hospital');

INSERT INTO `wp_users` (`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `display_name`) VALUES (2001, 'jsmith', '$P$BqAVN4yCjgbqG8jZUWSCQ5s2pD.3b1/', 'jsmith', 'j.smith@example.com', 'John Smith');
INSERT INTO `wp_usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES
(2001, 'wp_capabilities', 'a:1:{s:13:"tan_requester";b:1;}'), (2001, 'tan_status', 'active'), (2001, 'tan_context', 'Hospital');

-- CONTEXT: School
INSERT INTO `wp_users` (`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `display_name`) VALUES (1002, 'agrant', '$P$BqAVN4yCjgbqG8jZUWSCQ5s2pD.3b1/', 'agrant', 'a.grant@example.com', 'Prof. Alan Grant');
INSERT INTO `wp_usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES
(1002, 'wp_capabilities', 'a:1:{s:12:"tan_approver";b:1;}'), (1002, 'tan_status', 'active'), (1002, 'tan_context', 'School'),
(1002, 'tan_designation', 'Paleontology Dept. Head'), (1002, 'tan_institute', 'State University');

INSERT INTO `wp_users` (`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `display_name`) VALUES (2002, 'sharding', '$P$BqAVN4yCjgbqG8jZUWSCQ5s2pD.3b1/', 'sharding', 's.harding@example.com', 'Sarah Harding');
INSERT INTO `wp_usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES
(2002, 'wp_capabilities', 'a:1:{s:13:"tan_requester";b:1;}'), (2002, 'tan_status', 'active'), (2002, 'tan_context', 'School');

-- =================================================================
--  STEP 3: CREATE AVAILABILITY SLOTS
--  Uses dynamic dates based on when the script is run.
-- =================================================================
-- Availability for Dr. Evelyn Reed (ID: 1001)
INSERT INTO `wp_am_availability` (`approver_id`, `start_time`, `end_time`, `created_at`) VALUES
(1001, DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 09:00:00'), DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 10:00:00'), NOW()), -- For TOMORROW (tests < 24h rule)
(1001, DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 7 DAY), '%Y-%m-%d 11:00:00'), DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 7 DAY), '%Y-%m-%d 12:00:00'), NOW()), -- For NEXT WEEK (cancellable)
(1001, DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 8 DAY), '%Y-%m-%d 14:00:00'), DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 8 DAY), '%Y-%m-%d 15:00:00'), NOW()); -- For NEXT WEEK (will be 'approved')

-- Availability for Prof. Alan Grant (ID: 1002)
INSERT INTO `wp_am_availability` (`approver_id`, `start_time`, `end_time`, `created_at`) VALUES
(1002, DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 9 DAY), '%Y-%m-%d 10:00:00'), DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 9 DAY), '%Y-%m-%d 11:00:00'), NOW()),
(1002, DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 9 DAY), '%Y-%m-%d 11:00:00'), DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 9 DAY), '%Y-%m-%d 12:00:00'), NOW()),
(1002, DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 10 DAY), '%Y-%m-%d 15:00:00'), DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 10 DAY), '%Y-%m-%d 16:00:00'), NOW());

-- =================================================================
--  STEP 4: CREATE APPOINTMENTS FOR DEMONSTRATION
-- =================================================================
-- A PENDING appointment for NEXT WEEK (Cancellable by Requester)
INSERT INTO `wp_am_appointments` (`approver_id`, `requester_id`, `start_time`, `end_time`, `status`, `reason`, `created_at`) VALUES
(1001, 2001, DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 7 DAY), '%Y-%m-%d 11:00:00'), DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 7 DAY), '%Y-%m-%d 12:00:00'), 'pending', 'Follow-up consultation.', NOW());

-- A PENDING appointment for TOMORROW (NOT cancellable by Requester)
INSERT INTO `wp_am_appointments` (`approver_id`, `requester_id`, `start_time`, `end_time`, `status`, `reason`, `created_at`) VALUES
(1001, 2001, DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 09:00:00'), DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 10:00:00'), 'pending', 'Urgent - Review test results.', NOW());

-- An APPROVED appointment (Cancellable by Approver, but not Requester)
INSERT INTO `wp_am_appointments` (`approver_id`, `requester_id`, `start_time`, `end_time`, `status`, `reason`, `created_at`) VALUES
(1001, 2001, DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 8 DAY), '%Y-%m-%d 14:00:00'), DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 8 DAY), '%Y-%m-%d 15:00:00'), 'approved', 'Standard procedure discussion.', NOW());

-- A REJECTED appointment to show in the logs
INSERT INTO `wp_am_appointments` (`approver_id`, `requester_id`, `start_time`, `end_time`, `status`, `reason`, `created_at`) VALUES
(1002, 2002, DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 9 DAY), '%Y-%m-%d 10:00:00'), DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 9 DAY), '%Y-%m-%d 11:00:00'), 'rejected', 'Thesis proposal review.', NOW());

-- A CANCELLED appointment to show in the logs
INSERT INTO `wp_am_appointments` (`approver_id`, `requester_id`, `start_time`, `end_time`, `status`, `reason`, `cancelled_by_role`, `created_at`) VALUES
(1002, 2002, DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 10 DAY), '%Y-%m-%d 15:00:00'), DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 10 DAY), '%Y-%m-%d 16:00:00'), 'cancelled', 'Review of final paper.', 'tan_approver', NOW());


UPDATE wp_users
SET user_pass = '$wp$2y$10$6IgONybNP6xFHiDV.LK2We3HzgP1aIdJsytL64yf2mTsIV8RSZtoe'