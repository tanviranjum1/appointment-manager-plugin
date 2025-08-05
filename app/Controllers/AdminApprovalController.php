<?php

namespace App\Controllers;
use App\Models\Appointment; // Use the model


class AdminApprovalController {
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        add_action( 'admin_init', [ $this, 'register_admin_settings' ] );

    }

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
            'Pending Approvals',     // Menu Title
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
 public function render_settings_page() {
        include_once APPOINTMENT_MANAGER_PATH . 'templates/admin-settings-page.php';
    }

    public function print_contexts_section_info() {
        echo '<p>Add or remove available contexts for your appointment system. Enter one context per line.</p>';
    }

    public function render_setup_guide_page() {
        include_once APPOINTMENT_MANAGER_PATH . 'templates/admin-setup-guide.php';
    }


     public function render_contexts_field() {
        $contexts_option = get_option( 'tan_appointment_contexts' );
        $contexts = is_array($contexts_option) ? implode( "\n", $contexts_option ) : '';
        printf(
            '<textarea id="contexts_list" name="tan_appointment_contexts" rows="5" cols="50">%s</textarea>',
            esc_textarea( $contexts )
        );
    }

    public function render_pending_approvals_page() {
        // Handle approval/rejection actions
        $this->handle_actions();

        // --- START OF THE FIX ---
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
        // --- END OF THE FIX ---


        // Render the view
        include_once APPOINTMENT_MANAGER_PATH . 'templates/admin-approvals.php';
    }


   public function render_all_appointments_page() {
        global $wpdb;
        $appointments_table = $wpdb->prefix . 'am_appointments';
        $users_table = $wpdb->prefix . 'users';

        // --- START OF PHASE 10 UPDATE ---
        // Get filter values from URL
        $filter_status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : '';
        $filter_approver = isset($_GET['filter_approver']) ? intval($_GET['filter_approver']) : 0;

        // Build WHERE clauses dynamically and safely
        $where_clauses = [];
        if (!empty($filter_status)) {
            $where_clauses[] = $wpdb->prepare("a.status = %s", $filter_status);
        }
        if (!empty($filter_approver)) {
            $where_clauses[] = $wpdb->prepare("a.approver_id = %d", $filter_approver);
        }

        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = "WHERE " . implode(' AND ', $where_clauses);
        }

        // The main query now includes the dynamic WHERE clause
        $all_appointments = $wpdb->get_results(
            "SELECT 
                a.*, 
                approver.display_name as approver_name, 
                requester.display_name as requester_name 
            FROM $appointments_table a
            LEFT JOIN $users_table approver ON a.approver_id = approver.ID
            LEFT JOIN $users_table requester ON a.requester_id = requester.ID
            $where_sql
            ORDER BY a.start_time DESC"
        );
        
        // Data for the filter dropdowns
        $all_approvers = get_users(['role' => 'tan_approver']);
        $all_statuses = ['pending', 'approved', 'rejected', 'cancelled'];
        // --- END OF PHASE 10 UPDATE ---
        
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
     * Converts a newline-separated string into a clean array.
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
            // In a future phase, we would email the user about their approval. [cite: 75]
            $user = get_user_by('id', $user_id);
            if ($user) {
                \App\Services\EmailService::notifyUserOfApproval( $user->user_email, $user );
            }

        } elseif ( $action === 'reject' ) {
            update_user_meta( $user_id, 'tan_status', 'rejected' );
        }
    }
}