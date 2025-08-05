<div class="wrap">
    <h1>All Appointments</h1>
    <p>This page shows a complete log of all appointments in the system.</p>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col">Requester</th>
                <th scope="col">Approver</th>
                <th scope="col">Appointment Time</th>
                <th scope="col">Reason</th>
                <th scope="col">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $all_appointments ) ) : ?>
                <?php foreach ( $all_appointments as $app ) : ?>
                    <tr>
                        <td><?php echo esc_html( $app->requester_name ); ?></td>
                        <td><?php echo esc_html( $app->approver_name ); ?></td>
                        <td><?php echo esc_html( (new DateTime($app->start_time))->format('F j, Y, g:i a') ); ?></td>
                        <td><?php echo esc_html( $app->reason ); ?></td>
                        <td style="text-transform: capitalize; font-weight: bold;">
                            <?php echo esc_html( $app->status ); ?>
                            <?php if ($app->status === 'cancelled' && !empty($app->cancelled_by_role)) : ?>
                                <br>
                                <small>(by <?php echo esc_html(str_replace('tan_', '', $app->cancelled_by_role)); ?>)</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5">No appointments found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>