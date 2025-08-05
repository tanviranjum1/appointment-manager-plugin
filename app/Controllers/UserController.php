<?php

namespace App\Controllers;

class UserController {

    public function __construct() {
        add_action( 'init', [ $this, 'handle_registration' ] );
    }

    public function handle_registration() {
        if ( ! isset( $_POST['tan_registration_form'] ) || ! wp_verify_nonce( $_POST['tan_registration_nonce_field'], 'tan_registration_nonce' ) ) {
            return;
        }

        $username = sanitize_user( $_POST['tan_username'] );
        $email    = sanitize_email( $_POST['tan_email'] );
        $password = $_POST['tan_password'];
        $role     = sanitize_text_field( $_POST['tan_role'] );
        $context  = sanitize_text_field( $_POST['tan_context'] );



          $allowed_contexts = get_option('tan_appointment_contexts', []);

             // Validate the selected context against our defined list

        if ( ! is_array($allowed_contexts) || ! in_array($context, $allowed_contexts) ) {
            $this->redirect_with_error( 'Invalid context selected.' );
            return;
        }


        // Basic validation
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

        add_user_meta( $user_id, 'tan_context', $context ); // Save the context



        if ( $role === 'tan_approver' ) {
            add_user_meta( $user_id, 'tan_status', 'pending' );

        // Add Designation and Institute as user meta
        if ( ! empty( $_POST['tan_designation'] ) ) {
            add_user_meta( $user_id, 'tan_designation', sanitize_text_field( $_POST['tan_designation'] ) );
        }
        if ( ! empty( $_POST['tan_institute'] ) ) {
            add_user_meta( $user_id, 'tan_institute', sanitize_text_field( $_POST['tan_institute'] ) );
        }


            \App\Services\EmailService::notifyAdminOfPendingApprover( get_option('admin_email'), $user );
            // In a future phase, we'd call an email service here to notify the admin. [cite: 52]
        } else {
            add_user_meta( $user_id, 'tan_status', 'active' );
        }

        // Redirect to login page after successful registration
        wp_redirect( wp_login_url() . '?registration=success' );
        exit;
    }

    private function redirect_with_error( $message ) {
        $url = add_query_arg( 'reg_error', urlencode( $message ), wp_get_referer() );
        wp_redirect( $url );
        exit;
    }
}