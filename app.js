document.addEventListener('DOMContentLoaded', () => {
  // ===== Accordion =====
  document.querySelectorAll('.accordion-header').forEach(btn => {
    btn.addEventListener('click', () => {
      const item = btn.closest('.accordion-item');
      if (!item) return;
      item.classList.toggle('open');
    });
  });

  // ===== Mobile menu =====
  const burger = document.getElementById('burgerBtn');
  const mobilePanel = document.getElementById('mobilePanel');

  function closeMenu() {
    document.body.classList.remove('menu-open');
    if (burger) {
      burger.setAttribute('aria-expanded', 'false');
      burger.setAttribute('aria-label', 'Menü öffnen');
    }
  }

  function toggleMenu() {
    const isOpen = document.body.classList.toggle('menu-open');
    if (burger) {
      burger.setAttribute('aria-expanded', String(isOpen));
      burger.setAttribute('aria-label', isOpen ? 'Menü schließen' : 'Menü öffnen');
    }
  }

  if (burger) burger.addEventListener('click', toggleMenu);
  if (mobilePanel) {
    mobilePanel.querySelectorAll('a').forEach(a => a.addEventListener('click', closeMenu));
  }

  // ===== Overlay =====
  let lastFocus = null;
  const overlay = document.getElementById('page2Overlay');
  const overlayPanel = overlay ? overlay.querySelector('.overlay-panel') : null;

  function openOverlay() {
    closeMenu();
    if (!overlay) return;
    lastFocus = document.activeElement;
    overlay.classList.add('is-open');
    document.body.classList.add('overlay-open');
    overlay.setAttribute('aria-hidden', 'false');
    if (overlayPanel) overlayPanel.focus();
  }

  function closeOverlay() {
    if (!overlay) return;
    overlay.classList.remove('is-open');
    document.body.classList.remove('overlay-open');
    overlay.setAttribute('aria-hidden', 'true');
    if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
  }

  document.querySelectorAll('[data-open-overlay="page2"]').forEach(el => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      openOverlay();
    });
  });

  document.querySelectorAll('[data-close-overlay="page2"]').forEach(el => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      closeOverlay();
    });
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeOverlay();
      closeMenu();
    }
  });
});
