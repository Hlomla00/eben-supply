// PRT362S — Eben Supply | Group KN3
//
// Alpine is not imported here: Livewire/Flux (@fluxScripts) already ships and
// starts it, and a second copy ran every directive twice. The old user menu,
// mobile menu, search bar and toast scripts were removed when Flux replaced them.

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.addEventListener('DOMContentLoaded', () => {

    // ── 1. Back-to-top button ─────────────────────────────────────────────
    const backToTop = document.getElementById('back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            backToTop.classList.toggle('hidden', window.scrollY < 380);
        }, { passive: true });
        backToTop.addEventListener('click', () =>
            window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' }));
    }

    // ── 2. Scroll-reveal (data-reveal attribute) ──────────────────────────
    const revealEls = document.querySelectorAll('[data-reveal]');
    if (reduceMotion || !('IntersectionObserver' in window)) {
        revealEls.forEach((el) => el.classList.add('revealed'));
    } else if (revealEls.length) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((en) => {
                if (en.isIntersecting) {
                    en.target.classList.add('revealed');
                    io.unobserve(en.target);
                }
            });
        }, { threshold: 0.1 });
        revealEls.forEach((el) => io.observe(el));
    }

    // ── 3. Product card image zoom on hover (mouse only) ──────────────────
    if (!reduceMotion && window.matchMedia('(hover: hover)').matches) {
        document.querySelectorAll('.product-img-wrap').forEach((wrap) => {
            const img = wrap.querySelector('img');
            if (!img) return;
            wrap.addEventListener('mouseenter', () => {
                img.style.transition = 'transform 0.55s cubic-bezier(0.25,0.46,0.45,0.94)';
                img.style.transform  = 'scale(1.08)';
            });
            wrap.addEventListener('mouseleave', () => { img.style.transform = 'scale(1)'; });
        });
    }

    // ── 4. Size selector buttons ──────────────────────────────────────────
    document.querySelectorAll('[data-size-group] .size-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            btn.closest('[data-size-group]')
               .querySelectorAll('.size-btn')
               .forEach((b) => b.classList.remove('size-btn-active'));
            btn.classList.add('size-btn-active');
            const inp = document.getElementById('selected-size');
            if (inp) inp.value = btn.dataset.size;
        });
    });

    // ── 5. Quantity stepper (+/-) ─────────────────────────────────────────
    document.querySelectorAll('[data-qty-dec], [data-qty-inc]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const inputId = btn.dataset.qtyDec || btn.dataset.qtyInc;
            const input   = document.getElementById(inputId);
            if (!input) return;
            let v = parseInt(input.value, 10) || 1;
            if ('qtyDec' in btn.dataset) v = Math.max(1, v - 1);
            if ('qtyInc' in btn.dataset) v = Math.min(99, v + 1);
            input.value = v;
        });
    });

    // ── 6. Cart badge pulse when adding item ──────────────────────────────
    if (!reduceMotion) {
        document.querySelectorAll('[data-add-cart]').forEach((form) => {
            form.addEventListener('submit', () => {
                const badge = document.getElementById('cart-badge');
                if (badge) {
                    badge.style.transition = 'transform 0.15s ease';
                    badge.style.transform  = 'scale(1.6)';
                    setTimeout(() => { badge.style.transform = 'scale(1)'; }, 200);
                }
            });
        });
    }

});
