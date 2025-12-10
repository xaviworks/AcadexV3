/**
 * Instructor Final Grades Page JavaScript
 * Handles print functionality, notes viewing, and report generation
 */

// Close print modal helper
export function closePrintModal() {
    if (typeof window.modal !== 'undefined') {
        window.modal.close('printOptionsModal');
    } else {
        const modalEl = document.getElementById('printOptionsModal');
        if (modalEl && window.bootstrap?.Modal) {
            window.bootstrap.Modal.getInstance(modalEl)?.hide();
        }
    }
}

// Print HTML via hidden iframe (preferred) with window.open fallback
export function printHtml(html) {
    try {
        const iframe = document.createElement('iframe');
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        iframe.style.visibility = 'hidden';
        iframe.setAttribute('id', 'aca_print_iframe');
        
        // Use srcdoc when available
        if ('srcdoc' in iframe) {
            iframe.srcdoc = html;
        } else {
            // Fallback for older browsers — use a Blob URL to avoid printing the current page URL
            try {
                const blob = new Blob([html], { type: 'text/html' });
                iframe.src = URL.createObjectURL(blob);
            } catch (e) {
                iframe.src = 'about:blank';
            }
        }
        document.body.appendChild(iframe);

        const onLoad = () => {
            try {
                const win = iframe.contentWindow || iframe;
                win.focus();
                // Give browser a moment to render
                setTimeout(() => {
                    try {
                        win.print();
                    } finally {
                        // Remove iframe after printing
                        setTimeout(() => { document.body.removeChild(iframe); }, 500);
                    }
                }, 250);
            } catch (e) {
                console.error('Iframe print failed, falling back to window.open', e);
                try { document.body.removeChild(iframe); } catch (e2) {}
                // Fallback to Blob URL opened in new window (better than opening a route)
                try {
                    const blob2 = new Blob([html], { type: 'text/html' });
                    const blobUrl = URL.createObjectURL(blob2);
                    const w = window.open(blobUrl, '_blank', 'width=900,height=650');
                    if (!w) { alert('Please allow pop-ups to print the report.'); return; }
                    // Attempt to print once the new window loads
                    w.addEventListener('load', function(){
                        try { w.print(); } finally { setTimeout(() => URL.revokeObjectURL(blobUrl), 1000); }
                    });
                } catch (e2) {
                    const w = window.open('', '', 'width=900,height=650');
                    if (!w) { alert('Please allow pop-ups to print the report.'); return; }
                    w.document.open(); w.document.write(html); w.document.close();
                    setTimeout(() => w.print(), 400);
                }
            }
        };

        if ('srcdoc' in iframe) {
            iframe.onload = onLoad;
        } else {
            // Write content into iframe
            const doc = iframe.contentDocument || iframe.contentWindow.document;
            doc.open(); doc.write(html); doc.close();
            onLoad();
        }
    } catch (e) {
        console.error('printHtml error', e);
        // Last resort fallback
        const w = window.open('', '', 'width=900,height=650');
        if (!w) { alert('Please allow pop-ups to print the report.'); return; }
        w.document.open(); w.document.write(html); w.document.close();
        setTimeout(() => w.print(), 400);
    }
}

// Helper to format numeric score strings: drop trailing .00
function formatScore(txt) {
    if (!txt && txt !== 0) return '';
    const raw = String(txt).trim();
    // Extract numeric portion (allow negative, decimals)
    const cleaned = raw.replace(/[^0-9.\-]/g, '');
    if (cleaned === '') return raw;
    const n = parseFloat(cleaned);
    if (isNaN(n)) return raw;
    if (Math.abs(n - Math.round(n)) < 0.0001) return String(Math.round(n));
    return String(Math.round(n * 100) / 100);
}

// Print specific table function (handles both summary and term sheets)
export function printSpecificTable(tableType) {
    const pageData = window.pageData || {};
    const currentSubjectId = pageData.currentSubjectId;
    const termReportUrl = pageData.termReportUrl;
    const bannerUrl = pageData.bannerUrl;
    
    if (!currentSubjectId) {
        alert('Please select a subject first.');
        return;
    }

    if (tableType === 'summary') {
        // Print the final summary table
        printFinalSummary();
    } else {
        // Print individual term sheet — fetch HTML then print via iframe to avoid about:blank footers
        const url = new URL(termReportUrl);
        url.searchParams.set('subject_id', currentSubjectId);
        url.searchParams.set('term', tableType);

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Unable to prepare the term sheet.');
            }
            return response.text();
        })
        .then(html => {
            printHtml(html);
        })
        .catch(error => {
            console.error(error);
            alert(error.message || 'Failed to generate the term sheet. Please try again.');
        });
    }
}

