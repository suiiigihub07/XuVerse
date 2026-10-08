(() => {
  'use strict';
  document.querySelectorAll('[data-post-gallery]').forEach(gallery => {
    const track = gallery.querySelector('.post-gallery-track');
    const slides = [...track.children];
    const previous = gallery.querySelector('[data-gallery-previous]');
    const next = gallery.querySelector('[data-gallery-next]');
    const counter = gallery.querySelector('[data-gallery-counter]');
    const index = () => Math.max(0, Math.min(slides.length - 1, Math.round(track.scrollLeft / Math.max(track.clientWidth, 1))));
    const update = () => {
      const current = index();
      if (counter) counter.textContent = `${current + 1} / ${slides.length}`;
      if (previous) previous.disabled = current === 0;
      if (next) next.disabled = current === slides.length - 1;
    };
    const move = direction => {
      const target = Math.max(0, Math.min(slides.length - 1, index() + direction));
      track.scrollTo({left:target * track.clientWidth, behavior:matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth'});
    };
    previous?.addEventListener('click', () => move(-1));
    next?.addEventListener('click', () => move(1));
    track.addEventListener('scroll', update, {passive:true});
    track.addEventListener('keydown', event => {
      if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') { event.preventDefault(); move(event.key === 'ArrowRight' ? 1 : -1); }
    });
    if ('ResizeObserver' in window) new ResizeObserver(update).observe(track);
    update();
  });
  document.querySelectorAll('[data-open-gallery]').forEach(link => {
    const modal = document.getElementById(link.dataset.openGallery);
    if (!modal || typeof modal.showModal !== 'function') return;
    const close = () => {modal.close(); document.body.classList.remove('lightbox-open'); link.focus();};
    link.addEventListener('click', event => {
      if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
      event.preventDefault(); modal.showModal(); document.body.classList.add('lightbox-open'); modal.querySelector('.gallery-close').focus();
    });
    modal.querySelector('.gallery-close').addEventListener('click', close);
    modal.addEventListener('cancel', event => {event.preventDefault(); close();});
    modal.addEventListener('click', event => {if (event.target === modal) close();});
  });
})();
