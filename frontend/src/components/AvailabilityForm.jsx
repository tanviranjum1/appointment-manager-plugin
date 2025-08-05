import { useState, useEffect } from "@wordpress/element";

const AvailabilityForm = () => {
  const [allSlots, setAllSlots] = useState([]);
  const [selectedDate, setSelectedDate] = useState("");
  const [startTime, setStartTime] = useState("");
  const [endTime, setEndTime] = useState("");
  const [error, setError] = useState("");
  const [isLoading, setIsLoading] = useState(true);

  const fetchAllSlots = () => {
    setIsLoading(true);
    fetch(tan_data.api_url + "availability", {
      headers: { "X-WP-Nonce": tan_data.nonce },
    })
      .then((response) => response.json())
      .then((data) => {
        if (Array.isArray(data)) {
          setAllSlots(data);
        }
        setIsLoading(false);
      });
  };

  useEffect(() => {
    fetchAllSlots();
  }, []);

  const handleAddSlot = (e) => {
    e.preventDefault();
    setError("");

    if (!startTime || !endTime || !selectedDate) {
      setError("Please select a date, start time, and end time.");
      return;
    }

    const fullStartTime = `${selectedDate}T${startTime}`;
    const fullEndTime = `${selectedDate}T${endTime}`;

    if (new Date(fullStartTime) >= new Date(fullEndTime)) {
      setError("End time must be after start time.");
      return;
    }

    fetch(tan_data.api_url + "availability", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": tan_data.nonce,
      },
      body: JSON.stringify({
        start_time: fullStartTime,
        end_time: fullEndTime,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          fetchAllSlots(); // Refresh the list of all slots
          setStartTime("");
          setEndTime("");
        } else {
          setError(data.message || "An unknown error occurred.");
        }
      });
  };

  const slotsToDisplay = (
    selectedDate
      ? allSlots.filter((slot) => slot.start_time.startsWith(selectedDate))
      : allSlots
  ).sort((a, b) => new Date(a.start_time) - new Date(b.start_time));

  return (
    <div>
      <h2>Set Your Availability</h2>

      <div>
        <label>
          <strong>1. Select a Date to Add or View Slots:</strong>
        </label>
        <input
          type="date"
          value={selectedDate}
          onChange={(e) => setSelectedDate(e.target.value)}
          required
        />
        <button
          onClick={() => setSelectedDate("")}
          style={{ marginLeft: "10px" }}
          className="button button-secondary"
        >
          Show All
        </button>
      </div>

      {selectedDate && (
        <form
          onSubmit={handleAddSlot}
          style={{
            marginTop: "20px",
            padding: "15px",
            border: "1px solid #eee",
          }}
        >
          <h4>Add a new time slot for {selectedDate}</h4>
          <div>
            <label>Start Time: </label>
            <input
              type="time"
              value={startTime}
              onChange={(e) => setStartTime(e.target.value)}
              required
            />
          </div>
          <div style={{ marginTop: "10px" }}>
            <label>End Time: </label>
            <input
              type="time"
              value={endTime}
              onChange={(e) => setEndTime(e.target.value)}
              required
            />
          </div>
          {error && <p style={{ color: "red" }}>{error}</p>}
          <div style={{ marginTop: "20px" }}>
            <button type="submit" className="button button-primary">
              Add Time Slot
            </button>
          </div>
        </form>
      )}
      <hr style={{ margin: "30px 0" }} />

      <h3>Your Current Slots {selectedDate && `for ${selectedDate}`}</h3>
      {isLoading ? (
        <p>Loading...</p>
      ) : slotsToDisplay.length > 0 ? (
        <ul style={{ listStyleType: "disc", marginLeft: "20px" }}>
          {slotsToDisplay.map((slot) => {
            // --- START OF THE FIX ---
            const sTime = new Date(slot.start_time);
            const eTime = new Date(slot.end_time);
            const timeOptions = {
              hour: "numeric",
              minute: "2-digit",
              hour12: true,
            };
            const formattedSlot = `${sTime.toLocaleDateString()} &nbsp; ${sTime.toLocaleTimeString(
              [],
              timeOptions
            )} - ${eTime.toLocaleTimeString([], timeOptions)}`;

            return (
              <li
                key={slot.id}
                dangerouslySetInnerHTML={{ __html: formattedSlot }}
              />
            );
            // --- END OF THE FIX ---
          })}
        </ul>
      ) : (
        <p>No availability set for this date.</p>
      )}
    </div>
  );
};
export default AvailabilityForm;
