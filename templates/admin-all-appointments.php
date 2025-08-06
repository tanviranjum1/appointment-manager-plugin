<div class="wrap">
    <h1 class="wp-heading-inline">All Appointments</h1>
    <p>This page shows a complete log of all appointments in the system.</p>
    <hr class="wp-header-end">

    <form method="get">
        <input type="hidden" name="page" value="tan-all-appointments" />
        <div class="d-flex align-items-center gap-2 mb-3 p-3 bg-light border rounded">
            <select name="filter_status" class="form-select w-auto">
                <option value="">All Statuses</option>
                <?php foreach ($all_statuses as $status) : ?>
                    <option value="<?php echo esc_attr($status); ?>" <?php selected(isset($_GET['filter_status']) ? $_GET['filter_status'] : '', $status); ?>>
                        <?php echo esc_html(ucfirst($status)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="filter_approver" class="form-select w-auto">
                <option value="">All Approvers</option>
                <?php foreach ($all_approvers as $approver) : ?>
                    <option value="<?php echo esc_attr($approver->ID); ?>" <?php selected(isset($_GET['filter_approver']) ? $_GET['filter_approver'] : 0, $approver->ID); ?>>
                        <?php echo esc_html($approver->display_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <input type="submit" class="btn btn-secondary" value="Filter">
        </div>
    </form>

    <table class="table table-striped table-hover">
        <thead>
            <tr class="table-light">
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
                        <td>
                            <span class="badge bg-<?php echo esc_attr($app->status === 'approved' ? 'success' : ($app->status === 'pending' ? 'warning text-dark' : 'danger')); ?>">
                                <?php echo esc_html( $app->status ); ?>
                            </span>
                            <?php if ($app->status === 'cancelled' && !empty($app->cancelled_by_role)) : ?>
                                <br>
                                <small>(by <?php echo esc_html(str_replace('tan_', '', $app->cancelled_by_role)); ?>)</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5">
                        <div class="alert alert-info mb-0">No appointments found.</div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>