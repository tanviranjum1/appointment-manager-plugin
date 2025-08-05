-- =================================================================
--  PHASE 1: CREATE USERS (2 Approvers, 2 Requesters)
--  All users will have the password: password
-- =================================================================

-- == Approver 1: Dr. Eleanor Vance (ID: 101) ==
INSERT INTO `wp_users` (`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `user_registered`, `display_name`) VALUES
(101, 'evance', '$P$BqAVN4yCjgbqG8jZUWSCQ5s2pD.3b1/', 'evance', 'eleanor.vance@example.com', '2025-08-05 08:00:00', 'Dr. Eleanor Vance');

INSERT INTO `wp_usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES
(101, 'nickname', 'evance'),
(101, 'first_name', 'Eleanor'),
(101, 'last_name', 'Vance'),
(101, 'wp_capabilities', 'a:1:{s:12:"tan_approver";b:1;}'),
(101, 'tan_status', 'active');

-- == Approver 2: Dr. Theodora (ID: 102) ==
INSERT INTO `wp_users` (`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `user_registered`, `display_name`) VALUES
(102, 'theo', '$P$BqAVN4yCjgbqG8jZUWSCQ5s2pD.3b1/', 'theo', 'theo@example.com', '2025-08-05 08:00:00', 'Dr. Theodora');

INSERT INTO `wp_usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES
(102, 'nickname', 'theo'),
(102, 'first_name', 'Theodora'),
(102, 'last_name', ''),
(102, 'wp_capabilities', 'a:1:{s:12:"tan_approver";b:1;}'),
(102, 'tan_status', 'active');

-- == Requester 1: Luke Sanderson (ID: 201) ==
INSERT INTO `wp_users` (`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `user_registered`, `display_name`) VALUES
(201, 'luke', '$P$BqAVN4yCjgbqG8jZUWSCQ5s2pD.3b1/', 'luke', 'luke.sanderson@example.com', '2025-08-05 08:00:00', 'Luke Sanderson');

INSERT INTO `wp_usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES
(201, 'nickname', 'luke'),
(201, 'first_name', 'Luke'),
(201, 'last_name', 'Sanderson'),
(201, 'wp_capabilities', 'a:1:{s:13:"tan_requester";b:1;}'),
(201, 'tan_status', 'active');

-- == Requester 2: Nell Vance (ID: 202) ==
INSERT INTO `wp_users` (`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `user_registered`, `display_name`) VALUES
(202, 'nell', '$P$BqAVN4yCjgbqG8jZUWSCQ5s2pD.3b1/', 'nell', 'nell.vance@example.com', '2025-08-05 08:00:00', 'Nell Vance');

INSERT INTO `wp_usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES
(202, 'nickname', 'nell'),
(202, 'first_name', 'Nell'),
(202, 'last_name', 'Vance'),
(202, 'wp_capabilities', 'a:1:{s:13:"tan_requester";b:1;}'),
(202, 'tan_status', 'active');


-- =================================================================
--  PHASE 2: CREATE AVAILABILITY SLOTS
--  Inserts into the CORRECT table: wp_am_availability
-- =================================================================

-- == Availability for Dr. Eleanor Vance (ID: 101) ==
INSERT INTO `wp_am_availability` (`approver_id`, `start_time`, `end_time`, `created_at`) VALUES
(101, '2025-08-11 10:00:00', '2025-08-11 11:00:00', '2025-08-05 08:00:00'),
(101, '2025-08-11 11:00:00', '2025-08-11 12:00:00', '2025-08-05 08:00:00'),
(101, '2025-08-12 14:00:00', '2025-08-12 15:00:00', '2025-08-05 08:00:00');

-- == Availability for Dr. Theodora (ID: 102) ==
INSERT INTO `wp_am_availability` (`approver_id`, `start_time`, `end_time`, `created_at`) VALUES
(102, '2025-08-11 09:00:00', '2025-08-11 10:00:00', '2025-08-05 08:00:00'),
(102, '2025-08-13 16:00:00', '2025-08-13 17:00:00', '2025-08-05 08:00:00');


-- =================================================================
--  PHASE 3: CREATE PENDING APPOINTMENT REQUESTS
--  Inserts into the CORRECT table: wp_am_appointments
-- =================================================================

-- == Luke (201) requests an appointment with Dr. Vance (101) ==
-- This matches the first availability slot for Dr. Vance
INSERT INTO `wp_am_appointments` (`approver_id`, `requester_id`, `start_time`, `end_time`, `status`, `created_at`) VALUES
(101, 201, '2025-08-11 10:00:00', '2025-08-11 11:00:00', 'pending', '2025-08-05 08:00:00');

-- == Nell (202) requests an appointment with Dr. Vance (101) ==
-- This matches the third availability slot for Dr. Vance
INSERT INTO `wp_am_appointments` (`approver_id`, `requester_id`, `start_time`, `end_time`, `status`, `created_at`) VALUES
(101, 202, '2025-08-12 14:00:00', '2025-08-12 15:00:00', 'pending', '2025-08-05 08:00:00');

-- == Luke (201) requests an appointment with Dr. Theodora (102) ==
-- This matches the first availability slot for Dr. Theodora
INSERT INTO `wp_am_appointments` (`approver_id`, `requester_id`, `start_time`, `end_time`, `status`, `created_at`) VALUES
(102, 201, '2025-08-11 09:00:00', '2025-08-11 10:00:00', 'pending', '2025-08-05 08:00:00');


UPDATE `wp_users` SET `user_pass` ='$wp$2y$10$6IgONybNP6xFHiDV.LK2We3HzgP1aIdJsytL64yf2mTsIV8RSZtoe' 