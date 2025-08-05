<div class="wrap">
    <h1>Pending Approver Registrations</h1>
    <table class="wp-list-table widefat fixed striped users">
        <thead>
            <tr>
                <th scope="col">Username</th>
                <th scope="col">Email</th>
                <th scope="col">Designation</th>
                <th scope="col">Institute</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $pending_users ) ) : ?>
                <?php foreach ( $pending_users as $user ) : ?>
                    <tr>
                        <td><?php echo esc_html( $user->user_login ); ?></td>
                        <td><?php echo esc_html( $user->user_email ); ?></td>
                        <td><?php echo esc_html( get_user_meta( $user->ID, 'tan_designation', true ) ); ?></td>
                        <td><?php echo esc_html( get_user_meta( $user->ID, 'tan_institute', true ) ); ?></td>
                        <td>
                            <?php
                            // --- START OF THE FIX ---
                            // The 'page' parameter in the URL must match the slug of the menu page.
                            $approve_url = wp_nonce_url( admin_url( 'admin.php?page=tan-main-admin-page&action=approve&user_id=' . $user->ID ), 'tan_change_status_' . $user->ID );
                            $reject_url = wp_nonce_url( admin_url( 'admin.php?page=tan-main-admin-page&action=reject&user_id=' . $user->ID ), 'tan_change_status_' . $user->ID );
                            // --- END OF THE FIX ---
                            ?>
                            <a href="<?php echo esc_url( $approve_url ); ?>" class="button button-primary">Approve</a>
                            <a href="<?php echo esc_url( $reject_url ); ?>" class="button button-secondary">Reject</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="3">No pending approvals.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>