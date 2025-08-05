<?php

namespace App\Migrations;

class AddReasonToAppointmentsTable {
    
    public static function up() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_appointments';

        // Check if the column already exists
        $column = $wpdb->get_results( $wpdb->prepare(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = %s AND column_name = 'reason'",
            $table_name
        ));

        if (empty($column)) {
           $wpdb->query("ALTER TABLE $table_name ADD reason TEXT NULL");
        }
    }
}