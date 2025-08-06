<div class="wrap">
    <h1 class="wp-heading-inline">Appointment System Settings</h1>
    <hr class="wp-header-end">

    <div class="card mt-4">
        <div class="card-body">
            <form method="post" action="options.php">
                <?php
                settings_fields( 'tan_settings_group' );
                do_settings_sections( 'tan-settings-page' );
                submit_button('Save Contexts', 'btn btn-primary');
                ?>
            </form>
        </div>
    </div>
</div>