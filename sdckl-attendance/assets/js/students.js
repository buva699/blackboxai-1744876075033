// Sample student data for demonstration
let students = [
    {
        id: "1001",
        name: "John Smith",
        class: "10A",
        contact: "+1234567890",
        status: "active"
    },
    {
        id: "1002",
        name: "Emma Johnson",
        class: "10A",
        contact: "+1234567891",
        status: "active"
    },
    {
        id: "1003",
        name: "Michael Brown",
        class: "10B",
        contact: "+1234567892",
        status: "inactive"
    }
];

// Pagination state
let currentPage = 1;
const recordsPerPage = 10;
let filteredStudents = [...students];

// DOM Elements
const tableBody = document.getElementById('studentsTableBody');
const studentModal = document.getElementById('studentModal');
const deleteModal = document.getElementById('deleteModal');
const pageStart = document.getElementById('pageStart');
const pageEnd = document.getElementById('pageEnd');
const totalStudents = document.getElementById('totalStudents');

// Current student being edited or deleted
let currentStudentId = null;

// Initialize page
document.addEventListener('DOMContentLoaded', () => {
    // Load students from localStorage if available
    const storedStudents = localStorage.getItem('students');
    if (storedStudents) {
        students = JSON.parse(storedStudents);
        filteredStudents = [...students];
    }
    
    updateTable();
    setupEventListeners();
});

// Setup event listeners
function setupEventListeners() {
    document.getElementById('searchStudent').addEventListener('input', applyFilters);
    document.getElementById('filterClass').addEventListener('change', applyFilters);
    document.getElementById('filterStatus').addEventListener('change', applyFilters);
}

// Apply filters
function applyFilters() {
    const search = document.getElementById('searchStudent').value.toLowerCase();
    const classFilter = document.getElementById('filterClass').value;
    const statusFilter = document.getElementById('filterStatus').value;

    filteredStudents = students.filter(student => {
        const matchSearch = !search || 
            student.name.toLowerCase().includes(search) || 
            student.id.toLowerCase().includes(search);
        const matchClass = !classFilter || student.class === classFilter;
        const matchStatus = !statusFilter || student.status === statusFilter;

        return matchSearch && matchClass && matchStatus;
    });

    currentPage = 1;
    updateTable();
}

// Update table with current data and pagination
function updateTable() {
    const start = (currentPage - 1) * recordsPerPage;
    const end = start + recordsPerPage;
    const pageData = filteredStudents.slice(start, end);

    // Clear existing rows
    tableBody.innerHTML = '';

    // Add data rows
    pageData.forEach(student => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                ${student.id}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${student.name}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${student.class}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${student.contact}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                ${getStatusBadge(student.status)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <button onclick="editStudent('${student.id}')" class="text-blue-600 hover:text-blue-900 mr-3">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="openDeleteModal('${student.id}')" class="text-red-600 hover:text-red-900">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });

    // Update pagination info
    pageStart.textContent = Math.min(start + 1, filteredStudents.length);
    pageEnd.textContent = Math.min(end, filteredStudents.length);
    totalStudents.textContent = filteredStudents.length;
}

// Navigation functions
function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        updateTable();
    }
}

function nextPage() {
    const maxPage = Math.ceil(filteredStudents.length / recordsPerPage);
    if (currentPage < maxPage) {
        currentPage++;
        updateTable();
    }
}

// Modal functions
function openAddStudentModal() {
    currentStudentId = null;
    document.getElementById('modalTitle').textContent = 'Add New Student';
    document.getElementById('studentId').value = '';
    document.getElementById('studentName').value = '';
    document.getElementById('studentClass').value = '10A';
    document.getElementById('studentContact').value = '';
    document.getElementById('studentStatus').value = 'active';
    document.getElementById('studentId').disabled = false;
    studentModal.classList.remove('hidden');
}

function openEditModal(student) {
    document.getElementById('modalTitle').textContent = 'Edit Student';
    document.getElementById('studentId').value = student.id;
    document.getElementById('studentName').value = student.name;
    document.getElementById('studentClass').value = student.class;
    document.getElementById('studentContact').value = student.contact;
    document.getElementById('studentStatus').value = student.status;
    document.getElementById('studentId').disabled = true;
    studentModal.classList.remove('hidden');
}

function closeStudentModal() {
    studentModal.classList.add('hidden');
}

function openDeleteModal(studentId) {
    currentStudentId = studentId;
    deleteModal.classList.remove('hidden');
}

function closeDeleteModal() {
    deleteModal.classList.add('hidden');
    currentStudentId = null;
}

// CRUD Operations
function editStudent(studentId) {
    currentStudentId = studentId;
    const student = students.find(s => s.id === studentId);
    if (student) {
        openEditModal(student);
    }
}

function saveStudent(event) {
    event.preventDefault();

    const studentData = {
        id: document.getElementById('studentId').value,
        name: document.getElementById('studentName').value,
        class: document.getElementById('studentClass').value,
        contact: document.getElementById('studentContact').value,
        status: document.getElementById('studentStatus').value
    };

    if (currentStudentId) {
        // Update existing student
        const index = students.findIndex(s => s.id === currentStudentId);
        if (index !== -1) {
            students[index] = studentData;
        }
    } else {
        // Add new student
        if (students.some(s => s.id === studentData.id)) {
            alert('Student ID already exists!');
            return;
        }
        students.push(studentData);
    }

    // Save to localStorage
    localStorage.setItem('students', JSON.stringify(students));
    
    // Update filtered list and table
    filteredStudents = [...students];
    applyFilters();
    
    // Close modal
    closeStudentModal();
}

function confirmDelete() {
    if (currentStudentId) {
        students = students.filter(s => s.id !== currentStudentId);
        localStorage.setItem('students', JSON.stringify(students));
        filteredStudents = [...students];
        applyFilters();
        closeDeleteModal();
    }
}

// Utility functions
function getStatusBadge(status) {
    const badges = {
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-red-100 text-red-800'
    };

    return `
        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${badges[status]}">
            ${status.charAt(0).toUpperCase() + status.slice(1)}
        </span>
    `;
}
