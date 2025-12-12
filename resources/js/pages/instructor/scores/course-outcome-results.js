/**
 * Instructor Course Outcome Results page logic
 * Restores display toggles, term switching, and print/export helpers.
 */

let currentTerm = null;

function isCourseOutcomeResultsPage() {
    return document.querySelector('[data-page="instructor.course-outcome-results"]');
}

function setDisplayType(type, icon, text) {
    const currentIcon = document.getElementById('currentIcon');
    const currentText = document.getElementById('currentText');
    if (currentIcon) currentIcon.textContent = icon;
    if (currentText) currentText.textContent = text;

    const scoreTypeSelect = document.getElementById('scoreType');
    if (scoreTypeSelect) scoreTypeSelect.value = type;

    document.querySelectorAll('.dropdown-item').forEach(item => item.classList.remove('active'));
    document.querySelectorAll('.dropdown-item').forEach(item => {
        const handler = item.getAttribute('onclick') || '';
        if (handler.includes(`'${type}'`)) item.classList.add('active');
    });

    const dropdownElement = document.getElementById('displayTypeDropdown');
    if (dropdownElement) {
        const dropdown = bootstrap.Dropdown.getInstance(dropdownElement);
        if (dropdown) dropdown.hide();
    }

    const termStepperContainer = document.getElementById('term-navigation-container');
    const compactStepper = document.querySelector('.compact-stepper');
    const stepperColumn = document.querySelector('.col-md-6.text-end');

    if (type === 'passfail' || type === 'copasssummary') {
        if (termStepperContainer) {
            termStepperContainer.style.display = 'none';
            termStepperContainer.style.visibility = 'hidden';
        }
        if (compactStepper) {
            compactStepper.style.display = 'none';
            compactStepper.style.visibility = 'hidden';
        }
        if (stepperColumn) stepperColumn.style.display = 'none';
    } else {
        if (termStepperContainer) {
            termStepperContainer.style.display = 'flex';
            termStepperContainer.style.visibility = 'visible';
        }
        if (compactStepper) {
            compactStepper.style.display = 'flex';
            compactStepper.style.visibility = 'visible';
        }
        if (stepperColumn) stepperColumn.style.display = 'block';
    }

    toggleScoreTypeWithValue(type);
}

function toggleScoreTypeWithValue(type) {
    const passfailTable = document.getElementById('passfail-table');
    const copasssummaryTable = document.getElementById('copasssummary-table');
    const mainTables = document.querySelectorAll('.main-table');
    const termTables = document.querySelectorAll('.term-table');
    const summaryLabel = document.getElementById('summaryLabel');
    const termSummaryLabels = document.querySelectorAll('.term-summary-label');
    const termStepperContainer = document.getElementById('term-navigation-container');

    if (type === 'passfail') {
        if (passfailTable) passfailTable.style.display = 'block';
        if (copasssummaryTable) copasssummaryTable.style.display = 'none';
        mainTables.forEach(tbl => (tbl.style.display = 'none'));
        termTables.forEach(tbl => (tbl.style.display = 'none'));
        if (termStepperContainer) {
            termStepperContainer.style.display = 'none';
            termStepperContainer.style.visibility = 'hidden';
        }
        document.querySelectorAll('.passfail-term-table').forEach(tbl => (tbl.style.display = 'none'));
    } else if (type === 'copasssummary') {
        if (passfailTable) passfailTable.style.display = 'none';
        if (copasssummaryTable) copasssummaryTable.style.display = 'block';
        mainTables.forEach(tbl => (tbl.style.display = 'none'));
        termTables.forEach(tbl => (tbl.style.display = 'none'));
        if (termStepperContainer) {
            termStepperContainer.style.display = 'none';
            termStepperContainer.style.visibility = 'hidden';
        }
        document.querySelectorAll('.summary-term-table').forEach(tbl => (tbl.style.display = 'none'));
    } else {
        if (passfailTable) passfailTable.style.display = 'none';
        if (copasssummaryTable) copasssummaryTable.style.display = 'none';
        if (termStepperContainer) {
            termStepperContainer.style.display = 'flex';
            termStepperContainer.style.visibility = 'visible';
        }
        document.querySelectorAll('.passfail-term-table').forEach(tbl => (tbl.style.display = 'none'));
        document.querySelectorAll('.summary-term-table').forEach(tbl => (tbl.style.display = 'none'));

        if (!currentTerm) {
            mainTables.forEach(tbl => (tbl.style.display = 'block'));
            termTables.forEach(tbl => (tbl.style.display = 'none'));
        } else {
            mainTables.forEach(tbl => (tbl.style.display = 'none'));
            termTables.forEach(tbl => (tbl.style.display = 'none'));
            const activeTerm = document.getElementById(`term-${currentTerm}`);
            if (activeTerm) activeTerm.style.display = 'block';
        }

        document.querySelectorAll('.score-value').forEach(el => {
            el.style.display = 'inline';
            const score = el.getAttribute('data-score');
            const percent = el.getAttribute('data-percentage');
            el.classList.remove('text-success', 'text-danger');
            if (type === 'score') {
                el.textContent = score;
            } else {
                el.textContent = percent !== '' && percent !== null ? `${percent}%` : '-';
                if (type === 'percentage' && percent !== '' && percent !== null && percent !== '-') {
                    const percentValue = parseFloat(percent);
                    if (percentValue >= 75) {
                        el.classList.add('text-success');
                    } else {
                        el.classList.add('text-danger');
                    }
                }
            }
        });
    }

    if (type === 'percentage') {
        if (summaryLabel && summaryLabel.closest('tr')) summaryLabel.closest('tr').style.display = 'none';
        termSummaryLabels.forEach(label => label.closest('tr').style.display = 'none');
    } else {
        if (summaryLabel && summaryLabel.closest('tr')) {
            summaryLabel.closest('tr').style.display = '';
            summaryLabel.textContent = 'Total number of items';
        }
        termSummaryLabels.forEach(label => {
            if (label.closest('tr')) {
                label.closest('tr').style.display = '';
                label.textContent = 'Total number of items';
            }
        });
    }
}

