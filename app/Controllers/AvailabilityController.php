<?php

namespace App\Controllers;

class AvailabilityController {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( 'appointment-manager/v1', '/availability', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_items' ],
                'permission_callback' => [ $this, 'permissions_check' ],
            ],
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_item' ],
                'permission_callback' => [ $this, 'permissions_check' ],
            ],
        ] );
    }

    public function permissions_check( $request ) {
        $user = wp_get_current_user();
        if ( ! $user->ID ) {
            return false;
        }
        $status = get_user_meta( $user->ID, 'tan_status', true );
        return in_array( 'tan_approver', (array) $user->roles ) && $status === 'active';
    }

    public function get_items( $request ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_availability';
        $user_id = get_current_user_id();

        $results = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM $table_name WHERE approver_id = %d ORDER BY start_time DESC", $user_id )
        );

        return new \WP_REST_Response( $results, 200 );
    }

    public function create_item( $request ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_availability';
        $user_id = get_current_user_id();

        $params = $request->get_json_params();
        $start_time = sanitize_text_field( $params['start_time'] );
        $end_time = sanitize_text_field( $params['end_time'] );

        // Basic validation
        if ( strtotime( $start_time ) >= strtotime( $end_time ) ) {
            return new \WP_Error( 'invalid_times', 'End time must be after start time.', [ 'status' => 400 ] );
        }

        $wpdb->insert(
            $table_name,
            [
                'approver_id' => $user_id,
                'start_time'  => $start_time,
                'end_time'    => $end_time,
                'created_at'  => current_time( 'mysql', 1 ),
            ]
        );

        return new \WP_REST_Response( [ 'success' => true, 'id' => $wpdb->insert_id ], 201 );
    }
}