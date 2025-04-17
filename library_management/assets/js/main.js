/**
 * Main JavaScript file for the Library Management System
 */

// Toast notification handler
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg transform transition-transform duration-300 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white`;
    toast.textContent = message;
    document.body.appendChild(toast);

    // Remove toast after 3 seconds
    setTimeout(() => {
        toast.classList.add('translate-y-full');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Form validation
function validateForm(formId, rules) {
    const form = document.getElementById(formId);
    if (!form) return true;

    let isValid = true;
    const errors = [];

    rules.forEach(rule => {
        const field = form.querySelector(`[name="${rule.field}"]`);
        if (!field) return;

        const value = field.value.trim();
        
        // Required field
        if (rule.required && !value) {
            isValid = false;
            errors.push(`${rule.label} is required`);
            field.classList.add('border-red-500');
        }

        // Minimum length
        if (rule.minLength && value.length < rule.minLength) {
            isValid = false;
            errors.push(`${rule.label} must be at least ${rule.minLength} characters`);
            field.classList.add('border-red-500');
        }

        // Maximum length
        if (rule.maxLength && value.length > rule.maxLength) {
            isValid = false;
            errors.push(`${rule.label} must not exceed ${rule.maxLength} characters`);
            field.classList.add('border-red-500');
        }

        // Pattern match
        if (rule.pattern && !rule.pattern.test(value)) {
            isValid = false;
            errors.push(`${rule.label} is invalid`);
            field.classList.add('border-red-500');
        }

        // Custom validation
        if (rule.validate && !rule.validate(value)) {
            isValid = false;
            errors.push(rule.errorMessage);
            field.classList.add('border-red-500');
        }

        // Remove error styling on input
        field.addEventListener('input', () => {
            field.classList.remove('border-red-500');
        });
    });

    // Show errors if any
    if (!isValid) {
        errors.forEach(error => showToast(error, 'error'));
    }

    return isValid;
}

// Modal handler
function toggleModal(modalId, show = true) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    if (show) {
        modal.classList.remove('hidden');
        // Close modal when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                toggleModal(modalId, false);
            }
        });
    } else {
        modal.classList.add('hidden');
    }
}

// Confirmation dialog
function confirmAction(message) {
    return new Promise((resolve) => {
        const dialog = document.createElement('div');
        dialog.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center';
        dialog.innerHTML = `
            <div class="bg-white p-6 rounded-lg shadow-xl">
                <p class="mb-4">${message}</p>
                <div class="flex justify-end space-x-2">
                    <button class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300" onclick="this.closest('.fixed').remove(); resolve(false);">
                        Cancel
                    </button>
                    <button class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600" onclick="this.closest('.fixed').remove(); resolve(true);">
                        Confirm
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(dialog);
    });
}

// Date formatter
function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Currency formatter
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Search functionality
function initializeSearch(inputId, tableId, columns) {
    const searchInput = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!searchInput || !table) return;

    searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            let found = false;
            columns.forEach(colIndex => {
                const cell = row.cells[colIndex];
                if (cell && cell.textContent.toLowerCase().includes(searchTerm)) {
                    found = true;
                }
            });
            row.style.display = found ? '' : 'none';
        });
    });
}

// Table sorting
function initializeTableSort(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const headers = table.querySelectorAll('th');
    headers.forEach((header, index) => {
        if (header.classList.contains('sortable')) {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                const direction = header.classList.contains('sort-asc') ? -1 : 1;
                sortTable(table, index, direction);
                
                // Update sort indicators
                headers.forEach(h => {
                    h.classList.remove('sort-asc', 'sort-desc');
                });
                header.classList.add(direction === 1 ? 'sort-asc' : 'sort-desc');
            });
        }
    });
}

function sortTable(table, column, direction) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    const sortedRows = rows.sort((a, b) => {
        const aValue = a.cells[column].textContent.trim();
        const bValue = b.cells[column].textContent.trim();

        if (!isNaN(aValue) && !isNaN(bValue)) {
            return (Number(aValue) - Number(bValue)) * direction;
        }
        return aValue.localeCompare(bValue) * direction;
    });

    while (tbody.firstChild) {
        tbody.removeChild(tbody.firstChild);
    }

    tbody.append(...sortedRows);
}

// Initialize all interactive elements
document.addEventListener('DOMContentLoaded', () => {
    // Initialize all sortable tables
    document.querySelectorAll('table[data-sortable]').forEach(table => {
        initializeTableSort(table.id);
    });

    // Initialize all search inputs
    document.querySelectorAll('[data-search-input]').forEach(input => {
        const tableId = input.dataset.searchTable;
        const columns = input.dataset.searchColumns.split(',').map(Number);
        initializeSearch(input.id, tableId, columns);
    });

    // Initialize all tooltips
    document.querySelectorAll('[data-tooltip]').forEach(element => {
        element.addEventListener('mouseenter', (e) => {
            const tooltip = document.createElement('div');
            tooltip.className = 'absolute bg-gray-800 text-white px-2 py-1 rounded text-sm z-50';
            tooltip.textContent = e.target.dataset.tooltip;
            document.body.appendChild(tooltip);

            const rect = e.target.getBoundingClientRect();
            tooltip.style.top = `${rect.bottom + 5}px`;
            tooltip.style.left = `${rect.left}px`;

            e.target.addEventListener('mouseleave', () => tooltip.remove());
        });
    });
});
