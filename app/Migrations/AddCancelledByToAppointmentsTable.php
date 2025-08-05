<?php

namespace App\Migrations;

class AddCancelledByToAppointmentsTable {
    
    public static function up() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_appointments';

        // Check if the column already exists to prevent errors on re-activation
        $column = $wpdb->get_results( $wpdb->prepare(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = %s AND column_name = 'cancelled_by_role'",
            $table_name
        ));

        if (empty($column)) {
           $wpdb->query("ALTER TABLE $table_name ADD cancelled_by_role VARCHAR(20) NULL DEFAULT NULL");
        }
    }
}