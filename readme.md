# Appointment Management System - WordPress Plugin

A flexible, reusable WordPress plugin that provides a complete system for managing appointments within different contexts (e.g., School, Hospital, Court). It allows designated "Approvers" (like doctors or teachers) to set their availability, and "Requesters" (like patients or students) from the same context to book appointments.

The system features a modern, decoupled architecture with a PHP OOP backend providing a REST API, and a React.js frontend for a dynamic user experience.

## Key Features

- **Context-Based System**: Isolate Approvers and Requesters into distinct groups (e.g., a patient can only see doctors, not teachers).
- **Role-Based Permissions**: Two custom roles (Approver and Requester) with specific capabilities.
- **Admin Dashboards**: A full suite of admin panels to approve new users, manage settings, and view all appointments across the system.
- **Dynamic Frontend**: A responsive and user-friendly interface built with React and Bootstrap.
- **Appointment Lifecycle Management**: A complete workflow including booking, approval, rejection, and cancellation

---

## Usage & Shortcodes

To make the plugin functional, an administrator needs to create pages and place the following shortcodes in the page content. A setup guide is also available in the admin dashboard at Appointment Admin -> Setup Guide.

Registration Page: [tan_registration]
Approver Availability Page: [tan_approver_portal]
Booking Page: [tan_booking]
"My Appointments" Dashboard: [tan_my_appointments]

## Installation

You can install this plugin in two ways:
ZIP Upload (Recommended):
Create a .zip file of the appointment-manager directory. Before zipping, ensure you remove any files and folders listed in the .distignore file (like .git, .vscode, node_modules, etc.).
In your WordPress admin dashboard, navigate to Plugins -> Add New -> Upload Plugin.
Choose the .zip file you created and click "Install Now".

Manual Upload:
Upload the entire appointment-manager folder to the wp-content/plugins/ directory of your WordPress installation.
After installation, navigate to the Plugins page and activate the "Appointment Management System".

Crucially: Go to Settings -> Permalinks and click "Save Changes" to ensure all API routes are correctly registered.

## Database Structure

The plugin maintains a clean database structure with two custom tables:

| Table                | Description                                                              |
| -------------------- | ------------------------------------------------------------------------ |
| `wp_am_appointments` | Stores appointment metadata, statuses, `cancelled_by` & `reason` columns |
| `wp_am_availability` | Manages time slots per Approver                                          |
| `wp_usermeta`        | Stores user context, institute & designation as user meta                |
| `wp_options`         | Stores a custom option tan_appointment_contexts list                     |

**Migrations**: Table creation and updates are handled by migration scripts in `app/Migrations/`, which run automatically on plugin activation.

## Demonstration & Usage Guide

This guide demonstrates the complete workflow of the plugin, from initial setup by an administrator to the day-to-day usage by Approvers and Requesters.

### Admin Setup and Management

1. **Plugin Activation**  
   First, the administrator installs the plugin and activates it from the main WordPress "Plugins" page.  
   ![Plugin Activation](assets/screenshots/screenshot-1.png?raw=true)

2. **Configure Settings**  
   The administrator navigates to Appointment Admin -> Settings to define the different "contexts" for the system (e.g., Hospital, School). This is a crucial step that determines how the plugin will be used.  
   ![Configure Settings](assets/screenshots/screenshot-2.png?raw=true)

3. **Create Frontend Pages**  
   The administrator creates the four necessary pages for the plugin's frontend interface and places the corresponding shortcode inside each one (e.g., `[tan_registration]` for the Register page).  
   ![Create Frontend Pages](assets/screenshots/screenshot-3.png?raw=true)

### User Registration and Management

4. **New User Registration**  
   A new user visits the public registration page to sign up. Here, they can register as an "Approver" (like a professor), providing their designation and institute, or as a "Requester" (like a student), selecting the appropriate context.  
   ![New User Registration](assets/screenshots/screenshot-4.png?raw=true)
   ![New User Registration](assets/screenshots/screenshot-04.png?raw=true)

5. **Admin Approval Queue**  
   After an Approver registers, they appear in the Appointment Admin -> Pending Approvers queue. The administrator can see their details and has the option to "Approve" or "Reject" their application. Once approved, they will be able to add their availability.  
   ![Admin Approval Queue](assets/screenshots/screenshot-5.png?raw=true)

### The Approver’s Workflow

6. **Setting Availability**  
   After being approved, an Approver can go to the "Approver Portal." Here they can select a date and add the specific time slots when they are available for appointments.  
   ![Setting Availability](assets/screenshots/screenshot-6.png?raw=true)

### The Requester’s Workflow

7. **Booking an Appointment**  
   A Requester navigates to the "Book an Appointment" page. They can see a list of Approvers from their same context, view their available time slots, and click "Book Now" after providing a reason.  
   ![Booking an Appointment](assets/screenshots/screenshot-7.png?raw=true)

8. **Successful Booking Confirmation**  
   After clicking "Book Now," the user receives an instant confirmation message, and the booked slot is immediately removed from the list to prevent double-booking.  
   ![Booking Confirmation](assets/screenshots/screenshot-8.png?raw=true)

### Appointment Management

9. **Requester’s "My Appointments" Page**  
   The Requester can visit their "My Appointments" page at any time to track the status of all their sent requests (Pending, Approved, Rejected, etc.).  
   ![Requester Appointments](assets/screenshots/screenshot-9.png?raw=true)

10. **Approver’s "My Appointments" Page**  
    The Approver uses the same "My Appointments" page but has different actions available. They can manage their incoming requests by approving or rejecting them.  
    ![Approver Appointments](assets/screenshots/screenshot-10.png?raw=true)

### Data and System Overview

11. **Admin Appointment Log**  
    The administrator can get a powerful overview by navigating to Appointment Admin -> All Appointments. Here they can see a filterable master log of every appointment in the system.  
    ![Admin Appointment Log](assets/screenshots/screenshot-11.png?raw=true)

12. **Database Structure**  
    The plugin creates two custom tables (`wp_am_appointments` and `wp_am_availability`) to store its data. It also adds new roles (`approver`, `requester`) and saves custom user data (institute, designation, context) to the `wp_usermeta` table.  
    ![Database Structure](assets/screenshots/screenshot-12.png?raw=true)

---

## For Developers: Getting Started

This section provides instructions for setting up a local development environment.

### Prerequisites

- A local WordPress installation
- Node.js and npm
- Git
- Xampp for apache and mysql server

### Setup Instructions

1. Clone this repository into your WordPress `wp-content/plugins/` directory.
2. Navigate to the plugin's `frontend/` directory in your terminal:
   ```bash
   cd path/to/wp-content/plugins/appointment-manager/frontend/
   ```
3. npm install # Install dependencies
   npm run build # Create optimized build
   In WordPress admin:
   Activate the plugin
   Crucially: Go to Settings → Permalinks and click "Save Changes" to flush rewrite rules and activate REST API endpoints
