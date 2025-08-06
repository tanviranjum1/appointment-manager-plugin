<div class="wrap">
    <h1 class="wp-heading-inline">Pending Approver Role Assign</h1>
    <hr class="wp-header-end">
    
    <table class="table table-striped table-hover">
        <thead>
            <tr class="table-light">
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
                            $approve_url = wp_nonce_url( admin_url( 'admin.php?page=tan-main-admin-page&action=approve&user_id=' . $user->ID ), 'tan_change_status_' . $user->ID );
                            $reject_url = wp_nonce_url( admin_url( 'admin.php?page=tan-main-admin-page&action=reject&user_id=' . $user->ID ), 'tan_change_status_' . $user->ID );
                            ?>
                            <a href="<?php echo esc_url( $approve_url ); ?>" class="btn btn-success btn-sm">Approve</a>
                            <a href="<?php echo esc_url( $reject_url ); ?>" class="btn btn-warning btn-sm">Reject</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5">
                        <div class="alert alert-info mb-0">No pending approvals.</div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>