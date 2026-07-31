const PAGE_SIZES = [10, 25, 50, 100];
const AUTO_ENHANCE_EXCLUDED_SELECTORS = [
  '.acadex-table-card',
  '.dataTables_wrapper',
  '.modal',
  '.grade-table-fullscreen',
  '.target-level-grid',
  '.po-matrix-wrap',
  '.custom-scrollbar',
  '[data-no-acadex-table]',
];

const AUTO_ENHANCE_EXCLUDED_TABLE_CLASSES = [
  'acadex-table',
  'dataTable',
  'print-table',
  'header-table',
  'co-table',
  'po-matrix-table',
  'plo-definition-table',
  'table-sm',
];

function isDataTableManaged(table) {
  return table.classList.contains('dataTable') || table.closest('.dataTables_wrapper');
}

function getRows(table) {
  const body = table.tBodies[0];

  return body ? Array.from(body.rows) : [];
}

function setEmptyRowVisibility(rows, visible) {
  rows.forEach((row) => {
    if (row.cells.length === 1 && row.querySelector('.empty-state, [class*="empty"]')) {
      row.hidden = !visible;
    }
  });
}

function initializeTableCard(card) {
  if (card.dataset.acadexTableInitialized === 'true') {
    return;
  }

  const table = card.querySelector('table.acadex-table');
  const pageSizeSelect = card.querySelector('[data-acadex-page-size]');
  const previousButton = card.querySelector('[data-acadex-prev-page]');
  const nextButton = card.querySelector('[data-acadex-next-page]');
  const pageIndicator = card.querySelector('[data-acadex-page-indicator]');
  const pageSummary = card.querySelector('[data-acadex-page-summary]');
  const toolbar = card.querySelector('[data-acadex-table-toolbar]');
  const pagination = card.querySelector('[data-acadex-pagination]');

  if (!table || !pageSizeSelect || !previousButton || !nextButton || !pageIndicator || !pageSummary) {
    return;
  }

  if (isDataTableManaged(table)) {
    toolbar?.setAttribute('hidden', 'hidden');
    pagination?.setAttribute('hidden', 'hidden');
    return;
  }

  card.dataset.acadexTableInitialized = 'true';

  const state = {
    page: 1,
    pageSize: PAGE_SIZES.includes(Number(pageSizeSelect.value)) ? Number(pageSizeSelect.value) : 10,
  };

  const render = () => {
    const rows = getRows(table);
    const dataRows = rows.filter((row) => row.cells.length > 1 || !row.querySelector('.empty-state, [class*="empty"]'));
    const totalRows = dataRows.length;
    const totalPages = Math.max(1, Math.ceil(totalRows / state.pageSize));
    state.page = Math.min(Math.max(1, state.page), totalPages);

    const startIndex = (state.page - 1) * state.pageSize;
    const endIndex = startIndex + state.pageSize;

    dataRows.forEach((row, index) => {
      row.hidden = index < startIndex || index >= endIndex;
    });

    setEmptyRowVisibility(rows.filter((row) => !dataRows.includes(row)), totalRows === 0);

    previousButton.disabled = state.page <= 1;
    nextButton.disabled = state.page >= totalPages;
    pageIndicator.textContent = `Page ${state.page} of ${totalPages}`;

    if (totalRows === 0) {
      pageSummary.textContent = 'Showing 0 entries';
      return;
    }

    pageSummary.textContent = `Showing ${startIndex + 1} to ${Math.min(endIndex, totalRows)} of ${totalRows} entries`;
  };

  pageSizeSelect.addEventListener('change', () => {
    state.pageSize = PAGE_SIZES.includes(Number(pageSizeSelect.value)) ? Number(pageSizeSelect.value) : 10;
    state.page = 1;
    render();
  });

  previousButton.addEventListener('click', () => {
    state.page -= 1;
    render();
  });

  nextButton.addEventListener('click', () => {
    state.page += 1;
    render();
  });

  render();
}

function shouldAutoEnhanceTable(table) {
  if (!table.tHead || !table.tBodies[0]) {
    return false;
  }

  if (AUTO_ENHANCE_EXCLUDED_TABLE_CLASSES.some((className) => table.classList.contains(className))) {
    return false;
  }

  if (AUTO_ENHANCE_EXCLUDED_SELECTORS.some((selector) => table.closest(selector))) {
    return false;
  }

  return table.classList.contains('table');
}

function createAutoTableCard(table) {
  const responsive = table.closest('.table-responsive');
  const source = responsive || table;
  const card = document.createElement('section');
  card.className = 'acadex-table-card acadex-table-card--auto';
  card.dataset.acadexTableCard = 'true';

  const toolbar = document.createElement('div');
  toolbar.className = 'acadex-table-card__toolbar';
  toolbar.dataset.acadexTableToolbar = '';
  toolbar.innerHTML = `
    <label class="acadex-table-card__length">
      <span>Show</span>
      <select class="form-select form-select-sm" data-acadex-page-size aria-label="Rows per page">
        <option value="10">10</option>
        <option value="25">25</option>
        <option value="50">50</option>
        <option value="100">100</option>
      </select>
      <span>entries</span>
    </label>
  `;

  const pagination = document.createElement('div');
  pagination.className = 'acadex-table-card__pagination';
  pagination.dataset.acadexPagination = '';
  pagination.innerHTML = `
    <div class="acadex-table-card__page-summary" data-acadex-page-summary></div>
    <div class="acadex-table-card__pager" aria-label="Table pagination">
      <button type="button" class="btn btn-outline-success btn-sm" data-acadex-prev-page>Previous</button>
      <span class="acadex-table-card__page-indicator" data-acadex-page-indicator>Page 1</span>
      <button type="button" class="btn btn-outline-success btn-sm" data-acadex-next-page>Next</button>
    </div>
  `;

  table.classList.add('acadex-table');
  table.querySelector('thead')?.classList.remove('table-light', 'table-success');

  source.parentNode?.insertBefore(card, source);
  card.appendChild(toolbar);

  if (responsive) {
    responsive.classList.add('acadex-table-responsive');
    card.appendChild(responsive);
  } else {
    const wrapper = document.createElement('div');
    wrapper.className = 'acadex-table-responsive';
    card.appendChild(wrapper);
    wrapper.appendChild(table);
  }

  card.appendChild(pagination);
  initializeTableCard(card);
}

function autoEnhanceLegacyTables() {
  document.querySelectorAll('table.table').forEach((table) => {
    if (shouldAutoEnhanceTable(table)) {
      createAutoTableCard(table);
    }
  });
}

export function initializeDataTables() {
  document.querySelectorAll('[data-acadex-table-card="true"]').forEach(initializeTableCard);
}

export function moveDataTablesControlsToHeader(tableOrSelector) {
  const table =
    typeof tableOrSelector === 'string' ? document.querySelector(tableOrSelector) : tableOrSelector;

  if (!table?.id) {
    return false;
  }

  const card = table.closest('.acadex-table-card');
  const headerControls = card?.querySelector('[data-acadex-datatables-header-controls]');
  const filter = document.getElementById(`${table.id}_filter`);
  const length = document.getElementById(`${table.id}_length`);

  if (!card || !headerControls || !filter || !length) {
    return false;
  }

  headerControls.append(filter, length);
  card.dataset.acadexDatatablesControlsInHeader = 'true';

  return true;
}

window.AcadexDataTable = {
  ...(window.AcadexDataTable || {}),
  initializeDataTables,
  moveDataTablesControlsToHeader,
};

document.addEventListener('DOMContentLoaded', () => {
  initializeDataTables();
  window.setTimeout(autoEnhanceLegacyTables, 0);
});
