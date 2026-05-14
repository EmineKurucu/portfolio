/* ═══════════════════════════════════════════════════════════
   Emine Kurucu — Portfolio | main.js
   • Dark/Light mode toggle
   • Hamburger menu
   • Active nav highlight on scroll
   • Contact form validation + AJAX submit
   • Projects AJAX load from php/projects.php
   ═══════════════════════════════════════════════════════════ */

// EmailJS kimlik bilgileri — emailjs.com'dan alınır
const EMAILJS_PUBLIC_KEY  = 'TLMyyUs8txO161oLE';
const EMAILJS_SERVICE_ID  = 'service_4ja2ifv';
const EMAILJS_TEMPLATE_ID = 'template_oqfjw9h';

document.addEventListener('DOMContentLoaded', () => {
  if (typeof emailjs !== 'undefined') {
    emailjs.init({ publicKey: EMAILJS_PUBLIC_KEY });
  }

  // ── 1. DARK / LIGHT MODE TOGGLE ───────────────────────────
  const body        = document.body;
  const themeBtn    = document.getElementById('theme-toggle');
  const themeIcon   = document.getElementById('theme-icon');

  // Persist preference
  const savedTheme = localStorage.getItem('theme') || 'dark';
  applyTheme(savedTheme);

  themeBtn.addEventListener('click', () => {
    const isDark = body.classList.contains('dark-mode');
    applyTheme(isDark ? 'light' : 'dark');
  });

  function applyTheme(theme) {
    if (theme === 'light') {
      body.classList.replace('dark-mode', 'light-mode');
      themeIcon.textContent = '🌙';
      localStorage.setItem('theme', 'light');
    } else {
      body.classList.replace('light-mode', 'dark-mode');
      if (!body.classList.contains('dark-mode')) body.classList.add('dark-mode');
      themeIcon.textContent = '☀️';
      localStorage.setItem('theme', 'dark');
    }
  }

  // ── 2. HAMBURGER MENU ─────────────────────────────────────
  const hamburger  = document.getElementById('hamburger');
  const mobileMenu = document.getElementById('mobile-menu');

  hamburger.addEventListener('click', () => {
    mobileMenu.classList.toggle('open');
  });

  // Close mobile menu when a link is clicked
  mobileMenu.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => mobileMenu.classList.remove('open'));
  });

  // ── 3. ACTIVE NAV LINK ON SCROLL ─────────────────────────
  const sections  = document.querySelectorAll('section[id]');
  const navLinks  = document.querySelectorAll('.nav-links a, .mobile-menu a');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        navLinks.forEach(a => {
          a.classList.toggle('active', a.getAttribute('href') === `#${entry.target.id}`);
        });
      }
    });
  }, { threshold: 0.4 });

  sections.forEach(s => observer.observe(s));

  // ── 4. NAVBAR SCROLL SHADOW ───────────────────────────────
  const navbar = document.getElementById('navbar');
  window.addEventListener('scroll', () => {
    navbar.style.boxShadow = window.scrollY > 40
      ? '0 2px 24px rgba(0,0,0,0.4)'
      : 'none';
  });

  // ── 5. CONTACT FORM VALIDATION + AJAX SUBMIT ─────────────
  const form        = document.getElementById('contact-form');
  const formMsg     = document.getElementById('form-message');
  const submitBtn   = document.getElementById('submit-btn');
  const submitText  = document.getElementById('submit-text');
  const submitLoad  = document.getElementById('submit-loading');

  const fields = {
    name:    { el: document.getElementById('name'),    err: document.getElementById('err-name'),    label: 'Full Name' },
    email:   { el: document.getElementById('email'),   err: document.getElementById('err-email'),   label: 'Email' },
    subject: { el: document.getElementById('subject'), err: document.getElementById('err-subject'), label: 'Subject' },
    message: { el: document.getElementById('message'), err: document.getElementById('err-message'), label: 'Message' },
  };

  // Live validation on blur
  Object.values(fields).forEach(({ el, err, label }) => {
    el.addEventListener('blur', () => validateField(el, err, label));
    el.addEventListener('input', () => {
      if (el.classList.contains('invalid')) validateField(el, err, label);
    });
  });

  function validateField(el, errEl, label) {
    let msg = '';
    const val = el.value.trim();

    if (!val) {
      msg = `${label} is required.`;
    } else if (el.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
      msg = 'Please enter a valid email address.';
    } else if (el.tagName === 'TEXTAREA' && val.length < 10) {
      msg = 'Message must be at least 10 characters.';
    } else if (el.id === 'name' && val.length < 2) {
      msg = 'Name must be at least 2 characters.';
    }

    errEl.textContent = msg;
    el.classList.toggle('invalid', !!msg);
    return !msg;
  }

  function validateAll() {
    let valid = true;
    Object.entries(fields).forEach(([, { el, err, label }]) => {
      if (!validateField(el, err, label)) valid = false;
    });
    return valid;
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    formMsg.className = 'form-message';
    formMsg.style.display = '';

    if (!validateAll()) return;

    // Loading state
    submitText.style.display = 'none';
    submitLoad.style.display = 'inline';
    submitBtn.disabled = true;

    const data = {
      name:    fields.name.el.value.trim(),
      email:   fields.email.el.value.trim(),
      subject: fields.subject.el.value.trim(),
      message: fields.message.el.value.trim(),
    };

    try {
      // DB'ye kaydet
      const res  = await fetch('php/contact.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(data),
      });
      const json = await res.json();
      if (!json.success) throw new Error(json.message || 'DB error');

      // Email gönder (EmailJS)
      if (typeof emailjs !== 'undefined') {
        await emailjs.send(EMAILJS_SERVICE_ID, EMAILJS_TEMPLATE_ID, {
          name:    data.name,
          email:   data.email,
          subject: data.subject,
          message: data.message,
        });
      }

      formMsg.textContent = '✅ Your message was sent successfully! I will get back to you as soon as possible.';
      formMsg.className = 'form-message success';
      form.reset();
      Object.values(fields).forEach(({ el }) => el.classList.remove('invalid'));
    } catch (err) {
      formMsg.textContent = '❌ Could not connect to the server. Please try again later.';
      formMsg.className = 'form-message error';
    } finally {
      submitText.style.display = 'inline';
      submitLoad.style.display = 'none';
      submitBtn.disabled = false;
    }
  });

  // ── 6. LOAD PROJECTS VIA AJAX ─────────────────────────────
  loadProjects();

  async function loadProjects() {
    const grid      = document.getElementById('projects-grid');
    const loadState = document.getElementById('projects-load-state');

    try {
      const res  = await fetch('php/projects.php');
      const json = await res.json();

      if (!json.success || !json.data || json.data.length === 0) return;

      // Clear static cards and render from DB
      grid.innerHTML = '';
      json.data.forEach((project, i) => {
        const card = document.createElement('div');
        card.className = 'project-card';
        card.innerHTML = `
          <div class="project-header">
            <span class="project-num">0${i + 1}</span>
            <div class="project-links">
              ${project.github_url  ? `<a href="${project.github_url}"  target="_blank" rel="noopener">GitHub ↗</a>` : ''}
              ${project.demo_url    ? `<a href="${project.demo_url}"    target="_blank" rel="noopener">Demo ↗</a>` : ''}
            </div>
          </div>
          <h3>${escapeHtml(project.title)}</h3>
          <p>${escapeHtml(project.description)}</p>
          <div class="project-tags">
            ${project.tags ? project.tags.split(',').map(t => `<span>${escapeHtml(t.trim())}</span>`).join('') : ''}
          </div>
        `;
        grid.appendChild(card);
      });

    } catch (err) {
      // Silently keep static cards if server not available
      loadState.textContent = '// Showing static data';
    }
  }

  // Helper: prevent XSS
  function escapeHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
  }

  // ── 7. SCROLL REVEAL ANIMATION ────────────────────────────
  const revealEls = document.querySelectorAll(
    '.timeline-card, .project-card, .skill-group, .about-stats .stat'
  );

  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, { threshold: 0.1 });

  revealEls.forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(24px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    revealObserver.observe(el);
  });

});