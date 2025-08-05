<?php

namespace App\Services;

class EmailService {

    private static function sendEmail( $to, $subject, $body ) {
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        return wp_mail( $to, $subject, $body, $headers );
    }

    public static function notifyAdminOfPendingApprover( $admin_email, $user ) {
        $subject = 'New Approver Registration Pending Approval';
        $message = "
            <h2>New Approver Registration</h2>
            <p>A new user has registered as an Approver and is awaiting your approval.</p>
            <ul>
                <li><strong>Username:</strong> {$user->user_login}</li>
                <li><strong>Email:</strong> {$user->user_email}</li>
            </ul>
            <p>Please log in to your WordPress dashboard to approve or reject this user.</p>
        ";
        self::sendEmail( $admin_email, $subject, $message );
    }

    public static function notifyUserOfApproval( $user_email, $user ) {
        $subject = 'Your Account Has Been Approved!';
        $message = "
            <h2>Welcome, {$user->display_name}!</h2>
            <p>Your account with the Approver role has been approved. You can now log in and set your availability.</p>
            <p><a href='" . wp_login_url() . "'>Click here to log in</a></p>
        ";
        self::sendEmail( $user_email, $subject, $message );
    }

    public static function notifyApproverOfNewRequest( $approver_email, $data ) {
        $subject = 'You have a new appointment request';
        $time = new \DateTime($data['start_time']);
        $reason_html = !empty($data['reason']) ? "<li><strong>Reason:</strong> " . esc_html($data['reason']) . "</li>" : "";

        $message = "
            <h2>New Appointment Request</h2>
            <p>You have received a new appointment request.</p>
            <ul>
                <li><strong>From:</strong> {$data['requester_name']}</li>
                <li><strong>Time:</strong> {$time->format('F j, Y, g:i a')}</li>
                {$reason_html}
            </ul>
            <p>Please log in to your 'My Appointments' dashboard to approve or reject this request.</p>
        ";
        self::sendEmail( $approver_email, $subject, $message );
    }

    public static function notifyRequesterOfStatusUpdate( $requester_email, $data ) {
        $status_text = ucfirst($data['status']);
        $subject = "Your Appointment has been {$status_text}";
        $time = new \DateTime($data['start_time']);
        $message = "
            <h2>Appointment {$status_text}</h2>
            <p>An update on your appointment request:</p>
            <ul>
                <li><strong>With:</strong> {$data['approver_name']}</li>
                <li><strong>Time:</strong> {$time->format('F j, Y, g:i a')}</li>
                <li><strong>New Status:</strong> <strong>{$status_text}</strong></li>
            </ul>
        ";
        self::sendEmail( $requester_email, $subject, $message );
    }


    public static function notifyOfCancellation($requester, $approver, $appointment, $cancelled_by_role) {
        $time = new \DateTime($appointment->start_time);
        $appointment_details = "
            <ul>
                <li><strong>Approver:</strong> {$approver->display_name}</li>
                <li><strong>Requester:</strong> {$requester->display_name}</li>
                <li><strong>Time:</strong> {$time->format('F j, Y, g:i a')}</li>
            </ul>
        ";

        if ($cancelled_by_role === 'tan_requester') {
            $subject = 'An appointment has been cancelled';
            $body = "
                <h2>Appointment Cancelled</h2>
                <p>The following appointment has been cancelled by the requester:</p>
                {$appointment_details}
            ";
            self::sendEmail($approver->user_email, $subject, $body);
        }

        if ($cancelled_by_role === 'tan_approver') {
            $subject = 'Your appointment has been cancelled';
            $body = "
                <h2>Appointment Cancelled</h2>
                <p>Your upcoming appointment has been cancelled by the approver:</p>
                {$appointment_details}
            ";
            self::sendEmail($requester->user_email, $subject, $body);
        }
    }
}