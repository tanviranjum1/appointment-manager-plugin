<div class="tan-plugin-container">
    <?php

    if ( isset($_GET['registration']) && $_GET['registration'] === 'success' ) {
        echo '<div class="alert alert-success">Registration successful! Please login.</div>';
    }

    if ( isset( $_GET['reg_error'] ) ) {
        echo '<div class="alert alert-danger">' . esc_html( urldecode( $_GET['reg_error'] ) ) . '</div>';
    }
    ?>

    <form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post">
        
        <div class="mb-3">
            <label for="tan_context" class="form-label">Select Your Context</label>
            <select name="tan_context" id="tan_context" class="form-select" required>
                <option value="">-- Choose a Context --</option>
                <?php 
                $contexts_option = get_option('tan_appointment_contexts');
                $contexts = is_array($contexts_option) ? $contexts_option : [];
                foreach ($contexts as $context) {
                    echo '<option value="' . esc_attr($context) . '">' . esc_html($context) . '</option>';
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="tan_username" class="form-label">Username</label>
            <input type="text" name="tan_username" id="tan_username" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="tan_email" class="form-label">Email</label>
            <input type="email" name="tan_email" id="tan_email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="tan_password" class="form-label">Password</label>
            <input type="password" name="tan_password" id="tan_password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="tan_role" class="form-label">Register As</label>
            <select name="tan_role" id="tan_role" class="form-select" required onchange="toggleApproverFields(this.value)">
                <option value="tan_requester">Requester</option>
                <option value="tan_approver">Approver</option>
            </select>
        </div>
        
        <div id="tan-approver-fields" style="display: none;" class="card card-body mb-3">
            <div class="mb-3">
                <label for="tan_designation" class="form-label">Designation / Position</label>
                <input type="text" name="tan_designation" id="tan_designation" class="form-control">
            </div>
            <div class="mb-3">
                <label for="tan_institute" class="form-label">Institute / Organization</label>
                <input type="text" name="tan_institute" id="tan_institute" class="form-control">
            </div>
        </div>

        <?php wp_nonce_field( 'tan_registration_nonce', 'tan_registration_nonce_field' ); ?>
        <input type="hidden" name="tan_registration_form" value="1">
        
        <div class="d-grid">
            <input type="submit" value="Register" class="btn btn-primary">
        </div>
    </form>
</div>

<script type="text/javascript">
    function toggleApproverFields(role) {
        var approverFields = document.getElementById('tan-approver-fields');
        if (role === 'tan_approver') {
            approverFields.style.display = 'block';
        } else {
            approverFields.style.display = 'none';
        }
    }
    // Trigger on page load in case of errors
    document.addEventListener('DOMContentLoaded', function() {
        var roleSelect = document.getElementById('tan_role');
        if(roleSelect) {
            toggleApproverFields(roleSelect.value);
        }
    });
</script>