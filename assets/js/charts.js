/**
 * HMCMS Super Admin Module - Analytics Charts
 * Requires Chart.js to be loaded
 */

document.addEventListener('DOMContentLoaded', () => {
    initPerformanceChart();
});

function initPerformanceChart() {
    const ctx = document.getElementById('performanceChart');
    if (!ctx) return;

    // Use CSS variables for colors to keep styling consistent
    const primaryColor = '#0f766e'; // Deep Teal
    const secondaryColor = '#10b981'; // Emerald
    const textMuted = '#64748b';

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [
                {
                    label: 'Total Patients Treated',
                    data: [1200, 1900, 3000, 2500, 2200, 3100, 3400],
                    backgroundColor: primaryColor,
                    borderRadius: 4,
                    barPercentage: 0.6
                },
                {
                    label: 'Camps Conducted',
                    data: [8, 12, 18, 15, 14, 20, 22],
                    backgroundColor: secondaryColor,
                    borderRadius: 4,
                    barPercentage: 0.6,
                    type: 'line',
                    tension: 0.4,
                    borderWidth: 2,
                    borderColor: secondaryColor,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: textMuted,
                        font: { family: "'Inter', sans-serif" },
                        usePointStyle: true,
                        boxWidth: 8
                    }
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { family: "'Inter', sans-serif" },
                    bodyFont: { family: "'Inter', sans-serif" },
                    padding: 12,
                    cornerRadius: 8
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: textMuted, font: { family: "'Inter', sans-serif" } }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: { color: '#e2e8f0', borderDash: [5, 5] },
                    ticks: { color: textMuted, font: { family: "'Inter', sans-serif" } }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: { color: textMuted, font: { family: "'Inter', sans-serif" } }
                }
            }
        }
    });
}
