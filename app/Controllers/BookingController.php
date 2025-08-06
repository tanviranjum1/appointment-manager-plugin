<?php

namespace App\Controllers;
use App\Models\Availability;
use App\Models\Appointment;


/**
 * Handles REST API requests for the requester booking process.
 * @package Appointment_Manager
 */
class BookingController {


    /**
     * Permission check to ensure the user is logged in.
     *
     * @return bool
     */
    public function requester_permissions_check() {
        return current_user_can('read'); // Basic check, can be enhanced
    }


     /**
     * API callback to get a list of all active, context-aware approvers.
     *
     * @return \WP_REST_Response The JSON response with a list of approvers.
     */
    public function get_approvers() {

        // Get the current user (the Requester) and their context
        $requester_id = get_current_user_id();
        $requester_context = get_user_meta($requester_id, 'tan_context', true);

        if ( ! $requester_context ) {
            // Return an empty array if the requester has no context set
            return new \WP_REST_Response( [], 200 );
        }

        // Using a more robust meta_query, same as we did for the admin page.
        $users = get_users([
            'role' => 'tan_approver',
             'meta_query' => [
                'relation' => 'AND', // Both conditions must be true
                [
                    'key'     => 'tan_status',
                    'value'   => 'active',
                    'compare' => '=',
                ],
                [
                    'key'     => 'tan_context',
                    'value'   => $requester_context,
                    'compare' => '=',
                ],
            ],
        ]);

        $approvers = array_map(function($user) {
            return ['id' => $user->ID, 'name' => $user->display_name];
        }, $users);

        return new \WP_REST_Response( $approvers, 200 );
    }


    /**
     * API callback to get available slots for a specific approver.
     *
     * @param \WP_REST_Request $request The request object, containing the approver's ID.
     * @return \WP_REST_Response The JSON response with available slots.
     */
    public function get_approver_availability( $request ) {
        $approver_id = (int) $request['id'];

        // Get data from models
        $booked_slots = Appointment::get_active_booked_slots_for_approver( $approver_id );
        $all_slots = Availability::get_by_approver_id( $approver_id );

        // Business logic stays in the controller
        $available_slots = array_filter($all_slots, function($slot) use ($booked_slots) {
            return new \DateTime($slot->start_time) > new \DateTime() && !in_array($slot->start_time, $booked_slots);
        });

        return new \WP_REST_Response( array_values($available_slots), 200 );
    }


      /**
     * API callback to create a new appointment request.
     *
     * @param \WP_REST_Request $request The request object.
     * @return \WP_REST_Response|\WP_Error The JSON response on success or error object on failure.
     */
     public function create_appointment( $request ) {
         global $wpdb; // Ensure $wpdb is available
        $params = $request->get_json_params();

        // getting and sanitizing all parameters 
        $approver_id = isset($params['approver_id']) ? intval($params['approver_id']) : 0;
        $start_time  = isset($params['start_time']) ? sanitize_text_field($params['start_time']) : '';
        $end_time    = isset($params['end_time']) ? sanitize_text_field($params['end_time']) : '';
        $reason      = isset($params['reason']) ? sanitize_textarea_field($params['reason']) : '';
        $requester_id = get_current_user_id();


        // 1. Check if this specific requester has a previously rejected or cancelled appointment for this exact slot.
        $table_name = $wpdb->prefix . 'am_appointments';
        $previous_attempt = $wpdb->get_row( $wpdb->prepare(
            "SELECT status FROM $table_name WHERE requester_id = %d AND approver_id = %d AND start_time = %s",
            $requester_id, $approver_id, $start_time
        ) );

        if ( $previous_attempt && in_array($previous_attempt->status, ['rejected', 'cancelled']) ) {
            return new \WP_Error(
                'previously_denied', 
                'You cannot re-book a slot that was previously rejected or that you cancelled.', 
                ['status' => 403] // 403 Forbidden is appropriate here
            );
        }


        // Server-side validation to ensure reason is not empty
       if ( empty( trim( $reason ) ) ) {
            return new \WP_Error('missing_reason', 'A reason for the appointment is required.', ['status' => 400]);
        }

       // 2. Check if the slot is booked by ANYONE (existing double-booking check)
        if ( Appointment::check_if_slot_is_booked( $approver_id, $start_time ) ) {
            return new \WP_Error('double_booking', 'This slot has just been booked. Please choose another.', ['status' => 409]);
        }  
        
        
        $insert_id = Appointment::create([
            'approver_id'  => $approver_id,
            'requester_id' => $requester_id,
            'start_time'   => $start_time,
            'end_time'     => $end_time,
            'status'       => 'pending',
            'created_at'   => current_time('mysql', 1),
             'reason'       => $reason, // Insert the reason
        ]);



     if ($insert_id) {
        $approver  = get_user_by('id', $approver_id);
        $requester = get_user_by('id', $requester_id);
        if ($approver && $requester) {
            $appointment_data = [
                'start_time' => $start_time,
                'requester_name' => $requester->display_name,
                 'reason' => $reason // Pass the reason to the email service

            ];
            // \App\Services\EmailService::notifyApproverOfNewRequest($approver->user_email, $appointment_data);
        }
    }

                return new \WP_REST_Response(['success' => true, 'id' => $insert_id], 201);

    }
}