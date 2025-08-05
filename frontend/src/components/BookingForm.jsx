import { useState, useEffect } from "@wordpress/element";

const BookingForm = () => {
  const [approvers, setApprovers] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedApprover, setSelectedApprover] = useState("");
  const [availability, setAvailability] = useState([]);
  const [isLoadingSlot, setIsLoadingSlot] = useState(false);
  const [message, setMessage] = useState("");
  const [reason, setReason] = useState("");
  // 1. Fetch all approvers when the component loads
  useEffect(() => {
    setIsLoading(true);
    fetch(tan_data.api_url + "approvers", {
      headers: { "X-WP-Nonce": tan_data.nonce },
    })
      .then((response) => response.json())
      .then((data) => {
        setApprovers(data);
        setIsLoading(false);
      });
  }, []);

  const fetchAvailabilityForSelectedApprover = () => {
    if (!selectedApprover) {
      setAvailability([]);
      return;
    }
    setIsLoadingSlot(true);
    fetch(tan_data.api_url + `availability/${selectedApprover}`, {
      headers: { "X-WP-Nonce": tan_data.nonce },
    })
      .then((response) => response.json())
      .then((data) => {
        setAvailability(data);
        setIsLoadingSlot(false);
      });
  };

  // 2. Fetch availability when an approver is selected
  // useEffect(() => {
  //           fetchAvailabilityForSelectedApprover();

  //   if (!selectedApprover) {
  //     setAvailability([]);
  //     return;
  //   }
  //   setMessage("");
  //   fetch(tan_data.api_url + `availability/${selectedApprover}`, {
  //     headers: { "X-WP-Nonce": tan_data.nonce },
  //   })
  //     .then((response) => response.json())
  //     .then(setAvailability);
  // }, [selectedApprover]);

  // Update the original useEffect to use this new function
  useEffect(() => {
    fetchAvailabilityForSelectedApprover();
  }, [selectedApprover]);

  // 3. Handle the booking action
  const handleBooking = (slot) => {
    // 1. Check if the reason is empty
    if (!reason.trim()) {
      alert("Please provide a reason for your appointment before booking.");
      return; // Stop the function if reason is empty
    }

    if (!window.confirm(`Confirm booking for this time slot?`)) {
      return;
    }

    fetch(tan_data.api_url + "appointments", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": tan_data.nonce,
      },
      body: JSON.stringify({
        approver_id: slot.approver_id,
        start_time: slot.start_time,
        end_time: slot.end_time,
        reason: reason, // Send the reason
      }),
    })
      .then((response) => {
        // Check if the response is not OK (e.g., 409 Conflict, 400 Bad Request)
        if (!response.ok) {
          // Return the error response to be caught by the next .catch() or .then()
          return response.json().then((err) => Promise.reject(err));
        }
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          setMessage("Appointment requested successfully!");
          // Refetch availability to remove the booked slot from the list
          setReason(""); // Clear the reason field after successful booking
          setAvailability((prev) => prev.filter((s) => s.id !== slot.id));
        } else {
          setMessage(`Error: ${data.message}`);
        }
      })
      .catch((error) => {
        // This will now catch both network errors and server-sent errors
        setMessage(`Error: ${error.message || "An unknown error occurred."}`);
        // --- THIS IS THE FIX ---
        // On any error, refresh the entire list of slots from the server
        // to get the most up-to-date availability.
        // If the error is a double booking, remove the stale slot from the UI
        if (error.code === "double_booking") {
          setAvailability((prev) => prev.filter((s) => s.id !== slot.id));
        }
      });
  };

  return (
    <div>
      <h2>Book an Appointment</h2>

      {message && (
        <p style={{ color: message.startsWith("Error:") ? "red" : "green" }}>
          {message}
        </p>
      )}

      <div>
        <label>
          <strong>1. Select an Approver:</strong>
        </label>
        {isLoading ? (
          <p>Loading approvers...</p>
        ) : (
          <select
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
        <div style={{ marginTop: "20px" }}>
          <h3>
            <strong>
              2. Provide a Reason for Your Appointment (Required):
            </strong>
          </h3>
          <textarea
            value={reason}
            onChange={(e) => setReason(e.target.value)}
            rows="4"
            style={{ width: "100%", marginBottom: "20px" }}
            placeholder="e.g., Discuss why you need this appointment..."
            required
          ></textarea>

          <h3>
            <strong>3. Choose an Available Slot:</strong>
          </h3>

          {isLoadingSlot ? (
            <p>Loading slots...</p>
          ) : availability.length > 0 ? (
            <ul style={{ listStyle: "none", paddingLeft: 0 }}>
              {availability.map((slot) => {
                const startTime = new Date(slot.start_time);
                const endTime = new Date(slot.end_time);
                const timeOptions = {
                  hour: "numeric",
                  minute: "2-digit",
                  hour12: true,
                };
                const formattedTime = `${startTime.toLocaleDateString()}, ${startTime.toLocaleTimeString(
                  [],
                  timeOptions
                )} - ${endTime.toLocaleTimeString([], timeOptions)}`;

                return (
                  <li
                    key={slot.id}
                    style={{
                      marginBottom: "10px",
                      padding: "10px",
                      background: "#f0f0f0",
                      display: "flex",
                      justifyContent: "space-between",
                      alignItems: "center",
                    }}
                  >
                    <span>{formattedTime}</span>
                    <button
                      onClick={() => handleBooking(slot)}
                      className="button button-primary"
                    >
                      Book Now
                    </button>
                  </li>
                );
              })}
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
