<?php

namespace Includes;

class Shortcodes {
  public function __construct() {
        add_shortcode( 'tan_registration', [ $this, 'render_registration_form' ] );
        add_shortcode( 'tan_approver_portal', [ $this, 'render_approver_portal' ] ); // Add this line
        add_shortcode( 'tan_booking', [ $this, 'render_booking_portal' ] ); // Add this line
        add_shortcode( 'tan_my_appointments', [ $this, 'render_my_appointments_portal' ] ); // Add this line


    }
    public function render_registration_form() {
        if ( is_user_logged_in() ) {
            return '<p>You are already registered and logged in.</p>';
        }

        // Use output buffering to capture the template file's HTML
        ob_start();
        include_once APPOINTMENT_MANAGER_PATH . 'templates/registration-form.php';
        return ob_get_clean();
    }

      public function render_approver_portal() {
        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            return '<p>You must be logged in to view this page.</p>';
        }

        $user = wp_get_current_user();
        $user_meta = get_user_meta( $user->ID, 'tan_status', true );

        // Check if user is an approver and is active
        if ( ! in_array( 'tan_approver', (array) $user->roles ) || $user_meta !== 'active' ) {
            return '<p>You do not have permission to view this content.</p>';
        }

        // Enqueue the React script and pass data to it
        wp_enqueue_script(
            'approver-portal-script',
            APPOINTMENT_MANAGER_URL . 'frontend/build/index.js',
            ['wp-element'], // Dependency for React in WordPress
            APPOINTMENT_MANAGER_VERSION,
            true // Load in footer
        );

        wp_localize_script(
            'approver-portal-script',
            'tan_data', // Object name in JavaScript
            [
                'api_url' => esc_url_raw( rest_url( 'appointment-manager/v1/' ) ),
                'nonce'   => wp_create_nonce( 'wp_rest' )
            ]
        );

        // This is the root element where our React app will mount
        return '<div id="approver-portal-app"></div>';
    }

    public function render_booking_portal() {
        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            return '<p>You must be logged in to book an appointment.</p>';
        }

        $user = wp_get_current_user();
        if ( ! in_array( 'tan_requester', (array) $user->roles ) ) {
            return '<p>Only Requesters can book appointments.</p>';
        }

        // Enqueue the same React script bundle
        wp_enqueue_script(
            'booking-portal-script',
            APPOINTMENT_MANAGER_URL . 'frontend/build/index.js',
            ['wp-element'],
            APPOINTMENT_MANAGER_VERSION,
            true
        );

        // Pass the same data object
        wp_localize_script(
            'booking-portal-script',
            'tan_data',
            [
                'api_url' => esc_url_raw( rest_url( 'appointment-manager/v1/' ) ),
                'nonce'   => wp_create_nonce( 'wp_rest' )
            ]
        );

        // Provide the root element for the booking React app
        return '<div id="booking-page-app"></div>';
    }


 public function render_my_appointments_portal() {
        if ( ! is_user_logged_in() ) {
            return '<p>You must be logged in to view your appointments.</p>';
        }

        $user = wp_get_current_user();
        $user_role = ! empty( $user->roles ) ? $user->roles[0] : '';

        // Enqueue the same React script bundle
        wp_enqueue_script(
            'my-appointments-script',
            APPOINTMENT_MANAGER_URL . 'frontend/build/index.js',
            ['wp-element'],
            APPOINTMENT_MANAGER_VERSION,
            true
        );

        // Pass the API URL, nonce, AND the user's role
        wp_localize_script(
            'my-appointments-script',
            'tan_data',
            [
                'api_url'   => esc_url_raw( rest_url( 'appointment-manager/v1/' ) ),
                'nonce'     => wp_create_nonce( 'wp_rest' ),
                'user_role' => $user_role // This tells React how to behave
            ]
        );

        // Provide the root element for the new React app
        return '<div id="my-appointments-app"></div>';
    }

}