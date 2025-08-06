<?php

namespace App\Controllers;
use App\Models\Appointment; // Use the model


/**
 * Manages all admin-facing pages and functionality for the plugin.
 */
class AdminApprovalController {

    /**
     * Constructor. Hooks into WordPress admin actions.
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        add_action( 'admin_init', [ $this, 'register_admin_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
    }


    /**
     * Enqueues CSS and JS for the custom admin pages.
     *
     * @param string $hook The current admin page hook.
     * @return void
     */
    public function enqueue_admin_assets( $hook ) {
        // A list of our plugin's admin page hooks
        $plugin_pages = [
            'toplevel_page_tan-main-admin-page',
            'appointment-admin_page_tan-all-appointments',
            'appointment-admin_page_tan-settings',
        ];

        // Only load our assets on our plugin's pages
        if ( in_array( $hook, $plugin_pages ) ) {
            wp_enqueue_style(
                'bootstrap-css',
                APPOINTMENT_MANAGER_URL . 'assets/css/bootstrap.min.css',
                [],
                '5.3.3'
            );
        }
    }

    /**
     * Adds the main menu and submenu pages to the WordPress admin dashboard.
     *
     * @return void
     */
    public function add_admin_menu() {
        add_menu_page(
            'Appointment System',    // Page Title
            'Appointment Admin',   // Menu Title
            'manage_options',        // Capability
            'tan-main-admin-page',   // Menu Slug
            [ $this, 'render_pending_approvals_page' ], // Function
            'dashicons-groups',      // Icon
            30
        );

        add_submenu_page(
            'tan-main-admin-page',   // Parent Slug
            'Pending Approvals',     // Page Title
            'Pending Approvers',     // Menu Title
            'manage_options',        // Capability
            'tan-main-admin-page',   // Menu Slug (same as parent to make it the default)
            [ $this, 'render_pending_approvals_page' ]  // Function
        );

        add_submenu_page(
            'tan-main-admin-page',   // Parent Slug
            'All Appointments',      // Page Title
            'All Appointments',      // Menu Title
            'manage_options',        // Capability
            'tan-all-appointments',  // Menu Slug
            [ $this, 'render_all_appointments_page' ] // NEW Function
        );

        // add_submenu_page(
        //     'tan-main-admin-page',   // Parent Slug
        //     'Setup Guide',           // Page Title
        //     'Setup Guide',           // Menu Title
        //     'manage_options',        // Capability
        //     'tan-setup-guide',       // Menu Slug
        //     [ $this, 'render_setup_guide_page' ] // NEW Function
        // );

        add_submenu_page(
            'tan-main-admin-page',
            'Settings',
            'Settings',
            'manage_options',
            'tan-settings',
            [ $this, 'render_settings_page' ]
        );
      
    }

  /**
     * Renders the main settings page view.
     *
     * @return void
     */
 public function render_settings_page() {
        include_once APPOINTMENT_MANAGER_PATH . 'templates/admin-settings-page.php';
    }


     /**
     * Prints the informational text for the contexts settings section.
     *
     * @return void
     */
    public function print_contexts_section_info() {
        echo '<p>Add or remove available contexts for your appointment system. Enter one context per line.</p>';
    }

    public function render_setup_guide_page() {
        include_once APPOINTMENT_MANAGER_PATH . 'templates/admin-setup-guide.php';
    }



      /**
     * Renders the textarea field for managing contexts.
     *
     * @return void
     */
     public function render_contexts_field() {
        $contexts_option = get_option( 'tan_appointment_contexts' );
        $contexts = is_array($contexts_option) ? implode( "\n", $contexts_option ) : '';
        printf(
            '<textarea id="contexts_list" name="tan_appointment_contexts" rows="5" cols="50">%s</textarea>',
            esc_textarea( $contexts )
        );
    }


    /**
     * Renders the "Pending Approvals" admin page.
     *
     * @return void
     */
    public function render_pending_approvals_page() {
        // Handle approval/rejection actions
        $this->handle_actions();

        // The previous query was sometimes unreliable. This is the robust way.
        $args = [
            'role' => 'tan_approver',
            'meta_query' => [
                [
                    'key'     => 'tan_status',
                    'value'   => 'pending',
                    'compare' => '=',
                ],
            ],
        ];
        $pending_users = get_users( $args );

        // Render the view
        include_once APPOINTMENT_MANAGER_PATH . 'templates/admin-approvals.php';
    }



    /**
     * Renders the "All Appointments" admin page.
     *
     * @return void
     */
   public function render_all_appointments_page() {
        // The controller is now only responsible for getting user input
        // and passing it to the model.
        $filters = [
            'status'      => isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : '',
            'approver_id' => isset($_GET['filter_approver']) ? intval($_GET['filter_approver']) : 0
        ];
        
        // All database logic is now handled by the Appointment model.
        $all_appointments = Appointment::get_all_filtered( $filters );
        // --- END OF REFACTOR ---
        
        // Data for the filter dropdowns
        $all_approvers = get_users(['role' => 'tan_approver']);
        $all_statuses = ['pending', 'approved', 'rejected', 'cancelled'];
        
        // Render the view
        include_once APPOINTMENT_MANAGER_PATH . 'templates/admin-all-appointments.php';
    }


    public function register_admin_settings() {
        register_setting(
            'tan_settings_group', // Option group
            'tan_appointment_contexts' // Option name
             // Add this callback to correctly save the textarea data as an array
            ,[ 'sanitize_callback' => [ $this, 'sanitize_contexts_list' ] ]
        );

        add_settings_section(
            'tan_contexts_section', // ID
            'Appointment Contexts', // Title
            [ $this, 'print_contexts_section_info' ], // Callback
            'tan-settings-page' // Page
        );

        add_settings_field(
            'contexts_list', // ID
            'Available Contexts', // Title
            [ $this, 'render_contexts_field' ], // Callback
            'tan-settings-page', // Page
            'tan_contexts_section' // Section
        );
    }

    /**
     * Sanitization callback for the contexts textarea.
     * Converts a newline-separated string into a clean array before saving.
     *
     * @param string $input The raw string from the textarea.
     * @return array The cleaned array of contexts.
     */
    public function sanitize_contexts_list( $input ) {
        if ( ! is_string( $input ) ) {
            return [];
        }
        // Split the string by new lines
        $contexts = explode( "\n", $input );
        // Trim whitespace from each item and remove any empty lines
        $clean_contexts = array_filter( array_map( 'trim', $contexts ) );
        return $clean_contexts;
    }

    private function handle_actions() {
        if ( ! isset( $_GET['action'] ) || ! isset( $_GET['user_id'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'tan_change_status_' . $_GET['user_id'] ) ) {
            wp_die( 'Security check failed.' );
        }

        $user_id = intval( $_GET['user_id'] );
        $action = sanitize_key( $_GET['action'] );

        if ( $action === 'approve' ) {
            update_user_meta( $user_id, 'tan_status', 'active' ); // 
            // for email
            // $user = get_user_by('id', $user_id);
            // if ($user) {
            //     \App\Services\EmailService::notifyUserOfApproval( $user->user_email, $user );
            // }

        } elseif ( $action === 'reject' ) {
            update_user_meta( $user_id, 'tan_status', 'rejected' );
        }
    }
}