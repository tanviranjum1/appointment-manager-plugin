<?php

namespace App\Models;

class Appointment {

    /**
     * Get a single appointment by its ID.
     *
     * @param int $id The appointment ID.
     * @return object|null
     */
    public static function find( $id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_appointments';
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ) );
    }

    /**
     * Get all appointments with user names for the admin view.
     *
     * @return array
     */
    public static function get_all_with_user_details() {
        global $wpdb;
        $appointments_table = $wpdb->prefix . 'am_appointments';
        $users_table = $wpdb->prefix . 'users';

        return $wpdb->get_results(
            "SELECT 
                a.*, 
                approver.display_name as approver_name, 
                requester.display_name as requester_name 
            FROM $appointments_table a
            LEFT JOIN $users_table approver ON a.approver_id = approver.ID
            LEFT JOIN $users_table requester ON a.requester_id = requester.ID
            ORDER BY a.start_time DESC"
        );
    }

    /**
     * Get appointments for a specific approver.
     *
     * @param int $approver_id
     * @return array
     */
    public static function get_by_approver_id( $approver_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'am_appointments';
        $users_table = $wpdb->prefix . 'users';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT a.*, u.display_name as requester_name FROM $table a
             LEFT JOIN $users_table u ON a.requester_id = u.ID
             WHERE a.approver_id = %d ORDER BY a.start_time DESC",
            $approver_id
        ));
    }

    /**
     * Get appointments for a specific requester.
     *
     * @param int $requester_id
     * @return array
     */
    public static function get_by_requester_id( $requester_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'am_appointments';
        $users_table = $wpdb->prefix . 'users';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT a.*, u.display_name as approver_name FROM $table a
             LEFT JOIN $users_table u ON a.approver_id = u.ID
             WHERE a.requester_id = %d ORDER BY a.start_time DESC",
            $requester_id
        ));
    }

    /**
     * Get start times of active appointments for a specific approver.
     *
     * @param int $approver_id
     * @return array
     */
    public static function get_active_booked_slots_for_approver( $approver_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_appointments';
        return $wpdb->get_col( $wpdb->prepare(
            "SELECT start_time FROM $table_name WHERE approver_id = %d AND status IN ('pending', 'approved')",
            $approver_id
        ));
    }

    /**
     * Check if a specific slot is already booked.
     *
     * @param int $approver_id
     * @param string $start_time
     * @return int|null
     */
    public static function check_if_slot_is_booked( $approver_id, $start_time ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_appointments';
        return $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM $table_name WHERE approver_id = %d AND start_time = %s AND status IN ('pending', 'approved')",
            $approver_id, $start_time
        ));
    }

    /**
     * Create a new appointment.
     *
     * @param array $data
     * @return int|false
     */
    public static function create( $data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_appointments';
        $wpdb->insert( $table_name, $data );
        return $wpdb->insert_id;
    }

    /**
     * Update the status of an appointment.
     *
     * @param int $id
     * @param string $status
     * @return int|false
     */
    public static function update_status( $id, $status ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_appointments';
        return $wpdb->update( $table_name, ['status' => $status], ['id' => $id] );
    }

    /**
     * Cancel an appointment.
     *
     * @param int $id
     * @param string $cancelled_by_role
     * @return int|false
     */
    public static function cancel( $id, $cancelled_by_role ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_appointments';
        return $wpdb->update(
            $table_name,
            ['status' => 'cancelled', 'cancelled_by_role' => $cancelled_by_role],
            ['id' => $id]
        );
    }
}