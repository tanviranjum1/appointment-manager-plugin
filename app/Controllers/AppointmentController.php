<?php

namespace App\Controllers;

use App\Models\Appointment;


/**
 * Handles REST API requests for managing appointments (viewing, updating status, cancelling).
 */
class AppointmentController {
    
    /**
     * Permission check to ensure user is logged in.
     *
     * @return bool
     */
    public function permissions_check() {
        return is_user_logged_in();
    }


    /**
     * API callback to get appointments for the current user.
     *
     * @param \WP_REST_Request $request The request object.
     * @return \WP_REST_Response The JSON response with appointments data.
     */
    public function get_items( $request ) {
        $user = wp_get_current_user();
        $results = ['appointments' => [], 'total_pages' => 1];
        
        // Get filter and pagination params from the request and ensure they are clean.
        $status_filter = sanitize_text_field($request->get_param('status'));
        $filters = [];
        if (!empty($status_filter)) {
            $filters['status'] = $status_filter;
        }
        $page = $request->get_param('page') ? intval($request->get_param('page')) : 1;

        if ( in_array('tan_approver', (array) $user->roles) ) {
            $results = Appointment::get_by_approver_id( $user->ID, $filters, $page );
        } elseif ( in_array('tan_requester', (array) $user->roles) ) {
            $results = Appointment::get_by_requester_id( $user->ID, $filters, $page );
        }
        return new \WP_REST_Response( $results, 200 );
    }


     /**
     * Permission check for updating an appointment's status.
     * Ensures the user is the assigned approver for the appointment.
     *
     * @param \WP_REST_Request $request The request object.
     * @return bool
     */
    public function update_permissions_check( $request ) {
        $user = wp_get_current_user();
        $appointment = Appointment::find( (int) $request['id'] );

        if (!$appointment || !in_array('tan_approver', (array) $user->roles)) {
            return false;
        }
        
        return (int) $appointment->approver_id === $user->ID;
    }
    
  

    /**
     * API callback to update an appointment's status (approve/reject).
     *
     * @param \WP_REST_Request $request The request object.
     * @return \WP_REST_Response|\WP_Error The JSON response on success or error object on failure.
     */
    public function update_item_status( $request ) {
        $appointment_id = (int) $request['id'];
        $params = $request->get_json_params();
        $new_status = sanitize_text_field( $params['status'] );

        // Update the status using the Model
        $updated = Appointment::update_status( $appointment_id, $new_status );

        // Send email notification after successful update
        // if ($updated) {
        //     // Re-fetch the appointment details to get requester/approver IDs
        //     $appointment = Appointment::find($appointment_id);
        //     if ($appointment) {
        //         $requester = get_user_by('id', $appointment->requester_id);
        //         $approver = get_user_by('id', $appointment->approver_id);
        //         if ($requester && $approver) {
        //             $email_data = [
        //                 'start_time' => $appointment->start_time,
        //                 'status' => $new_status,
        //                 'approver_name' => $approver->display_name
        //             ];
        //             \App\Services\EmailService::notifyRequesterOfStatusUpdate($requester->user_email, $email_data);
        //         }
        //     }
        // }

        return new \WP_REST_Response( ['success' => true, 'new_status' => $new_status], 200 );
    }



    /**
     * Permission check for cancelling an appointment.
     * Ensures the user is either the requester or the approver for the appointment.
     *
     * @param \WP_REST_Request $request The request object.
     * @return bool
     */
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


     /**
     * API callback to cancel an appointment.
     *
     * @param \WP_REST_Request $request The request object.
     * @return \WP_REST_Response|\WP_Error The JSON response on success or error object on failure.
     */
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
        // $requester = get_user_by('id', $appointment->requester_id);
        // $approver = get_user_by('id', $appointment->approver_id);
        // if ($requester && $approver) {
        //     \App\Services\EmailService::notifyOfCancellation($requester, $approver, $appointment, $user->roles[0]);
        // }

        return new \WP_REST_Response( ['success' => true, 'new_status' => 'cancelled'], 200 );
    }
}