/**
 * Formats an appointment object's start and end times into a readable string.
 * e.g., "8/5/2025, 5:30 PM - 6:30 PM"
 */
export const formattedTime = (item) => {
  if (!item || !item.start_time || !item.end_time) return "";
  const startTime = new Date(item.start_time);
  const endTime = new Date(item.end_time);
  const timeOptions = { hour: "numeric", minute: "2-digit", hour12: true };
  return `${startTime.toLocaleDateString()}, ${startTime.toLocaleTimeString(
    [],
    timeOptions
  )} - ${endTime.toLocaleTimeString([], timeOptions)}`;
};
