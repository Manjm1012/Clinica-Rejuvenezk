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
		if (window.innerWidth > 640) {
			closeMenu();
		}
	});

	closeMenu();
});
