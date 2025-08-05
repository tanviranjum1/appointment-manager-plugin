<?php

namespace App\Services;

class RoleService {

    public static function registerRoles() {
        // Add tan_approver role
        add_role(
            'tan_approver',
            __( 'Approver', 'appointment-manager' ),
            array(
                'read' => true,
            )
        );

        // Add tan_requester role
        add_role(
            'tan_requester',
            __( 'Requester', 'appointment-manager' ),
            array(
                'read' => true,
            )
        );
    }
}