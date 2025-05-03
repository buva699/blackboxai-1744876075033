// Sample data for demonstration
let sampleData = [
    {
        studentId: "1001",
        name: "John Smith",
        class: "10A",
        date: "2024-01-15",
        time: "08:30:00",
        status: "present",
        remarks: ""
    },
    {
        studentId: "1002",
        name: "Emma Johnson",
        class: "10A",
        date: "2024-01-15",
        time: "08:32:00",
        status: "present",
        remarks: ""
    },
    {
        studentId: "1003",
        name: "Michael Brown",
        class: "10B",
        date: "2024-01-15",
        time: "08:45:00",
        status: "late",
        remarks: "Arrived late due to traffic"
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
    // Fetch attendance data from API
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('date').value = today;

    fetch(`api.php?path=attendance&date=${today}`)
        .then(response => response.json())
        .then(data => {
            sampleData = data.map(item => ({
                studentId: item.student_id,
                name: item.full_name,
                class: item.class_name,
                date: item.date,
                time: item.time_in || '',
                status: item.status,
                remarks: item.notes || ''
            }));
            filteredData = [...sampleData];
            updateTable();
            showLowAttendanceNotifications();
        })
        .catch(error => {
            console.error('Error fetching attendance data:', error);
            // Fallback to sampleData if needed
            filteredData = [...sampleData];
            updateTable();
            showLowAttendanceNotifications();
        });
});

// Show notifications for students with attendance below 80%
function showLowAttendanceNotifications() {
    const notificationArea = document.getElementById('notificationArea');
    if (!notificationArea) return;

    // Calculate attendance percentage for each student
    const attendanceCounts = {};
    sampleData.forEach(record => {
        if (!attendanceCounts[record.studentId]) {
            attendanceCounts[record.studentId] = { present: 0, total: 0, name: record.name };
        }
        if (record.status === 'present' || record.status === 'late') {
            attendanceCounts[record.studentId].present++;
        }
        attendanceCounts[record.studentId].total++;
    });

    // Filter students below 80%
    const lowAttendanceStudents = Object.entries(attendanceCounts)
        .filter(([_, counts]) => (counts.present / counts.total) * 100 < 80)
        .map(([id, counts]) => ({ id, name: counts.name, percentage: ((counts.present / counts.total) * 100).toFixed(2) }));

    if (lowAttendanceStudents.length === 0) {
        notificationArea.innerHTML = `
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Good job!</strong>
                <span class="block sm:inline">All students have attendance above 80%.</span>
            </div>
        `;
        return;
    }

    // Build notification list
    let notificationHTML = `
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Attendance Alert!</strong>
            <span class="block sm:inline">The following students have attendance below 80% and may not be eligible for the SIT exam:</span>
            <ul class="mt-2 list-disc list-inside">
    `;

    lowAttendanceStudents.forEach(student => {
        notificationHTML += `<li>${student.name} (ID: ${student.id}) - ${student.percentage}% attendance</li>`;
    });

    notificationHTML += '</ul></div>';

    notificationArea.innerHTML = notificationHTML;
}

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

    // Calculate attendance percentage and eligibility for each student
    // For demo, we calculate based on sampleData counts
    const attendanceCounts = {};
    sampleData.forEach(record => {
        if (!attendanceCounts[record.studentId]) {
            attendanceCounts[record.studentId] = { present: 0, total: 0 };
        }
        if (record.status === 'present' || record.status === 'late') {
            attendanceCounts[record.studentId].present++;
        }
        attendanceCounts[record.studentId].total++;
    });

    // Clear existing rows
    tableBody.innerHTML = '';

    // Add data rows
    pageData.forEach(record => {
        const counts = attendanceCounts[record.studentId] || { present: 0, total: 1 };
        const attendancePercentage = (counts.present / counts.total) * 100;
        const eligible = attendancePercentage >= 80 ? 'Eligible' : 'Not Eligible';
        const eligibilityBadge = eligible === 'Eligible' 
            ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Eligible</span>'
            : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Not Eligible</span>';

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
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${record.remarks || ''}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                ${eligibilityBadge}
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
    const headers = ['Student ID', 'Name', 'Class', 'Date', 'Time', 'Status', 'Remarks'];
    const csvContent = [
        headers.join(','),
        ...filteredData.map(record => [
            record.studentId,
            record.name,
            record.class,
            record.date,
            record.time,
            record.status,
            `"${(record.remarks || '').replace(/"/g, '""')}"`
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
