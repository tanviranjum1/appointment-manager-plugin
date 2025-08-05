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
    
    public static function get_by_approver_id( $approver_id, $filters = [], $page = 1, $per_page = 5 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'am_appointments';
        $users_table = $wpdb->prefix . 'users';
        
        // --- START OF THE FIX ---
        // This logic correctly builds the query and parameter arrays.
        $where_clauses = ['a.approver_id = %d'];
        $query_params = [$approver_id];

        if (!empty($filters['status'])) {
            $where_clauses[] = "a.status = %s";
            $query_params[] = $filters['status'];
        }

        $where_sql = "WHERE " . implode(' AND ', $where_clauses);
        // --- END OF THE FIX ---

        // Get total count for pagination using the exact same filters
        $total_items_query = "SELECT COUNT(a.id) FROM $table a $where_sql";
        $total_items = $wpdb->get_var( $wpdb->prepare($total_items_query, $query_params) );

        // Add pagination parameters to the main query
        $offset = ($page - 1) * $per_page;
        $query_params_paginated = array_merge($query_params, [$per_page, $offset]);

        $appointments_query = "SELECT a.*, u.display_name as requester_name FROM $table a
                               LEFT JOIN $users_table u ON a.requester_id = u.ID
                               $where_sql
                               ORDER BY a.start_time DESC
                               LIMIT %d OFFSET %d";
        
        $appointments = $wpdb->get_results( $wpdb->prepare($appointments_query, $query_params_paginated) );
        
        return [
            'appointments' => $appointments,
            'total_pages'  => ceil( $total_items / $per_page )
        ];
    }


     /**
     * Get appointments for a specific requester.
     *
     * @param int $requester_id
     * @return array
     */
    public static function get_by_requester_id( $requester_id, $filters = [], $page = 1, $per_page = 5 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'am_appointments';
        $users_table = $wpdb->prefix . 'users';
        
        // --- START OF THE FIX ---
        $where_clauses = ['a.requester_id = %d'];
        $query_params = [$requester_id];

        if (!empty($filters['status'])) {
            $where_clauses[] = "a.status = %s";
            $query_params[] = $filters['status'];
        }

        $where_sql = "WHERE " . implode(' AND ', $where_clauses);
        // --- END OF THE FIX ---

        // Get total count for pagination
        $total_items_query = "SELECT COUNT(a.id) FROM $table a $where_sql";
        $total_items = $wpdb->get_var( $wpdb->prepare($total_items_query, $query_params) );
        
        // Add pagination parameters to the main query
        $offset = ($page - 1) * $per_page;
        $query_params_paginated = array_merge($query_params, [$per_page, $offset]);

        $appointments_query = "SELECT a.*, u.display_name as approver_name FROM $table a
                               LEFT JOIN $users_table u ON a.approver_id = u.ID
                               $where_sql
                               ORDER BY a.start_time DESC
                               LIMIT %d OFFSET %d";
        
        $appointments = $wpdb->get_results( $wpdb->prepare($appointments_query, $query_params_paginated) );

        return [
            'appointments' => $appointments,
            'total_pages'  => ceil( $total_items / $per_page )
        ];
    }


    /**
     * Helper function to execute paginated appointment queries.
     * This function is now corrected to properly handle query parameters.
     */
    private static function get_paged_appointments( $base_query, $query_params, $page = 1, $per_page = 5 ) {
        global $wpdb;
        
        // Get total count for pagination based on the same filters
        $total_query = preg_replace( '/SELECT a\.\*,.+? FROM/i', 'SELECT COUNT(a.id) FROM', $base_query );
        $total_items = $wpdb->get_var( $wpdb->prepare( $total_query, $query_params ) );

       // Get paginated results
        $offset = ($page - 1) * $per_page;
        $base_query .= " LIMIT %d OFFSET %d";
        $query_params[] = $per_page;
        $query_params[] = $offset;
        $appointments = $wpdb->get_results( $wpdb->prepare( $base_query, $query_params ) );

        return [
            'appointments' => $appointments,
            'total_pages'  => ceil($total_items / $per_page)
        ];
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