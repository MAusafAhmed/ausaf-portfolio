/* =========================================================
   Portfolio -- animations
   Scroll reveal, counters, nav scrollspy, portfolio stagger.
   This file drives every animation; main.js is left untouched.
   ========================================================= */

(function () {
    "use strict";

    var reduceMotion =
        window.matchMedia &&
        window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    /* ---------------------------------------------------------
       HELPERS
       --------------------------------------------------------- */

    var observer = null;

    function observe(el) {
        if (!el) return;
        if (observer) {
            observer.observe(el);
        } else {
            el.classList.add("is-visible");
        }
    }

    /* Tags elements so they get revealed on scroll.
       opts: { dir, stagger (ms), delay (ms) }  root: optional scope */
    function tag(selector, opts, root) {
        opts = opts || {};
        var nodes = (root || document).querySelectorAll(selector);
        var step = opts.stagger || 0;
        var base = opts.delay || 0;

        Array.prototype.forEach.call(nodes, function (el, i) {
            if (el.hasAttribute("data-reveal")) return;

            el.setAttribute("data-reveal", opts.dir || "up");

            var delay = base + i * step;
            if (delay) {
                el.style.setProperty("--reveal-delay", delay + "ms");
            }
            observe(el);
        });
    }

    /* Each section's heading block: outline text, sub heading, heading, paragraph */
    function tagHeading(sectionSelector) {
        var section = document.querySelector(sectionSelector);
        if (!section) return;

        tag(
            ":scope > .sub-heading-before, :scope > .sub-heading, " +
            ":scope > .section-heading, :scope > .para",
            { stagger: 90 },
            section
        );
    }

    /* ---------------------------------------------------------
       COUNTERS -- "2+", "4K+", "60+" count up from zero
       --------------------------------------------------------- */

    function prepCounters() {
        var nodes = document.querySelectorAll(".counter-number");

        Array.prototype.forEach.call(nodes, function (el) {
            var match = el.textContent.trim().match(/^(\d+(?:\.\d+)?)(.*)$/);
            if (!match) return;

            el.setAttribute("data-count-to", match[1]);
            el.setAttribute("data-count-suffix", match[2]);

            if (reduceMotion) return;

            el.textContent = "0" + match[2];

            /* The number is observed directly -- the parent (.counter-num-heading)
               has its own reveal, which does not trigger the count-up */
            if (observer) {
                observer.observe(el);
            } else {
                runCounter(el);
            }
        });
    }

    function runCounter(el) {
        var target = parseFloat(el.getAttribute("data-count-to"));
        var suffix = el.getAttribute("data-count-suffix") || "";

        if (isNaN(target) || reduceMotion) {
            el.textContent = (el.getAttribute("data-count-to") || "") + suffix;
            return;
        }

        var duration = 1600;
        var startedAt = null;
        var done = false;

        function finish() {
            if (done) return;
            done = true;
            el.textContent = target + suffix;
        }

        function frame(now) {
            if (done) return;
            if (startedAt === null) startedAt = now;

            var progress = Math.min((now - startedAt) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);

            el.textContent = Math.round(eased * target) + suffix;

            if (progress < 1) {
                requestAnimationFrame(frame);
            } else {
                finish();
            }
        }

        /* Safety net: if rAF never runs (background tab, throttling),
           the number must not stay stuck at "0". */
        setTimeout(finish, duration + 400);

        if (typeof requestAnimationFrame === "function") {
            requestAnimationFrame(frame);
        } else {
            finish();
        }
    }

    /* ---------------------------------------------------------
       INTERSECTION OBSERVER
       --------------------------------------------------------- */

    function initObserver() {
        if (!("IntersectionObserver" in window)) return;

        observer = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;

                    entry.target.classList.add("is-visible");
                    observer.unobserve(entry.target);

                    if (entry.target.hasAttribute("data-count-to")) {
                        runCounter(entry.target);
                    }
                });
            },
            { threshold: 0.1, rootMargin: "0px 0px -60px 0px" }
        );
    }

    /* ---------------------------------------------------------
       TAGGING -- which element animates in which way
       --------------------------------------------------------- */

    function tagEverything() {
        /* ---- left sidebar, on page load ---- */
        tag(".main-left .sub-heading-one", { delay: 100 });
        tag(".main-left .main-heading", { delay: 220 });
        tag(".main-left .menu-info", { delay: 360 });
        tag(".main-left .menu-info ul li", { stagger: 70, delay: 520 });

        /* ---- about ---- */
        tagHeading(".about-me-secion");
        tag(".about-me-secion .about-info-img", { dir: "left" });
        tag(
            ".about-me-secion .about-info-desc > *",
            { stagger: 90, delay: 120 }
        );
        tag(
            ".about-me-secion .about-counters .counter-num-heading",
            { stagger: 130 }
        );

        /* ---- portfolio ----
           Cards come from PHP, so they are already in the DOM on load.
           Each panel staggers separately -- otherwise the delay keeps growing. */
        tagHeading(".portfolio-section");
        tag(".portfolio-section .portfolio-tabs", { delay: 200 });

        Array.prototype.forEach.call(
            document.querySelectorAll(".portfolio-panels .tabcontent"),
            function (panel) {
                tag(".portfolio-content", { dir: "zoom", stagger: 80 }, panel);
            }
        );

        /* ---- services ---- */
        tagHeading(".services-section");
        tag(".services-section .services .service", { stagger: 120 });

        /* ---- testimonials ----
           Slick clones the slides, so the whole slider is revealed as one block */
        tagHeading(".testimonial-section");
        tag(".testimonial-section .testimonial-slider", { delay: 150 });

        /* ---- resume ---- */
        tagHeading(".resume-section");
        tag(".resume-section .education .heading-img", { dir: "left" });
        tag(".resume-section .experience .heading-img", { dir: "right" });
        tag(".resume-section .uni-office-img img", { dir: "line", delay: 200 });
        tag(
            ".resume-section .btns-info .btn-info",
            { dir: "left", stagger: 150, delay: 250 }
        );

        /* ---- contact ---- */
        tagHeading(".get-in-touch-section");
        tag(".get-in-touch-section .contact-ifo > div", { stagger: 110 });
        tag(
            ".get-in-touch-section .contact-form form > div",
            { stagger: 80, delay: 100 }
        );
    }

    /* ---------------------------------------------------------
       PORTFOLIO -- replay the card stagger when a tab is switched
       --------------------------------------------------------- */

    function playPanel(panel) {
        if (!panel) return;

        var cards = panel.querySelectorAll(".portfolio-content");

        Array.prototype.forEach.call(cards, function (card, i) {
            if (observer) observer.unobserve(card);

            card.classList.remove("is-visible");
            card.style.setProperty("--reveal-delay", i * 80 + "ms");

            /* force a reflow -- otherwise the browser merges the class remove/add */
            void card.offsetWidth;

            card.classList.add("is-visible");
        });
    }

    /* Re-run the stagger for a panel's cards as soon as its tab is opened.
       A hidden panel (display:none) never fires the IntersectionObserver,
       so it has to be triggered by hand here. */
    function watchPortfolio() {
        var panelsBox = document.querySelector(".portfolio-panels");
        if (!panelsBox || !("MutationObserver" in window)) return;

        var watcher = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (
                    mutation.attributeName === "class" &&
                    mutation.target.classList.contains("tabcontent") &&
                    mutation.target.classList.contains("active")
                ) {
                    playPanel(mutation.target);
                }
            });
        });

        watcher.observe(panelsBox, {
            subtree: true,
            attributes: true,
            attributeFilter: ["class"]
        });
    }

    /* ---------------------------------------------------------
       NAV -- highlight the active item while scrolling
       --------------------------------------------------------- */

    function initScrollSpy() {
        var links = document.querySelectorAll(".menu-info a[href^='#']");
        if (!links.length) return;

        var items = [];

        Array.prototype.forEach.call(links, function (link) {
            var section = document.querySelector(link.getAttribute("href"));
            if (section) {
                items.push({ li: link.parentElement, section: section });
            }
        });

        if (!items.length) return;

        var ticking = false;

        function update() {
            ticking = false;

            var line = window.pageYOffset + window.innerHeight * 0.35;
            var active = null;

            items.forEach(function (item) {
                if (item.section.offsetTop <= line) active = item;
            });

            /* at the very bottom of the page -- activate the last section */
            if (
                window.innerHeight + window.pageYOffset >=
                document.body.scrollHeight - 40
            ) {
                active = items[items.length - 1];
            }

            items.forEach(function (item) {
                item.li.classList.toggle("is-active", item === active);
            });
        }

        function onScroll() {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(update);
        }

        window.addEventListener("scroll", onScroll, { passive: true });
        window.addEventListener("resize", onScroll);
        update();
    }

    /* ---------------------------------------------------------
       START
       --------------------------------------------------------- */

    function init() {
        initObserver();
        prepCounters();
        tagEverything();
        watchPortfolio();
        initScrollSpy();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