function toggleScoreType() {
    const scoreTypeEl = document.getElementById('scoreType');
    if (!scoreTypeEl) return;
    toggleScoreTypeWithValue(scoreTypeEl.value);
}

function switchTerm(term, index) {
    currentTerm = term;
    const scoreTypeEl = document.getElementById('scoreType');
    const scoreType = scoreTypeEl ? scoreTypeEl.value : 'raw';

    const combinedTable = document.getElementById('combined-table');
    const termTables = document.querySelectorAll('.term-table');
    const passfailTable = document.getElementById('passfail-table');
    const copasssummaryTable = document.getElementById('copasssummary-table');
    const passfailTermTables = document.querySelectorAll('.passfail-term-table');
    const summaryTermTables = document.querySelectorAll('.summary-term-table');

    if (combinedTable) combinedTable.style.display = 'none';
    termTables.forEach(tbl => (tbl.style.display = 'none'));

    if (scoreType === 'passfail') {
        if (passfailTable) passfailTable.style.display = 'none';
        passfailTermTables.forEach(tbl => (tbl.style.display = 'none'));
        const activePassfailTable = document.getElementById(`passfail-term-${term}`);
        if (activePassfailTable) activePassfailTable.style.display = 'block';
    } else if (scoreType === 'copasssummary') {
        if (copasssummaryTable) copasssummaryTable.style.display = 'none';
        summaryTermTables.forEach(tbl => (tbl.style.display = 'none'));
        const activeSummaryTable = document.getElementById(`summary-term-${term}`);
        if (activeSummaryTable) activeSummaryTable.style.display = 'block';
    } else {
        const activeTable = document.getElementById(`term-${term}`);
        if (activeTable) activeTable.style.display = 'block';
    }

    const allSteps = document.querySelectorAll('.compact-step');
    allSteps.forEach((step, i) => {
        step.classList.remove('active', 'completed', 'upcoming');
        if (i === 0) {
            step.classList.add('completed');
        } else {
            const termIndex = i - 1;
            if (termIndex < index) {
                step.classList.add('completed');
            } else if (termIndex === index) {
                step.classList.add('active');
            } else {
                step.classList.add('upcoming');
            }
        }
    });

    const type = document.getElementById('scoreType')?.value || 'score';
    document.querySelectorAll('.score-value').forEach(el => {
        const score = el.getAttribute('data-score');
        const percent = el.getAttribute('data-percentage');
        el.textContent = type === 'score' ? score : (percent !== '' && percent !== null ? `${percent}%` : '-');
    });
}

