// Script to populate low attendance students table

// Sample data is imported from attendance.js sampleData or localStorage
// For simplicity, we will assume sampleData is available globally

document.addEventListener('DOMContentLoaded', () => {
    populateLowAttendanceTable();
});

function populateLowAttendanceTable() {
    const tableBody = document.getElementById('lowAttendanceTableBody');
    if (!tableBody) return;

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

    // Clear existing rows
    tableBody.innerHTML = '';

    if (lowAttendanceStudents.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="3" class="px-6 py-4 whitespace-nowrap text-center text-sm text-green-700">No students with attendance below 80%</td>`;
        tableBody.appendChild(row);
        return;
    }

    // Populate table rows
    lowAttendanceStudents.forEach(student => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${student.id}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${student.name}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-semibold">${student.percentage}%</td>
        `;
        tableBody.appendChild(row);
    });
}
