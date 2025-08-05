# Appointment Management System - WordPress Plugin

A flexible, reusable WordPress plugin that provides a complete system for managing appointments within different contexts (e.g., School, Hospital, Court). It allows certain users ("Approvers") to set their availability, and other users ("Requesters") from the same context to book appointments. The system includes an admin approval workflow for new Approvers, a full email notification system, and a detailed appointment cancellation workflow.

## For Users: How to Use the Plugin

To use the Appointment Management System on your WordPress site, create the following pages and add their corresponding shortcodes to the page content.

### 1. Registration Page

- **Purpose:** Allows new users to sign up as either a "Requester" or an "Approver" within a specific context.
- **Shortcode:** `[tan_registration]`
- **How it works:**
  - All new users must select a context (e.g., "Hospital").
  - New Requesters are automatically approved.
  - New Approvers must provide their Designation and Institute, and are then placed in a pending queue for an administrator to approve.

### 2. Approver Portal (For Approvers Only)

- **Purpose:** The main dashboard for Approvers to set their available time slots for appointments.
- **Shortcode:** `[tan_approver_portal]`
- **How it works:** Only logged-in, approved users with the "Approver" role can see this page. They can input dates and times to create their schedule.

### 3. Book an Appointment (For Requesters Only)

- **Purpose:** The main interface for Requesters to book an appointment.
- **Shortcode:** `[tan_booking]`
- **How it works:** A logged-in Requester can select from a list of Approvers who are in the **same context**. They can then see the available slots and request an appointment after providing a mandatory reason.

### 4. My Appointments (For Both Roles)

- **Purpose:** A unified dashboard to view and manage appointments.
- **Shortcode:** `[tan_my_appointments]`
- **How it works:**
  - **If you are an Approver:** This page lists your incoming appointments. You can "Approve," "Reject," or "Cancel" pending requests. You can also cancel already approved appointments at any time.
  - **If you are a Requester:** This page lists your sent requests and their status. You can cancel a **pending** appointment, but only if it is more than 24 hours away.
  - If an appointment is cancelled, the page will show who performed the cancellation.

### 5. Admin Management Area

- **Purpose:** For Site Administrators to manage the system.
- **Location:** WordPress Admin -> Appointment Admin
- **How it works:** This menu contains two pages:
  - **Pending Approvals:** A queue to approve or reject new Approver registrations.
  - **All Appointments:** A master log of every appointment in the system, showing its status, participants, and other details.

## For Developers: Getting Started & Contributing

This section provides instructions for setting up a development environment and guidelines for adding new features.

### Prerequisites

- A local WordPress installation.
- [Node.js and npm](https://nodejs.org/en/) installed on your computer.
- [Git](https://git-scm.com/downloads) installed on your computer.

### Setup Instructions

1.  Clone this repository into your WordPress `wp-content/plugins/` directory.
2.  Navigate to the plugin's `frontend/` directory in your terminal: `cd wp-content/plugins/appointment-manager/frontend/`.
3.  Install JavaScript packages. This includes the libraries for the upcoming visual calendar feature.
    ```bash
    npm install
    npm install --save @fullcalendar/react @fullcalendar/daygrid @fullcalendar/timegrid @fullcalendar/interaction
    ```
4.  For active development, run: `npm run start`.
5.  To create a production build, run: `npm run build`.
6.  Activate the plugin in WordPress.
7.  **Crucially:** Go to **Settings -> Permalinks** and click "Save Changes" to register the API routes. Deactivate and reactivate the plugin if database changes are needed.

### How to Add a New Feature

This plugin uses a decoupled architecture with a PHP backend providing a REST API and a React frontend for the UI. To add a new feature:

1.  **Database (if needed):** Create a new migration file in `app/Migrations/` and add it to `includes/class-activator.php`. Deactivate/reactivate the plugin to run it.
2.  **Backend (API):** Create or update a Controller in `app/Controllers/` to handle data logic and register a new REST API endpoint.
3.  **Frontend (UI):** Create a new React component in `frontend/src/components/`. This component will use `fetch` to communicate with your new API endpoint.
4.  **Display:** Add a new shortcode in `includes/class-shortcodes.php` to render the `div` for your React component and enqueue the necessary scripts.

---

## File & Directory Structure

Here is a breakdown of the purpose of each file and directory in the plugin.

```
appointment-manager/
├── appointment-manager.php         // Main plugin bootstrap file; initializes all components and defines contexts.
├── uninstall.php                 // Code to clean up database tables and roles on plugin deletion.
│
├── app/                          // Contains all core Object-Oriented PHP application logic.
│   ├── Controllers/              // Handles REST API requests and business logic.
│   │   ├── AdminApprovalController.php // Logic for admin pages (Pending Approvals, All Appointments).
│   │   ├── AppointmentController.php // API for fetching, updating, and cancelling appointments.
│   │   ├── AvailabilityController.php  // API for an approver's availability.
│   │   ├── BookingController.php     // API for the context-aware requester booking process.
│   │   └── UserController.php        // Handles user registration and saving all meta (status, context, etc.).
│   ├── Migrations/               // Scripts for creating and altering database tables.
│   │   ├── AddCancelledByToAppointmentsTable.php
│   │   ├── AddReasonToAppointmentsTable.php
│   │   ├── CreateAppointmentsTable.php
│   │   └── CreateAvailabilityTable.php
│   └── Services/                 // Contains specialized, reusable services.
│       ├── EmailService.php        // Manages the formatting and sending of all emails.
│       └── RoleService.php         // Handles creating custom user roles on activation.
│
├── includes/                     // WordPress-specific integration classes.
│   ├── class-activator.php       // Runs the activation sequence (creates roles, tables, migrations).
│   └── class-shortcodes.php      // Defines all shortcodes used by the plugin.
│
├── templates/                    // Simple PHP files for rendering server-side HTML views.
│   ├── admin-all-appointments.php// The view for the "All Appointments" admin table.
│   ├── admin-approvals.php       // The view for the admin approval table.
│   └── registration-form.php     // The HTML for the user registration form.
│
└── frontend/                     // Contains the entire React frontend application.
    ├── build/                    // (Ignored by Git) Compiled JavaScript/CSS output goes here.
    ├── node_modules/             // (Ignored by Git) Holds all Node.js package dependencies.
    ├── src/
    │   ├── components/           // Reusable React components.
    │   │   ├── AvailabilityForm.jsx  // UI for an approver to set their schedule.
    │   │   ├── BookingForm.jsx     // UI for a requester to book an appointment.
    │   │   └── MyAppointments.jsx    // UI for the "My Appointments" dashboard.
    │   └── index.js              // Main entry point for the React application.
    └── package.json              // Defines project dependencies and scripts for the frontend.
```
