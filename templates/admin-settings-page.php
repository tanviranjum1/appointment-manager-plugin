<div class="wrap">
    <h1>Appointment System Settings</h1>
    <form method="post" action="options.php">
        <?php
        settings_fields( 'tan_settings_group' );
        do_settings_sections( 'tan-settings-page' );
        submit_button();
        ?>
    </form>
</div>