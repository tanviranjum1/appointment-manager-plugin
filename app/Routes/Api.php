<?php
namespace App\Routes;

use App\Controllers\AppointmentController;
use App\Controllers\AvailabilityController;
use App\Controllers\BookingController;
use App\Controllers\UserController;

/**
 * Registers all REST API routes for the plugin.
 * @package Appointment_Manager
 */
class Api {

    /**
     * Constructor. Hooks into the WordPress REST API initialization.
     */
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    /**
     * Registers all REST API routes.
     *
     * @return void
     */
    public function register_routes() {
        $appointmentController = new AppointmentController();
        $availabilityController = new AvailabilityController();
        $bookingController = new BookingController();
        $userController = new UserController();

        // --- User Routes ---
        register_rest_route( 'appointment-manager/v1', '/register', [
            'methods'  => 'POST',
            'callback' => [ $userController, 'handle_registration' ],
            'permission_callback' => '__return_true',
        ]);
       

        // --- Availability Routes ---
        register_rest_route( 'appointment-manager/v1', '/availability', [
            [
                'methods'             => 'GET',
                'callback'            => [ $availabilityController, 'get_items' ],
                'permission_callback' => [ $availabilityController, 'permissions_check' ],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [ $availabilityController, 'create_item' ],
                'permission_callback' => [ $availabilityController, 'permissions_check' ],
            ],
        ] );

        // --- Booking Routes ---
        register_rest_route( 'appointment-manager/v1', '/approvers', [
            'methods'             => 'GET',
            'callback'            => [ $bookingController, 'get_approvers' ],
            'permission_callback' => [ $bookingController, 'requester_permissions_check' ],
        ] );
        register_rest_route( 'appointment-manager/v1', '/availability/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [ $bookingController, 'get_approver_availability' ],
            'permission_callback' => [ $bookingController, 'requester_permissions_check' ],
        ] );
        register_rest_route( 'appointment-manager/v1', '/appointments', [
            'methods'             => 'POST',
            'callback'            => [ $bookingController, 'create_appointment' ],
            'permission_callback' => [ $bookingController, 'requester_permissions_check' ],
        ] );
        
        // --- Appointment Management Routes ---
        register_rest_route( 'appointment-manager/v1', '/my-appointments', [
            'methods'             => 'GET',
            'callback'            => [ $appointmentController, 'get_items' ],
            'permission_callback' => [ $appointmentController, 'permissions_check' ],
        ] );
        register_rest_route( 'appointment-manager/v1', '/appointments/(?P<id>\d+)/status', [
            'methods'             => 'POST',
            'callback'            => [ $appointmentController, 'update_item_status' ],
            'permission_callback' => [ $appointmentController, 'update_permissions_check' ],
        ] );
        register_rest_route( 'appointment-manager/v1', '/appointments/(?P<id>\d+)/cancel', [
            'methods'             => 'POST',
            'callback'            => [ $appointmentController, 'cancel_item' ],
            'permission_callback' => [ $appointmentController, 'cancel_permissions_check' ],
        ] );
    }
}