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

document.addEventListener('DOMContentLoaded', () => {
	const sectionLinks = Array.from(document.querySelectorAll('.nav-links a[data-nav-section], .mobile-nav-links a[data-nav-section]'));
	const aboutLinks = Array.from(document.querySelectorAll('.nav-links a[data-nav-external="about"], .mobile-nav-links a[data-nav-external="about"]'));

	if (!sectionLinks.length) {
		return;
	}

	const sections = sectionLinks
		.map((link) => link.dataset.navSection)
		.filter(Boolean)
		.filter((value, index, all) => all.indexOf(value) === index)
		.map((id) => document.getElementById(id))
		.filter(Boolean);

	if (!sections.length) {
		return;
	}

	const setAboutActive = () => {
		sectionLinks.forEach((link) => {
			link.classList.remove('is-active');
			link.setAttribute('aria-current', 'false');
		});

		aboutLinks.forEach((link) => {
			link.classList.add('is-active');
			link.setAttribute('aria-current', 'true');
		});
	};

	const setActiveSection = (id) => {
		aboutLinks.forEach((link) => {
			link.classList.remove('is-active');
			link.setAttribute('aria-current', 'false');
		});

		sectionLinks.forEach((link) => {
			const isActive = link.dataset.navSection === id;
			link.classList.toggle('is-active', isActive);
			link.setAttribute('aria-current', isActive ? 'true' : 'false');
		});
	};

	const observer = new IntersectionObserver((entries) => {
		const visible = entries
			.filter((entry) => entry.isIntersecting)
			.sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

		if (!visible?.target?.id) {
			return;
		}

		setActiveSection(visible.target.id);
	}, {
		root: null,
		threshold: [0.2, 0.4, 0.6],
		rootMargin: '-30% 0px -45% 0px',
	});

	sections.forEach((section) => observer.observe(section));

	const applyStateFromHash = () => {
		const hashId = window.location.hash.replace('#', '');
		if (!hashId) {
			return false;
		}

		const hasMatchingSection = sections.some((section) => section.id === hashId);
		if (!hasMatchingSection) {
			return false;
		}

		setActiveSection(hashId);
		return true;
	};

	sectionLinks.forEach((link) => {
		link.addEventListener('click', () => {
			if (link.dataset.navSection) {
				setActiveSection(link.dataset.navSection);
			}
		});
	});

	aboutLinks.forEach((link) => {
		link.addEventListener('click', () => {
			sessionStorage.setItem('navReturnFromAbout', '1');
		});
	});

	window.addEventListener('hashchange', () => {
		if (applyStateFromHash()) {
			return;
		}

		const fallback = sections.find((section) => section.id === 'servicios') ?? sections[0];
		setActiveSection(fallback.id);
	});

	const defaultActive = sections.find((section) => section.id === 'servicios') ?? sections[0];
	const cameFromAbout = document.referrer.includes('/about') || sessionStorage.getItem('navReturnFromAbout') === '1';

	if (!applyStateFromHash() && cameFromAbout) {
		setAboutActive();
		sessionStorage.removeItem('navReturnFromAbout');
		return;
	}

	setActiveSection(defaultActive.id);
});

// Services catalog deep-link support via query string (e.g. /servicios?categoria=corporal)
document.addEventListener('DOMContentLoaded', () => {
	if (!window.location.pathname.includes('/servicios')) {
		return;
	}

	const params = new URLSearchParams(window.location.search);
	const categoria = (params.get('categoria') || '').trim().toLowerCase();

	if (!categoria) {
		return;
	}

	const targetId = `cat-${categoria}`;
	const target = document.getElementById(targetId);

	if (!target) {
		return;
	}

	if (window.location.hash !== `#${targetId}`) {
		history.replaceState(null, '', `${window.location.pathname}${window.location.search}#${targetId}`);
	}

	requestAnimationFrame(() => {
		target.scrollIntoView({ behavior: 'smooth', block: 'start' });
	});
});

