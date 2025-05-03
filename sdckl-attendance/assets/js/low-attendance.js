document.addEventListener('DOMContentLoaded', () => {
    // Fetch attendance data for a fixed sample date for testing
    fetch('api.php?path=attendance&date=2024-01-15')
        .then(response => response.json())
        .then(data => {
            console.log('Fetched attendance data:', data); // Debug log
            const attendanceCounts = {};
            data.forEach(record => {
                if (!attendanceCounts[record.student_id]) {
                    attendanceCounts[record.student_id] = { present: 0, total: 0, name: record.full_name };
                }
                if (record.status === 'present' || record.status === 'late') {
                    attendanceCounts[record.student_id].present++;
                }
                attendanceCounts[record.student_id].total++;
            });

            const lowAttendanceStudents = Object.entries(attendanceCounts)
                .filter(([_, counts]) => (counts.present / counts.total) * 100 < 80)
                .map(([id, counts]) => ({ id, name: counts.name, percentage: ((counts.present / counts.total) * 100).toFixed(2) }));

            const tableBody = document.getElementById('lowAttendanceTableBody');
            tableBody.innerHTML = '';

            if (lowAttendanceStudents.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="3" class="px-6 py-4 whitespace-nowrap text-center text-sm text-green-700">No students with attendance below 80%</td>`;
                tableBody.appendChild(row);
                return;
            }

            lowAttendanceStudents.forEach(student => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${student.id}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${student.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-semibold">${student.percentage}%</td>
                `;
                tableBody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error fetching attendance data:', error);
        });
});
