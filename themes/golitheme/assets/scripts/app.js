/* Header interactions: mobile panel + predictive search (site-wide) */
(function () {
	'use strict';
	// Utilities
	function $(sel, ctx) { return (ctx || document).querySelector(sel); }
	function $on(el, type, fn) { if (el) el.addEventListener(type, fn, { passive: true }); }
	function trapFocus(container) {
		if (!container) return function () {};
		var focusable = container.querySelectorAll('a, button, input, [tabindex]:not([tabindex="-1"])');
		function onKey(e) {
			if (e.key !== 'Tab') return;
			if (!focusable.length) return;
			var first = focusable[0], last = focusable[focusable.length - 1];
			if (e.shiftKey && document.activeElement === first) { last.focus(); e.preventDefault(); }
			else if (!e.shiftKey && document.activeElement === last) { first.focus(); e.preventDefault(); }
		}
		container.addEventListener('keydown', onKey);
		return function cleanup() { container.removeEventListener('keydown', onKey); };
	}
	// Mobile panel (safe no-op if markup not present)
	var toggle = $('.gn-nav-toggle');
	var panel  = $('#gn-mobile-panel');
	var overlay= $('#gn-overlay');
	var close  = $('.gn-panel-close');
	var releaseTrap = function(){};
	function openPanel() {
		if (!panel) return;
		panel.removeAttribute('aria-hidden');
		panel.removeAttribute('hidden');
		if (overlay) overlay.removeAttribute('hidden');
		document.body.style.overflow = 'hidden';
		releaseTrap = trapFocus(panel);
		var btn = panel.querySelector('.gn-panel-close') || panel;
		btn.focus();
		if (toggle) toggle.setAttribute('aria-expanded', 'true');
	}
	function closePanel() {
		if (!panel) return;
		panel.setAttribute('aria-hidden', 'true');
		panel.setAttribute('hidden', '');
		if (overlay) overlay.setAttribute('hidden', '');
		document.body.style.overflow = '';
		releaseTrap();
		if (toggle) toggle.setAttribute('aria-expanded', 'false');
		if (toggle) toggle.focus();
	}
	$on(toggle, 'click', function(){ openPanel(); });
	$on(close, 'click', function(){ closePanel(); });
	$on(overlay, 'click', function(){ closePanel(); });
	document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closePanel(); }, { passive: true });

	// Predictive search (site-wide)
	if (!(window.gnSearch)) return;
	var i18n = window.gnSearch.i18n || {};
	var input = $('#gn-search-input');
	var popover = $('#gn-search-popover');
	var listProducts = $('#gn-search-listbox');
	var listCourses = $('#gn-search-courses');

	function debounce(fn, wait) {
		var t; return function () { clearTimeout(t); var args = arguments, ctx = this; t = setTimeout(function(){ fn.apply(ctx, args); }, wait); };
	}
	function clearLists() {
		if (listProducts) listProducts.innerHTML = '';
		if (listCourses) listCourses.innerHTML = '';
	}
	function hidePopover() { if (popover) popover.hidden = true; if (input) input.setAttribute('aria-expanded', 'false'); }
	function showPopover() { if (popover) popover.hidden = false; if (input) input.setAttribute('aria-expanded', 'true'); }
	function renderItems(listEl, items) {
		if (!listEl) return;
		listEl.innerHTML = items.map(function (it, idx) {
			return '<li role="option" tabindex="-1" data-url="' + it.url + '">' + it.title + '</li>';
		}).join('');
	}
	var activeIndex = -1; var flatItems = [];
	function refreshActive(delta) {
		var all = Array.prototype.slice.call((listProducts ? listProducts.children : [])).concat(Array.prototype.slice.call((listCourses ? listCourses.children : [])));
		flatItems = all;
		if (!all.length) return;
		activeIndex = (activeIndex + delta + all.length) % all.length;
		all.forEach(function(li, i){ li.classList.toggle('is-active', i === activeIndex); });
		if (all[activeIndex]) all[activeIndex].focus();
	}
	var doSearch = debounce(function () {
		var q = (input && input.value || '').trim();
		if (!q) { clearLists(); hidePopover(); return; }
		fetch(window.gnSearch.restUrl + '?q=' + encodeURIComponent(q), {
			headers: { 'X-WP-Nonce': window.gnSearch.nonce }
		})
		.then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
		.then(function (data) {
			renderItems(listProducts, data.products || []);
			renderItems(listCourses, data.courses || []);
			if ((data.products && data.products.length) || (data.courses && data.courses.length)) showPopover(); else hidePopover();
			activeIndex = -1;
		})
		.catch(function(){ /* silent */ });
	}, 200);

	if (input) {
		input.setAttribute('placeholder', i18n.placeholder || input.getAttribute('placeholder') || '');
		$on(input, 'input', doSearch);
		$on(input, 'focus', function(){ if ((listProducts && listProducts.children.length) || (listCourses && listCourses.children.length)) showPopover(); });
		document.addEventListener('click', function (e) {
			if (!popover || !input) return;
			if (popover.contains(e.target) || input.contains(e.target)) return;
			hidePopover();
		}, { passive: true });
		input.addEventListener('keydown', function(e){
			if (!popover || popover.hidden) return;
			if (e.key === 'ArrowDown') { refreshActive(+1); e.preventDefault(); }
			else if (e.key === 'ArrowUp') { refreshActive(-1); e.preventDefault(); }
			else if (e.key === 'Enter') {
				if (flatItems[activeIndex]) {
					window.location.href = flatItems[activeIndex].getAttribute('data-url');
					e.preventDefault();
				}
			} else if (e.key === 'Escape') {
				hidePopover();
			}
		});
	}
})();