// Banners Carousel Auto-Rotation
document.addEventListener('DOMContentLoaded', () => {
	const carousel = document.querySelector('.banners-carousel');
	const carouselInner = document.querySelector('.banners-carousel-inner');
	const slides = document.querySelectorAll('.banner-slide');
	const indicators = document.querySelectorAll('.banner-indicator');
	const prevBtn = document.querySelector('.banner-prev');
	const nextBtn = document.querySelector('.banner-next');

	if (!carousel || !slides.length) return;

	let currentSlide = 0;
	let autoPlayInterval = null;
	const AUTO_PLAY_DELAY = 10000; // 10 seconds

	const updateSlide = (index) => {
		// Remove active from all slides and indicators
		slides.forEach((slide) => slide.classList.remove('active'));
		indicators.forEach((indicator) => indicator.classList.remove('active'));

		// Add active to current slide and indicator
		slides[index].classList.add('active');
		indicators[index].classList.add('active');

		currentSlide = index;
	};

	const nextSlide = () => {
		const next = (currentSlide + 1) % slides.length;
		updateSlide(next);
	};

	const prevSlide = () => {
		const prev = (currentSlide - 1 + slides.length) % slides.length;
		updateSlide(prev);
	};

	const goToSlide = (index) => {
		updateSlide(index);
		resetAutoPlay();
	};

	const startAutoPlay = () => {
		autoPlayInterval = setInterval(nextSlide, AUTO_PLAY_DELAY);
	};

	const resetAutoPlay = () => {
		if (autoPlayInterval) {
			clearInterval(autoPlayInterval);
		}
		startAutoPlay();
	};

	// Event listeners for buttons
	prevBtn?.addEventListener('click', () => {
		prevSlide();
		resetAutoPlay();
	});

	nextBtn?.addEventListener('click', () => {
		nextSlide();
		resetAutoPlay();
	});

	// Event listeners for indicators
	indicators.forEach((indicator, index) => {
		indicator.addEventListener('click', () => goToSlide(index));
	});

	// Pause on hover
	carousel?.addEventListener('mouseenter', () => {
		if (autoPlayInterval) {
			clearInterval(autoPlayInterval);
		}
	});

	carousel?.addEventListener('mouseleave', () => {
		startAutoPlay();
	});

	// Initialize
	updateSlide(0);
	startAutoPlay();
});

document.addEventListener('DOMContentLoaded', () => {
	const toggles = Array.from(document.querySelectorAll('.about-focus-btn[data-target]'));
	const panels = Array.from(document.querySelectorAll('.about-pillar[id]'));

	if (!toggles.length || !panels.length) {
		return;
	}

	const panelMap = new Map(panels.map((panel) => [panel.id, panel]));

	const activate = (targetId) => {
		if (!panelMap.has(targetId)) {
			return;
		}

		toggles.forEach((button) => {
			const isActive = button.dataset.target === targetId;
			button.classList.toggle('is-active', isActive);
			button.setAttribute('aria-expanded', isActive ? 'true' : 'false');
		});

		panels.forEach((panel) => {
			panel.classList.toggle('is-open', panel.id === targetId);
		});
	};

	const initialHash = window.location.hash.replace('#', '').trim().toLowerCase();
	if (initialHash && panelMap.has(initialHash)) {
		activate(initialHash);
	} else {
		const defaultTarget = toggles.find((button) => button.classList.contains('is-active'))?.dataset.target || toggles[0].dataset.target;
		activate(defaultTarget);
	}

	toggles.forEach((button) => {
		button.addEventListener('click', () => {
			const targetId = button.dataset.target;
			activate(targetId);
			history.replaceState(null, '', `#${targetId}`);

			const targetPanel = panelMap.get(targetId);
			targetPanel?.scrollIntoView({ behavior: 'smooth', block: 'center' });
		});
	});
});
