<?php
/**
 * Plugin Name:       Appointment Management System
 * Plugin URI:        https://example.com/
 * Description:       A reusable WordPress plugin that supports multiple appointment contexts.
 * Version:           1.0.0
 * Author:            Tanvir Anjum
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       appointment-manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define the available contexts for the system.
// define('TAN_APPOINTMENT_CONTEXTS', serialize(['School', 'Hospital', 'Court']));

// Define Plugin Version
define( 'APPOINTMENT_MANAGER_VERSION', '1.0.0' );
define( 'APPOINTMENT_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
define( 'APPOINTMENT_MANAGER_URL', plugin_dir_url( __FILE__ ) );

// Include all dependencies, including the new Models.
require_once APPOINTMENT_MANAGER_PATH . 'includes/class-activator.php';
require_once APPOINTMENT_MANAGER_PATH . 'includes/class-deactivator.php';
// require_once APPOINTMENT_MANAGER_PATH . 'app/Services/EmailService.php';
require_once APPOINTMENT_MANAGER_PATH . 'app/Services/RoleService.php';

// **These lines are essential for the Models to be found**
require_once APPOINTMENT_MANAGER_PATH . 'app/Models/Availability.php';
require_once APPOINTMENT_MANAGER_PATH . 'app/Models/Appointment.php';

require_once APPOINTMENT_MANAGER_PATH . 'app/Controllers/UserController.php';
require_once APPOINTMENT_MANAGER_PATH . 'app/Controllers/AdminApprovalController.php';
require_once APPOINTMENT_MANAGER_PATH . 'app/Controllers/AvailabilityController.php';
require_once APPOINTMENT_MANAGER_PATH . 'app/Controllers/BookingController.php';
require_once APPOINTMENT_MANAGER_PATH . 'app/Controllers/AppointmentController.php';


require_once APPOINTMENT_MANAGER_PATH . 'includes/class-shortcodes.php';


// Load the new routes file
require_once APPOINTMENT_MANAGER_PATH . 'app/Routes/Api.php';

/**
 * The code that runs during plugin activation.
 */
function activate_appointment_manager() {
    \Includes\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_appointment_manager() {
    \Includes\Deactivator::deactivate();
}




register_activation_hook( __FILE__, 'activate_appointment_manager' );
register_deactivation_hook( __FILE__, 'deactivate_appointment_manager' );

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

 // We must initialize the UserController here so its 'init' hook for the
        // PHP registration form is correctly registered.
        new \App\Controllers\UserController();

        // This controller is not part of the API, so it needs its own initialization.
        new \App\Controllers\AdminApprovalController();
        
        // This class initializes all API routes.
        new \App\Routes\Api();
        
        // This class initializes all shortcodes.
        new \Includes\Shortcodes();
    }
}

// Let's get this party started
new Appointment_Manager();