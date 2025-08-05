import { useState, useEffect } from "@wordpress/element";
import {
  fetchMyAppointments,
  updateAppointmentStatus,
  cancelAppointment,
} from "../services/api";
import { formattedTime } from "../utils/formatters";

const MyAppointments = () => {
  const [appointments, setAppointments] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const { user_role } = tan_data;

  const loadAppointments = () => {
    setIsLoading(true);
    fetchMyAppointments()
      .then((data) => {
        if (Array.isArray(data)) {
          setAppointments(data);
        }
      })
      .finally(() => setIsLoading(false));
  };

  useEffect(loadAppointments, []);

  const handleStatusChange = (id, newStatus) => {
    updateAppointmentStatus(id, newStatus).then((data) => {
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
    cancelAppointment(id)
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
  const renderStatusBadge = (status) => {
    let badgeClass = "bg-secondary";
    if (status === "approved") badgeClass = "bg-success";
    if (status === "rejected" || status === "cancelled")
      badgeClass = "bg-danger";
    if (status === "pending") badgeClass = "bg-warning text-dark";
    return <span className={`badge ${badgeClass}`}>{status}</span>;
  };

  if (isLoading) {
    return <p>Loading your appointments...</p>;
  }

  // --- RENDER FOR APPROVER ---
  if (user_role === "tan_approver") {
    return (
      <div>
        <h3>Incoming Appointment Requests</h3>
        {appointments.length === 0 && <p>You have no appointment requests.</p>}
        {appointments.map((app) => (
          <div key={app.id} className="tan-appointment-card">
            <div className="card-body">
              <h5 className="card-title">
                Appointment with: {app.requester_name}
              </h5>
              <p className="card-text mb-1">
                <strong>Time:</strong> {formattedTime(app)}
              </p>
              {app.reason && (
                <p className="card-text">
                  <strong>Reason:</strong> {app.reason}
                </p>
              )}
              <p className="card-text">
                <strong>Status:</strong> {renderStatusBadge(app.status)}
              </p>
              {app.status === "cancelled" && app.cancelled_by_role && (
                <p className="card-text">
                  <small className="text-muted">
                    Cancelled by:{" "}
                    {app.cancelled_by_role === "tan_requester"
                      ? "Requester"
                      : "You"}
                  </small>
                </p>
              )}
            </div>
            {(app.status === "pending" || app.status === "approved") && (
              <div className="card-footer">
                {app.status === "pending" && (
                  <>
                    <button
                      onClick={() => handleStatusChange(app.id, "approved")}
                      className="btn btn-success btn-sm me-2"
                    >
                      Approve
                    </button>
                    <button
                      onClick={() => handleStatusChange(app.id, "rejected")}
                      className="btn btn-warning btn-sm me-2"
                    >
                      Reject
                    </button>
                  </>
                )}
                <button
                  onClick={() => handleCancel(app.id)}
                  className="btn btn-danger btn-sm"
                >
                  Cancel
                </button>
              </div>
            )}
          </div>
        ))}
      </div>
    );
  }

  // --- RENDER FOR REQUESTER ---
  if (user_role === "tan_requester") {
    const now = new Date();
    const twentyFourHoursFromNow = new Date(
      now.getTime() + 24 * 60 * 60 * 1000
    );

    return (
      <div>
        <h2>My Sent Requests</h2>
        {appointments.length === 0 && (
          <p>You have not requested any appointments.</p>
        )}
        {appointments.map((app) => {
          const appointmentDate = new Date(app.start_time);
          const canCancel =
            app.status === "pending" &&
            appointmentDate > twentyFourHoursFromNow;

          return (
            <div key={app.id} className="tan-appointment-card">
              <div className="card-body">
                <h5 className="card-title">
                  Appointment with: {app.approver_name}
                </h5>
                <p className="card-text mb-1">
                  <strong>Time:</strong> {formattedTime(app)}
                </p>
                {app.reason && (
                  <p className="card-text">
                    <strong>Reason:</strong> {app.reason}
                  </p>
                )}
                <p className="card-text">
                  <strong>Status:</strong> {renderStatusBadge(app.status)}
                </p>
                {app.status === "cancelled" && app.cancelled_by_role && (
                  <p className="card-text">
                    <small className="text-muted">
                      Cancelled by:{" "}
                      {app.cancelled_by_role === "tan_requester"
                        ? "You"
                        : "The Approver"}
                    </small>
                  </p>
                )}
              </div>
              {canCancel && (
                <div className="card-footer">
                  <button
                    onClick={() => handleCancel(app.id)}
                    className="btn btn-danger btn-sm"
                  >
                    Cancel Request
                  </button>
                </div>
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
