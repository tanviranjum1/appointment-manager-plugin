<?php

namespace App\Migrations;

class CreateAppointmentsTable {

    public static function up() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_appointments';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            approver_id bigint(20) UNSIGNED NOT NULL,
            requester_id bigint(20) UNSIGNED NOT NULL,
            start_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            end_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            status varchar(20) DEFAULT 'pending' NOT NULL,
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id),
            KEY approver_id (approver_id),
            KEY requester_id (requester_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }
}