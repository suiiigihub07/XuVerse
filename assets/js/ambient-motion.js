/* A quiet, bounded animation field. No remote dependencies or interaction interception. */
(() => {
    'use strict';
    const start = () => {
        if (document.querySelector('.xv-ambient')) return;
        const reduced = matchMedia('(prefers-reduced-motion: reduce)');
        const precisePointer = matchMedia('(hover: hover) and (pointer: fine)');
        const field = document.createElement('div');
        field.className = 'xv-ambient';
        field.setAttribute('aria-hidden', 'true');
        field.dataset.reading = String(Boolean(document.querySelector('.article-body,.resume-paper')));
        const canvas = document.createElement('canvas');
        field.appendChild(canvas);
        document.body.prepend(field);
        document.body.classList.add('xv-motion-mounted');
        let motionEnabled = true;
        try { motionEnabled = localStorage.getItem('xuverse.backgroundMotion') !== 'off'; } catch (_) { /* Private browsing may reject storage. */ }
        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'xv-motion-toggle';
        toggle.setAttribute('aria-label', 'Background motion');
        (document.querySelector('.footer-core') || document.body).appendChild(toggle);
        const context = canvas.getContext('2d', { alpha: true });
        let width = 0, height = 0, scale = 1, frame = 0, resizeFrame = 0;
        let previousTime = 0, elapsed = 0, paintedAt = 0;
        const cursor = { x:0, y:0, targetX:0, targetY:0 };

        const draw = () => {
            if (!context) return;
            context.clearRect(0, 0, width, height);
            cursor.x += (cursor.targetX - cursor.x) * .045;
            cursor.y += (cursor.targetY - cursor.y) * .045;
            const time = elapsed * .00009;
            const count = width < 700 ? 16 : 28;
            const spread = Math.max(width, height);
            /* Parallel contour paths form a drifting composition, not a busy network. */
            for (let i = 0; i < 5; i++) {
                const shift = Math.sin(time + i * .75) * spread * .025;
                const x = width * (.54 + i * .095) + shift + cursor.x;
                context.beginPath();
                context.moveTo(x + spread * .19, -height * .12);
                context.bezierCurveTo(x - spread * .15, height * .26,
                    x + spread * .25, height * .67, x - spread * .25, height * 1.12);
                context.strokeStyle = `rgba(225,29,56,${.045 + i * .006})`;
                context.lineWidth = .65;
                context.stroke();
            }
            for (let i = 0; i < count; i++) {
                const seed = i * 2.399963;
                const baseX = ((i * 73.37 + 17.1) % 100) * .01;
                const baseY = ((i * 41.13 + 8.7) % 100) * .01;
                const x = width * baseX + Math.sin(time * .8 + seed) * 19 + cursor.x * .4;
                const y = height * baseY + Math.cos(time * .65 + seed) * 24 + cursor.y * .4;
                const breath = .5 + .5 * Math.sin(time * 2 + seed);
                context.beginPath();
                context.arc(x, y, .65 + breath * .5, 0, Math.PI * 2);
                context.fillStyle = `rgba(255,68,89,${.14 + breath * .18})`;
                context.fill();
                if (i % 6 === 0) {
                    context.beginPath();
                    context.moveTo(x - 11, y);
                    context.lineTo(x + 11, y);
                    context.moveTo(x, y - 11);
                    context.lineTo(x, y + 11);
                    context.strokeStyle = `rgba(225,29,56,${.07 + breath * .06})`;
                    context.lineWidth = .5;
                    context.stroke();
                }
            }
        };
        const resize = () => {
            resizeFrame = 0;
            width = innerWidth;
            height = innerHeight;
            scale = Math.min(devicePixelRatio || 1, 1.5, Math.sqrt(3200000 / (width * height)));
            canvas.width = Math.max(1, Math.round(width * scale));
            canvas.height = Math.max(1, Math.round(height * scale));
            context?.setTransform(scale, 0, 0, scale, 0, 0);
            draw();
        };
        const tick = (now) => {
            if (previousTime) elapsed += Math.min(now - previousTime, 60);
            previousTime = now;
            if (now - paintedAt >= 1000 / 30) { draw(); paintedAt = now; }
            frame = requestAnimationFrame(tick);
        };
        const sync = () => {
            cancelAnimationFrame(frame);
            frame = 0;
            previousTime = 0;
            const paused = reduced.matches || document.hidden || !motionEnabled || !context;
            document.body.classList.toggle('xv-motion-paused', paused);
            toggle.hidden = reduced.matches;
            toggle.setAttribute('aria-pressed', String(motionEnabled && !reduced.matches));
            toggle.textContent = motionEnabled ? 'Motion on' : 'Motion off';
            toggle.title = motionEnabled ? 'Pause background animation' : 'Resume background animation';
            if (!paused) frame = requestAnimationFrame(tick);
            else draw();
        };
        toggle.addEventListener('click', () => {
            motionEnabled = !motionEnabled;
            try { localStorage.setItem('xuverse.backgroundMotion', motionEnabled ? 'on' : 'off'); } catch (_) { /* The control still works without storage. */ }
            sync();
        });
        addEventListener('resize', () => {
            if (!resizeFrame) resizeFrame = requestAnimationFrame(resize);
        }, { passive:true });
        addEventListener('pointermove', (event) => {
            if (reduced.matches || !precisePointer.matches) return;
            cursor.targetX = (event.clientX / Math.max(width, 1) - .5) * 14;
            cursor.targetY = (event.clientY / Math.max(height, 1) - .5) * 10;
        }, { passive:true });
        document.addEventListener('pointerleave', () => { cursor.targetX = cursor.targetY = 0; });
        document.addEventListener('visibilitychange', sync);
        reduced.addEventListener('change', sync);
        addEventListener('pagehide', () => {
            cancelAnimationFrame(frame);
            cancelAnimationFrame(resizeFrame);
            frame = resizeFrame = 0;
        });
        addEventListener('pageshow', sync);
        resize();
        sync();

        const candidates = [...document.querySelectorAll('.public-card,.public-hero > div,.four-areas > div,.connection')]
            .filter(element => !element.classList.contains('reveal'));
        if (!reduced.matches && 'IntersectionObserver' in window) {
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('xv-enter-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold:0, rootMargin:'0px 0px -20px 0px' });
            candidates.forEach((element, i) => {
                element.style.setProperty('--xv-enter-delay', `${(i % 3) * 65}ms`);
                element.classList.add('xv-enter');
                observer.observe(element);
            });
            reduced.addEventListener('change', () => {
                if (reduced.matches) {
                    candidates.forEach(element => element.classList.add('xv-enter-visible'));
                    observer.disconnect();
                }
            });
        }
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once:true });
    else start();
})();
