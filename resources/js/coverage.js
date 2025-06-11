import Chart from 'chart.js/auto'; // Import Chart.js with auto registration

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', () => {
    const canvasElement = document.getElementById('coverageChart');

    if (!canvasElement) {
        console.log('Coverage chart canvas element not found on this page.');
        return;
    }

    // No need to check window.Chart anymore, as we import it directly

    const coverageDataString = canvasElement.dataset.coverage;
    if (!coverageDataString) {
        console.error('Data attribute "data-coverage" not found on canvas element.');
        return;
    }

    let coverageData;
    try {
        coverageData = JSON.parse(coverageDataString);
    } catch (e) {
        console.error('Failed to parse coverage data from data-coverage attribute:', e);
        return;
    }

    if (typeof coverageData.covered === 'undefined' || typeof coverageData.uncovered === 'undefined') {
        console.error('Parsed coverage data missing "covered" or "uncovered" properties.');
        return;
    }

    const ctxCoverage = canvasElement.getContext('2d');

    new Chart(ctxCoverage, {
        // Use the imported Chart directly
        type: 'doughnut',
        data: {
            labels: ['Covered by Known Place', 'Not Covered'],
            datasets: [
                {
                    label: 'Leaf Node Coverage',
                    data: [coverageData.covered, coverageData.uncovered],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)', // Teal
                        'rgba(255, 99, 132, 0.6)', // Red
                    ],
                    borderColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)'],
                    borderWidth: 1,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed !== null) {
                                label += context.parsed;
                            }
                            return label;
                        },
                    },
                },
            },
        },
    });

    // console.log('Coverage chart initialized via coverage-chart.js using import.');
});
