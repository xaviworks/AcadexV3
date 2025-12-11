/**
 * Course Outcomes Wildcards Page JavaScript
 * Handles generation form, year filtering, and subject card interactions
 */

// Submit Generate Form Function
export function submitGenerateForm() {
    const pageData = window.pageData || {};
    const form = document.getElementById('generateCOForm');
    if (!form) return false;
    
    const submitBtn = document.getElementById('generateSubmitBtn');
    if (!submitBtn) return false;
    
    const originalText = submitBtn.innerHTML;
    const generationModeEl = document.querySelector('input[name="generation_mode"]:checked');
    const generationMode = generationModeEl?.value || 'missing_only';
    const passwordField = document.getElementById('passwordConfirmation');
    const validatePasswordUrl = pageData.validatePasswordUrl;
    
    // Validate override mode requirements
    if (generationMode === 'override_all') {
        if (!passwordField || !passwordField.value.trim()) {
            if (passwordField) {
                // Show error for missing password
                passwordField.classList.add('is-invalid');
                passwordField.focus();
                
                // Create or update error message
                let errorDiv = passwordField.parentNode.querySelector('.invalid-feedback');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    passwordField.parentNode.appendChild(errorDiv);
                }
                errorDiv.textContent = 'Password confirmation is required for override operations';
            }
            
            return false;
        }
        
        // Validate password via AJAX before proceeding
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Verifying password...';
        
        // Create AJAX request to validate password
        fetch(validatePasswordUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                password: passwordField.value
            })
        })
        .then(response => response.json())
        .then(async data => {
            if (data.valid) {
                // Password is valid; proceed directly to submit (no extra backdrop)
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Overriding COs...';
                form.submit();
            } else {
                // Password is invalid
                passwordField.classList.add('is-invalid');
                passwordField.focus();
                passwordField.select();
                
                let errorDiv = passwordField.parentNode.querySelector('.invalid-feedback');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    passwordField.parentNode.appendChild(errorDiv);
                }
                errorDiv.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i>Incorrect password. Please try again.';
                
                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Password validation error:', error);
            
            // Show error message
            if (passwordField) {
                passwordField.classList.add('is-invalid');
                let errorDiv = passwordField.parentNode.querySelector('.invalid-feedback');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    passwordField.parentNode.appendChild(errorDiv);
                }
                errorDiv.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i>Error validating password. Please try again.';
            }
            
            // Reset button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
        
        return false; // Prevent form submission until password is validated
    } else {
        // For missing_only mode, proceed normally
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generating...';
        form.submit();
    }
}

// Filter by year
function filterByYear(selectedYear) {
    const yearSections = document.querySelectorAll('.year-section');
    
    yearSections.forEach(section => {
        if (selectedYear === 'all' || section.dataset.year == selectedYear) {
            section.style.display = 'block';
            section.style.animation = 'fadeInUp 0.6s ease-out';
        } else {
            section.style.display = 'none';
        }
    });
    
    // Update active filter button
    updateActiveFilterButton(selectedYear);
}

// Update active filter button
function updateActiveFilterButton(activeFilter) {
    document.querySelectorAll('.year-filter-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-success');
        
        if (btn.dataset.year == activeFilter) {
            btn.classList.add('active');
            btn.classList.remove('btn-outline-success');
            btn.classList.add('btn-success');
        }
    });
}

// Enhanced smooth scrolling for year navigation
function scrollToYear(year) {
    const target = document.getElementById(`year-${year}`);
    if (target) {
        const header = document.querySelector('.year-navigation');
        const headerHeight = header ? header.offsetHeight + 20 : 120;
        
        const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight;
        
        window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
        });
        
        // Add visual feedback
        target.style.transform = 'scale(1.02)';
        setTimeout(() => {
            target.style.transform = 'scale(1)';
        }, 300);
    }
}

