/* =========================================================
   Portfolio -- portfolio tabs + contact form

   Projects are now rendered server-side by PHP
   (includes/sections/portfolio.php), so all that is left
   here is tab switching -- no fetching.
   ========================================================= */

(function () {
    "use strict";

    var API_CONTACT = "api/contact.php";

    /* ---------------------------------------------------------
       PORTFOLIO TABS
       --------------------------------------------------------- */

    function initTabs() {
        var tabsBox = document.querySelector(".portfolio-tabs");
        var panelsBox = document.querySelector(".portfolio-panels");

        if (!tabsBox || !panelsBox) {
            return;
        }

        var tabs = tabsBox.querySelectorAll(".tablinks");
        var panels = panelsBox.querySelectorAll(".tabcontent");

        function showTab(name) {
            var i;
            for (i = 0; i < tabs.length; i++) {
                tabs[i].classList.toggle("active", tabs[i].dataset.tab === name);
            }
            for (i = 0; i < panels.length; i++) {
                panels[i].classList.toggle("active", panels[i].dataset.tab === name);
            }
        }

        Array.prototype.forEach.call(tabs, function (tab) {
            tab.addEventListener("click", function () {
                showTab(tab.dataset.tab);
            });
        });
    }

    /* ---------------------------------------------------------
       CONTACT FORM
       --------------------------------------------------------- */

    function setStatus(box, type, text) {
        box.className = "form-status is-" + type;
        box.textContent = text;
    }

    function initContactForm() {
        var form = document.getElementById("contact-form");
        if (!form) {
            return;
        }

        var status = form.querySelector(".form-status");
        var button = form.querySelector('button[type="submit"]');
        var original = button.textContent;

        form.addEventListener("submit", function (event) {
            event.preventDefault();

            /* clear any previous field errors */
            form.querySelectorAll(".has-error").forEach(function (el) {
                el.classList.remove("has-error");
            });

            button.disabled = true;
            button.textContent = "Sending...";
            setStatus(status, "loading", "Sending your message...");

            fetch(API_CONTACT, {
                method: "POST",
                body: new FormData(form),
                headers: { Accept: "application/json" }
            })
                .then(function (res) {
                    return res.json().then(function (body) {
                        return { ok: res.ok, body: body };
                    });
                })
                .then(function (res) {
                    if (res.body.ok) {
                        setStatus(status, "success", res.body.message);
                        form.reset();
                        return;
                    }

                    if (res.body.fields) {
                        Object.keys(res.body.fields).forEach(function (name) {
                            var input = form.querySelector('[name="' + name + '"]');
                            if (input) input.classList.add("has-error");
                        });
                    }
                    setStatus(status, "error", res.body.error || "Your message could not be sent.");
                })
                .catch(function (err) {
                    setStatus(status, "error", "Connection problem -- please try again in a moment.");
                    console.error("[contact]", err);
                })
                .then(function () {
                    button.disabled = false;
                    button.textContent = original;
                });
        });
    }

    /* --------------------------------------------------------- */

    document.addEventListener("DOMContentLoaded", function () {
        initTabs();
        initContactForm();
    });
})();