function showAllTerms() {
    currentTerm = null;
    const scoreType = document.getElementById('scoreType')?.value || 'score';

    const termTables = document.querySelectorAll('.term-table');
    const passfailTermTables = document.querySelectorAll('.passfail-term-table');
    const summaryTermTables = document.querySelectorAll('.summary-term-table');

    termTables.forEach(tbl => (tbl.style.display = 'none'));
    passfailTermTables.forEach(tbl => (tbl.style.display = 'none'));
    summaryTermTables.forEach(tbl => (tbl.style.display = 'none'));

    if (scoreType === 'passfail') {
        const passfailTable = document.getElementById('passfail-table');
        if (passfailTable) passfailTable.style.display = 'block';
    } else if (scoreType === 'copasssummary') {
        const copasssummaryTable = document.getElementById('copasssummary-table');
        if (copasssummaryTable) copasssummaryTable.style.display = 'block';
    } else {
        const combinedTable = document.getElementById('combined-table');
        if (combinedTable) combinedTable.style.display = 'block';
    }

    const allSteps = document.querySelectorAll('.compact-step');
    allSteps.forEach((step, i) => {
        step.classList.remove('active', 'completed', 'upcoming');
        if (i === 0) {
            step.classList.add('active');
        } else {
            step.classList.add('upcoming');
        }
    });

    toggleScoreType();
}

function coPrintTable() {
    coPrintSpecificTable('combined');
}

