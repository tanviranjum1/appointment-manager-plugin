<?php
// Display errors if they exist
if ( isset( $_GET['reg_error'] ) ) {
    echo '<p style="color:red;">' . esc_html( urldecode( $_GET['reg_error'] ) ) . '</p>';
}
?>

<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post">
    
    <p>
        <label for="tan_username">Username</label>
        <input type="text" name="tan_username" id="tan_username" required>
    </p>

    <p>
        <label for="tan_email">Email</label>
        <input type="email" name="tan_email" id="tan_email" required>
    </p>

    <p>
        <label for="tan_password">Password</label>
        <input type="password" name="tan_password" id="tan_password" required>
    </p>

    <p>
        <label for="tan_role">Register As</label>
        <select name="tan_role" id="tan_role" required onchange="toggleApproverFields(this.value)">
            <option value="tan_requester">Requester (e.g., Student, Patient)</option>
            <option value="tan_approver">Approver (e.g., Teacher, Doctor)</option>
        </select>
    </p>

    <div id="tan-approver-fields" style="display: none;">
        <p>
            <label for="tan_designation">Designation / Position</label>
            <input type="text" name="tan_designation" id="tan_designation">
        </p>
        <p>
            <label for="tan_institute">Institute / Organization</label>
            <input type="text" name="tan_institute" id="tan_institute">
        </p>
    </div>

    <?php wp_nonce_field( 'tan_registration_nonce', 'tan_registration_nonce_field' ); ?>
    <input type="hidden" name="tan_registration_form" value="1">
    
    <p>
        <input type="submit" value="Register">
    </p>
</form>

<script type="text/javascript">
    function toggleApproverFields(role) {
        var approverFields = document.getElementById('tan-approver-fields');
        if (role === 'tan_approver') {
            approverFields.style.display = 'block';
        } else {
            approverFields.style.display = 'none';
        }
    }
</script>