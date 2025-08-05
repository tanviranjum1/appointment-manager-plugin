import { useState, useEffect } from "@wordpress/element";
import { fetchAllAvailability, createAvailability } from "../services/api";
import { formattedTime } from "../utils/formatters";

const AvailabilityForm = () => {
  const [allSlots, setAllSlots] = useState([]);
  const [selectedDate, setSelectedDate] = useState("");
  const [startTime, setStartTime] = useState("");
  const [endTime, setEndTime] = useState("");
  const [error, setError] = useState("");
  const [isLoading, setIsLoading] = useState(true);

  const loadSlots = () => {
    setIsLoading(true);
    fetchAllAvailability()
      .then((data) => {
        if (Array.isArray(data)) {
          setAllSlots(data);
        }
      })
      .finally(() => setIsLoading(false));
  };

  useEffect(() => {
    loadSlots();
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

    createAvailability(fullStartTime, fullEndTime)
      .then(() => {
        loadSlots(); // Refresh the list of all slots
        setStartTime("");
        setEndTime("");
      })
      .catch((err) => {
        setError(err.message || "An unknown error occurred.");
      });
  };

  const slotsToDisplay = (
    selectedDate
      ? allSlots.filter((slot) => slot.start_time.startsWith(selectedDate))
      : allSlots
  ).sort((a, b) => new Date(a.start_time) - new Date(b.start_time));

  return (
    <div>
      <h3>Set Your Availability</h3>

      <div className="mb-3">
        <label htmlFor="tan-select-date" className="form-label">
          <strong>1. Select a Date to Add or View Slots:</strong>
        </label>
        <div className="d-flex">
          <input
            type="date"
            id="tan-select-date"
            className="form-control"
            style={{ maxWidth: "250px" }}
            value={selectedDate}
            onChange={(e) => setSelectedDate(e.target.value)}
          />
          <button
            onClick={() => setSelectedDate("")}
            style={{ marginLeft: "10px" }}
            className="button button-secondary"
          >
            Show All
          </button>
        </div>
      </div>

      {selectedDate && (
        <form onSubmit={handleAddSlot} className="card card-body mt-4">
          <h4>Add a new time slot for {selectedDate}</h4>
          <div className="row">
            <div className="col-md-6 mb-3">
              <label htmlFor="tan-start-time" className="form-label">
                Start Time:
              </label>
              <input
                type="time"
                id="tan-start-time"
                className="form-control"
                value={startTime}
                onChange={(e) => setStartTime(e.target.value)}
                required
              />
            </div>
            <div className="col-md-6 mb-3">
              <label htmlFor="tan-end-time" className="form-label">
                End Time:
              </label>
              <input
                type="time"
                id="tan-end-time"
                className="form-control"
                value={endTime}
                onChange={(e) => setEndTime(e.target.value)}
                required
              />
            </div>
          </div>
          {error && <div className="alert alert-danger mt-2">{error}</div>}
          <div>
            <button type="submit" className="btn btn-primary">
              Add Time Slot
            </button>
          </div>
        </form>
      )}

      <hr className="my-4" />

      <h3>Your Current Slots {selectedDate && `for ${selectedDate}`}</h3>
      {isLoading ? (
        <p>Loading...</p>
      ) : slotsToDisplay.length > 0 ? (
        <ul className="list-group">
          {slotsToDisplay.map((slot) => (
            <li key={slot.id} className="list-group-item">
              {formattedTime(slot)}
            </li>
          ))}
        </ul>
      ) : (
        <p>No availability set for this date.</p>
      )}
    </div>
  );
};

export default AvailabilityForm;
