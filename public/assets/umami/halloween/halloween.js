(() => {
    const decor = document.getElementById('halloweenDecor');
    if (!decor) return;

    const modal = document.getElementById('modal');
    const header = document.querySelector('.topbar');
    if (header) {
        const positionWebs = () => decor.style.setProperty('--hw-header-bottom', `${Math.ceil(header.getBoundingClientRect().bottom + 6)}px`);
        positionWebs();
        new ResizeObserver(positionWebs).observe(header);
        window.addEventListener('resize', positionWebs, {passive: true});
    }
    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
    const mobile = matchMedia('(max-width: 900px)');
    const flights = new Map();
    let timer;
    let ready = false;
    let paused = true;
    const random = (min, max) => min + Math.random() * (max - min);
    const removeFlight = (node, animation) => {
        animation.onfinish = null;
        animation.cancel();
        node.remove();
        flights.delete(node);
    };
    const launch = () => {
        if (paused || reducedMotion.matches || flights.size >= (mobile.matches ? 3 : 6)) return;
        const leftward = Math.random() < .5;
        const node = document.createElement('div');
        node.className = `hw-flight${leftward ? ' hw-flight-left' : ''}${Math.random() < .5 ? ' hw-flight-small' : ''}`;
        const sprite = document.createElement('span');
        sprite.className = 'hw-profile';
        for (const name of ['hw-profile-body', 'hw-profile-wing']) {
            const part = document.createElement('i');
            part.className = name;
            sprite.append(part);
        }
        node.append(sprite);
        decor.append(node);
        const width = innerWidth;
        const height = innerHeight;
        const startY = random(height * .18, height * .74);
        const endY = Math.max(100, Math.min(height - 80, startY + random(-100, 100)));
        const startX = leftward ? width + 120 : -120;
        const endX = leftward ? -120 : width + 120;
        const points = [0, .25, .5, .75, 1].map((progress, index) => {
            const x = startX + (endX - startX) * progress;
            const y = startY + (endY - startY) * progress + (index % 2 ? -24 : 12);
            return { transform: `translate3d(${x}px, ${y}px, 0) rotate(${random(-7, 7)}deg)` };
        });
        const animation = node.animate(points, {duration: random(mobile.matches ? 4500 : 6500, mobile.matches ? 6500 : 9500), easing: 'linear', fill: 'both'});
        flights.set(node, animation);
        animation.onfinish = () => removeFlight(node, animation);
    };
    const schedule = () => {
        clearTimeout(timer);
        if (paused || !ready || reducedMotion.matches) return;
        timer = setTimeout(() => {
            launch();
            schedule();
        }, random(mobile.matches ? 1600 : 1100, mobile.matches ? 2500 : 2000));
    };
    const update = () => {
        paused = document.hidden || document.body.classList.contains('cart-open')
            || modal?.getAttribute('aria-hidden') === 'false';
        decor.classList.toggle('is-paused', paused);
        decor.hidden = modal?.getAttribute('aria-hidden') === 'false';
        for (const [node, animation] of flights) {
            if (reducedMotion.matches) removeFlight(node, animation);
            else if (paused) animation.pause();
            else animation.play();
        }
        schedule();
    };
    const observer = new MutationObserver(update);
    observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
    if (modal) observer.observe(modal, { attributes: true, attributeFilter: ['aria-hidden'] });
    document.addEventListener('visibilitychange', update);
    window.addEventListener('pageshow', update);
    window.addEventListener('pagehide', () => {
        paused = true;
        clearTimeout(timer);
        decor.classList.add('is-paused');
        for (const animation of flights.values()) animation.pause();
    });
    reducedMotion.addEventListener('change', update);
    mobile.addEventListener('change', () => {
        // Rebuild flights for the new viewport without accumulating off-screen nodes.
        for (const [node, animation] of flights) removeFlight(node, animation);
        update();
    });
    update();
    // Decorative animation never competes with the first frame of the page.
    requestAnimationFrame(() => requestAnimationFrame(() => {
        decor.classList.add('is-ready');
        ready = true;
        update();
        if (!reducedMotion.matches) launch();
    }));
})();
