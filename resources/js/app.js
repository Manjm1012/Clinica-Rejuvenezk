import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
	const elements = document.querySelectorAll('.reveal');

	if (!elements.length) {
		return;
	}

	const showElement = (element) => {
		element.classList.add('is-visible');
	};

	if (!('IntersectionObserver' in window)) {
		elements.forEach(showElement);
		return;
	}

	const observer = new IntersectionObserver((entries) => {
		entries.forEach((entry) => {
			if (!entry.isIntersecting) {
				return;
			}

			showElement(entry.target);
			observer.unobserve(entry.target);
		});
	}, {
		threshold: 0.15,
		rootMargin: '0px 0px -8% 0px',
	});

	elements.forEach((element) => observer.observe(element));
});