function coPrintSpecificTable(tableType) {
    const bannerUrl = window.bannerUrl || '/images/banner-header.png';
    const academicPeriod = window.academicPeriod || 'N/A';
    const semester = window.semester || 'N/A';
    const currentDate = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    const subjectInfo = window.subjectInfo || 'Course Outcome Results';
    const courseCode = window.courseCode || 'N/A';
    const subjectDescription = window.subjectDescription || 'N/A';
    const units = window.units || 'N/A';
    const courseSection = window.courseSection || 'N/A';

    let content = '';
    let reportTitle = '';
    switch (tableType) {
        case 'prelim':
            content = getPrintTableContent('prelim');
            reportTitle = 'Course Outcome Attainment Results - Prelim Term';
            break;
        case 'midterm':
            content = getPrintTableContent('midterm');
            reportTitle = 'Course Outcome Attainment Results - Midterm';
            break;
        case 'prefinal':
            content = getPrintTableContent('prefinal');
            reportTitle = 'Course Outcome Attainment Results - Prefinal Term';
            break;
        case 'final':
            content = getPrintTableContent('final');
            reportTitle = 'Course Outcome Attainment Results - Final Term';
            break;
        case 'combined':
            content = getPrintTableContent('combined');
            reportTitle = 'Course Outcome Attainment Results - All Terms Combined';
            break;
        case 'passfail':
            content = getPassFailContent();
            reportTitle = 'Course Outcome Pass/Fail Analysis Report';
            break;
        case 'copasssummary':
            content = getCourseOutcomeSummaryContent();
            reportTitle = 'Course Outcomes Summary Dashboard Report';
            break;
        case 'all':
            content = getAllTablesContent();
            reportTitle = 'Complete Course Outcome Attainment Report';
            break;
        default:
            content = getPrintTableContent('combined');
            reportTitle = 'Course Outcome Attainment Results';
    }

    const printWindow = window.open('', '', 'width=900,height=650');
    if (!printWindow) return;

    printWindow.document.write(`
        <html>
            <head>
                <title>${reportTitle}</title>
                <style>
                    @media print {
                        @page { size: A4 portrait; margin: 0.75in 0.5in; }
                        body { font-size: 10px; }
                        table { font-size: 9px; }
                        .banner { max-height: 100px; }
                        .report-title { font-size: 16px; }
                        .percentage-value { color: #000000 !important; }
                        .text-success, .text-danger { color: #000000 !important; }
                    }
                    body { font-family: 'Arial', sans-serif; margin: 0; padding: 20px; color: #333; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; line-height: 1.6; }
                    .banner { width: 100%; max-height: 130px; object-fit: contain; margin-bottom: 15px; }
                    .header-content { margin-bottom: 20px; }
                    .report-title { font-size: 20px; font-weight: bold; text-align: center; margin: 15px 0; text-transform: uppercase; letter-spacing: 1px; color: #4a7c59; border-bottom: 2px solid #4a7c59; padding-bottom: 8px; }
                    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; background-color: #fff; font-size: 11px; border: 2px solid #4a7c59; }
                    .header-table td { padding: 8px 12px; border: 1px solid #2d4a35; }
                    .header-label { font-weight: bold; width: 120px; background-color: #4a7c59; color: #fff; }
                    .header-value { font-family: 'Arial', sans-serif; font-weight: 500; }
                    .print-table { width: 100%; border-collapse: collapse; border: 2px solid #4a7c59; background-color: #fff; margin-top: 15px; font-size: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                    .print-table th, .print-table td { border: 1px solid #2d4a35; padding: 6px 4px; text-align: center; vertical-align: middle; }
                    .print-table th { background-color: #4a7c59; color: #fff; font-weight: bold; text-transform: uppercase; white-space: nowrap; font-size: 9px; }
                    .print-table th:first-child { background-color: #2d4a35; text-align: left; }
                    .print-table .table-success th, .print-table th.table-success { background-color: #4a7c59 !important; color: white !important; }
                    .print-table .bg-primary, .print-table th.bg-primary, .print-table td.bg-primary { background-color: #2d4a35 !important; color: white !important; font-weight: bold !important; }
                    .print-table thead tr:first-child th { background-color: #4a7c59 !important; color: white !important; font-size: 10px !important; padding: 8px 4px !important; text-align: center !important; font-weight: bold !important; }
                    .print-table thead tr:nth-child(2) th { background-color: #4a7c59 !important; color: white !important; font-size: 8px !important; padding: 6px 2px !important; }
                    .print-table thead tr:nth-child(2) th.bg-primary { background-color: #2d4a35 !important; }
                    .print-table tbody td { background-color: white !important; font-size: 8px !important; padding: 4px 2px !important; }
                    .print-table tbody td:first-child { text-align: left; background-color: #f8f9fa !important; font-weight: normal !important; padding-left: 6px !important; }
                    .print-table tbody tr:first-child td { background-color: #e8f5e8 !important; font-weight: bold !important; }
                    .print-table .score-value { font-weight: bold !important; color: #000 !important; }
                    .print-table .bg-light { background-color: #f8f9fa !important; }
                    .print-table tr:nth-child(even) { background-color: #f0f7f4; }
                    .print-table td:first-child { text-align: left; font-weight: 500; background-color: #f8f9fa; }
                    .score-value { font-weight: bold; color: #1a5f38; }
                    .percentage-value { color: #000000; font-weight: 500; }
                    .average-cell { background-color: #e8f5e8 !important; font-weight: bold; color: #1a5f38; }
                    .term-section { margin-bottom: 30px; page-break-inside: avoid; }
                    .term-title { font-size: 16px; font-weight: bold; color: #1a5f38; margin: 20px 0 10px 0; padding: 8px 12px; background-color: #f0f7f4; border-left: 4px solid #1a5f38; }
                    .footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #dee2e6; font-size: 11px; color: #666; text-align: center; }
                    .page-break { page-break-before: always; }
                </style>
            </head>
            <body>
                <img src="${bannerUrl}" alt="Banner Header" class="banner">
                <div class="header-content">
                    <div class="report-title">${reportTitle}</div>
                    <table class="header-table">
                        <tr><td class="header-label">Course Code:</td><td class="header-value">${courseCode}</td><td class="header-label">Units:</td><td class="header-value">${units}</td></tr>
                        <tr><td class="header-label">Description:</td><td class="header-value">${subjectDescription}</td><td class="header-label">Semester:</td><td class="header-value">${semester}</td></tr>
                        <tr><td class="header-label">Course/Section:</td><td class="header-value">${courseSection}</td><td class="header-label">School Year:</td><td class="header-value">${academicPeriod}</td></tr>
                    </table>
                </div>
                ${content}
                <div class="footer">This is a computer-generated document. No signature is required.<br>Printed via ACADEX - Academic Grade System on ${currentDate}</div>
            </body>
        </html>
    `);
    printWindow.document.close();
    setTimeout(() => printWindow.print(), 500);
}

