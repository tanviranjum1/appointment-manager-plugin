<div class="wrap">
    <h1>All Appointments</h1>
    <p>This page shows a complete log of all appointments in the system.</p>



    <form method="get">
        <input type="hidden" name="page" value="tan-all-appointments" />
        <div style="display: flex; gap: 15px; margin-bottom: 20px;">
            <select name="filter_status">
                <option value="">All Statuses</option>
                <?php foreach ($all_statuses as $status) : ?>
                    <option value="<?php echo esc_attr($status); ?>" <?php selected(isset($_GET['filter_status']) ? $_GET['filter_status'] : '', $status); ?>>
                        <?php echo esc_html(ucfirst($status)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="filter_approver">
                <option value="">All Approvers</option>
                <?php foreach ($all_approvers as $approver) : ?>
                    <option value="<?php echo esc_attr($approver->ID); ?>" <?php selected(isset($_GET['filter_approver']) ? $_GET['filter_approver'] : 0, $approver->ID); ?>>
                        <?php echo esc_html($approver->display_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <input type="submit" class="button" value="Filter">
        </div>
    </form>
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