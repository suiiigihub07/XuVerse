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

/* Progressive category tabs. Plain hash links expose every section without JS. */
(() => {
  const navigation = document.querySelector('.media-categories');
  if (!navigation) return;
  const links = [...navigation.querySelectorAll('a')];
  const panels = links.map(link => document.getElementById(link.hash.slice(1)));
  if (panels.some(panel => !panel)) return;
  navigation.setAttribute('role', 'tablist');
  links.forEach((link, index) => {
    link.id = `media-tab-${panels[index].id}`;
    link.setAttribute('role', 'tab');
    link.setAttribute('aria-controls', panels[index].id);
    panels[index].setAttribute('role', 'tabpanel');
    panels[index].setAttribute('aria-labelledby', link.id);
    panels[index].tabIndex = 0;
  });
  const select = index => {
    links.forEach((link, i) => {
      link.setAttribute('aria-selected', String(i === index));
      link.tabIndex = i === index ? 0 : -1;
      link.removeAttribute('aria-current');
      panels[i].hidden = i !== index;
      panels[i].classList.toggle('is-current', i === index);
      // Stop a playing video when its category is closed, and load only the
      // selected category's embeds. A direct video page remains unchanged.
      panels[i].querySelectorAll('iframe').forEach(frame => {
        frame.dataset.mediaSource ||= frame.getAttribute('src') || '';
        if (i === index && !frame.hasAttribute('src')) frame.src = frame.dataset.mediaSource;
        else if (i !== index) frame.removeAttribute('src');
      });
    });
  };
  const fromHash = () => {
    let target;
    try { target = document.getElementById(decodeURIComponent(location.hash.slice(1))); } catch (_) { /* Invalid URL hashes use the default category. */ }
    const panel = target?.closest('.media-category');
    const index = panels.indexOf(panel);
    select(index < 0 ? 0 : index);
    // Preserve older item-specific bookmarks inside Published Work.
    if (target && panel && target !== panel) requestAnimationFrame(() => target.scrollIntoView({behavior:'instant', block:'start'}));
    else if (target === panel && panel) requestAnimationFrame(() => {
      // A category hash selects a tab rather than jumping past its controls.
      // Native fragment focus otherwise outlines the entire panel on arrival.
      if (document.activeElement === panel) panel.blur();
      scrollTo({top:0,behavior:'instant'});
    });
  };
  const activate = index => {
    const hash = links[index].hash;
    if (location.hash !== hash) history.pushState(null, '', hash);
    select(index);
  };
  links.forEach((link, index) => {
    link.addEventListener('click', event => {
      if (event.button || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
      event.preventDefault(); activate(index);
    });
    link.addEventListener('keydown', event => {
      if (event.key === ' ' || event.key === 'Enter') {
        event.preventDefault(); activate(index); return;
      }
      let next = index;
      if (event.key === 'ArrowRight') next = (index + 1) % links.length;
      else if (event.key === 'ArrowLeft') next = (index + links.length - 1) % links.length;
      else if (event.key === 'Home') next = 0;
      else if (event.key === 'End') next = links.length - 1;
      else return;
      event.preventDefault(); activate(next); links[next].focus();
    });
  });
  addEventListener('popstate', fromHash);
  addEventListener('hashchange', fromHash);
  addEventListener('pageshow', fromHash);
  fromHash();
})();
