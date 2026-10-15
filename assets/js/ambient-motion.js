/* Preserve atmospheric red motion and its preference control, without canvas noise. */
(() => {
    const reduced = matchMedia('(prefers-reduced-motion: reduce)');
    let enabled = true;
    try { enabled = localStorage.getItem('xuverse.backgroundMotion') !== 'off'; } catch (_) {}
    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'xv-motion-toggle';
    toggle.setAttribute('aria-label', 'Background motion');
    document.querySelector('.footer-brand')?.insertAdjacentElement('afterend', toggle);
    const sync = () => {
        document.body.classList.toggle('xv-motion-paused', !enabled || reduced.matches || document.hidden);
        toggle.hidden = reduced.matches;
        toggle.setAttribute('aria-pressed', String(enabled && !reduced.matches));
        toggle.textContent = enabled ? 'Motion on' : 'Motion off';
        toggle.title = enabled ? 'Pause background animation' : 'Resume background animation';
    };
    toggle.addEventListener('click', () => {
        enabled = !enabled;
        try { localStorage.setItem('xuverse.backgroundMotion', enabled ? 'on' : 'off'); } catch (_) {}
        sync();
    });
    reduced.addEventListener('change', sync);
    document.addEventListener('visibilitychange', sync);
    sync();
})();
