import { useState, useEffect } from "@wordpress/element";
import {
  fetchApprovers,
  fetchAvailabilityForApprover,
  createAppointment,
} from "../services/api";
import { formattedTime } from "../utils/formatters";

const BookingForm = () => {
  const [approvers, setApprovers] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedApprover, setSelectedApprover] = useState("");
  const [availability, setAvailability] = useState([]);
  const [isLoadingSlot, setIsLoadingSlot] = useState(false);
  const [message, setMessage] = useState("");
  const [reason, setReason] = useState("");

  useEffect(() => {
    setIsLoading(true);
    fetchApprovers()
      .then(setApprovers)
      .finally(() => setIsLoading(false));
  }, []);

  const loadAvailability = () => {
    if (!selectedApprover) {
      setAvailability([]);
      return;
    }
    setIsLoadingSlot(true);
    fetchAvailabilityForApprover(selectedApprover)
      .then(setAvailability)
      .finally(() => setIsLoadingSlot(false));
  };

  useEffect(loadAvailability, [selectedApprover]);

  const handleBooking = (slot) => {
    if (!reason.trim()) {
      alert("Please provide a reason for your appointment before booking.");
      return;
    }

    if (!window.confirm(`Confirm booking for this time slot?`)) {
      return;
    }

    createAppointment({
      approver_id: slot.approver_id,
      start_time: slot.start_time,
      end_time: slot.end_time,
      reason: reason,
    })
      .then(() => {
        setMessage("Appointment requested successfully!");
        setReason("");
        setAvailability((prev) => prev.filter((s) => s.id !== slot.id));
      })
      .catch((error) => {
        setMessage(`Error: ${error.message || "An unknown error occurred."}`);
        if (error.code === "double_booking") {
          setAvailability((prev) => prev.filter((s) => s.id !== slot.id));
        }
      });
  };

  return (
    <div>
      <h3>Book an Appointment</h3>

      {message && (
        <div
          className={`alert ${
            message.startsWith("Error:") ? "alert-danger" : "alert-success"
          }`}
        >
          {message}
        </div>
      )}

      <div className="mb-3">
        <label htmlFor="tan-select-approver" className="form-label">
          <strong>1. Select an Approver:</strong>
        </label>
        {isLoading ? (
          <p>Loading approvers...</p>
        ) : (
          <select
            id="tan-select-approver"
            className="form-select"
            value={selectedApprover}
            onChange={(e) => setSelectedApprover(e.target.value)}
          >
            <option value="">-- Choose an Approver --</option>
            {approvers.map((approver) => (
              <option key={approver.id} value={approver.id}>
                {approver.name}
              </option>
            ))}
          </select>
        )}
      </div>

      {selectedApprover && (
        <div className="mt-4">
          <div className="mb-3">
            <label htmlFor="tan-reason" className="form-label">
              <strong>
                2. Provide a Reason for Your Appointment (Required):
              </strong>
            </label>
            <textarea
              id="tan-reason"
              className="form-control"
              value={reason}
              onChange={(e) => setReason(e.target.value)}
              rows="4"
              placeholder="e.g., Discuss project proposal"
              required
            ></textarea>
          </div>

          <h3 className="mt-4">
            <strong>3. Choose an Available Slot:</strong>
          </h3>
          {isLoadingSlot ? (
            <p>Loading slots...</p>
          ) : availability.length > 0 ? (
            <ul className="list-group">
              {availability.map((slot) => (
                <li
                  key={slot.id}
                  className="list-group-item tan-availability-slot"
                >
                  <span>{formattedTime(slot)}</span>
                  <button
                    onClick={() => handleBooking(slot)}
                    className="btn btn-primary btn-sm"
                  >
                    Book Now
                  </button>
                </li>
              ))}
            </ul>
          ) : (
            <p>No available slots for this approver.</p>
          )}
        </div>
      )}
    </div>
  );
};

export default BookingForm;
