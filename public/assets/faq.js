(function () {
  'use strict';

  function prefersReducedMotion() {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  function setAnswerState(questionEl, isOpen) {
    questionEl.classList.toggle('is-open', isOpen);
    questionEl.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  }

  function getAnswerEl(questionEl) {
    return questionEl.querySelector('.faq--answer');
  }

  function animateOpen(answerEl) {
    const reduce = prefersReducedMotion();

    answerEl.style.display = 'block';
    answerEl.style.overflow = 'hidden';

    if (reduce) {
      answerEl.style.height = 'auto';
      answerEl.style.opacity = '1';
      answerEl.style.transform = 'translateY(0)';
      answerEl.style.willChange = '';
      return;
    }

    answerEl.style.willChange = 'height, opacity, transform';
    answerEl.style.height = '0px';
    answerEl.style.opacity = '0';
    answerEl.style.transform = 'translateY(-6px)';

    // Force layout
    // eslint-disable-next-line no-unused-expressions
    answerEl.offsetHeight;

    const target = answerEl.scrollHeight;
    answerEl.style.transition = 'height 520ms cubic-bezier(0.22, 1, 0.36, 1), opacity 360ms ease, transform 520ms cubic-bezier(0.22, 1, 0.36, 1)';
    answerEl.style.height = target + 'px';
    answerEl.style.opacity = '1';
    answerEl.style.transform = 'translateY(0)';

    const onEnd = (e) => {
      if (e.propertyName !== 'height') return;
      answerEl.removeEventListener('transitionend', onEnd);
      answerEl.style.transition = '';
      answerEl.style.height = 'auto';
      answerEl.style.overflow = '';
      answerEl.style.willChange = '';
    };

    answerEl.addEventListener('transitionend', onEnd);
  }

  function animateClose(answerEl) {
    const reduce = prefersReducedMotion();

    answerEl.style.overflow = 'hidden';

    if (reduce) {
      answerEl.style.height = '0px';
      answerEl.style.opacity = '0';
      answerEl.style.transform = 'translateY(-6px)';
      answerEl.style.display = 'none';
      answerEl.style.overflow = '';
      answerEl.style.willChange = '';
      return;
    }

    answerEl.style.willChange = 'height, opacity, transform';

    // Set current pixel height before collapsing
    const current = answerEl.scrollHeight;
    answerEl.style.height = current + 'px';
    answerEl.style.opacity = '1';
    answerEl.style.transform = 'translateY(0)';

    // Force layout
    // eslint-disable-next-line no-unused-expressions
    answerEl.offsetHeight;

    answerEl.style.transition = 'height 420ms cubic-bezier(0.4, 0, 0.2, 1), opacity 260ms ease, transform 420ms cubic-bezier(0.4, 0, 0.2, 1)';
    answerEl.style.height = '0px';
    answerEl.style.opacity = '0';
    answerEl.style.transform = 'translateY(-6px)';

    const onEnd = (e) => {
      if (e.propertyName !== 'height') return;
      answerEl.removeEventListener('transitionend', onEnd);
      answerEl.style.transition = '';
      answerEl.style.display = 'none';
      answerEl.style.overflow = '';
      answerEl.style.willChange = '';
    };

    answerEl.addEventListener('transitionend', onEnd);
  }

  function closeQuestion(questionEl) {
    const answerEl = getAnswerEl(questionEl);
    if (!answerEl) return;

    setAnswerState(questionEl, false);
    animateClose(answerEl);
  }

  function openQuestion(questionEl) {
    const answerEl = getAnswerEl(questionEl);
    if (!answerEl) return;

    setAnswerState(questionEl, true);
    animateOpen(answerEl);
  }

  function initFaq(root) {
    const questions = Array.from(root.querySelectorAll('.faq--question'));
    if (!questions.length) return;

    questions.forEach((q, idx) => {
      q.setAttribute('role', 'button');
      q.setAttribute('tabindex', '0');
      q.setAttribute('aria-expanded', 'false');

      const answer = getAnswerEl(q);
      if (answer) {
        answer.style.display = 'none';
        answer.style.height = '0px';
        answer.style.opacity = '0';
        answer.style.transform = 'translateY(-6px)';
      }

      const toggle = () => {
        const isOpen = q.classList.contains('is-open');

        // Close others (accordion)
        questions.forEach((other) => {
          if (other !== q && other.classList.contains('is-open')) {
            closeQuestion(other);
          }
        });

        if (isOpen) {
          closeQuestion(q);
        } else {
          openQuestion(q);
        }
      };

      q.addEventListener('click', (e) => {
        // Avoid toggling when selecting text
        if (window.getSelection && String(window.getSelection()).length) return;
        toggle();
      });

      q.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          toggle();
        }
        // Optional: arrow navigation
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          const next = questions[idx + 1] || questions[0];
          next.focus();
        }
        if (e.key === 'ArrowUp') {
          e.preventDefault();
          const prev = questions[idx - 1] || questions[questions.length - 1];
          prev.focus();
        }
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    const faqRoot = document.querySelector('.second-section--faq-wrapper');
    if (!faqRoot) return;
    initFaq(faqRoot);
  });
})();
