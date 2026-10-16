/**
 * UIU Career Portal - Core JavaScript (Vanilla JS)
 * No React, No npm runtime dependencies.
 */

// Global non-blocking Toast notification helper
window.showToast = function(message, type = 'info') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = 'toast';
  const icon = type === 'success' ? '✓' : type === 'error' ? '⚠️' : 'ℹ️';
  toast.innerHTML = `<span>${icon}</span><span>${message}</span>`;
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(10px)';
    setTimeout(() => toast.remove(), 300);
  }, 3500);
};

document.addEventListener('DOMContentLoaded', function() {
  console.log('UIU Career Portal Initialized (Pure PHP + MySQL + Vanilla JS)');

  // 1. Smooth Section Anchor Scrolling (#jobs, #companies, #about, #contact, #home)
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const targetId = this.getAttribute('href').substring(1);
      if (!targetId) return;
      const targetEl = document.getElementById(targetId);
      if (targetEl) {
        e.preventDefault();
        targetEl.scrollIntoView({ behavior: 'smooth' });
        history.pushState(null, '', '#' + targetId);
      }
    });
  });

  // 2. Hash check on initial page load
  if (window.location.hash) {
    const hashId = window.location.hash.substring(1);
    const targetEl = document.getElementById(hashId);
    if (targetEl) {
      setTimeout(() => targetEl.scrollIntoView({ behavior: 'smooth' }), 150);
    }
  }

  // 3. Modals toggle
  window.openModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'flex';
  };

  window.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'none';
  };

  // Close modal when clicking on overlay background
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
      if (e.target === this) {
        this.style.display = 'none';
      }
    });
  });

  // 4. Offer Letter acceptance / decline confirmation
  const offerForm = document.querySelector('form[action*="offer_letter"], form[action*="offer-letter"]');
  if (offerForm) {
    const acceptBtn = offerForm.querySelector('button[value="accept"]');
    if (acceptBtn) {
      acceptBtn.addEventListener('click', function(e) {
        window.showToast('You have officially accepted this employment offer!', 'success');
      });
    }
  }
});
