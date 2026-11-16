function toggleMenu() {
    var m = document.getElementById("navLinks");
    if (m) m.classList.toggle("active");
}

document.addEventListener("DOMContentLoaded", function () {
    var base = document.body.getAttribute("data-base") || "";

    // Track call / WhatsApp / email / website clicks
    document.querySelectorAll("[data-track]").forEach(function (el) {
        el.addEventListener("click", function () {
            var url = base + "/track.php?b=" + el.getAttribute("data-biz") + "&t=" + el.getAttribute("data-track");
            if (navigator.sendBeacon) { navigator.sendBeacon(url); } else { fetch(url, { keepalive: true }); }
        });
    });

    // City dropdown that follows the chosen province
    document.querySelectorAll("[data-province-select]").forEach(function (prov) {
        var city = document.getElementById(prov.getAttribute("data-province-select"));
        if (!city) return;
        var all = Array.prototype.slice.call(city.options);
        var current = city.getAttribute("data-current") || "";
        function refill() {
            var p = prov.value;
            city.innerHTML = "";
            all.forEach(function (o) {
                if (o.value === "" || !p || o.getAttribute("data-prov") === p) city.appendChild(o);
            });
            city.value = current;
            current = "";
        }
        prov.addEventListener("change", refill);
        refill();
    });

    // Category search filter
    var input = document.getElementById("categorySearch");
    if (input) {
        var cards = document.querySelectorAll(".cat-filter");
        var empty = document.getElementById("noCategories");
        input.addEventListener("input", function () {
            var term = this.value.toLowerCase().trim(), shown = 0;
            cards.forEach(function (c) {
                var match = c.getAttribute("data-name").indexOf(term) !== -1;
                c.style.display = match ? "" : "none";
                if (match) shown++;
            });
            if (empty) empty.style.display = shown === 0 ? "block" : "none";
        });
    }

    // Business page tabs: highlight the section being viewed
    var tabs = document.querySelectorAll(".tabs-bar a");
    if (tabs.length && "IntersectionObserver" in window) {
        var map = {};
        tabs.forEach(function (t) { map[t.getAttribute("href").slice(1)] = t; });
        var io2 = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) {
                    tabs.forEach(function (t) { t.classList.remove("on"); });
                    if (map[en.target.id]) map[en.target.id].classList.add("on");
                }
            });
        }, { rootMargin: "-30% 0px -60% 0px" });
        Object.keys(map).forEach(function (id) { var s = document.getElementById(id); if (s) io2.observe(s); });
    }

    // Reveal on scroll
    var items = document.querySelectorAll(".reveal");
    if ("IntersectionObserver" in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) { en.target.classList.add("in"); io.unobserve(en.target); }
            });
        }, { threshold: 0.1 });
        items.forEach(function (i) { io.observe(i); });
    } else {
        items.forEach(function (i) { i.classList.add("in"); });
    }
});
