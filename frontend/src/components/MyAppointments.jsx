import { useState, useEffect } from "@wordpress/element";

const MyAppointments = () => {
  const [appointments, setAppointments] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const { user_role, api_url, nonce } = tan_data;

  const fetchAppointments = () => {
    setIsLoading(true);
    fetch(api_url + "my-appointments", {
      headers: { "X-WP-Nonce": nonce },
    })
      .then((response) => response.json())
      .then((data) => {
        // Add a safety check to ensure data is an array
        if (Array.isArray(data)) {
          console.log("My Appointments Response:", data); // <-- ADD THIS

          setAppointments(data);
        } else {
          setAppointments([]); // Set to empty array if response is not as expected
        }
        setIsLoading(false);
      });
  };

  useEffect(() => {
    fetchAppointments();
  }, []);

  const handleStatusChange = (id, newStatus) => {
    fetch(`${api_url}appointments/${id}/status`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": nonce,
      },
      body: JSON.stringify({ status: newStatus }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          setAppointments((prev) =>
            prev.map((app) =>
              app.id == id ? { ...app, status: data.new_status } : app
            )
          );
        }
      });
  };

  const handleCancel = (id) => {
    if (!window.confirm("Are you sure you want to cancel this appointment?")) {
      return;
    }

    fetch(`${api_url}appointments/${id}/cancel`, {
      method: "POST",
      headers: { "X-WP-Nonce": nonce },
    })
      .then((response) => {
        if (!response.ok) {
          return response.json().then((err) => Promise.reject(err));
        }
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          setAppointments((prev) =>
            prev.map((app) =>
              app.id == id ? { ...app, status: data.new_status } : app
            )
          );
        }
      })
      .catch((error) => {
        alert(`Error: ${error.message || "Could not cancel appointment."}`);
      });
  };

  if (isLoading) {
    return <p>Loading your appointments...</p>;
  }

  // --- RENDER FOR APPROVER ---
  if (user_role === "tan_approver") {
    return (
      <div>
        <h2>Incoming Appointment Requests</h2>
        {appointments.length === 0 && <p>You have no appointment requests.</p>}
        {appointments.map((app) => (
          <div
            key={app.id}
            style={{
              border: "1px solid #ccc",
              padding: "15px",
              marginBottom: "15px",
            }}
          >
            <p>
              <strong>Requester:</strong> {app.requester_name}
            </p>
            <p>
              <strong>Time:</strong> {new Date(app.start_time).toLocaleString()}
            </p>
            {app.reason && (
              <p>
                <strong>Reason:</strong> {app.reason}
              </p>
            )}
            <p>
              <strong>Status:</strong>{" "}
              <span style={{ textTransform: "capitalize", fontWeight: "bold" }}>
                {app.status}
              </span>
            </p>
            {app.status === "cancelled" && app.cancelled_by_role && (
              <p>
                <em>
                  Cancelled by:{" "}
                  {app.cancelled_by_role === "tan_requester"
                    ? "Requester"
                    : "Approver"}
                </em>
              </p>
            )}

            {app.status === "pending" && (
              <div>
                <button
                  onClick={() => handleStatusChange(app.id, "approved")}
                  className="button button-primary"
                  style={{ marginRight: "10px" }}
                >
                  Approve
                </button>
                <button
                  onClick={() => handleStatusChange(app.id, "rejected")}
                  className="button button-secondary"
                  style={{ marginRight: "10px" }}
                >
                  Reject
                </button>
                <button onClick={() => handleCancel(app.id)} className="button">
                  Cancel
                </button>
              </div>
            )}
            {app.status === "approved" && (
              <button onClick={() => handleCancel(app.id)} className="button">
                Cancel Appointment
              </button>
            )}
          </div>
        ))}
      </div>
    );
  }

  // --- RENDER FOR REQUESTER ---
  if (user_role === "tan_requester") {
    // --- START OF THE FIX ---
    // This variable was missing, causing the crash.
    const now = new Date();
    const twentyFourHoursFromNow = new Date(
      now.getTime() + 24 * 60 * 60 * 1000
    );
    // --- END OF THE FIX ---

    return (
      <div>
        <h2>My Sent Requests</h2>
        {appointments.length === 0 && (
          <p>You have not requested any appointments.</p>
        )}
        {appointments.map((app) => {
          const appointmentDate = new Date(app.start_time);
          // This check will now work correctly
          const canCancel =
            app.status === "pending" &&
            appointmentDate > twentyFourHoursFromNow;

          return (
            <div
              key={app.id}
              style={{
                border: "1px solid #ccc",
                padding: "15px",
                marginBottom: "15px",
              }}
            >
              <p>
                <strong>Approver:</strong> {app.approver_name}
              </p>
              <p>
                <strong>Time:</strong> {appointmentDate.toLocaleString()}
              </p>
              <p>
                <strong>Status:</strong>{" "}
                <span
                  style={{ textTransform: "capitalize", fontWeight: "bold" }}
                >
                  {app.status}
                </span>
              </p>
              {app.status === "cancelled" && app.cancelled_by_role && (
                <p>
                  <em>
                    Cancelled by:{" "}
                    {app.cancelled_by_role === "tan_requester"
                      ? "You"
                      : "The Approver"}
                  </em>
                </p>
              )}
              {canCancel && (
                <button onClick={() => handleCancel(app.id)} className="button">
                  Cancel Request
                </button>
              )}
            </div>
          );
        })}
      </div>
    );
  }

  return <p>You do not have a valid role to view appointments.</p>;
};

export default MyAppointments;
