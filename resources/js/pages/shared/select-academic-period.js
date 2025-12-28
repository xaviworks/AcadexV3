/**
 * Select Academic Period Page Scripts
 * 
 * JavaScript functionality for the academic period selection page
 * including year filtering and form validation.
 */

document.addEventListener('DOMContentLoaded', function() {
    const yearFilter = document.getElementById('yearFilter');
    const periodList = document.getElementById('periodList');
    const yearGroups = document.querySelectorAll('.year-group');
    const submitBtn = document.getElementById('submitBtn');
    const radioInputs = document.querySelectorAll('input[name="academic_period_id"]');
    const noResults = document.getElementById('noResults');
    const visibleCount = document.getElementById('visibleCount');

    // Year dropdown filtering
    if (yearFilter) {
        yearFilter.addEventListener('change', function() {
            const selectedYear = this.value;
            let visibleItems = 0;

            yearGroups.forEach(group => {
                const groupYear = group.dataset.year;
                const items = group.querySelectorAll('.period-item');
                const isMatch = !selectedYear || groupYear === selectedYear;
                
                group.classList.toggle('hidden', !isMatch);
                
                if (isMatch) {
                    items.forEach(item => {
                        item.classList.remove('hidden');
                        visibleItems++;
                    });
                }
            });

            // Update no results message
            if (noResults) {
                noResults.style.display = visibleItems === 0 ? 'block' : 'none';
            }
            if (periodList) {
                periodList.style.display = visibleItems === 0 ? 'none' : 'block';
            }
            
            // Update visible count
            if (visibleCount) {
                visibleCount.textContent = visibleItems;
            }

            // Auto-select first visible if current selection is hidden
            const currentSelected = document.querySelector('input[name="academic_period_id"]:checked');
            if (currentSelected) {
                const parentItem = currentSelected.closest('.period-item');
                const parentGroup = currentSelected.closest('.year-group');
                if (parentGroup && parentGroup.classList.contains('hidden')) {
                    const firstVisible = document.querySelector('.year-group:not(.hidden) .period-item input[name="academic_period_id"]');
                    if (firstVisible) {
                        firstVisible.checked = true;
                    }
                }
            }
        });
    }

    // Enable submit button when radio is selected
    radioInputs.forEach(radio => {
        radio.addEventListener('change', function() {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        });
    });

    // Check if any radio is pre-selected (e.g., current period)
    const checkedRadio = document.querySelector('input[name="academic_period_id"]:checked');
    if (checkedRadio && submitBtn) {
        submitBtn.disabled = false;
    }
});
