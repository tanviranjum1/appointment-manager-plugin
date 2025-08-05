const { api_url, nonce } = tan_data; // Get global data from WordPress

/**
 * A helper function to handle fetch responses and errors.
 */
const handleResponse = (response) => {
  if (!response.ok) {
    return response.json().then((err) => Promise.reject(err));
  }
  return response.json();
};

/**
 * Fetches all active approvers.
 */
export const fetchApprovers = () => {
  return fetch(`${api_url}approvers`, {
    headers: { "X-WP-Nonce": nonce },
  }).then(handleResponse);
};

/**
 * Fetches all availability for the current logged-in approver.
 */
export const fetchAllAvailability = () => {
  return fetch(`${api_url}availability`, {
    headers: { "X-WP-Nonce": nonce },
  }).then(handleResponse);
};

/**
 * Fetches available slots for a specific approver ID.
 */
export const fetchAvailabilityForApprover = (approverId) => {
  return fetch(`${api_url}availability/${approverId}`, {
    headers: { "X-WP-Nonce": nonce },
  }).then(handleResponse);
};

/**
 * Creates a new availability slot for the current approver.
 * @param {string} startTime - ISO format start time
 * @param {string} endTime - ISO format end time
 */
export const createAvailability = (startTime, endTime) => {
  return fetch(`${api_url}availability`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-WP-Nonce": nonce,
    },
    body: JSON.stringify({ start_time: startTime, end_time: endTime }),
  }).then(handleResponse);
};

/**
 * Fetches appointments for the current logged-in user (role-dependent).
 */
export const fetchMyAppointments = () => {
  return fetch(`${api_url}my-appointments`, {
    headers: { "X-WP-Nonce": nonce },
  }).then(handleResponse);
};

/**
 * Creates a new appointment request.
 * @param {object} bookingData - Contains approver_id, start_time, end_time, reason
 */
export const createAppointment = (bookingData) => {
  return fetch(`${api_url}appointments`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-WP-Nonce": nonce,
    },
    body: JSON.stringify(bookingData),
  }).then(handleResponse);
};

/**
 * Updates the status of an appointment.
 * @param {number} id - The appointment ID
 * @param {string} newStatus - 'approved' or 'rejected'
 */
export const updateAppointmentStatus = (id, newStatus) => {
  return fetch(`${api_url}appointments/${id}/status`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-WP-Nonce": nonce,
    },
    body: JSON.stringify({ status: newStatus }),
  }).then(handleResponse);
};

/**
 * Cancels an appointment.
 * @param {number} id - The appointment ID
 */
export const cancelAppointment = (id) => {
  return fetch(`${api_url}appointments/${id}/cancel`, {
    method: "POST",
    headers: { "X-WP-Nonce": nonce },
  }).then(handleResponse);
};
