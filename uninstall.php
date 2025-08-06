<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * This file is responsible for cleaning up all plugin data from the database,
 * including custom tables, roles, and options.
 *
 * @package Appointment_Manager
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// 1. Drop Custom Database Tables
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}am_appointments" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}am_availability" );

// 2. Remove Custom User Roles
remove_role( 'tan_approver' );
remove_role( 'tan_requester' );

// 3. Delete Custom Options
delete_option( 'tan_appointment_contexts' );