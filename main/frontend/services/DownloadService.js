module.exports = {
    /**
     * Download given data as CSV file.
     * @param data
     */
    downloadAsCsv(data) {
        const downloadUrl = window.URL.createObjectURL(
            new Blob([data])
        );

        const link = document.createElement("a");

        link.href = downloadUrl;

        link.setAttribute("download", "feedback.csv");

        document.body.appendChild(link);

        link.click();

        link.remove();
    }
}