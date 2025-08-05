import { render } from "@wordpress/element";
import "./index.css"; // Add this line to import your new CSS file
import AvailabilityForm from "./components/AvailabilityForm";
import BookingForm from "./components/BookingForm"; // Import the new component
import MyAppointments from "./components/MyAppointments"; // Import the new component

document.addEventListener("DOMContentLoaded", () => {
  const approverRoot = document.getElementById("approver-portal-app");
  const requesterRoot = document.getElementById("booking-page-app");
  const myAppointmentsRoot = document.getElementById("my-appointments-app"); // Add this line

  if (approverRoot) {
    render(<AvailabilityForm />, approverRoot);
  } else if (requesterRoot) {
    render(<BookingForm />, requesterRoot);
  } else if (myAppointmentsRoot) {
    // Add this block
    render(<MyAppointments />, myAppointmentsRoot);
  }
});
