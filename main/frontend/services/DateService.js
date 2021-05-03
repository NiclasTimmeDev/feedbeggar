export default {
    /**
     * Names of all months.
     */
    monthNames: [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "May",
        "Jun",
        "Jul",
        "Aug",
        "Sep",
        "Oct",
        "Nov",
        "Dec",
    ],
    /**
     * Get the month name from a date object.
     *
     * @param {Date} date
     *
     * @returns {string}
     */
    getMonthName(date) {
        return this.monthNames[date.getMonth()];
    },
    /**
     * Display a date object in a pretty format.
     *
     * @param {Date} date
     *
     * @returns {string}
     */
    getFormattedDate(date) {
        const year = date.getFullYear();

        const month = this.getMonthName(date);

        const monthDay = date.getDate();

        return `${month} ${monthDay}, ${year}`;
    }
}