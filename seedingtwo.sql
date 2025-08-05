-- =================================================================
--  STEP 1: CLEAR EXISTING APPOINTMENT & AVAILABILITY DATA
--  This ensures a clean slate for testing.
-- =================================================================

TRUNCATE TABLE `wp_am_appointments`;
TRUNCATE TABLE `wp_am_availability`;


-- =================================================================
--  STEP 2: CREATE NEW AVAILABILITY SLOTS
-- =================================================================

-- == Availability for Dr. Eleanor Vance (ID: 101) ==
INSERT INTO `wp_am_availability` (`approver_id`, `start_time`, `end_time`, `created_at`) VALUES
(101, '2025-08-06 10:00:00', '2025-08-06 11:00:00', '2025-08-05 11:30:00'), -- TOMORROW (less than 24 hours)
(101, '2025-08-12 10:00:00', '2025-08-12 11:00:00', '2025-08-05 11:30:00'); -- NEXT WEEK (more than 24 hours)

-- == Availability for Dr. Theodora (ID: 102) ==
INSERT INTO `wp_am_availability` (`approver_id`, `start_time`, `end_time`, `created_at`) VALUES
(102, '2025-08-13 14:00:00', '2025-08-13 15:00:00', '2025-08-05 11:30:00'); -- NEXT WEEK (more than 24 hours)


-- =================================================================
--  STEP 3: CREATE NEW APPOINTMENTS WITH VARIOUS STATUSES & DATES
-- =================================================================

-- == APPOINTMENT 1: APPROVED (for next week) ==
-- Requester Luke (201) with Approver Dr. Theodora (102)
-- Approvers can cancel this. Requesters cannot.
INSERT INTO `wp_am_appointments` (`approver_id`, `requester_id`, `start_time`, `end_time`, `status`, `reason`, `created_at`) VALUES
(102, 201, '2025-08-13 14:00:00', '2025-08-13 15:00:00', 'approved', 'Yearly check-up.', '2025-08-05 11:35:00');

-- == APPOINTMENT 2: PENDING (for TOMORROW) ==
-- Requester Nell (202) with Approver Dr. Vance (101)
-- Requester cannot cancel this because it is less than 24 hours away.
INSERT INTO `wp_am_appointments` (`approver_id`, `requester_id`, `start_time`, `end_time`, `status`, `reason`, `created_at`) VALUES
(101, 202, '2025-08-06 10:00:00', '2025-08-06 11:00:00', 'pending', 'Urgent follow-up.', '2025-08-05 11:35:00');

-- == APPOINTMENT 3: PENDING (for NEXT WEEK) ==
-- Requester Luke (201) with Approver Dr. Vance (101)
-- Requester CAN cancel this because it is pending and more than 24 hours away.
INSERT INTO `wp_am_appointments` (`approver_id`, `requester_id`, `start_time`, `end_time`, `status`, `reason`, `created_at`) VALUES
(101, 201, '2025-08-12 10:00:00', '2025-08-12 11:00:00', 'pending', 'Review of test results.', '2025-08-05 11:35:00');