function getPrintTableContent(termType) {
    let tableSelector = '';
    let termTitle = '';
    switch (termType) {
        case 'prelim': tableSelector = '#term-prelim table'; termTitle = 'Prelim Term Results'; break;
        case 'midterm': tableSelector = '#term-midterm table'; termTitle = 'Midterm Results'; break;
        case 'prefinal': tableSelector = '#term-prefinal table'; termTitle = 'Prefinal Term Results'; break;
        case 'final': tableSelector = '#term-final table'; termTitle = 'Final Term Results'; break;
        case 'combined': tableSelector = '#combined-table table'; termTitle = 'All Terms Combined'; break;
    }

    const table = document.querySelector(tableSelector);
    if (!table) return '<p>No data available for the selected term.</p>';

    let tableHTML = '<div class="term-section">';
    if (termType !== 'combined') tableHTML += `<h3 class="term-title">${termTitle}</h3>`;
    tableHTML += '<table class="print-table">';

    const rows = table.querySelectorAll('tr');
    const currentScoreType = document.getElementById('scoreType')?.value || 'score';

    rows.forEach(row => {
        const isHeader = row.closest('thead') !== null;
        const tag = isHeader ? 'th' : 'td';
        if (currentScoreType === 'percentage' && !isHeader) {
            const firstCell = row.querySelector('td');
            if (firstCell && firstCell.textContent.includes('Total number of items')) return;
        }

        tableHTML += '<tr>';
        const cells = row.querySelectorAll(isHeader ? 'th' : 'td');
        cells.forEach(cell => {
            let cellContent = cell.textContent.trim();
            let cellClass = '';
            let cellAttrs = '';
            if (cell.hasAttribute('colspan')) cellAttrs += ` colspan="${cell.getAttribute('colspan')}"`;
            if (cell.hasAttribute('rowspan')) cellAttrs += ` rowspan="${cell.getAttribute('rowspan')}"`;
            if (cell.classList.contains('bg-primary') || cell.classList.contains('text-white')) cellClass += ' bg-primary text-white';
            if (cell.classList.contains('table-success')) cellClass += ' table-success';
            if (cell.classList.contains('align-middle')) cellClass += ' align-middle';
            if (cell.classList.contains('text-center')) cellClass += ' text-center';
            if (cell.classList.contains('fw-bold')) cellClass += ' fw-bold';
            if (cell.classList.contains('bg-light')) cellClass += ' bg-light';
            if (cell.classList.contains('score-value') || /^\d+$/.test(cellContent)) cellClass += ' score-value';
            else if (cellContent.includes('%')) cellClass += ' percentage-value';
            else if (cell.textContent.includes('Average') || cell.classList.contains('average-cell')) cellClass += ' average-cell';
            if (cell.style && cell.style.cssText) cellAttrs += ` style="${cell.style.cssText}"`;
            tableHTML += `<${tag}${cellAttrs} class="${cellClass.trim()}">${cellContent}</${tag}>`;
        });
        tableHTML += '</tr>';
    });

    tableHTML += '</table></div>';
    return tableHTML;
}

function getAllTablesContent() {
    const terms = ['prelim', 'midterm', 'prefinal', 'final'];
    let content = '';
    terms.forEach((term, index) => {
        if (index > 0) content += '<div class="page-break"></div>';
        content += getPrintTableContent(term);
    });
    content += '<div class="page-break"></div>';
    content += getPrintTableContent('combined');
    content += '<div class="page-break"></div>';
    content += getPassFailContent();
    content += '<div class="page-break"></div>';
    content += getCourseOutcomeSummaryContent();
    return content;
}

function getPassFailContent() {
    const passFailTable = document.querySelector('#passfail-table table');
    if (!passFailTable) return '<p>No Pass/Fail analysis data available.</p>';

    let content = '<div class="term-section">';
    content += '<h3 class="term-title">Pass/Fail Analysis Summary</h3>';
    content += '<table class="print-table">';

    const rows = passFailTable.querySelectorAll('tr');
    rows.forEach(row => {
        const isHeader = row.closest('thead') !== null;
        const tag = isHeader ? 'th' : 'td';
        content += '<tr>';
        const cells = row.querySelectorAll(isHeader ? 'th' : 'td');
        cells.forEach(cell => {
            let cellContent = cell.textContent.trim();
            let cellClass = '';
            let cellAttrs = '';
            if (cell.hasAttribute('colspan')) cellAttrs += ` colspan="${cell.getAttribute('colspan')}"`;
            if (cell.hasAttribute('rowspan')) cellAttrs += ` rowspan="${cell.getAttribute('rowspan')}"`;
            if (cell.classList.contains('table-success')) cellClass += ' table-success';
            if (cell.classList.contains('text-center')) cellClass += ' text-center';
            if (cell.classList.contains('fw-bold')) cellClass += ' fw-bold';
            if (cell.classList.contains('bg-light')) cellClass += ' bg-light';
            content += `<${tag}${cellAttrs} class="${cellClass.trim()}">${cellContent}</${tag}>`;
        });
        content += '</tr>';
    });

    content += '</table></div>';
    return content;
}

