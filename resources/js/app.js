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

// Results carousel pagination dots
document.addEventListener('DOMContentLoaded', () => {
	const grid = document.getElementById('resultCarousel');
	const dotsContainer = document.getElementById('resultDots');

	if (!grid || !dotsContainer) return;

	const cards = Array.from(grid.querySelectorAll('.result-visual-card'));
	if (!cards.length) return;

	// Build dots
	cards.forEach((card, i) => {
		const btn = document.createElement('button');
		btn.className = 'result-visual-dot' + (i === 0 ? ' is-active' : '');
		btn.setAttribute('aria-label', `Ver caso ${i + 1}`);
		btn.addEventListener('click', () => {
			card.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
		});
		dotsContainer.appendChild(btn);
	});

	const dots = Array.from(dotsContainer.querySelectorAll('.result-visual-dot'));

	// Update active dot on scroll
	let scrollTimer;
	grid.addEventListener('scroll', () => {
		clearTimeout(scrollTimer);
		scrollTimer = setTimeout(() => {
			const center = grid.scrollLeft + grid.clientWidth / 2;
			let closest = 0;
			let closestDist = Infinity;
			cards.forEach((card, i) => {
				const cardCenter = card.offsetLeft + card.offsetWidth / 2;
				const dist = Math.abs(cardCenter - center);
				if (dist < closestDist) { closestDist = dist; closest = i; }
			});
			dots.forEach((d, i) => d.classList.toggle('is-active', i === closest));
		}, 50);
	}, { passive: true });
});

// Services tab filter
document.addEventListener('DOMContentLoaded', () => {
	const tabs = document.querySelectorAll('.services-tab');

	if (!tabs.length) return;

	tabs.forEach((tab) => {
		tab.addEventListener('click', () => {
			const targetId = tab.dataset.tab;

			tabs.forEach((t) => {
				t.classList.remove('is-active');
				t.setAttribute('aria-selected', 'false');
			});

			document.querySelectorAll('.services-panel').forEach((panel) => {
				panel.classList.remove('is-active');
			});

			tab.classList.add('is-active');
			tab.setAttribute('aria-selected', 'true');
			document.getElementById(targetId)?.classList.add('is-active');
		});
	});
});

document.addEventListener('DOMContentLoaded', () => {
	const toggle = document.querySelector('.nav-toggle');
	const panel = document.querySelector('.mobile-nav-panel');
	const overlay = document.querySelector('.mobile-nav-overlay');
	const closeButton = document.querySelector('.mobile-nav-close');

	if (!toggle || !panel || !overlay || !closeButton) {
		return;
	}

	const closeMenu = () => {
		toggle.classList.remove('is-open');
		toggle.setAttribute('aria-expanded', 'false');
		panel.classList.remove('is-open');
		overlay.classList.remove('is-open');
		document.body.style.overflow = '';
		panel.hidden = true;
		overlay.hidden = true;
	};

	const openMenu = () => {
		toggle.classList.add('is-open');
		toggle.setAttribute('aria-expanded', 'true');
		panel.hidden = false;
		overlay.hidden = false;
		document.body.style.overflow = 'hidden';
		requestAnimationFrame(() => {
			panel.classList.add('is-open');
			overlay.classList.add('is-open');
		});
	};

	toggle.addEventListener('click', () => {
		if (toggle.classList.contains('is-open')) {
			closeMenu();
			return;
		}

		openMenu();
	});

	panel.querySelectorAll('a').forEach((link) => {
		link.addEventListener('click', () => closeMenu());
	});

	overlay.addEventListener('click', () => closeMenu());
	closeButton.addEventListener('click', () => closeMenu());
	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && toggle.classList.contains('is-open')) {
			closeMenu();
		}
	});

	window.addEventListener('resize', () => {
		if (window.innerWidth > 980) {
			closeMenu();
		}
	});

	closeMenu();
});
