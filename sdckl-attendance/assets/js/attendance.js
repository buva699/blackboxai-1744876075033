// Sample data for demonstration
let sampleData = [
    {
        studentId: "1001",
        name: "John Smith",
        class: "10A",
        date: "2024-01-15",
        time: "08:30:00",
        status: "present"
    },
    {
        studentId: "1002",
        name: "Emma Johnson",
        class: "10A",
        date: "2024-01-15",
        time: "08:32:00",
        status: "present"
    },
    {
        studentId: "1003",
        name: "Michael Brown",
        class: "10B",
        date: "2024-01-15",
        time: "08:45:00",
        status: "late"
    }
];

// Pagination state
let currentPage = 1;
const recordsPerPage = 10;
let filteredData = [...sampleData];

// DOM Elements
const tableBody = document.getElementById('attendanceTableBody');
const pageStart = document.getElementById('pageStart');
const pageEnd = document.getElementById('pageEnd');
const totalRecords = document.getElementById('totalRecords');

// Initialize page
document.addEventListener('DOMContentLoaded', () => {
    // Load attendance data from localStorage if available
    const storedAttendance = localStorage.getItem('attendance');
    if (storedAttendance) {
        const attendanceData = JSON.parse(storedAttendance);
        // Merge with sample data
        sampleData = [...sampleData, ...attendanceData];
        filteredData = [...sampleData];
    }
    
    // Set default date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('date').value = today;
    
    updateTable();
});

// Apply filters
function applyFilters() {
    const date = document.getElementById('date').value;
    const className = document.getElementById('class').value;
    const status = document.getElementById('status').value;
    const search = document.getElementById('search').value.toLowerCase();

    filteredData = sampleData.filter(record => {
        const matchDate = !date || record.date === date;
        const matchClass = !className || record.class === className;
        const matchStatus = !status || record.status === status;
        const matchSearch = !search || 
            record.name.toLowerCase().includes(search) || 
            record.studentId.toLowerCase().includes(search);

        return matchDate && matchClass && matchStatus && matchSearch;
    });

    currentPage = 1;
    updateTable();
}

// Update table with current data and pagination
function updateTable() {
    const start = (currentPage - 1) * recordsPerPage;
    const end = start + recordsPerPage;
    const pageData = filteredData.slice(start, end);

    // Clear existing rows
    tableBody.innerHTML = '';

    // Add data rows
    pageData.forEach(record => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                ${record.studentId}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${record.name}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${record.class}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${formatDate(record.date)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${formatTime(record.time)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                ${getStatusBadge(record.status)}
            </td>
        `;
        tableBody.appendChild(row);
    });

    // Update pagination info
    pageStart.textContent = Math.min(start + 1, filteredData.length);
    pageEnd.textContent = Math.min(end, filteredData.length);
    totalRecords.textContent = filteredData.length;
}

// Navigation functions
function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        updateTable();
    }
}

function nextPage() {
    const maxPage = Math.ceil(filteredData.length / recordsPerPage);
    if (currentPage < maxPage) {
        currentPage++;
        updateTable();
    }
}

// Export data to CSV
function exportData() {
    const headers = ['Student ID', 'Name', 'Class', 'Date', 'Time', 'Status'];
    const csvContent = [
        headers.join(','),
        ...filteredData.map(record => [
            record.studentId,
            record.name,
            record.class,
            record.date,
            record.time,
            record.status
        ].join(','))
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', 'attendance_report.csv');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

// Utility functions
function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString();
}

function formatTime(timeStr) {
    return new Date(`2000-01-01T${timeStr}`).toLocaleTimeString([], { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

function getStatusBadge(status) {
    const badges = {
        present: 'bg-green-100 text-green-800',
        absent: 'bg-red-100 text-red-800',
        late: 'bg-yellow-100 text-yellow-800'
    };

    return `
        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${badges[status]}">
            ${status.charAt(0).toUpperCase() + status.slice(1)}
        </span>
    `;
}

// Event listeners for filters
document.getElementById('date').addEventListener('change', applyFilters);
document.getElementById('class').addEventListener('change', applyFilters);
document.getElementById('status').addEventListener('change', applyFilters);
document.getElementById('search').addEventListener('input', applyFilters);