function getCourseOutcomeSummaryContent() {
    const summaryTable = document.querySelector('#copasssummary-table table');
    if (!summaryTable) return '<p>No Course Outcomes Summary data available.</p>';

    let content = '<div class="term-section">';
    content += '<h3 class="term-title">Course Outcomes Summary Dashboard</h3>';
    content += '<table class="print-table">';

    const rows = summaryTable.querySelectorAll('tr');
    rows.forEach(row => {
        const isHeader = row.closest('thead') !== null;
        const tag = isHeader ? 'th' : 'td';
        content += '<tr>';
        const cells = row.querySelectorAll(isHeader ? 'th' : 'td');
        cells.forEach(cell => {
            let cellContent = cell.textContent.trim();
            let cellClass = '';
            let cellAttrs = '';
            if (cell.hasAttribute('colspan')) cellAttrs += ` colspan="${cell.getAttribute('colspan')}"`;
            if (cell.hasAttribute('rowspan')) cellAttrs += ` rowspan="${cell.getAttribute('rowspan')}"`;
            if (cell.classList.contains('table-success')) cellClass += ' table-success';
            if (cell.classList.contains('text-center')) cellClass += ' text-center';
            if (cell.classList.contains('fw-bold')) cellClass += ' fw-bold';
            if (cell.classList.contains('bg-light')) cellClass += ' bg-light';
            if (cell.classList.contains('average-cell')) cellClass += ' average-cell';
            if (cellContent.includes('%')) cellClass += ' percentage-value';
            content += `<${tag}${cellAttrs} class="${cellClass.trim()}">${cellContent}</${tag}>`;
        });
        content += '</tr>';
    });

    content += '</table></div>';
    return content;
}

function dismissWarning() {
    const warningAlert = document.querySelector('.alert-warning');
    if (!warningAlert) return;
    warningAlert.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
    warningAlert.style.opacity = '0';
    warningAlert.style.transform = 'translateY(-10px)';
    setTimeout(() => { warningAlert.style.display = 'none'; }, 300);
}

function refreshData() {
    const refreshButton = document.querySelector('button[onclick="refreshData()"]');
    if (!refreshButton) return;
    const originalHTML = refreshButton.innerHTML;
    refreshButton.innerHTML = '<i class="bi bi-arrow-clockwise me-2 spin"></i>Refreshing...';
    refreshButton.disabled = true;
    const style = document.createElement('style');
    style.textContent = `.spin { animation: spin 1s linear infinite; } @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }`;
    document.head.appendChild(style);
    setTimeout(() => window.location.reload(), 1000);
}

function coClosePrintModal() {
    const modalEl = document.getElementById('printOptionsModal');
    if (!modalEl) return;
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.hide();
}

export function initCourseOutcomeResultsPage() {
    if (!isCourseOutcomeResultsPage()) return;

    // Debug: Log print button status
    const printBtn = document.getElementById('coPrintOptionsButton');
    console.log('[CO Results] Print button found:', !!printBtn);
    if (printBtn) {
        console.log('[CO Results] Button display:', window.getComputedStyle(printBtn).display);
        console.log('[CO Results] Button visibility:', window.getComputedStyle(printBtn).visibility);
        console.log('[CO Results] Button opacity:', window.getComputedStyle(printBtn).opacity);
    }

    // Expose functions for inline handlers in Blade
    Object.assign(window, {
        setDisplayType,
        toggleScoreType,
        toggleScoreTypeWithValue,
        switchTerm,
        showAllTerms,
        coPrintTable,
        coPrintSpecificTable,
        dismissWarning,
        refreshData,
        coClosePrintModal,
    });

    toggleScoreType();
    document.querySelectorAll('.term-step').forEach(step => {
        step.addEventListener('click', () => {
            setTimeout(() => {
                const tableContainer = document.querySelector('.results-card:not([style*="display: none"])');
                if (tableContainer) {
                    tableContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 100);
        });
    });

    // Default to percentage view on load
    setDisplayType('percentage', '📊', 'Percentage');
}

document.addEventListener('DOMContentLoaded', initCourseOutcomeResultsPage);

// Expose globally for initPage registry
window.initCourseOutcomeResultsPage = initCourseOutcomeResultsPage;
