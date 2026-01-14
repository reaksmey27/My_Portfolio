/* ===============================
   1. Typewriter Effect
=============================== */
const initTypewriter = () => {
  const textElement = document.getElementById('typewriter');
  if (!textElement) return;

  const phrases = ["a Web Developer.", "a Designer.", "a YouTuber.", "a Vlogger."];
  let phraseIndex = 0;
  let charIndex = 0;
  let isDeleting = false;

  function type() {
    const currentPhrase = phrases[phraseIndex];
    // Added a tiny bit of randomness to make it feel more "human"
    let speed = isDeleting ? 50 : 100 + Math.random() * 50;

    textElement.textContent = currentPhrase.substring(0, charIndex);

    if (isDeleting) {
      charIndex--;
    } else {
      charIndex++;
    }

    if (!isDeleting && charIndex === currentPhrase.length + 1) {
      speed = 2000; // Pause at end of phrase
      isDeleting = true;
    } else if (isDeleting && charIndex === 0) {
      isDeleting = false;
      phraseIndex = (phraseIndex + 1) % phrases.length;
      speed = 500;
    }

    setTimeout(type, speed);
  }
  type();
};

/* ===============================
   2. Integrated Portfolio (Filter + Pagination)
=============================== */
const initPortfolio = () => {
  const filterButtons = document.querySelectorAll('.filter-btn');
  const portfolioItems = document.querySelectorAll('.portfolio-item');
  const seeMoreBtn = document.getElementById('see-more-btn');
  const initialCount = 3;
  let currentFilter = 'all';
  let showingAll = false;

  const updateGallery = () => {
    let visibleCount = 0;

    portfolioItems.forEach((item) => {
      const isMatch = currentFilter === 'all' || item.dataset.category === currentFilter;

      if (isMatch) {
        // If we aren't "showing all", only show the first 3 matches
        if (showingAll || visibleCount < initialCount) {
          item.style.display = 'block';
          item.style.animation = 'fadeIn 0.5s ease forwards';
        } else {
          item.style.display = 'none';
        }
        visibleCount++;
      } else {
        item.style.display = 'none';
      }
    });

    // Hide "See More" button if we are filtering or there are no more items to show
    if (seeMoreBtn) {
      seeMoreBtn.style.display = (currentFilter === 'all' && visibleCount > initialCount) ? 'inline-block' : 'none';
    }
  };

  filterButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      filterButtons.forEach(b => b.classList.remove('active', 'btn-primary'));
      filterButtons.forEach(b => b.classList.add('btn-outline-primary'));

      btn.classList.add('active', 'btn-primary');
      btn.classList.remove('btn-outline-primary');

      currentFilter = btn.getAttribute('data-filter');
      showingAll = false; // Reset toggle on filter change
      if (seeMoreBtn) seeMoreBtn.innerHTML = 'See More Projects <i class="fas fa-arrow-down ms-2"></i>';
      updateGallery();
    });
  });

  if (seeMoreBtn) {
    seeMoreBtn.addEventListener('click', () => {
      showingAll = !showingAll;
      seeMoreBtn.innerHTML = showingAll ?
        'See Less <i class="fas fa-arrow-up ms-2"></i>' :
        'See More Projects <i class="fas fa-arrow-down ms-2"></i>';
      updateGallery();
    });
  }

  updateGallery();
};

/* ===============================
   3. Theme & Scroll Effects
=============================== */
const initDarkMode = () => {
  const themeBtn = document.getElementById('theme-toggle');
  const themeIcon = document.getElementById('theme-icon');
  if (!themeBtn) return;

  const setTheme = (theme) => {
    document.documentElement.setAttribute('data-bs-theme', theme);
    if (themeIcon) themeIcon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    localStorage.setItem('theme', theme);
  };

  themeBtn.addEventListener('click', () => {
    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
    setTheme(currentTheme === 'light' ? 'dark' : 'light');
  });

  setTheme(localStorage.getItem('theme') || 'light');
};

const initScrollEffects = () => {
  // Intersection Observer for Section Reveal
  const revealObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('show');
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15 });

  document.querySelectorAll('section').forEach(section => {
    section.classList.add('reveal-hidden');
    revealObserver.observe(section);
  });

  // Active Nav Link & Navbar Shadow
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.nav-link');
  const navbar = document.querySelector('.navbar');

  window.addEventListener('scroll', () => {
    // Navbar Shadow
    if (window.scrollY > 50) navbar.classList.add('scrolled');
    else navbar.classList.remove('scrolled');

    // Active Link
    let current = "";
    sections.forEach(section => {
      const sectionTop = section.offsetTop;
      if (window.scrollY >= sectionTop - 100) {
        current = section.getAttribute('id');
      }
    });

    navLinks.forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('href').includes(current)) {
        link.classList.add('active');
      }
    });
  }, { passive: true });
};

/* ===============================
   4. Contact & Top Button
=============================== */
const initUIComponents = () => {
  // Back to Top
  const backBtn = document.getElementById('back-to-top');
  if (backBtn) {
    window.addEventListener('scroll', () => {
      backBtn.style.display = window.scrollY > 300 ? 'flex' : 'none';
    }, { passive: true });

    backBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // FIX: Form Submission for PHP
  // We removed e.preventDefault() so the browser actually sends the POST data
  const contactForm = document.getElementById('contact-form');
  if (contactForm) {
    contactForm.addEventListener('submit', function () {
      const btn = this.querySelector('button[type="submit"]');
      // Show loading state while the page prepares to refresh/redirect
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
      btn.classList.add('disabled');
    });
  }
};

/* ===============================
   Initialize
=============================== */
document.addEventListener('DOMContentLoaded', () => {
  initTypewriter();
  initPortfolio();
  initDarkMode();
  initScrollEffects();
  initUIComponents();
});