export function initCourseOutcomesWildcardsPage() {
    const pageData = window.pageData || {};
    const isChairpersonOrGE = pageData.isChairpersonOrGE || false;
    const hasValidationErrors = pageData.hasValidationErrors || false;
    const oldGenerationMode = pageData.oldGenerationMode || '';
    const oldYearLevels = pageData.oldYearLevels || [];
    
    // Store the modal instance reference
    let generateCOModalInstance = null;
    
    // Helper function to safely open the Generate CO modal
    function openGenerateCOModal() {
        const modalEl = document.getElementById('generateCOModal');
        if (!modalEl) return;
        
        // Create a new modal instance if it doesn't exist, or get the existing one
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            generateCOModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl, {
                backdrop: false,
                keyboard: true
            });
            generateCOModalInstance.show();
        }
    }
    
    // Generation-specific functionality (only for chairpersons and GE coordinators)
    if (isChairpersonOrGE) {
        // Set up the open modal button handler
        const openModalBtn = document.getElementById('openGenerateCOModalBtn');
        if (openModalBtn) {
            openModalBtn.addEventListener('click', openGenerateCOModal);
        }
        
        const allYearsCheckbox = document.getElementById('year_all');
        const yearSpecificCheckboxes = document.querySelectorAll('.year-specific');
        const overrideWarning = document.getElementById('overrideWarning');
        const passwordField = document.getElementById('passwordConfirmation');
        const modeRadios = document.querySelectorAll('input[name="generation_mode"]');
        const generateBtn = document.getElementById('generateSubmitBtn');
        
        // Handle generation mode changes
        modeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'override_all') {
                    if (overrideWarning) overrideWarning.style.display = 'block';
                    if (passwordField) {
                        passwordField.disabled = false;
                        passwordField.required = true;
                    }
                    if (generateBtn) {
                        generateBtn.className = 'btn btn-danger rounded-pill fw-semibold';
                        generateBtn.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Override Course Outcomes';
                    }
                } else {
                    if (overrideWarning) overrideWarning.style.display = 'none';
                    if (passwordField) {
                        passwordField.disabled = true;
                        passwordField.required = false;
                        passwordField.value = '';
                        passwordField.classList.remove('is-invalid');
                    }
                    if (generateBtn) {
                        generateBtn.className = 'btn btn-success rounded-pill fw-semibold';
                        generateBtn.innerHTML = '<i class="bi bi-magic me-1"></i>Generate Course Outcomes';
                    }
                    
                    // Remove any error messages
                    if (passwordField) {
                        const errorDiv = passwordField.parentNode.querySelector('.invalid-feedback');
                        if (errorDiv) {
                            errorDiv.remove();
                        }
                    }
                }
            });
        });
        
        // Password field validation
        if (passwordField) {
            passwordField.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                    const errorDiv = this.parentNode.querySelector('.invalid-feedback');
                    if (errorDiv) {
                        errorDiv.remove();
                    }
                }
            });
        }
        
        // Auto-show modal if there are validation errors (form was submitted with errors)
        if (hasValidationErrors && oldGenerationMode) {
            // Use the helper function to open the modal
            openGenerateCOModal();
            
            // Restore form state
            if (oldGenerationMode) {
                const modeInput = document.querySelector(`input[name="generation_mode"][value="${oldGenerationMode}"]`);
                if (modeInput) {
                    modeInput.checked = true;
                    modeInput.dispatchEvent(new Event('change'));
                }
            }
            
            // Restore year level selections
            if (oldYearLevels && oldYearLevels.length > 0) {
                oldYearLevels.forEach(year => {
                    const checkbox = document.querySelector(`input[name="year_levels[]"][value="${year}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }
        }
        
        if (allYearsCheckbox) {
            allYearsCheckbox.addEventListener('change', function() {
                yearSpecificCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                    checkbox.disabled = this.checked;
                });
            });
            
            yearSpecificCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        allYearsCheckbox.checked = false;
                    }
                    
                    // If no specific years are selected, check "All Years"
                    const anyChecked = Array.from(yearSpecificCheckboxes).some(cb => cb.checked);
                    if (!anyChecked) {
                        allYearsCheckbox.checked = true;
                        yearSpecificCheckboxes.forEach(cb => cb.disabled = true);
                    } else {
                        yearSpecificCheckboxes.forEach(cb => cb.disabled = false);
                    }
                });
            });
        }
    }

    // Filter button event listeners
    document.querySelectorAll('.year-filter-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedYear = this.dataset.year;
            filterByYear(selectedYear);
            
            // If filtering to a specific year, scroll to it
            if (selectedYear !== 'all') {
                setTimeout(() => scrollToYear(selectedYear), 100);
            }
        });
    });
    
    // Modern subject card interactions
    document.querySelectorAll('.subject-card[data-url]').forEach(card => {
        card.addEventListener('click', function() {
            window.location.href = this.dataset.url;
        });
        
        // Add accessibility support
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'button');
        card.setAttribute('aria-label', 'Select subject');
        
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
    
    // Keyboard navigation support for filtering
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
            const currentActive = document.querySelector('.year-filter-btn.active');
            if (!currentActive) return;
            
            const allButtons = Array.from(document.querySelectorAll('.year-filter-btn'));
            const currentIndex = allButtons.indexOf(currentActive);
            
            let nextIndex;
            if (e.key === 'ArrowLeft') {
                nextIndex = currentIndex > 0 ? currentIndex - 1 : allButtons.length - 1;
            } else {
                nextIndex = currentIndex < allButtons.length - 1 ? currentIndex + 1 : 0;
            }
            
            const nextButton = allButtons[nextIndex];
            if (nextButton) {
                nextButton.click();
                e.preventDefault();
            }
        }
    });
    
    // Add enhanced CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @keyframes ripple {
            0% {
                transform: scale(0);
                opacity: 0.6;
            }
            100% {
                transform: scale(2);
                opacity: 0;
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .year-level-section {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .year-section {
            transition: all 0.3s ease;
        }
        
        .year-filter-btn {
            transition: all 0.3s ease;
        }
        
        .year-filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }
        
        .year-filter-btn.active {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(25, 135, 84, 0.3);
        }
    `;
    document.head.appendChild(style);
    
    // Initialize "Show All" as active
    const showAllButton = document.querySelector('.year-filter-btn[data-year="all"]');
    if (showAllButton) {
        showAllButton.classList.add('active');
    }
    
    // Global functions
    window.scrollToYear = scrollToYear;
    window.filterByYear = filterByYear;
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', initCourseOutcomesWildcardsPage);

// Expose functions globally
window.submitGenerateForm = submitGenerateForm;
window.initCourseOutcomesWildcardsPage = initCourseOutcomesWildcardsPage;
