/* ===============================
   1. Typewriter Effect
=============================== */
const textElement = document.getElementById('typewriter');
const phrases = ["a Web Developer.", "a Designer.", "a YouTuber.", "a Vlogger."];

let phraseIndex = 0;
let charIndex = 0;
let isDeleting = false;

function type() {
  const currentPhrase = phrases[phraseIndex];
  let speed = isDeleting ? 60 : 120;

  if (isDeleting) {
    charIndex--;
    textElement.textContent = currentPhrase.substring(0, charIndex);
  } else {
    charIndex++;
    textElement.textContent = currentPhrase.substring(0, charIndex);
  }

  if (!isDeleting && charIndex === currentPhrase.length) {
    speed = 2000;
    isDeleting = true;
  } else if (isDeleting && charIndex === 0) {
    isDeleting = false;
    phraseIndex = (phraseIndex + 1) % phrases.length;
    speed = 500;
  }

  setTimeout(type, speed);
}

/* ===============================
   2. Portfolio Filter
=============================== */
const initPortfolioFilter = () => {
  const filterButtons = document.querySelectorAll('.filter-btn');
  const portfolioItems = document.querySelectorAll('.portfolio-item');

  filterButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      filterButtons.forEach(button => {
        button.classList.remove('active', 'btn-primary');
        button.classList.add('btn-outline-primary');
      });
      btn.classList.add('active');
      btn.classList.remove('btn-primary');

      const filter = btn.getAttribute('data-filter');

      portfolioItems.forEach(item => {
        if (filter === 'all' || item.dataset.category === filter) {
          item.style.display = 'block';
          item.style.animation = 'fadeIn 0.5s ease forwards';
        } else {
          item.style.animation = 'fadeOut 0.5s ease forwards';
          setTimeout(() => item.style.display = 'none', 500);
        }
      });
    });
  });
};

/* ===============================
   3. Dark Mode Toggle
=============================== */
const initDarkMode = () => {
  const themeBtn = document.getElementById('theme-toggle');
  const themeIcon = document.getElementById('theme-icon');

  const setTheme = (theme) => {
    document.documentElement.setAttribute('data-bs-theme', theme);
    themeIcon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    localStorage.setItem('theme', theme);
  };

  themeBtn.addEventListener('click', () => {
    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
    setTheme(currentTheme === 'light' ? 'dark' : 'light');
  });

  const savedTheme = localStorage.getItem('theme') || 'light';
  setTheme(savedTheme);
};

/* ===============================
   4. Scroll Reveal
=============================== */
const initScrollReveal = () => {
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('show');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15 });

  document.querySelectorAll('section').forEach(section => {
    section.classList.add('reveal-hidden');
    observer.observe(section);
  });
};

/* ===============================
   5. Smooth Scroll
=============================== */
const initSmoothScroll = () => {
  const navbar = document.querySelector('.navbar');
  const navHeight = navbar ? navbar.offsetHeight : 70;

  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', e => {
      e.preventDefault();
      const target = document.querySelector(anchor.getAttribute('href'));
      if (target) {
        window.scrollTo({
          top: target.offsetTop - navHeight,
          behavior: 'smooth'
        });
      }
    });
  });
};

/* ===============================
   6. Contact Form
=============================== */
const initContactForm = () => {
  const contactForm = document.getElementById('contact-form');
  if (!contactForm) return;

  contactForm.addEventListener('submit', e => {
    e.preventDefault();
    const submitBtn = contactForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

    // Simulated API call
    setTimeout(() => {
      submitBtn.classList.remove('btn-primary');
      submitBtn.classList.add('btn-success');
      submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Message Sent!';

      contactForm.reset();

      setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.classList.remove('btn-success');
        submitBtn.classList.add('btn-primary');
        submitBtn.innerHTML = originalText;
      }, 3000);
    }, 1500);
  });
};

/* ===============================
   7. Navbar Scroll Shadow
=============================== */
const initNavbarScroll = () => {
  const navbar = document.querySelector('.navbar');
  if (!navbar) return;

  window.addEventListener('scroll', () => {
    if (window.scrollY > 50) navbar.classList.add('scrolled');
    else navbar.classList.remove('scrolled');
  });
};

/* ===============================
   8. Active Navbar Link on Scroll
=============================== */
const initActiveNav = () => {
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.nav-link');

  const navbar = document.querySelector('.navbar');
  const navHeight = navbar ? navbar.offsetHeight : 70;

  window.addEventListener('scroll', () => {
    const scrollPos = window.scrollY + navHeight + 5; // Add small offset

    sections.forEach(section => {
      const top = section.offsetTop;
      const bottom = top + section.offsetHeight;

      const id = section.getAttribute('id');

      if (scrollPos >= top && scrollPos <= bottom) {
        navLinks.forEach(link => {
          link.classList.remove('active');
          if (link.getAttribute('href') === `#${id}`) {
            link.classList.add('active');
          }
        });
      }
    });
  });
};

/* ===============================
   Initialize Everything
=============================== */
document.addEventListener('DOMContentLoaded', () => {
  type();
  initPortfolioFilter();
  initDarkMode();
  initScrollReveal();
  initSmoothScroll();
  initContactForm();
  initNavbarScroll();
  initActiveNav();
});
