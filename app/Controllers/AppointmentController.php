<?php

namespace App\Controllers;

use App\Models\Appointment;

class AppointmentController {
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        // GET /my-appointments (unchanged)
        register_rest_route( 'appointment-manager/v1', '/my-appointments', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_items' ],
            'permission_callback' => [ $this, 'permissions_check' ],
        ] );

        // POST /appointments/{id}/status (This was previously EDITABLE)
        register_rest_route( 'appointment-manager/v1', '/appointments/(?P<id>\d+)/status', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'update_item_status' ],
            'permission_callback' => [ $this, 'update_permissions_check' ],
            'args' => [
                'status' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return in_array($param, ['approved', 'rejected']);
                    }
                ],
            ],
        ] );

        // POST /appointments/{id}/cancel (unchanged)
        register_rest_route( 'appointment-manager/v1', '/appointments/(?P<id>\d+)/cancel', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'cancel_item' ],
            'permission_callback' => [ $this, 'cancel_permissions_check' ],
        ] );
    }

    public function permissions_check() {
        return is_user_logged_in();
    }

    public function get_items() {
        $user = wp_get_current_user();
        $results = [];
        if ( in_array('tan_approver', (array) $user->roles) ) {
            $results = Appointment::get_by_approver_id( $user->ID );
        } elseif ( in_array('tan_requester', (array) $user->roles) ) {
            $results = Appointment::get_by_requester_id( $user->ID );
        }
        return new \WP_REST_Response( $results, 200 );
    }

    public function update_permissions_check( $request ) {
        $user = wp_get_current_user();
        $appointment = Appointment::find( (int) $request['id'] );

        if (!$appointment || !in_array('tan_approver', (array) $user->roles)) {
            return false;
        }
        
        return (int) $appointment->approver_id === $user->ID;
    }
    
    // --- START OF THE FIX ---
    // This entire function has been corrected.
    public function update_item_status( $request ) {
        $appointment_id = (int) $request['id'];
        $params = $request->get_json_params();
        $new_status = sanitize_text_field( $params['status'] );

        // Update the status using the Model
        $updated = Appointment::update_status( $appointment_id, $new_status );

        // Send email notification after successful update
        if ($updated) {
            // Re-fetch the appointment details to get requester/approver IDs
            $appointment = Appointment::find($appointment_id);
            if ($appointment) {
                $requester = get_user_by('id', $appointment->requester_id);
                $approver = get_user_by('id', $appointment->approver_id);
                if ($requester && $approver) {
                    $email_data = [
                        'start_time' => $appointment->start_time,
                        'status' => $new_status,
                        'approver_name' => $approver->display_name
                    ];
                    \App\Services\EmailService::notifyRequesterOfStatusUpdate($requester->user_email, $email_data);
                }
            }
        }

        return new \WP_REST_Response( ['success' => true, 'new_status' => $new_status], 200 );
    }
    // --- END OF THE FIX ---

    public function cancel_permissions_check( $request ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }
        
        $appointment = Appointment::find( (int) $request['id'] );

        if ( ! $appointment ) {
            return false;
        }

        $user_id = get_current_user_id();
        return ( (int) $appointment->requester_id === $user_id || (int) $appointment->approver_id === $user_id );
    }

    public function cancel_item( $request ) {
        $appointment_id = (int) $request['id'];
        $appointment = Appointment::find($appointment_id);
        $user = wp_get_current_user();

        // Rule for Requesters
        if ( in_array('tan_requester', (array) $user->roles) ) {
            if ($appointment->status !== 'pending') {
                return new \WP_Error('cancel_forbidden', 'Only pending appointments can be cancelled.', ['status' => 403]);
            }
            $appointment_time = strtotime($appointment->start_time);
            $current_time = current_time('timestamp');
            if (($appointment_time - $current_time) < 24 * 60 * 60) {
                return new \WP_Error('cancel_too_late', 'Appointments must be cancelled at least 24 hours in advance.', ['status' => 403]);
            }
        }

        // Rule for Approvers
        if ( in_array('tan_approver', (array) $user->roles) ) {
             if (!in_array($appointment->status, ['pending', 'approved'])) {
                return new \WP_Error('cancel_forbidden', 'This appointment cannot be cancelled.', ['status' => 403]);
            }
        }

        Appointment::cancel( $appointment_id, $user->roles[0] );

        // Send email notification
        $requester = get_user_by('id', $appointment->requester_id);
        $approver = get_user_by('id', $appointment->approver_id);
        if ($requester && $approver) {
            \App\Services\EmailService::notifyOfCancellation($requester, $approver, $appointment, $user->roles[0]);
        }

        return new \WP_REST_Response( ['success' => true, 'new_status' => 'cancelled'], 200 );
    }
}