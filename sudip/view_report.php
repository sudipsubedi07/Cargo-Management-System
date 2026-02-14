<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
        .history-list {
            list-style: none;
            padding: 0;
        }
        .history-item {
            background: #e3e3e3;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
        }
        select {
            display: block;
            margin: 10px auto;
            padding: 5px;
        }
        .print-button {
            margin-top: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .print-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Report History</h2>
        <label for="historyFilter">Select Time Range:</label>
        <select id="historyFilter">
            <option value="today">Today</option>
            <option value="last7days">Last 7 Days</option>
            <option value="last1month">Last 1 Month</option>
        </select>
        <ul class="history-list" id="historyList"></ul>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const historyData = [
                { date: "2025-02-16", report: "Prepared final report summary" },
                { date: "2025-02-15", report: "Conducted user testing" },
                { date: "2025-02-14", report: "Fixed 3 system bugs" },
                { date: "2025-02-13", report: "Reviewed weekly report" },
                { date: "2025-02-12", report: "Updated system logs" },
                { date: "2025-02-11", report: "Completed project milestone" },
                { date: "2025-02-10", report: "User logged 5 tasks" },
                { date: "2025-01-25", report: "Monthly planning" },
                { date: "2025-01-10", report: "Initial project kickoff" }
            ];
            
            const historyList = document.getElementById("historyList");
            const filterSelect = document.getElementById("historyFilter");

            function filterHistory() {
                const filterValue = filterSelect.value;
                const today = new Date();
                const filteredData = historyData.filter(entry => {
                    const entryDate = new Date(entry.date);
                    if (filterValue === "today") {
                        return entryDate.toDateString() === today.toDateString();
                    } else if (filterValue === "last7days") {
                        const pastWeek = new Date();
                        pastWeek.setDate(today.getDate() - 7);
                        return entryDate >= pastWeek;
                    } else if (filterValue === "last1month") {
                        const pastMonth = new Date();
                        pastMonth.setMonth(today.getMonth() - 1);
                        return entryDate >= pastMonth;
                    }
                });
                
                historyList.innerHTML = "";
                filteredData.forEach(entry => {
                    const listItem = document.createElement("li");
                    listItem.classList.add("history-item");
                    listItem.innerHTML = `<strong>${entry.date}</strong>: ${entry.report}`;
                    
                    // Add the print button
                    const printButton = document.createElement("button");
                    printButton.textContent = "Print Report";
                    printButton.classList.add("print-button");
                    printButton.addEventListener("click", function() {
                        printReport(entry);
                    });

                    listItem.appendChild(printButton);
                    historyList.appendChild(listItem);
                });
            }

            function printReport(entry) {
                const printWindow = window.open("", "", "width=600,height=400");
                printWindow.document.write("<html><head><title>Print Report</title></head><body>");
                printWindow.document.write(`<h3>Report for ${entry.date}</h3>`);
                printWindow.document.write(`<p>${entry.report}</p>`);
                printWindow.document.write("<button onclick='window.print()'>Print</button>");
                printWindow.document.write("</body></html>");
                printWindow.document.close();
            }

            filterSelect.addEventListener("change", filterHistory);
            filterHistory(); // Initialize with default filter
        });
    </script>
</body>
</html>
