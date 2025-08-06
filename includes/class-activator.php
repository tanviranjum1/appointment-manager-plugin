<?php

namespace Includes;

// Autoload necessary classes
require_once plugin_dir_path( __FILE__ ) . '../app/Services/RoleService.php';
require_once plugin_dir_path( __FILE__ ) . '../app/Migrations/CreateAvailabilityTable.php';
require_once plugin_dir_path( __FILE__ ) . '../app/Migrations/CreateAppointmentsTable.php';
require_once plugin_dir_path( __FILE__ ) . '../app/Migrations/AddReasonToAppointmentsTable.php'; // Add this line
require_once plugin_dir_path( __FILE__ ) . '../app/Migrations/AddCancelledByToAppointmentsTable.php'; // Add this line


/**
 * Fired during plugin activation.
 * This class defines all code necessary to run during the plugin's activation.
 * @package Appointment_Manager
 */
class Activator {


     /**
     * Runs the activation sequence.
     * Creates custom roles, database tables, and runs migrations.
     *
     * @return void
     */
    public static function activate() {
        // Register custom roles [cite: 80]
        \App\Services\RoleService::registerRoles();

        // Run database migrations [cite: 82]
        \App\Migrations\CreateAvailabilityTable::up();
        \App\Migrations\CreateAppointmentsTable::up();

        \App\Migrations\AddReasonToAppointmentsTable::up(); // Add this line
        \App\Migrations\AddCancelledByToAppointmentsTable::up(); // Add this line


        // Clear the permalinks
        flush_rewrite_rules();
    }
}