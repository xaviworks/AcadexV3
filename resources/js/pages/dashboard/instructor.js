/**
 * Instructor Dashboard JavaScript
 * Handles chart initialization for course completion status
 */

/**
 * Initialize the subject performance chart
 * @param {Array} subjectData - The subject chart data from PHP
 */
function initSubjectPerformanceChart(subjectData) {
    const canvas = document.getElementById('subjectPerformanceChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Color palette for different subjects
    const colors = [
        { border: '#4e73df', background: 'rgba(78, 115, 223, 0.1)' },
        { border: '#1cc88a', background: 'rgba(28, 200, 138, 0.1)' },
        { border: '#36b9cc', background: 'rgba(54, 185, 204, 0.1)' },
        { border: '#f6c23e', background: 'rgba(246, 194, 62, 0.1)' },
        { border: '#e74a3b', background: 'rgba(231, 74, 59, 0.1)' },
        { border: '#858796', background: 'rgba(133, 135, 150, 0.1)' },
        { border: '#5a5c69', background: 'rgba(90, 92, 105, 0.1)' },
        { border: '#6f42c1', background: 'rgba(111, 66, 193, 0.1)' },
    ];
    
    // Transform data to Chart.js format
    const datasets = subjectData.map((subject, index) => {
        const colorIndex = index % colors.length;
        return {
            label: subject.code,
            data: subject.termPercentages,
            borderColor: colors[colorIndex].border,
            backgroundColor: colors[colorIndex].background,
            fill: true,
            tension: 0.4
        };
    });

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Prelim', 'Midterm', 'Prefinal', 'Final'],
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        color: '#5a5c69'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + '% completed';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        drawBorder: false,
                        color: '#eaecf4'
                    },
                    ticks: {
                        color: '#5a5c69',
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#5a5c69'
                    }
                }
            },
            elements: {
                point: {
                    radius: 4,
                    hoverRadius: 6
                }
            }
        }
    });
}

// Make available globally for inline script usage
window.initSubjectPerformanceChart = initSubjectPerformanceChart;

// Export for module usage
export { initSubjectPerformanceChart };
