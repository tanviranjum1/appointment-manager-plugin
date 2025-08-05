<?php

namespace App\Controllers;

class AppointmentController {
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        // Route to get appointments for the current user (role-dependent)
        register_rest_route( 'appointment-manager/v1', '/my-appointments', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_items' ],
            'permission_callback' => [ $this, 'permissions_check' ],
        ] );

        // Route to cancel an appointment
        register_rest_route( 'appointment-manager/v1', '/appointments/(?P<id>\d+)/cancel', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'cancel_item' ],
            'permission_callback' => [ $this, 'cancel_permissions_check' ],
        ] );


        // Route to update an appointment's status
        register_rest_route( 'appointment-manager/v1', '/appointments/(?P<id>\d+)/status', [
            'methods'             => \WP_REST_Server::EDITABLE, // Using EDITABLE which maps to POST/PUT/PATCH
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
    }

    public function permissions_check() {
        return is_user_logged_in();
    }

    public function get_items() {
        global $wpdb;
        $user = wp_get_current_user();
        $table = $wpdb->prefix . 'am_appointments';
        $users_table = $wpdb->prefix . 'users';


        $query = "";
        if ( in_array('tan_approver', (array) $user->roles) ) {
             $query = $wpdb->prepare(
                "SELECT a.*, u.display_name as requester_name FROM $table a
                 LEFT JOIN $users_table u ON a.requester_id = u.ID
                 WHERE a.approver_id = %d ORDER BY a.start_time DESC",
                $user->ID
            );
        } elseif ( in_array('tan_requester', (array) $user->roles) ) {
             $query = $wpdb->prepare(
                "SELECT a.*, u.display_name as approver_name FROM $table a
                 LEFT JOIN $users_table u ON a.approver_id = u.ID
                 WHERE a.requester_id = %d ORDER BY a.start_time DESC",
                $user->ID
            );
        }

        $results = $wpdb->get_results( $query );
        return new \WP_REST_Response( $results, 200 );
    }

    public function update_permissions_check( $request ) {
        $user = wp_get_current_user();
        $appointment_id = (int) $request['id'];

        if ( ! in_array('tan_approver', (array) $user->roles) ) {
            return false; // Only approvers can change status
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'am_appointments';
        $approver_id = $wpdb->get_var($wpdb->prepare("SELECT approver_id FROM $table WHERE id = %d", $appointment_id));

        // Ensure the approver owns this appointment
        return (int) $approver_id === $user->ID;
    }

    public function update_item_status( $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'am_appointments';
        $appointment_id = (int) $request['id'];
        $params = $request->get_json_params();

        $new_status = sanitize_text_field( $params['status'] );

        $updated = $wpdb->update(
            $table,
            ['status' => $new_status],
            ['id' => $appointment_id]
        );


        if ($updated) {
        $appointment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $appointment_id));
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

    public function cancel_permissions_check( $request ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }
        
        global $wpdb;
        $appointment_id = (int) $request['id'];
        $table = $wpdb->prefix . 'am_appointments';
        $appointment = $wpdb->get_row($wpdb->prepare("SELECT requester_id, approver_id FROM $table WHERE id = %d", $appointment_id));

        if ( ! $appointment ) {
            return false; // Appointment doesn't exist
        }

        $user_id = get_current_user_id();
        // Allow if the current user is either the requester or the approver for this appointment
        return ( (int) $appointment->requester_id === $user_id || (int) $appointment->approver_id === $user_id );
    }

    public function cancel_item( $request ) {
        global $wpdb;
        $appointment_id = (int) $request['id'];
        $table = $wpdb->prefix . 'am_appointments';
        $appointment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $appointment_id));
        $user = wp_get_current_user();
        $user_role = $user->roles[0]; // Get the current user's role


        // Rule for Requesters
        if ( in_array('tan_requester', (array) $user->roles) ) {
            if ($appointment->status !== 'pending') {
                return new \WP_Error('cancel_forbidden', 'Only pending appointments can be cancelled.', ['status' => 403]);
            }
            // Check if cancellation is at least 24 hours in advance
            $appointment_time = strtotime($appointment->start_time);
            $current_time = current_time('timestamp');
            if (($appointment_time - $current_time) < 24 * 60 * 60) {
                return new \WP_Error('cancel_too_late', 'Appointments must be cancelled at least 24 hours in advance.', ['status' => 403]);
            }
        }

        // Rule for Approvers (they can cancel pending or approved)
        if ( in_array('tan_approver', (array) $user->roles) ) {
             if (!in_array($appointment->status, ['pending', 'approved'])) {
                return new \WP_Error('cancel_forbidden', 'This appointment cannot be cancelled.', ['status' => 403]);
            }
        }

        // Update status to 'cancelled'
        $wpdb->update($table,  [
                'status' => 'cancelled',
                'cancelled_by_role' => $user_role // Save who cancelled it
            ], ['id' => $appointment_id]);

        // Send email notification
        $requester = get_user_by('id', $appointment->requester_id);
        $approver = get_user_by('id', $appointment->approver_id);
        if ($requester && $approver) {
            \App\Services\EmailService::notifyOfCancellation($requester, $approver, $appointment, $user->roles[0]);
        }

        return new \WP_REST_Response( ['success' => true, 'new_status' => 'cancelled'], 200 );
    }


    
}