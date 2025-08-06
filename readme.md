# Appointment Management System - WordPress Plugin

A flexible, reusable WordPress plugin that provides a complete system for managing appointments within different contexts (e.g., School, Hospital, Court). It allows certain users ("Approvers") to set their availability, and other users ("Requesters") from the same context to book appointments. The system includes an admin approval workflow and a detailed appointment cancellation workflow.

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
- **How it works:** Only logged-in, approved users with the "Approver" role can see this page.

### 3. Book an Appointment (For Requesters Only)

- **Purpose:** The main interface for Requesters to book an appointment.
- **Shortcode:** `[tan_booking]`
- **How it works:** A logged-in Requester can select from a list of Approvers who are in the **same context**. They can then see the available slots and request an appointment after providing a mandatory reason.

### 4. My Appointments (For Both Roles)

- **Purpose:** A unified dashboard to view and manage appointments.
- **Shortcode:** `[tan_my_appointments]`
- **How it works:**
  - **If you are an Approver:** This page lists your incoming appointments. You can "Approve," "Reject," or "Cancel" pending requests. You can also cancel already approved appointments.
  - **If you are a Requester:** This page lists your sent requests and their status. You can cancel a **pending** appointment, but only if it is more than 24 hours away.
  - If an appointment is cancelled, the page will show who performed the cancellation.

### 5. Admin Management Area

- **Purpose:** For Site Administrators to manage the system.
- **Location:** WordPress Admin -> Appointment Admin
- **How it works:** This menu contains three pages:
  - **Pending Approvals:** A queue to approve or reject new Approver registrations.
  - **All Appointments:** A master log of every appointment in the system.
  - **Setup Guide:** A helpful guide for admins on how to create the necessary pages with shortcodes.

## For Developers: Getting Started & Contributing

This section provides instructions for setting up a development environment and guidelines for adding new features.

### Prerequisites

- A local WordPress installation.
- [Node.js and npm](https://nodejs.org/en/) installed on your computer.
- [Git](https://git-scm.com/downloads) installed on your computer.

### Setup Instructions

1.  Clone this repository into your WordPress `wp-content/plugins/` directory.
2.  Navigate to the plugin's `frontend/` directory in your terminal: `cd wp-content/plugins/appointment-manager/frontend/`.
3.  Install JavaScript packages: `npm install`.
4.  For active development, run: `npm run start`.
5.  To create a production build, run: `npm run build`.
6.  Activate the plugin in WordPress.
7.  **Crucially:** Go to **Settings -> Permalinks** and click "Save Changes" to register the API routes. Deactivate and reactivate the plugin if database changes are needed.

---

## File & Directory Structure

Here is a breakdown of the purpose of each file and directory in the plugin.

```
appointment-manager/
├── appointment-manager.php         // Main plugin bootstrap file; initializes all components and defines contexts.
│
├── app/                          // Contains all core Object-Oriented PHP application logic.
│   ├── Controllers/              // Handles REST API requests and business logic.
│   ├── Migrations/               // Scripts for creating and altering database tables.
│   ├── Models/                   // Classes that handle direct database interactions (CRUD).
│   │   ├── Appointment.php
│   │   └── Availability.php
│   └── Services/                 // Contains specialized, reusable services.
│       ├── EmailService.php
│       └── RoleService.php
│
├── includes/                     // WordPress-specific integration classes.
│   ├── class-activator.php       // Runs the activation sequence.
│   └── class-shortcodes.php      // Defines all shortcodes and enqueues assets.
│
├── templates/                    // Simple PHP files for rendering server-side HTML views.
│   ├── admin-all-appointments.php
│   ├── admin-approvals.php
│   ├── admin-setup-guide.php
│   └── registration-form.php
│
└── frontend/                     // Contains the entire React frontend application.
    ├── build/                    // (Ignored by Git) Compiled JavaScript/CSS output.
    ├── node_modules/             // (Ignored by Git) Node.js package dependencies.
    └── src/
        ├── components/           // React components focused on rendering UI and managing state.
        │   ├── AvailabilityForm.jsx
        │   ├── BookingForm.jsx
        │   └── MyAppointments.jsx
        ├── services/             // Handles all communication with the backend REST API.
        │   └── api.js
        ├── utils/                // Contains reusable helper functions (e.g., for formatting).
        │   └── formatters.js
        ├── index.css             // Custom CSS styles for the components.
        └── index.js              // Main entry point for the React application.
```
