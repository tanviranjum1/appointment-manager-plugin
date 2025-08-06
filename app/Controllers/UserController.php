<?php

namespace App\Controllers;
use App\Services\EmailService;

/**
 * Handles the server-side processing of the user registration form.
 * @package Appointment_Manager
 */
class UserController {

    /**
     * Constructor. Hooks the registration handler into WordPress's init action.
     */
    public function __construct() {
        add_action( 'init', [ $this, 'handle_registration' ] );
    }

    /**
     * Processes the registration form submission from the [tan_registration] shortcode.
     * @return void
     */
    public function handle_registration() {
        if ( ! isset( $_POST['tan_registration_form'] ) || ! wp_verify_nonce( $_POST['tan_registration_nonce_field'], 'tan_registration_nonce' ) ) {
            return;
        }

        $username    = sanitize_user( $_POST['tan_username'] );
        $email       = sanitize_email( $_POST['tan_email'] );
        $password    = $_POST['tan_password'];
        $role        = sanitize_text_field( $_POST['tan_role'] );
        $context     = sanitize_text_field( $_POST['tan_context'] );
        $designation = sanitize_text_field( $_POST['tan_designation'] ?? '' );
        $institute   = sanitize_text_field( $_POST['tan_institute'] ?? '' );
        
        $allowed_contexts = get_option('tan_appointment_contexts', []);
        if ( ! is_array($allowed_contexts) || ! in_array($context, $allowed_contexts) ) {
            $this->redirect_with_error( 'Invalid context selected.' );
            return;
        }

        if ( username_exists( $username ) || email_exists( $email ) ) {
            $this->redirect_with_error( 'User already exists.' );
            return;
        }

        $user_id = wp_create_user( $username, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            $this->redirect_with_error( $user_id->get_error_message() );
            return;
        }

        $user = get_user_by( 'id', $user_id );
        $user->set_role( $role );
        add_user_meta( $user_id, 'tan_context', $context );

        if ( $role === 'tan_approver' ) {
            add_user_meta( $user_id, 'tan_status', 'pending' );
            if ( ! empty( $designation ) ) {
                add_user_meta( $user_id, 'tan_designation', $designation );
            }
            if ( ! empty( $institute ) ) {
                add_user_meta( $user_id, 'tan_institute', $institute );
            }
            // EmailService::notifyAdminOfPendingApprover( get_option('admin_email'), $user );
        } else {
            add_user_meta( $user_id, 'tan_status', 'active' );
        }

        wp_redirect( wp_login_url() . '?registration=success' );
        exit;
    }

    /**
     * Helper function to redirect back to the registration page with an error message.
     * @param string $message The error message to display.
     */
    private function redirect_with_error( $message ) {
        $url = add_query_arg( 'reg_error', urlencode( $message ), wp_get_referer() );
        wp_redirect( $url );
        exit;
    }
}