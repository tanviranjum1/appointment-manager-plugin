<?php
/**
 * Plugin Name:       Appointment Management System
 * Plugin URI:        https://example.com/
 * Description:       A reusable WordPress plugin that supports multiple appointment contexts.
 * Version:           1.0.0
 * Author:            Your Name
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       appointment-manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define Plugin Version
define( 'APPOINTMENT_MANAGER_VERSION', '1.0.0' );
define( 'APPOINTMENT_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
define( 'APPOINTMENT_MANAGER_URL', plugin_dir_url( __FILE__ ) ); // Add this new constant

// Include dependencies
require_once APPOINTMENT_MANAGER_PATH . 'includes/class-activator.php';
require_once APPOINTMENT_MANAGER_PATH . 'app/Services/EmailService.php'; // Add this line
require_once APPOINTMENT_MANAGER_PATH . 'app/Controllers/UserController.php';
require_once APPOINTMENT_MANAGER_PATH . 'app/Controllers/AdminApprovalController.php';
require_once APPOINTMENT_MANAGER_PATH . 'app/Controllers/AvailabilityController.php'; // Add this line
require_once APPOINTMENT_MANAGER_PATH . 'app/Controllers/BookingController.php'; // Add this line
require_once APPOINTMENT_MANAGER_PATH . 'app/Controllers/AppointmentController.php'; // Add this line
require_once APPOINTMENT_MANAGER_PATH . 'includes/class-shortcodes.php';


/**
 * The code that runs during plugin activation.
 */
function activate_appointment_manager() {
    \Includes\Activator::activate();
}
register_activation_hook( __FILE__, 'activate_appointment_manager' );


/**
 * Main plugin class
 */
final class Appointment_Manager {
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize the plugin components.
     */
    public function init() {
        // Initialize controllers and shortcodes
        new \App\Controllers\UserController();
        new \App\Controllers\AdminApprovalController();
        new \App\Controllers\AvailabilityController(); // Add this line
        new \App\Controllers\BookingController(); // ✅ Add this line
        new \App\Controllers\AppointmentController(); // And add this line
        new \Includes\Shortcodes();
    }
}

// Let's get this party started
new Appointment_Manager();