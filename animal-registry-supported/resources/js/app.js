import Chart from 'chart.js/auto';

window.Chart = Chart;

const chartColors = ['#0f766e', '#2563eb', '#e8792e', '#be185d', '#65a30d', '#7c3aed', '#0891b2', '#ca8a04'];
const mutedText = '#64748b';
const gridColor = '#e2e8f0';

Chart.defaults.font.family = 'Figtree, ui-sans-serif, sans-serif';
Chart.defaults.color = mutedText;

document.querySelectorAll('[data-analytics-chart]').forEach((canvas) => {
	const labels = JSON.parse(canvas.dataset.labels ?? '[]');
	const values = JSON.parse(canvas.dataset.values ?? '[]').map(Number);
	const type = canvas.dataset.chartType ?? 'bar';
	const isCircular = type === 'doughnut' || type === 'pie';
	const isLine = type === 'line';

	new Chart(canvas, {
		type,
		data: {
			labels,
			datasets: [{
				data: values,
				backgroundColor: isLine ? 'rgba(15, 118, 110, 0.12)' : chartColors,
				borderColor: isLine ? '#0f766e' : '#ffffff',
				borderWidth: isLine ? 2.5 : 2,
				borderRadius: type === 'bar' ? 4 : 0,
				fill: isLine,
				tension: 0.35,
				pointRadius: isLine ? 2.5 : 0,
				pointHoverRadius: isLine ? 5 : 0,
				pointBackgroundColor: '#0f766e',
				hoverOffset: isCircular ? 5 : 0,
			}],
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			animation: { duration: 500 },
			cutout: type === 'doughnut' ? '68%' : undefined,
			plugins: {
				legend: {
					display: isCircular,
					position: 'bottom',
					labels: {
						usePointStyle: true,
						pointStyle: 'circle',
						boxWidth: 8,
						boxHeight: 8,
						padding: 16,
						color: mutedText,
						font: { size: 11 },
					},
				},
				tooltip: {
					backgroundColor: '#0f172a',
					titleColor: '#f8fafc',
					bodyColor: '#e2e8f0',
					padding: 10,
					cornerRadius: 6,
					displayColors: isCircular,
				},
			},
			scales: isCircular ? {} : {
				x: {
					grid: { display: false },
					border: { display: false },
					ticks: { color: mutedText, maxRotation: 0, autoSkip: true, font: { size: 10 } },
				},
				y: {
					beginAtZero: true,
					grid: { color: gridColor },
					border: { display: false, dash: [3, 3] },
					ticks: { color: mutedText, precision: 0, padding: 8, font: { size: 10 } },
				},
			},
		},
	});
});
