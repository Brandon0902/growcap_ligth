import './bootstrap';

const isDebugEnabled = () => localStorage.getItem('gc_debug') === '1';
const logDebug = (...args) => {
  if (isDebugEnabled()) {
    console.info('[Growcap]', ...args);
  }
};

const hasElement = (selector) => document.querySelector(selector);
const loadIfPresent = (selector, loader, label) => {
  if (hasElement(selector)) {
    logDebug(`Cargando módulo: ${label}`);
    loader().catch((error) => {
      console.error(`[Growcap] Error cargando módulo: ${label}`, error);
    });
  } else {
    logDebug(`Selector no encontrado para módulo: ${label}`, selector);
  }
};

const bootPageModules = () => {
  loadIfPresent(
    '[data-investment-plan-select], [data-investment-form]',
    () => import('./inversion/plans'),
    'inversion/plans'
  );

  loadIfPresent(
    '[data-savings-plan-select], [data-savings-form]',
    () => import('./ahorro/plans'),
    'ahorro/plans'
  );

  loadIfPresent(
    '[data-loan-plan-select], [data-loan-form], [data-loan-aval-toggle]',
    () => import('./prestamos/plans'),
    'prestamos/plans'
  );

  loadIfPresent(
    '[data-requests-feed]',
    () => import('./solicitudes/feed'),
    'solicitudes/feed'
  );
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', bootPageModules);
} else {
  bootPageModules();
}
