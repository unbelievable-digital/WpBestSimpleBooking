/**
 * Dashboard Charts
 *
 * @package Unbelievable_Salon_Booking
 */

/* global Chart, unbsbChartData */

document.addEventListener('DOMContentLoaded', function() {
	if (typeof Chart === 'undefined' || typeof unbsbChartData === 'undefined') return;

	var chartData = unbsbChartData.data;
	var currencySymbol = unbsbChartData.currencySymbol;
	var labels = unbsbChartData.labels;

	// Chart.js defaults.
	Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
	Chart.defaults.color = '#64748b';

	// Weekly Bookings (Bar Chart).
	var weeklyCtx = document.getElementById('weeklyChart');
	if (weeklyCtx) {
		new Chart(weeklyCtx, {
			type: 'bar',
			data: {
				labels: chartData.weekly.labels,
				datasets: [{
					label: labels.bookings,
					data: chartData.weekly.data,
					backgroundColor: 'rgba(99, 102, 241, 0.8)',
					borderColor: 'rgba(99, 102, 241, 1)',
					borderWidth: 0,
					borderRadius: 6,
					borderSkipped: false
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: { display: false }
				},
				scales: {
					y: {
						beginAtZero: true,
						ticks: { stepSize: 1 },
						grid: { color: 'rgba(0,0,0,0.05)' }
					},
					x: {
						grid: { display: false }
					}
				}
			}
		});
	}

	// Monthly Revenue (Line Chart).
	var revenueCtx = document.getElementById('revenueChart');
	if (revenueCtx) {
		new Chart(revenueCtx, {
			type: 'line',
			data: {
				labels: chartData.monthly.labels,
				datasets: [{
					label: labels.revenue,
					data: chartData.monthly.data,
					borderColor: 'rgba(16, 185, 129, 1)',
					backgroundColor: 'rgba(16, 185, 129, 0.1)',
					borderWidth: 3,
					fill: true,
					tension: 0.4,
					pointBackgroundColor: 'rgba(16, 185, 129, 1)',
					pointBorderColor: '#fff',
					pointBorderWidth: 2,
					pointRadius: 5,
					pointHoverRadius: 7
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: { display: false },
					tooltip: {
						callbacks: {
							label: function(context) {
								return context.parsed.y.toLocaleString('tr-TR') + ' ' + currencySymbol;
							}
						}
					}
				},
				scales: {
					y: {
						beginAtZero: true,
						grid: { color: 'rgba(0,0,0,0.05)' },
						ticks: {
							callback: function(value) {
								return value.toLocaleString('tr-TR') + ' ' + currencySymbol;
							}
						}
					},
					x: {
						grid: { display: false }
					}
				}
			}
		});
	}

	// Service Distribution (Doughnut Chart).
	var servicesCtx = document.getElementById('servicesChart');
	if (servicesCtx && chartData.services.labels.length > 0) {
		new Chart(servicesCtx, {
			type: 'doughnut',
			data: {
				labels: chartData.services.labels,
				datasets: [{
					data: chartData.services.data,
					backgroundColor: chartData.services.colors,
					borderWidth: 0,
					hoverOffset: 4
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				cutout: '65%',
				plugins: {
					legend: {
						position: 'bottom',
						labels: {
							padding: 15,
							usePointStyle: true,
							pointStyle: 'circle'
						}
					}
				}
			}
		});
	}
});