// Print Final Summary Function
export function printFinalSummary() {
    const pageData = window.pageData || {};
    const content = document.getElementById('print-area')?.innerHTML || '';
    
    const subjectCode = pageData.subjectCode || '';
    const subjectDesc = pageData.subjectDesc || '';
    const subject = `${subjectCode} - ${subjectDesc}`;
    
    const passedStudents = pageData.passedStudents || 0;
    const failedStudents = pageData.failedStudents || 0;
    const totalStudents = pageData.totalStudents || 0;
    const passRate = pageData.passRate || 0;
    const academicPeriod = pageData.academicPeriod || '';
    const semester = pageData.semester || '';
    const units = pageData.units || 'N/A';
    const courseSection = pageData.courseSection || 'N/A';
    const bannerUrl = pageData.bannerUrl || '';

    const currentDate = new Date().toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });

    const html = `
        <html>
            <head>
                <title>Grade Report - ${subject}</title>
                <style>
                    @media print {
                        @page {
                            size: portrait;
                            margin: 0.5in;
                        }
                    }
                    
                    body {
                        font-family: 'Arial', sans-serif;
                        margin: 0;
                        padding: 20px;
                        color: #333;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                        line-height: 1.6;
                    }

                    .banner {
                        width: 100%;
                        max-height: 130px;
                        object-fit: contain;
                        margin-bottom: 15px;
                    }

                    .header-content {
                        margin-bottom: 20px;
                    }

                    .report-title {
                        font-size: 22px;
                        font-weight: bold;
                        text-align: center;
                        margin: 15px 0;
                        text-transform: uppercase;
                        letter-spacing: 2px;
                        color: #1a5f38;
                        border-bottom: 2px solid #1a5f38;
                        padding-bottom: 8px;
                    }

                    .header-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 25px;
                        background-color: #fff;
                        font-size: 11px;
                        border: 2px solid #1a5f38;
                    }

                    .header-table td {
                        padding: 8px 12px;
                        border: 1px solid #7fb3a3;
                    }

                    .header-label {
                        font-weight: bold;
                        width: 120px;
                        background-color: #1a5f38;
                        color: #fff;
                    }

                    .header-value {
                        font-family: 'Arial', sans-serif;
                        font-weight: 500;
                    }

                    .stats-container {
                        background-color: #f0f7f4;
                        border: 1px solid #7fb3a3;
                        border-radius: 4px;
                        margin: 0;
                        padding: 8px;
                        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
                    }

                    .stats-title {
                        font-weight: 600;
                        text-transform: uppercase;
                        margin-bottom: 6px;
                        font-size: 10px;
                        color: #1a5f38;
                        border-bottom: 1px solid #7fb3a3;
                        padding-bottom: 3px;
                    }

                    .stats-grid {
                        display: grid;
                        grid-template-columns: repeat(3, 1fr);
                        gap: 6px;
                    }

                    .stat-item {
                        background-color: #fff;
                        padding: 4px;
                        border-radius: 3px;
                        border: 1px solid #7fb3a3;
                        text-align: center;
                    }

                    .stat-label {
                        font-size: 9px;
                        color: #1a5f38;
                        margin-bottom: 1px;
                        letter-spacing: 0.5px;
                        font-weight: 600;
                    }

                    .stat-value {
                        font-size: 12px;
                        font-weight: bold;
                        color: #1a5f38;
                    }

                    .passed-count { color: #28a745; }
                    .failed-count { color: #dc3545; }
                    .total-count { color: #1a5f38; }

                    .print-table {
                        width: 100%;
                        border-collapse: collapse;
                        border: 2px solid #1a5f38;
                        background-color: #fff;
                        margin-top: 15px;
                        font-size: 11px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }

                    .print-table th, .print-table td {
                        border: 1px solid #7fb3a3;
                        padding: 8px;
                        font-size: 11px;
                        text-align: center;
                        vertical-align: middle;
                    }

                    .print-table th {
                        background-color: #1a5f38;
                        color: #fff;
                        font-weight: bold;
                        text-transform: uppercase;
                        text-align: center;
                        white-space: nowrap;
                        border: 1px solid #1a5f38;
                        padding: 10px 8px;
                    }

                    .print-table th:first-child {
                        background-color: #0d4b2a;
                    }

                    .print-table tr:nth-child(even) {
                        background-color: #f0f7f4;
                    }

                    .print-table tr:hover {
                        background-color: #e8f3ef;
                    }

                    .print-table th:nth-child(1) { width: 5%; }
                    .print-table th:nth-child(2) { width: 25%; text-align: left; }
                    .print-table th:nth-child(3) { width: 12%; }
                    .print-table th:nth-child(4) { width: 12%; }
                    .print-table th:nth-child(5) { width: 12%; }
                    .print-table th:nth-child(6) { width: 12%; }
                    .print-table th:nth-child(7) { width: 12%; }
                    .print-table th:nth-child(8) { width: 10%; }

                    .print-table td:first-child {
                        text-align: center;
                        background-color: #f0f7f4;
                        font-weight: 500;
                    }

                    .print-table td:nth-child(2) {
                        text-align: left;
                        font-weight: 500;
                    }

                    .print-table td:not(:first-child):not(:nth-child(2)) {
                        text-align: center;
                    }

                    .print-table td:nth-child(3),
                    .print-table td:nth-child(4),
                    .print-table td:nth-child(5),
                    .print-table td:nth-child(6),
                    .print-table td:nth-child(7) {
                        font-family: 'Arial', sans-serif;
                        font-weight: 500;
                    }

                    .print-table td:nth-child(7) {
                        font-weight: bold;
                        color: #1a5f38;
                    }

                    .print-badge {
                        padding: 4px 8px;
                        border-radius: 3px;
                        font-size: 10px;
                        font-weight: bold;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                        display: inline-block;
                        text-align: center;
                        min-width: 60px;
                    }

                    .print-badge.passed {
                        background-color: #d4edda;
                        color: #155724;
                        border: 1px solid #c3e6cb;
                    }

                    .print-badge.failed {
                        background-color: #f8d7da;
                        color: #721c24;
                        border: 1px solid #f5c6cb;
                    }

                    .footer {
                        margin-top: 20px;
                        padding-top: 15px;
                        border-top: 1px solid #dee2e6;
                        font-size: 11px;
                        color: #666;
                        text-align: center;
                    }
                </style>
            </head>
            <body>
                <img src="${bannerUrl}" alt="Banner Header" class="banner">
                
                <div class="header-content">
                    <div class="report-title">Report of Grades</div>
                    
                    <table class="header-table">
                        <tr>
                            <td class="header-label">Course Code:</td>
                            <td class="header-value">${subjectCode}</td>
                            <td class="header-label">Units:</td>
                            <td class="header-value">${units}</td>
                        </tr>
                        <tr>
                            <td class="header-label">Description:</td>
                            <td class="header-value">${subjectDesc}</td>
                            <td class="header-label">Semester:</td>
                            <td class="header-value">${semester}</td>
                        </tr>
                        <tr>
                            <td class="header-label">Course/Section:</td>
                            <td class="header-value">${courseSection}</td>
                            <td class="header-label">School Year:</td>
                            <td class="header-value">${academicPeriod}</td>
                        </tr>
                        <tr>
                            <td colspan="4" style="padding: 0;">
                                <div class="stats-container">
                                    <div class="stats-title">Class Performance Summary</div>
                                    <div class="stats-grid">
                                        <div class="stat-item">
                                            <div class="stat-label">PASSED STUDENTS</div>
                                            <div class="stat-value passed-count">${passedStudents}</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-label">FAILED STUDENTS</div>
                                            <div class="stat-value failed-count">${failedStudents}</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-label">PASSING RATE</div>
                                            <div class="stat-value total-count">${passRate}%</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <table class="print-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Prelim</th>
                                <th>Midterm</th>
                                <th>Prefinal</th>
                                <th>Final</th>
                                <th>Final Average</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${Array.from(document.querySelectorAll('#print-area tbody tr')).map((row, index) => `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${row.cells[0].textContent.trim()}</td>
                                    <td>${formatScore(row.cells[1].textContent)}</td>
                                    <td>${formatScore(row.cells[2].textContent)}</td>
                                    <td>${formatScore(row.cells[3].textContent)}</td>
                                    <td>${formatScore(row.cells[4].textContent)}</td>
                                    <td>${formatScore(row.cells[5].textContent)}</td>
                                    <td>
                                        ${row.cells[6].textContent.trim().includes('Passed') 
                                            ? `<span class="print-badge passed">Passed</span>`
                                            : row.cells[6].textContent.trim().includes('Failed')
                                            ? `<span class="print-badge failed">Failed</span>`
                                            : row.cells[6].textContent.trim()}
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>

                <div class="footer">
                    This is a computer-generated document. No signature is required.
                    <br>
                    Printed via ACADEX - Academic Grade System
                </div>
            </body>
        </html>
    `;
    
    // Use iframe-based printing to avoid browser URL footers when possible
    printHtml(html);
}

export function initFinalGradesPage() {
    // View Notes Modal Handler
    const viewStudentNameDisplay = document.getElementById('viewStudentNameDisplay');
    const viewNotesContent = document.getElementById('viewNotesContent');

    // Handle view notes button click
    document.querySelectorAll('.view-notes-btn').forEach(button => {
        button.addEventListener('click', function() {
            const studentName = this.dataset.studentName;
            const notes = this.dataset.notes || 'No notes available.';
            
            // Populate modal
            if (viewStudentNameDisplay) {
                viewStudentNameDisplay.textContent = studentName;
            }
            if (viewNotesContent) {
                viewNotesContent.textContent = notes;
            }
            
            // Show modal
            if (typeof window.modal !== 'undefined') {
                window.modal.open('viewNotesModal', { studentName, notes });
            }
        });
    });
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', initFinalGradesPage);

// Expose functions globally
window.printSpecificTable = printSpecificTable;
window.printFinalSummary = printFinalSummary;
window.closePrintModal = closePrintModal;
window.printHtml = printHtml;
window.initFinalGradesPage = initFinalGradesPage;
