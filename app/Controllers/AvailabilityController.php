<?php

namespace App\Controllers;
use App\Models\Availability;


/**
 * Handles REST API requests for managing an Approver's availability.
 * @package Appointment_Manager
 */
class AvailabilityController {
    
    /**
     * Permission check for all availability routes.
     * Ensures the user is a logged-in and active approver.
     *
     * @param \WP_REST_Request $request The request object.
     * @return bool True if the user has permission, false otherwise.
     */
    public function permissions_check( $request ) {
        $user = wp_get_current_user();
        if ( ! $user->ID ) {
            return false;
        }
        $status = get_user_meta( $user->ID, 'tan_status', true );
        return in_array( 'tan_approver', (array) $user->roles ) && $status === 'active';
    }


    /**
     * API callback to get all availability slots for the current approver.
     *
     * @param \WP_REST_Request $request The request object.
     * @return \WP_REST_Response The JSON response with availability data.
     */
    public function get_items( $request ) {
        $user_id = get_current_user_id();
        $results = Availability::get_by_approver_id( $user_id );
        return new \WP_REST_Response( $results, 200 );
    }

    /**
     * API callback to create a new availability slot.
     *
     * @param \WP_REST_Request $request The request object.
     * @return \WP_REST_Response|\WP_Error The JSON response on success or error object on failure.
     */
      public function create_item( $request ) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params();
        $start_time = sanitize_text_field( $params['start_time'] );
        $end_time = sanitize_text_field( $params['end_time'] );

        // Business logic/validation stays in the controller
        if ( strtotime( $start_time ) >= strtotime( $end_time ) ) {
            return new \WP_Error( 'invalid_times', 'End time must be after start time.', [ 'status' => 400 ] );
        }

        $insert_id = Availability::create([
            'approver_id' => $user_id,
            'start_time'  => $start_time,
            'end_time'    => $end_time,
            'created_at'  => current_time( 'mysql', 1 ),
        ]);

        return new \WP_REST_Response( [ 'success' => true, 'id' => $insert_id ], 201 );
    }
}