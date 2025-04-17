</main>

    <!-- Footer -->
    <footer class="bg-white shadow-lg mt-8">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex justify-between items-center">
                <div class="text-gray-600">
                    &copy; <?php echo date('Y'); ?> SDCKL Library Management System
                </div>
                <div class="text-gray-500 text-sm">
                    Version 1.0.0
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Toggle user menu dropdown
        const userMenuButton = document.getElementById('user-menu-button');
        const userMenuDropdown = document.getElementById('user-menu-dropdown');
        
        if (userMenuButton && userMenuDropdown) {
            userMenuButton.addEventListener('click', () => {
                userMenuDropdown.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (event) => {
                if (!userMenuButton.contains(event.target) && !userMenuDropdown.contains(event.target)) {
                    userMenuDropdown.classList.add('hidden');
                }
            });
        }

        // Toggle mobile menu
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }

        // Initialize tooltips
        const tooltips = document.querySelectorAll('[data-tooltip]');
        tooltips.forEach(tooltip => {
            tooltip.addEventListener('mouseenter', (e) => {
                const tip = document.createElement('div');
                tip.className = 'absolute bg-gray-800 text-white text-xs rounded py-1 px-2 -mt-8';
                tip.textContent = e.target.getAttribute('data-tooltip');
                e.target.appendChild(tip);
            });
            
            tooltip.addEventListener('mouseleave', (e) => {
                const tip = e.target.querySelector('div');
                if (tip) {
                    tip.remove();
                }
            });
        });

        // Initialize datepickers
        const datepickers = document.querySelectorAll('input[type="date"]');
        datepickers.forEach(datepicker => {
            // Set min date to today for future dates
            if (datepicker.getAttribute('data-future-only') === 'true') {
                datepicker.min = new Date().toISOString().split('T')[0];
            }
        });

        // Form validation
        const forms = document.querySelectorAll('form[data-validate]');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('border-red-500');
                        
                        // Add error message
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'text-red-500 text-sm mt-1';
                        errorDiv.textContent = 'This field is required';
                        
                        // Remove existing error message if any
                        const existingError = field.parentNode.querySelector('.text-red-500');
                        if (existingError) {
                            existingError.remove();
                        }
                        
                        field.parentNode.appendChild(errorDiv);
                    } else {
                        field.classList.remove('border-red-500');
                        const errorDiv = field.parentNode.querySelector('.text-red-500');
                        if (errorDiv) {
                            errorDiv.remove();
                        }
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                }
            });
        });

        // Dynamic search functionality
        const searchInputs = document.querySelectorAll('input[data-search]');
        searchInputs.forEach(input => {
            let timeout = null;
            
            input.addEventListener('input', (e) => {
                clearTimeout(timeout);
                
                timeout = setTimeout(() => {
                    const searchUrl = input.getAttribute('data-search');
                    const searchTerm = e.target.value.trim();
                    
                    if (searchTerm.length >= 2) {
                        fetch(`${searchUrl}?q=${encodeURIComponent(searchTerm)}`)
                            .then(response => response.json())
                            .then(data => {
                                const resultsContainer = document.querySelector(input.getAttribute('data-results'));
                                if (resultsContainer) {
                                    resultsContainer.innerHTML = ''; // Clear previous results
                                    
                                    if (data.length > 0) {
                                        data.forEach(item => {
                                            const div = document.createElement('div');
                                            div.className = 'p-2 hover:bg-gray-100 cursor-pointer';
                                            div.textContent = item.title || item.name || item.full_name;
                                            div.addEventListener('click', () => {
                                                input.value = div.textContent;
                                                resultsContainer.innerHTML = '';
                                            });
                                            resultsContainer.appendChild(div);
                                        });
                                    } else {
                                        resultsContainer.innerHTML = '<div class="p-2 text-gray-500">No results found</div>';
                                    }
                                }
                            })
                            .catch(error => console.error('Search error:', error));
                    }
                }, 300);
            });
        });
    </script>
</body>
</html>
