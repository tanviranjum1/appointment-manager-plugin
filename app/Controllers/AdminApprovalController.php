<?php

namespace App\Controllers;
use App\Models\Appointment; // Use the model


class AdminApprovalController {
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
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

        add_submenu_page(
            'tan-main-admin-page',   // Parent Slug
            'Setup Guide',           // Page Title
            'Setup Guide',           // Menu Title
            'manage_options',        // Capability
            'tan-setup-guide',       // Menu Slug
            [ $this, 'render_setup_guide_page' ] // NEW Function
        );


        // add_menu_page(
        //     'Pending Approvals',
        //     'Appointment Admin',
        //     'manage_options',
        //     'tan-approvals',
        //     [ $this, 'render_admin_page' ],
        //     'dashicons-groups',
        //     30
        // );
    }


    public function render_setup_guide_page() {
        include_once APPOINTMENT_MANAGER_PATH . 'templates/admin-setup-guide.php';
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
        $all_appointments = Appointment::get_all_with_user_details();
        include_once APPOINTMENT_MANAGER_PATH . 'templates/admin-all-appointments.php';
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