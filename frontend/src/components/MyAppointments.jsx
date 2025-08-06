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

  const [activeFilter, setActiveFilter] = useState(""); // '' means all
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const allStatuses = ["pending", "approved", "rejected", "cancelled"];

  const loadAppointments = () => {
    setIsLoading(true);
    fetchMyAppointments(activeFilter, currentPage)
      .then((data) => {
        if (Array.isArray(data.appointments)) {
          setAppointments(data.appointments);
          setTotalPages(data.total_pages > 0 ? data.total_pages : 1);
        }
      })
      .finally(() => setIsLoading(false));
  };
  // Reload appointments when filter or page changes
  useEffect(loadAppointments, [activeFilter, currentPage]);

  const handleStatusChange = (id, newStatus) => {
    updateAppointmentStatus(id, newStatus).then(() => loadAppointments());
  };

  const handleFilterChange = (e) => {
    setCurrentPage(1); // Reset to first page when filter changes
    setActiveFilter(e.target.value);
  };

  const handleCancel = (id) => {
    if (!window.confirm("Are you sure you want to cancel this appointment?")) {
      return;
    }
    cancelAppointment(id)
      .then(() => loadAppointments())
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

  return (
    <div>
      <h2 className="mb-4">
        {user_role === "tan_approver"
          ? "Incoming Appointment Requests"
          : "My Sent Requests"}
      </h2>

      <div className="d-flex justify-content-between align-items-center mb-4">
        <div className="d-flex align-items-center">
          <label htmlFor="status-filter" className="form-label me-2 mb-0">
            Filter by status:
          </label>
          <select
            id="status-filter"
            className="form-select w-auto"
            value={activeFilter}
            onChange={handleFilterChange}
          >
            <option value="">All Statuses</option>
            {allStatuses.map((status) => (
              <option key={status} value={status}>
                {status.charAt(0).toUpperCase() + status.slice(1)}
              </option>
            ))}
          </select>
        </div>
      </div>

      {isLoading ? (
        <p>Loading your appointments...</p>
      ) : appointments.length === 0 ? (
        <p>No appointments found for this filter.</p>
      ) : (
        <div className="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
          {appointments.map((app) => {
            const now = new Date();
            const twentyFourHoursFromNow = new Date(
              now.getTime() + 24 * 60 * 60 * 1000
            );
            const appointmentDate = new Date(app.start_time);
            const canCancel =
              app.status === "pending" &&
              appointmentDate > twentyFourHoursFromNow;

            return (
              <div key={app.id} className="col">
                <div className="card h-100 shadow-sm border border-light-subtle">
                  <div className="card-header bg-light">
                    <h6 className="mb-0">
                      Appointment with:{" "}
                      {user_role === "tan_approver"
                        ? app.requester_name
                        : app.approver_name}
                    </h6>
                  </div>

                  <div className="card-body">
                    <p className="card-text mb-2">
                      <strong>Time:</strong> {formattedTime(app)}
                    </p>
                    {app.reason && (
                      <p className="card-text mb-2">
                        <strong>Reason:</strong> {app.reason}
                      </p>
                    )}
                    <p className="card-text mb-2">
                      <strong>Status:</strong> {renderStatusBadge(app.status)}
                    </p>
                    {app.status === "cancelled" && app.cancelled_by_role && (
                      <p className="card-text mb-0">
                        <small className="text-muted">
                          Cancelled by:{" "}
                          {app.cancelled_by_role === "tan_requester"
                            ? user_role === "tan_approver"
                              ? "Requester"
                              : "You"
                            : user_role === "tan_approver"
                            ? "You"
                            : "The Approver"}
                        </small>
                      </p>
                    )}
                  </div>

                  {(user_role === "tan_approver" &&
                    (app.status === "pending" || app.status === "approved")) ||
                  (user_role === "tan_requester" && canCancel) ? (
                    <div className="card-footer bg-white d-flex justify-content-end gap-2">
                      {user_role === "tan_approver" &&
                        app.status === "pending" && (
                          <>
                            <button
                              onClick={() =>
                                handleStatusChange(app.id, "approved")
                              }
                              className="btn btn-outline-success btn-sm"
                            >
                              Approve
                            </button>
                            <button
                              onClick={() =>
                                handleStatusChange(app.id, "rejected")
                              }
                              className="btn btn-outline-warning btn-sm"
                            >
                              Reject
                            </button>
                          </>
                        )}
                      {(user_role === "tan_approver" ||
                        (user_role === "tan_requester" && canCancel)) && (
                        <button
                          onClick={() => handleCancel(app.id)}
                          className="btn btn-outline-danger btn-sm"
                        >
                          Cancel
                        </button>
                      )}
                    </div>
                  ) : null}
                </div>
              </div>
            );
          })}
        </div>
      )}

      {totalPages > 1 && (
        <div className="d-flex justify-content-between align-items-center mt-4">
          <button
            className="btn btn-secondary"
            onClick={() => setCurrentPage((p) => p - 1)}
            disabled={currentPage === 1}
          >
            &laquo; Previous
          </button>
          <span>
            Page {currentPage} of {totalPages}
          </span>
          <button
            className="btn btn-secondary"
            onClick={() => setCurrentPage((p) => p + 1)}
            disabled={currentPage >= totalPages}
          >
            Next &raquo;
          </button>
        </div>
      )}
    </div>
  );
};

export default MyAppointments;
