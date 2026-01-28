/**
 * SITUBA Landing Animations
 * Lightweight, high-performance scroll interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // --- 1. Scroll Progress Bar ---
    const progressBar = document.getElementById('scroll-progress');
    if (progressBar) {
        const updateProgress = () => {
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
            progressBar.style.width = `${progress}%`;
            requestAnimationFrame(updateProgress);
        };
        if (!prefersReducedMotion) {
            requestAnimationFrame(updateProgress);
        }
    }

    // --- 2. Intersection Observer for Reveals ---
    const revealCallback = (entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                
                // Trigger count up if applicable
                if (entry.target.hasAttribute('data-countup')) {
                    startCountUp(entry.target);
                }

                observer.unobserve(entry.target);
            }
        });
    };

    const revealObserver = new IntersectionObserver(revealCallback, {
        root: null,
        threshold: 0.15,
        rootMargin: '0px 0px -50px 0px'
    });

    const revealElements = document.querySelectorAll('.reveal-base, .pipeline-title');
    if (!prefersReducedMotion) {
        revealElements.forEach(el => revealObserver.observe(el));
    } else {
        revealElements.forEach(el => el.classList.add('is-visible'));
    }

    // --- 3. Count Up Animation ---
    function startCountUp(element) {
        const target = parseInt(element.getAttribute('data-target'), 10);
        if (isNaN(target)) return;

        let startTimestamp = null;
        const duration = 2000;

        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            
            // Ease out quart
            const easeProgress = 1 - Math.pow(1 - progress, 4);
            
            element.textContent = Math.floor(easeProgress * target).toLocaleString('id-ID');

            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
                element.textContent = target.toLocaleString('id-ID') + (element.getAttribute('data-suffix') || '');
            }
        };

        window.requestAnimationFrame(step);
    }

    // --- 4. Mobile Menu Toggle ---
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
            mobileMenu.classList.toggle('flex'); // Assuming flex layout for menu
            
            const icon = mobileMenuBtn.querySelector('i');
            if (icon) {
                if (mobileMenu.classList.contains('hidden')) {
                    icon.classList.remove('fa-xmark');
                    icon.classList.add('fa-bars');
                } else {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-xmark');
                }
            }
        });
    }

    // --- 5. Stepper Scroll Sync (Simple Version) ---
    // This logic updates the active state of stepper items based on scroll
    const stepperItems = document.querySelectorAll('.stepper-item');
    if (stepperItems.length && !prefersReducedMotion) {
        const stepperObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, { threshold: 0.5 });
        
        stepperItems.forEach(item => stepperObserver.observe(item));
    }
});
