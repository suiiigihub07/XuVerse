document.addEventListener('DOMContentLoaded', () => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const navToggle = document.querySelector('.nav-toggle');
    const navLinks = document.querySelector('#primary-links');
    const printResume = document.querySelector('[data-print-resume]');
    const topProgress = document.querySelector('.top-progress');
    const navbar = document.querySelector('.navbar');

    document.body.classList.add('motion-ready');
    document.querySelectorAll('.nav-links a.active').forEach((link) => link.setAttribute('aria-current', 'page'));

    if (printResume) {
        printResume.addEventListener('click', () => window.print());
    }

    if (csrfToken) {
        document.querySelectorAll('form[method="post"], form[method="POST"]').forEach((form) => {
            if (form.querySelector('input[name="csrf_token"]')) {
                return;
            }

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'csrf_token';
            input.value = csrfToken;
            form.appendChild(input);
        });
    }

    // Keep destructive actions reviewable without browser-native blocking prompts.
    const confirmedForms = new WeakSet();
    let pendingConfirmation = null;
    const confirmation = document.createElement('dialog');
    if (typeof confirmation.showModal === 'function') {
        confirmation.className = 'confirmation-dialog';
        confirmation.setAttribute('aria-labelledby', 'confirm-title');
        confirmation.setAttribute('aria-describedby', 'confirm-description');
        confirmation.innerHTML = '<h2 id="confirm-title">Confirm deletion</h2><p id="confirm-description"></p><div class="confirmation-actions"><button type="button" class="btn secondary-btn" data-cancel>Keep item</button><button type="button" class="btn danger-btn" data-approve>Delete item</button></div>';
        document.body.appendChild(confirmation);
        const finishConfirmation = (approved) => {
            const pending = pendingConfirmation;
            pendingConfirmation = null;
            confirmation.close();
            document.body.classList.remove('lightbox-open');
            pending?.submitter?.focus();
            if (approved && pending) {
                confirmedForms.add(pending.form);
                pending.form.requestSubmit(pending.submitter || undefined);
            }
        };
        confirmation.querySelector('[data-cancel]').addEventListener('click', () => finishConfirmation(false));
        confirmation.querySelector('[data-approve]').addEventListener('click', () => finishConfirmation(true));
        confirmation.addEventListener('cancel', (event) => {
            event.preventDefault();
            finishConfirmation(false);
        });
        confirmation.addEventListener('click', (event) => {
            if (event.target === confirmation) { finishConfirmation(false); }
        });
        document.querySelectorAll('form[data-confirm]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (confirmedForms.has(form)) { confirmedForms.delete(form); return; }
                event.preventDefault();
                if (pendingConfirmation) { return; }
                pendingConfirmation = { form, submitter: event.submitter };
                const title = form.closest('article')?.querySelector('h2,h3')?.textContent?.trim();
                confirmation.querySelector('p').textContent = (title ? `“${title}” — ` : '') + (form.dataset.confirm || 'Delete this item?') + ' This cannot be undone.';
                confirmation.showModal();
                document.body.classList.add('lightbox-open');
                confirmation.querySelector('[data-cancel]').focus();
            });
        });
    } else {
        document.querySelectorAll('form[data-confirm]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!window.confirm(form.dataset.confirm || 'Delete this item?')) { event.preventDefault(); }
            });
        });
    }

    if (navToggle && navLinks) {
        const closeNavigation = () => {
            navToggle.setAttribute('aria-expanded', 'false');
            navLinks.classList.remove('is-open');
        };
        navToggle.addEventListener('click', () => {
            const willOpen = navToggle.getAttribute('aria-expanded') !== 'true';
            navToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            navLinks.classList.toggle('is-open', willOpen);
        });

        navLinks.addEventListener('click', (event) => {
            if (event.target.closest('a')) {
                closeNavigation();
            }
        });
        document.addEventListener('pointerdown', (event) => {
            if (!navbar?.contains(event.target)) { closeNavigation(); }
        });
        navbar?.addEventListener('focusout', () => {
            window.requestAnimationFrame(() => {
                if (!navbar.contains(document.activeElement)) { closeNavigation(); }
            });
        });
        window.matchMedia('(max-width: 1060px)').addEventListener('change', () => {
            const focusWasInMenu = navLinks.contains(document.activeElement);
            closeNavigation();
            if (focusWasInMenu && window.matchMedia('(max-width: 1060px)').matches) { navToggle.focus(); }
        });
    }

    const measureNavigation = () => {
        if (navbar) { document.documentElement.style.setProperty('--nav-height', `${Math.ceil(navbar.getBoundingClientRect().height)}px`); }
    };
    if (navbar && 'ResizeObserver' in window) { new ResizeObserver(measureNavigation).observe(navbar); }
    measureNavigation();

    document.querySelectorAll('.profile-menu').forEach((menu) => {
        const trigger = menu.querySelector('.profile-trigger');

        if (!trigger) {
            return;
        }

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const willOpen = !menu.classList.contains('is-open');
            menu.classList.toggle('is-open', willOpen);
            trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('.profile-menu.is-open').forEach((menu) => {
            menu.classList.remove('is-open');
            menu.querySelector('.profile-trigger')?.setAttribute('aria-expanded', 'false');
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        document.querySelectorAll('.profile-menu.is-open').forEach((menu) => {
            menu.classList.remove('is-open');
            const trigger = menu.querySelector('.profile-trigger');
            trigger?.setAttribute('aria-expanded', 'false');
            trigger?.focus();
        });
        if (navLinks?.classList.contains('is-open')) {
            navLinks.classList.remove('is-open');
            navToggle?.setAttribute('aria-expanded', 'false');
            navToggle?.focus();
        }
    });

    const revealItems = [...document.querySelectorAll('.reveal')];
    revealItems.forEach((item) => {
        const siblings = [...(item.parentElement?.children || [])].filter((child) => child.classList.contains('reveal'));
        const siblingIndex = Math.max(0, siblings.indexOf(item));
        item.style.setProperty('--reveal-delay', `${Math.min(siblingIndex * 70, 280)}ms`);
    });

    if (!reduceMotion && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { rootMargin: '0px', threshold: 0 });

        revealItems.forEach((item) => observer.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    }

    if (!reduceMotion && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        document.querySelectorAll('.project-card, .article-card, .media-card, .social-card, .deck-card, .snap-card').forEach((card) => {
            card.addEventListener('pointermove', (event) => {
                const bounds = card.getBoundingClientRect();
                const x = ((event.clientX - bounds.left) / bounds.width) * 100;
                const y = ((event.clientY - bounds.top) / bounds.height) * 100;
                card.style.setProperty('--mx', `${x.toFixed(1)}%`);
                card.style.setProperty('--my', `${y.toFixed(1)}%`);
            }, { passive: true });

            card.addEventListener('pointerleave', () => {
                card.style.removeProperty('--mx');
                card.style.removeProperty('--my');
            });
        });
    }

    let scrollQueued = false;
    const updateScrollUi = () => {
        scrollQueued = false;
        const scrollable = document.documentElement.scrollHeight - window.innerHeight;
        const progress = scrollable > 0 ? window.scrollY / scrollable : 0;

        if (topProgress) {
            topProgress.style.transform = `scaleX(${Math.min(1, Math.max(0, progress))})`;
        }

        navbar?.classList.toggle('nav-scrolled', window.scrollY > 12);
        backToTop.classList.toggle('is-visible', window.scrollY > 700);
    };

    const requestScrollUpdate = () => {
        if (!scrollQueued) {
            scrollQueued = true;
            window.requestAnimationFrame(updateScrollUi);
        }
    };

    const backToTop = document.createElement('button');
    backToTop.type = 'button';
    backToTop.className = 'back-to-top';
    backToTop.setAttribute('aria-label', 'Back to top');
    backToTop.textContent = 'Top';
    backToTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
    });
    document.body.appendChild(backToTop);

    window.addEventListener('scroll', requestScrollUpdate, { passive: true });
    updateScrollUi();

    document.querySelectorAll('.share-button').forEach((button) => {
        button.addEventListener('click', async () => {
            const url = button.dataset.shareUrl || window.location.href;
            const title = button.dataset.shareTitle || document.title;

            try {
                if (navigator.share) {
                    await navigator.share({ title, url });
                    return;
                }

                await navigator.clipboard.writeText(url);
                const original = button.textContent;
                button.textContent = 'Link copied';
                window.setTimeout(() => {
                    button.textContent = original;
                }, 1500);
            } catch (error) {
                // A cancelled share is not an application error.
            }
        });
    });

    document.querySelectorAll('.email-action').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            const mailto = button.dataset.mailto || button.getAttribute('href');
            const email = button.dataset.email || '';
            const status = document.querySelector('.mail-status');

            if (mailto) {
                window.location.href = mailto;
            }

            if (status) {
                status.textContent = email
                    ? `If no email draft opened, write to ${email}.`
                    : 'If no email draft opened, use your preferred email application.';
            }
        });
    });

    document.querySelectorAll('[data-collection-tools]').forEach((toolbar) => {
        const input = toolbar.querySelector('input[type="search"]');
        const collection = document.getElementById(input?.getAttribute('aria-controls'));
        if (!input || !collection) { return; }
        const cards = [...collection.children];
        const empty = toolbar.parentElement.querySelector('[data-collection-empty]');
        const count = toolbar.querySelector('.collection-count');
        const clear = toolbar.querySelector('[data-clear-search]');
        const normalize = (value) => value.normalize('NFKD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase().trim();
        const searchable = cards.map((card) => normalize(card.textContent));
        let announceTimer;
        const filter = (announce = true) => {
            const words = normalize(input.value).split(/\s+/).filter(Boolean);
            let matches = 0;
            cards.forEach((card, index) => {
                card.hidden = !words.every((word) => searchable[index].includes(word));
                if (!card.hidden) { matches++; card.classList.add('is-visible'); }
            });
            if (empty) { empty.hidden = matches !== 0; }
            clear.disabled = input.value.length === 0;
            window.clearTimeout(announceTimer);
            const updateCount = () => { count.textContent = `${matches} of ${cards.length} shown`; };
            if (announce) { announceTimer = window.setTimeout(updateCount, 180); } else { updateCount(); }
        };
        toolbar.hidden = false;
        input.addEventListener('input', () => filter());
        clear.addEventListener('click', () => { input.value = ''; filter(); input.focus(); });
        window.addEventListener('pageshow', () => filter(false));
        filter(false);
    });

    document.querySelectorAll('.article-body').forEach((body) => {
        const headings = [...body.querySelectorAll('h2,h3')];
        if (headings.length < 2) { return; }
        const contents = document.createElement('details');
        contents.className = 'article-contents';
        contents.innerHTML = '<summary>On this page</summary><nav aria-label="Article sections"></nav>';
        const list = contents.querySelector('nav');
        headings.forEach((heading, index) => {
            heading.id = heading.id || `article-section-${index + 1}`;
            const link = document.createElement('a');
            link.href = `#${heading.id}`;
            link.textContent = heading.textContent;
            list.appendChild(link);
        });
        body.before(contents);
    });

    document.querySelectorAll('[data-copy-email]').forEach((button) => {
        button.hidden = false;
        button.addEventListener('click', async () => {
            const status = document.querySelector('.mail-status');
            try {
                await navigator.clipboard.writeText(button.dataset.copyEmail);
                if (status) { status.textContent = 'Email address copied. Paste it into your preferred email app.'; }
            } catch {
                if (status) { status.textContent = `Select and copy the address above: ${button.dataset.copyEmail}`; }
            }
        });
    });

    const photoLinks = [...document.querySelectorAll('.photo-card .media-link')];
    if (photoLinks.length && typeof HTMLDialogElement !== 'undefined' && typeof HTMLDialogElement.prototype.showModal === 'function') {
        const lightbox = document.createElement('dialog');
        lightbox.className = 'media-lightbox';
        lightbox.setAttribute('role', 'dialog');
        lightbox.setAttribute('aria-modal', 'true');
        lightbox.setAttribute('aria-label', 'Photo preview');
        lightbox.innerHTML = '<div class="lightbox-toolbar"><p class="lightbox-counter"></p><button type="button" class="media-lightbox-close">Close</button></div><div class="lightbox-stage"><img alt=""></div><div class="lightbox-bottom"><button type="button" data-previous aria-label="Previous photo">←</button><p class="lightbox-caption"><span></span><a>View photo details</a></p><button type="button" data-next aria-label="Next photo">→</button></div><p class="lightbox-status" role="status" aria-live="polite"></p>';
        document.body.appendChild(lightbox);

        const preview = lightbox.querySelector('img');
        const close = lightbox.querySelector('.media-lightbox-close');
        const previous = lightbox.querySelector('[data-previous]');
        const next = lightbox.querySelector('[data-next]');
        const status = lightbox.querySelector('.lightbox-status');
        let photoIndex = 0;
        let opener;
        const showPhoto = (index) => {
            photoIndex = (index + photoLinks.length) % photoLinks.length;
            const link = photoLinks[photoIndex];
            const image = link.querySelector('img');
            preview.src = link.dataset.fullImage || image.src;
            preview.alt = image.alt;
            const counter = `${photoIndex + 1} / ${photoLinks.length}`;
            lightbox.querySelector('.lightbox-counter').textContent = counter;
            lightbox.querySelector('.lightbox-caption span').textContent = image.alt || 'Photo';
            lightbox.querySelector('.lightbox-caption a').href = link.href;
            status.textContent = `Photo ${counter}: ${image.alt}`;
            previous.disabled = next.disabled = photoLinks.length < 2;
        };
        const closeLightbox = () => {
            lightbox.classList.remove('is-open');
            lightbox.close();
            document.body.classList.remove('lightbox-open');
            preview.removeAttribute('src');
            opener?.focus();
        };

        photoLinks.forEach((link) => {
            link.addEventListener('click', (event) => {
                if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) { return; }
                const image = link.querySelector('img');

                if (!image) {
                    return;
                }

                event.preventDefault();
                opener = link;
                showPhoto(photoLinks.indexOf(link));
                lightbox.classList.add('is-open');
                lightbox.showModal();
                document.body.classList.add('lightbox-open');
                close.focus();
            });
        });

        close.addEventListener('click', closeLightbox);
        previous.addEventListener('click', () => showPhoto(photoIndex - 1));
        next.addEventListener('click', () => showPhoto(photoIndex + 1));
        lightbox.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
                event.preventDefault();
                showPhoto(photoIndex + (event.key === 'ArrowRight' ? 1 : -1));
            }
        });
        let touchStart;
        const stage = lightbox.querySelector('.lightbox-stage');
        stage.addEventListener('touchstart', (event) => {
            touchStart = event.touches.length === 1 ? { x:event.touches[0].clientX, y:event.touches[0].clientY } : null;
        }, { passive:true });
        stage.addEventListener('touchend', (event) => {
            if (!touchStart || !event.changedTouches.length) { return; }
            const dx = event.changedTouches[0].clientX - touchStart.x;
            const dy = event.changedTouches[0].clientY - touchStart.y;
            if (Math.abs(dx) > 60 && Math.abs(dx) > Math.abs(dy) * 1.5) { showPhoto(photoIndex + (dx < 0 ? 1 : -1)); }
            touchStart = null;
        }, { passive:true });
        preview.addEventListener('error', () => { status.textContent = 'This photo could not load. Use View photo details to open its page.'; });
        lightbox.addEventListener('cancel', (event) => {
            event.preventDefault();
            closeLightbox();
        });
        lightbox.addEventListener('click', (event) => {
            if (event.target === lightbox) {
                closeLightbox();
            }
        });
    }

});
