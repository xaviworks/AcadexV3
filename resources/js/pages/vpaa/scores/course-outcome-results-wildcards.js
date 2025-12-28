/**
 * VPAA Course Outcome Results Wildcards Page JavaScript
 * Handles subject card selection with search and pagination
 */

export function initVpaaCourseOutcomeResultsWildcardsPage() {
  // Subject card click handlers
  document.querySelectorAll('.subject-card[data-url]').forEach((card) => {
    card.addEventListener('click', function () {
      window.location.href = this.dataset.url;
    });
  });

  // Client-side search + pagination
  const grid = document.getElementById('subject-selection');
  if (!grid) return;

  const cards = Array.from(grid.querySelectorAll('.col-md-4'));
  const searchInput = document.getElementById('subject-search');
  const perPageSelect = document.getElementById('items-per-page');
  const pagination = document.getElementById('subjects-pagination');
  const countEl = document.getElementById('subjects-count');

  let filtered = cards.slice();
  let currentPage = 1;

  function getPerPage() {
    const val = parseInt(perPageSelect?.value || '9', 10);
    return val === 0 ? Number.MAX_SAFE_INTEGER : val;
  }

  function matchesSearch(card, term) {
    if (!term) return true;
    const text = card.textContent.toLowerCase();
    return text.includes(term);
  }

  function applyFilter() {
    const term = (searchInput?.value || '').trim().toLowerCase();
    filtered = cards.filter((c) => matchesSearch(c, term));
    currentPage = 1;
    render();
  }

  function render() {
    const perPage = getPerPage();
    const total = filtered.length;
    const totalPages = Math.max(1, Math.ceil(total / perPage));
    currentPage = Math.min(currentPage, totalPages);

    // Hide all, then show slice
    cards.forEach((c) => (c.style.display = 'none'));
    const start = (currentPage - 1) * perPage;
    const end = perPage === Number.MAX_SAFE_INTEGER ? total : start + perPage;
    filtered.slice(start, end).forEach((c) => (c.style.display = ''));

    // Update count text
    if (countEl) {
      countEl.textContent = `Showing ${Math.min(total, end) === 0 ? 0 : start + 1}-${Math.min(total, end)} of ${total}`;
    }

    // Build pagination
    if (pagination) {
      pagination.innerHTML = '';
      if (perPage === Number.MAX_SAFE_INTEGER || totalPages <= 1) return;

      const createItem = (label, page, disabled = false, active = false) => {
        const li = document.createElement('li');
        li.className = `page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}`;
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = label;
        a.addEventListener('click', (e) => {
          e.preventDefault();
          if (disabled || page === currentPage) return;
          currentPage = page;
          render();
        });
        li.appendChild(a);
        return li;
      };

      pagination.appendChild(createItem('«', Math.max(1, currentPage - 1), currentPage === 1));
      const maxPages = 7; // compact
      let startPage = Math.max(1, currentPage - 3);
      let endPage = Math.min(totalPages, startPage + maxPages - 1);
      if (endPage - startPage + 1 < maxPages) startPage = Math.max(1, endPage - (maxPages - 1));
      for (let p = startPage; p <= endPage; p++) {
        pagination.appendChild(createItem(String(p), p, false, p === currentPage));
      }
      pagination.appendChild(createItem('»', Math.min(totalPages, currentPage + 1), currentPage === totalPages));
    }
  }

  searchInput?.addEventListener('input', () => {
    // simple debounce
    clearTimeout(searchInput._t);
    searchInput._t = setTimeout(applyFilter, 150);
  });
  perPageSelect?.addEventListener('change', () => {
    currentPage = 1;
    render();
  });

  // Initial
  applyFilter();
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', initVpaaCourseOutcomeResultsWildcardsPage);

// Expose function globally
window.initVpaaCourseOutcomeResultsWildcardsPage = initVpaaCourseOutcomeResultsWildcardsPage;
