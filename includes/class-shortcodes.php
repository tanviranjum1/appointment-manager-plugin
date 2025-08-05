<?php

namespace Includes;

class Shortcodes {
    public function __construct() {
        add_shortcode( 'tan_registration', [ $this, 'render_registration_form' ] );
        add_shortcode( 'tan_approver_portal', [ $this, 'render_approver_portal' ] );
        add_shortcode( 'tan_booking', [ $this, 'render_booking_portal' ] );
        add_shortcode( 'tan_my_appointments', [ $this, 'render_my_appointments_portal' ] );
    }

    public function render_registration_form() {
        if ( is_user_logged_in() ) {
            return '<p>You are already registered and logged in.</p>';
        }

        ob_start();
        include_once APPOINTMENT_MANAGER_PATH . 'templates/registration-form.php';
        return ob_get_clean();
    }

    public function render_approver_portal() {
        if ( ! is_user_logged_in() ) {
            return '<p>You must be logged in to view this page.</p>';
        }

        $user = wp_get_current_user();
        $user_meta = get_user_meta( $user->ID, 'tan_status', true );

        if ( ! in_array( 'tan_approver', (array) $user->roles ) || $user_meta !== 'active' ) {
            return '<p>You do not have permission to view this content.</p>';
        }

        $this->enqueue_react_assets();

        return '<div id="approver-portal-app"></div>';
    }

    public function render_booking_portal() {
        if ( ! is_user_logged_in() ) {
            return '<p>You must be logged in to book an appointment.</p>';
        }

        $user = wp_get_current_user();
        if ( ! in_array( 'tan_requester', (array) $user->roles ) ) {
            return '<p>Only Requesters can book appointments.</p>';
        }

        $this->enqueue_react_assets();

        return '<div id="booking-page-app"></div>';
    }

    public function render_my_appointments_portal() {
        if ( ! is_user_logged_in() ) {
            return '<p>You must be logged in to view your appointments.</p>';
        }

        $this->enqueue_react_assets();

        return '<div id="my-appointments-app"></div>';
    }

    /**
     * Enqueue all necessary scripts and styles for React apps.
     */
    private function enqueue_react_assets() {
        // Enqueue Bootstrap CSS
        wp_enqueue_style(
            'bootstrap-css',
            APPOINTMENT_MANAGER_URL . 'assets/css/bootstrap.min.css',
            [],
            '5.3.3'
        );

        // Enqueue Bootstrap JS
        wp_enqueue_script(
            'bootstrap-js',
            APPOINTMENT_MANAGER_URL . 'assets/js/bootstrap.bundle.min.js',
            [],
            '5.3.3',
            true
        );

        // Main plugin CSS (depends on Bootstrap)
        wp_enqueue_style(
            'tan-plugin-main-css',
            APPOINTMENT_MANAGER_URL . 'frontend/build/index.css',
            ['bootstrap-css'],
            APPOINTMENT_MANAGER_VERSION
        );

        // Main plugin JS (depends on wp-element + Bootstrap)
        wp_enqueue_script(
            'tan-plugin-main-script',
            APPOINTMENT_MANAGER_URL . 'frontend/build/index.js',
            ['wp-element', 'bootstrap-js'],
            APPOINTMENT_MANAGER_VERSION,
            true
        );

        // Localize script
        wp_localize_script(
            'tan-plugin-main-script',
            'tan_data',
            [
                'api_url'   => esc_url_raw( rest_url( 'appointment-manager/v1/' ) ),
                'nonce'     => wp_create_nonce( 'wp_rest' ),
                'user_role' => is_user_logged_in() ? wp_get_current_user()->roles[0] : ''
            ]
        );
    }
}
