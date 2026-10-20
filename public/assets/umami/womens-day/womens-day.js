(() => {
    "use strict";
    const decor = document.getElementById("womensDayDecor");
    if (!decor) return;
    const petalUrl = new URL("petal.webp", document.currentScript.src).href;
    const modal = document.getElementById("modal");
    const mobile = matchMedia("(max-width: 700px)");
    const motion = matchMedia("(prefers-reduced-motion: reduce)");
    const random = (min, max) => min + Math.random() * (max - min);
    const petals = () => {
        decor.querySelectorAll(".wd-petal").forEach(node => node.remove());
        const count = motion.matches ? 0 : mobile.matches ? 6 : 12;
        const fragment = document.createDocumentFragment();
        for (let i = 0; i < count; i++) {
            const petal = document.createElement("span");
            petal.className = "wd-petal";
            // Keep the main reading column clear; alternate between the two edges.
            petal.style.left = `${i % 2 ? random(88, 98) : random(0, 10)}%`;
            petal.style.setProperty("--wd-size", `${random(mobile.matches ? 18 : 24, mobile.matches ? 28 : 38)}px`);
            petal.style.setProperty("--wd-duration", `${random(24, 36)}s`);
            petal.style.setProperty("--wd-delay", `${-random(0, 36)}s`);
            petal.style.setProperty("--wd-drift", `${random(-18, 18)}px`);
            petal.style.setProperty("--wd-flutter", `${random(4, 7)}s`);
            const image = document.createElement("img");
            image.src = petalUrl;
            image.alt = "";
            image.draggable = false;
            petal.append(image);
            fragment.append(petal);
        }
        decor.append(fragment);
    };
    const update = () => {
        const blocked = document.body.classList.contains("cart-open") || modal?.getAttribute("aria-hidden") === "false";
        decor.hidden = blocked;
        decor.classList.toggle("is-paused", document.hidden || blocked);
    };
    const observer = new MutationObserver(update);
    observer.observe(document.body, { attributes: true, attributeFilter: ["class"] });
    if (modal) observer.observe(modal, { attributes: true, attributeFilter: ["aria-hidden"] });
    document.addEventListener("visibilitychange", update);
    window.addEventListener("pagehide", () => decor.classList.add("is-paused"));
    window.addEventListener("pageshow", update);
    mobile.addEventListener("change", petals);
    motion.addEventListener("change", petals);
    petals();
    update();
})();
