<div class="wrap">
    <h1>Appointment Management System - Setup Guide</h1>

    <div class="card">
        <h2>Welcome!</h2>
        <p>
            Welcome to the Appointment Management System. To get started, you need to create a few pages on your site so that users can register, book appointments, and manage their schedules. Please follow the steps below.
        </p>
    </div>

    <div class="card">
        <h2>Step 1: Create the Registration Page</h2>
        <p>This page allows new users to sign up as either a "Requester" or an "Approver".</p>
        <ol>
            <li>Create a new Page and title it "Register" (or similar).</li>
            <li>Paste the following shortcode into the page's content block.</li>
        </ol>
        <p><strong>Shortcode:</strong> <input type="text" readonly value="[tan_registration]" onclick="this.select();"></p>
    </div>

    <div class="card">
        <h2>Step 2: Create the Booking Page</h2>
        <p>This page is where "Requesters" (e.g., patients, students) will go to book an appointment with an available "Approver".</p>
        <ol>
            <li>Create a new Page and title it "Book an Appointment" (or similar).</li>
            <li>Paste the following shortcode into the page's content block.</li>
        </ol>
        <p><strong>Shortcode:</strong> <input type="text" readonly value="[tan_booking]" onclick="this.select();"></p>
    </div>

    <div class="card">
        <h2>Step 3: Create the Approver Portal</h2>
        <p>This page is where "Approvers" (e.g., doctors, teachers) will go to set their weekly availability.</p>
        <ol>
            <li>Create a new Page and title it "Approver Portal" (or similar).</li>
            <li>Paste the following shortcode into the page's content block.</li>
        </ol>
        <p><strong>Shortcode:</strong> <input type="text" readonly value="[tan_approver_portal]" onclick="this.select();"></p>
    </div>

     <div class="card">
        <h2>Step 4: Create the "My Appointments" Dashboard</h2>
        <p>This page serves as a dashboard for both user roles to view and manage their appointments.</p>
        <ol>
            <li>Create a new Page and title it "My Appointments" (or similar).</li>
            <li>Paste the following shortcode into the page's content block.</li>
        </ol>
        <p><strong>Shortcode:</strong> <input type="text" readonly value="[tan_my_appointments]" onclick="this.select();"></p>
    </div>
</div>