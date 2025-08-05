import { useState, useEffect } from "@wordpress/element";

const AvailabilityForm = () => {
  const [slots, setSlots] = useState([]);
  const [startTime, setStartTime] = useState("");
  const [endTime, setEndTime] = useState("");
  const [error, setError] = useState("");

  const fetchSlots = () => {
    fetch(tan_data.api_url + "availability", {
      headers: {
        "X-WP-Nonce": tan_data.nonce,
      },
    })
      .then((response) => response.json())
      .then((data) => {
        if (Array.isArray(data)) {
          setSlots(data);
        }
      });
  };

  // Fetch slots when the component loads
  useEffect(() => {
    fetchSlots();
  }, []);

  const handleSubmit = (e) => {
    e.preventDefault();
    setError(""); // Clear previous errors

    if (!startTime || !endTime) {
      setError("Please select both a start and end time.");
      return;
    }

    fetch(tan_data.api_url + "availability", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": tan_data.nonce,
      },
      body: JSON.stringify({ start_time: startTime, end_time: endTime }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          fetchSlots(); // Refresh the list of slots
          setStartTime("");
          setEndTime("");
        } else {
          setError(data.message || "An unknown error occurred.");
        }
      });
  };

  return (
    <div>
      <h2>Set Your Availability</h2>
      <form onSubmit={handleSubmit}>
        <div>
          <label>Start Time: </label>
          <input
            type="datetime-local"
            value={startTime}
            onChange={(e) => setStartTime(e.target.value)}
            required
          />
        </div>
        <div style={{ marginTop: "10px" }}>
          <label>End Time: </label>
          <input
            type="datetime-local"
            value={endTime}
            onChange={(e) => setEndTime(e.target.value)}
            required
          />
        </div>
        {error && <p style={{ color: "red" }}>{error}</p>}
        <div style={{ marginTop: "20px" }}>
          <button type="submit" className="button button-primary">
            Save Availability
          </button>
        </div>
      </form>
      <hr style={{ margin: "30px 0" }} />
      <h3>Your Current Slots</h3>
      {slots.length > 0 ? (
        <ul style={{ listStyleType: "disc", marginLeft: "20px" }}>
          {slots.map((slot) => (
            <li key={slot.id}>
              <strong>From:</strong>{" "}
              {new Date(slot.start_time).toLocaleString()} |{" "}
              <strong>To:</strong> {new Date(slot.end_time).toLocaleString()}
            </li>
          ))}
        </ul>
      ) : (
        <p>You have not set any availability yet.</p>
      )}
    </div>
  );
};

export default AvailabilityForm;
