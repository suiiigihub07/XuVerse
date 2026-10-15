/* A static typographic signature keeps the background quiet and motion-free. */
(() => {
    const start = () => {
        if (document.querySelector('.xv-ambient')) return;
        const field = document.createElement('div');
        field.className = 'xv-ambient';
        field.setAttribute('aria-hidden', 'true');
        field.innerHTML = '<span class="xv-signature"><span>X</span><span>S</span></span>';
        document.body.prepend(field);
        document.body.classList.add('xv-motion-mounted');
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, {once:true});
    else start();
})();
