<?php

namespace App\Models;

class Availability {

    /**
     * Get all availability slots for a specific approver.
     *
     * @param int $approver_id The ID of the approver.
     * @return array
     */
    public static function get_by_approver_id( $approver_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_availability';
        
        return $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM $table_name WHERE approver_id = %d ORDER BY start_time ASC", $approver_id )
        );
    }

    /**
     * Create a new availability slot.
     *
     * @param array $data The data to insert.
     * @return int|false The ID of the new row or false on error.
     */
    public static function create( $data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_availability';

        $wpdb->insert( $table_name, $data );
        return $wpdb->insert_id;
    }
}