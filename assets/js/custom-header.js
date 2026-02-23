document.addEventListener('DOMContentLoaded', function () {
	const categoriesButton = document.querySelector('.jaspi-categories-button');
	const categoriesDropdown = document.getElementById('jaspi-categories-dropdown');
	const categoriesTrigger = document.querySelector('.jaspi-categories-trigger');
	const mobileToggle = document.querySelector('.jaspi-mobile-menu-toggle');
	const mobilePanel = document.getElementById('jaspi-mobile-panel');
	const mobileClose = document.querySelector('.jaspi-mobile-close');
	const tabs = document.querySelectorAll('.jaspi-mobile-tab');
	const tabContents = document.querySelectorAll('.jaspi-mobile-tab-content');

	if (categoriesButton && categoriesDropdown && categoriesTrigger) {
		let categoriesCloseTimer = null;

		function openCategoriesDropdown() {
			if (categoriesCloseTimer) {
				window.clearTimeout(categoriesCloseTimer);
				categoriesCloseTimer = null;
			}

			categoriesButton.setAttribute('aria-expanded', 'true');
			categoriesDropdown.hidden = false;
			window.requestAnimationFrame(function () {
				categoriesDropdown.classList.add('is-open');
			});
		}

		function closeCategoriesDropdown() {
			categoriesButton.setAttribute('aria-expanded', 'false');
			categoriesDropdown.classList.remove('is-open');
			categoriesCloseTimer = window.setTimeout(function () {
				categoriesDropdown.hidden = true;
				categoriesCloseTimer = null;
			}, 240);
		}

		categoriesTrigger.addEventListener('mouseenter', openCategoriesDropdown);
		categoriesTrigger.addEventListener('mouseleave', closeCategoriesDropdown);

		categoriesButton.addEventListener('focus', openCategoriesDropdown);
		categoriesDropdown.addEventListener('focusin', openCategoriesDropdown);
		categoriesTrigger.addEventListener('focusout', function (event) {
			if (!categoriesTrigger.contains(event.relatedTarget)) {
				closeCategoriesDropdown();
			}
		});

		categoriesButton.addEventListener('click', function () {
			const isOpen = categoriesButton.getAttribute('aria-expanded') === 'true';
			if (isOpen) {
				closeCategoriesDropdown();
			} else {
				openCategoriesDropdown();
			}
		});

		document.addEventListener('click', function (event) {
			if (!categoriesTrigger.contains(event.target)) {
				closeCategoriesDropdown();
			}
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape') {
				closeCategoriesDropdown();
			}
		});
	}

	function closeMobilePanel() {
		if (!mobilePanel || !mobileToggle) {
			return;
		}
		mobilePanel.hidden = true;
		mobileToggle.setAttribute('aria-expanded', 'false');
		document.body.classList.remove('jaspi-mobile-open');
	}

	if (mobileToggle && mobilePanel) {
		mobileToggle.addEventListener('click', function () {
			const isOpen = mobileToggle.getAttribute('aria-expanded') === 'true';
			mobileToggle.setAttribute('aria-expanded', String(!isOpen));
			mobilePanel.hidden = isOpen;
			document.body.classList.toggle('jaspi-mobile-open', !isOpen);
		});
	}

	if (mobileClose) {
		mobileClose.addEventListener('click', closeMobilePanel);
	}

	if (mobilePanel) {
		mobilePanel.addEventListener('click', function (event) {
			if (event.target === mobilePanel) {
				closeMobilePanel();
			}
		});
	}

	tabs.forEach(function (tab) {
		tab.addEventListener('click', function () {
			const target = tab.getAttribute('data-target');

			tabs.forEach(function (item) {
				item.classList.toggle('is-active', item === tab);
			});

			tabContents.forEach(function (content) {
				const isCurrent = content.getAttribute('data-content') === target;
				content.classList.toggle('is-active', isCurrent);
			});
		});
	});
});
