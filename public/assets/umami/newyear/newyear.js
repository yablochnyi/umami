(() => {
    "use strict";
    const decor = document.getElementById("newyearDecor");
    if (!decor) return;
    const snowflakeUrl = new URL("snowflake.svg", document.currentScript.src).href;
    const header = document.querySelector(".topbar");
    const modal = document.getElementById("modal");
    const mobile = matchMedia("(max-width: 700px)");
    const motion = matchMedia("(prefers-reduced-motion: reduce)");
    const random = (min, max) => min + Math.random() * (max - min);
    const position = () => {
        const bottom = header ? header.getBoundingClientRect().bottom + 8 : 16;
        decor.style.setProperty("--ny-top", `${Math.max(8, Math.min(innerHeight / 2, bottom))}px`);
    };
    const snow = () => {
        decor.querySelectorAll(".ny-snow").forEach(node => node.remove());
        const count = motion.matches ? 6 : mobile.matches ? 20 : 42;
        for (let i = 0; i < count; i++) {
            const flake = document.createElement("img");
            flake.src = snowflakeUrl;
            flake.alt = "";
            flake.draggable = false;
            flake.className = "ny-snow";
            flake.style.left = `${random(1, 98)}%`;
            flake.style.setProperty("--ny-size", `${random(mobile.matches ? 10 : 12, mobile.matches ? 18 : 26)}px`);
            flake.style.setProperty("--ny-duration", `${random(14, 26)}s`);
            flake.style.setProperty("--ny-delay", `${-random(0, 26)}s`);
            flake.style.setProperty("--ny-drift", `${random(-24, 24)}px`);
            flake.style.setProperty("--ny-static-top", `${random(16, 90)}%`);
            decor.append(flake);
        }
    };
    const update = () => {
        const blocked = document.body.classList.contains("cart-open") || modal?.getAttribute("aria-hidden") === "false";
        decor.hidden = blocked;
        decor.classList.toggle("is-paused", document.hidden || blocked);
        position();
    };
    const observer = new MutationObserver(update);
    observer.observe(document.body, { attributes: true, attributeFilter: ["class"] });
    if (modal) observer.observe(modal, { attributes: true, attributeFilter: ["aria-hidden"] });
    if (header) new ResizeObserver(position).observe(header);
    document.addEventListener("visibilitychange", update);
    window.addEventListener("resize", position, { passive: true });
    window.addEventListener("scroll", position, { passive: true });
    window.addEventListener("pagehide", () => {
        decor.classList.add("is-paused");
    });
    window.addEventListener("pageshow", update);
    mobile.addEventListener("change", () => { snow(); update(); });
    motion.addEventListener("change", snow);
    snow();
    update();
})();
