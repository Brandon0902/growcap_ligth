import './bootstrap';

const hasElement = (selector) => document.querySelector(selector);
const loadIfPresent = (selector, loader) => {
  if (hasElement(selector)) {
    loader();
  }
};

loadIfPresent('[data-login-form]', () => import('./auth/login'));

loadIfPresent('[data-investment-plan-select], [data-investment-form]', () =>
  import('./inversion/plans')
);

loadIfPresent('[data-savings-plan-select], [data-savings-form]', () =>
  import('./ahorro/plans')
);

loadIfPresent(
  '[data-loan-plan-select], [data-loan-form], [data-loan-aval-toggle]',
  () => import('./prestamos/plans')
);
