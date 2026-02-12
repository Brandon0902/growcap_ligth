const loanForm = document.querySelector('[data-loan-form]');
const loanWizard = document.querySelector('[data-loan-wizard]');
const plansSelect = document.querySelector('[data-loan-plan-select]');
const periodInput = document.querySelector('[data-loan-plan-period]');
const weeksInput = document.querySelector('[data-loan-plan-weeks]');
const interestInput = document.querySelector('[data-loan-plan-interest]');
const maxInput = document.querySelector('[data-loan-plan-max]');
const amountInput = document.querySelector('[data-loan-amount]');
const avalToggles = document.querySelectorAll('[data-loan-aval-toggle]');
const avalCodeWrapper = document.querySelector('[data-loan-aval-code]');
const avalDocsWrapper = document.querySelector('[data-loan-aval-docs]');

const TOTAL_STEPS = 4;
let currentStep = 1;

const buildApiUrl = (baseUrl, endpoint) => {
  const cleanBase = (baseUrl || '').replace(/\/$/, '');
  let cleanEndpoint = endpoint || '';

  if (cleanBase.endsWith('/api') && cleanEndpoint.startsWith('/api/')) {
    cleanEndpoint = cleanEndpoint.slice(4);
  }

  return `${cleanBase}/${cleanEndpoint.replace(/^\//, '')}`;
};

const getJson = async (response) => {
  try {
    return await response.json();
  } catch (error) {
    return null;
  }
};

const logPlanError = (message, details = null) => {
  if (details) {
    console.error(`[Growcap préstamos] ${message}`, details);
  } else {
    console.error(`[Growcap préstamos] ${message}`);
  }
};

const setHiddenToken = (token, tokenType = 'Bearer') => {
  if (!loanForm) return;
  const tokenInput = loanForm.querySelector('input[name="auth_token"]');
  const tokenTypeInput = loanForm.querySelector('input[name="auth_token_type"]');

  if (tokenInput) tokenInput.value = token || '';
  if (tokenTypeInput) tokenTypeInput.value = tokenType || 'Bearer';
};

const showStep = (step) => {
  if (!loanWizard) return;
  currentStep = Math.max(1, Math.min(TOTAL_STEPS, step));

  loanWizard.querySelectorAll('[data-step-panel]').forEach((panel) => {
    const panelStep = Number(panel.getAttribute('data-step-panel'));
    panel.classList.toggle('hidden', panelStep !== currentStep);
    panel.classList.toggle('grid', panelStep === currentStep);
  });

  const progress = loanWizard.querySelector('[data-loan-progress]');
  const currentStepLabel = loanWizard.querySelector('[data-loan-current-step]');
  const currentTitleLabel = loanWizard.querySelector('[data-loan-current-title]');

  if (progress) progress.style.width = `${(currentStep / TOTAL_STEPS) * 100}%`;
  if (currentStepLabel) currentStepLabel.textContent = String(currentStep);
  if (currentTitleLabel) {
    const titles = { 1: 'Elige el plan', 2: 'Monto solicitado', 3: 'Validación de aval', 4: 'Confirmación' };
    currentTitleLabel.textContent = titles[currentStep] || 'Proceso';
  }
};

const validateCurrentStep = () => {
  if (!loanForm) return false;

  if (currentStep === 1 && (!plansSelect || !plansSelect.value)) {
    window.alert('Primero elige un plan para continuar.');
    return false;
  }

  if (currentStep === 2) {
    if (!amountInput?.value || Number(amountInput.value) <= 0) {
      window.alert('Escribe un monto válido para continuar.');
      return false;
    }
  }

  return true;
};

const bindWizardNavigation = () => {
  if (!loanWizard) return;

  loanWizard.querySelectorAll('[data-step-next]').forEach((button) => {
    button.addEventListener('click', () => {
      if (!validateCurrentStep()) return;
      showStep(currentStep + 1);
    });
  });

  loanWizard.querySelectorAll('[data-step-prev]').forEach((button) => {
    button.addEventListener('click', () => showStep(currentStep - 1));
  });

  const isCompleted = loanWizard.getAttribute('data-loan-completed') === '1';
  const hasErrors = loanWizard.getAttribute('data-loan-has-errors') === '1';

  if (isCompleted) {
    showStep(4);
    return;
  }

  if (hasErrors) {
    const hasPlan = !!plansSelect?.value;
    const hasAmount = !!amountInput?.value;
    showStep(hasPlan && hasAmount ? 3 : hasPlan ? 2 : 1);
    return;
  }

  showStep(1);
};

const formatPercentage = (value) => {
  if (value === null || value === undefined || value === '') return '';
  const numeric = Number(value);
  if (Number.isNaN(numeric)) return String(value);
  return `${numeric.toFixed(2).replace(/\.?0+$/, '')}%`;
};

const formatCurrency = (value) => {
  const numeric = Number(value);
  if (Number.isNaN(numeric)) return '';

  try {
    return new Intl.NumberFormat('es-MX', {
      style: 'currency',
      currency: 'MXN',
    }).format(numeric);
  } catch (error) {
    return `$${numeric.toFixed(2)}`;
  }
};

const renderPlans = (plans) => {
  if (!plansSelect) return;
  const selectedValue = plansSelect.dataset.loanSelected || plansSelect.value || '';
  plansSelect.innerHTML = '<option value="">Selecciona un plan</option>';

  if (!Array.isArray(plans) || plans.length === 0) {
    const option = document.createElement('option');
    option.disabled = true;
    option.textContent = 'No hay planes disponibles';
    plansSelect.appendChild(option);
    return;
  }

  plans.forEach((plan) => {
    const option = document.createElement('option');
    option.value = plan.id ?? '';
    option.dataset.periodo = plan.periodo ?? '';
    option.dataset.semanas = plan.semanas ?? '';
    option.dataset.interes = plan.interes ?? '';
    option.dataset.montoMin = plan.monto_min ?? plan.monto_minimo ?? '';
    option.dataset.montoMax = plan.monto_max ?? plan.monto_maximo ?? '';
    option.textContent = plan.label ?? 'Plan sin nombre';
    if (selectedValue && option.value === selectedValue) option.selected = true;
    plansSelect.appendChild(option);
  });
};

const updatePlanFields = () => {
  if (!plansSelect) return;
  const selected = plansSelect.options[plansSelect.selectedIndex];
  const periodo = selected?.dataset?.periodo ?? '';
  const semanas = selected?.dataset?.semanas ?? '';
  const interes = selected?.dataset?.interes ?? '';
  const montoMin = selected?.dataset?.montoMin ?? '';
  const montoMax = selected?.dataset?.montoMax ?? '';

  if (periodInput) periodInput.value = periodo !== '' ? String(periodo) : '';
  if (weeksInput) weeksInput.value = semanas !== '' ? String(semanas) : '';
  if (interestInput) interestInput.value = interes !== '' ? formatPercentage(interes) : '';
  if (maxInput) maxInput.value = montoMax !== '' ? formatCurrency(montoMax) : '';

  if (amountInput) {
    if (montoMin !== '') amountInput.min = String(montoMin);
    else amountInput.removeAttribute('min');

    if (montoMax !== '') amountInput.max = String(montoMax);
    else amountInput.removeAttribute('max');
  }
};

const updateAvalMode = () => {
  const selected = loanForm?.querySelector('input[name="aval_method"]:checked');
  const method = selected?.value ?? 'codigo';
  const useCode = method === 'codigo';

  if (avalCodeWrapper) avalCodeWrapper.hidden = !useCode;
  if (avalDocsWrapper) avalDocsWrapper.hidden = useCode;

  const codeInput = loanForm?.querySelector('input[name="codigo_aval"]');
  const docInputs = loanForm?.querySelectorAll(
    'input[name="doc_solicitud_aval"], input[name="doc_comprobante_domicilio"], input[name="doc_ine_frente"], input[name="doc_ine_reverso"]'
  );

  if (codeInput) codeInput.required = useCode;

  if (docInputs && docInputs.length > 0) {
    docInputs.forEach((input) => {
      input.required = !useCode;
    });
  }
};

const loadPlans = async () => {
  if (!loanForm) return;

  const apiBaseUrl = (loanForm.getAttribute('data-api-base-url') || '').replace(/\/$/, '');
  const endpoint = loanForm.getAttribute('data-loan-plans-endpoint') || '/prestamos/planes';
  const url = buildApiUrl(apiBaseUrl, endpoint);

  const token = localStorage.getItem('gc_access_token');
  const tokenType = localStorage.getItem('gc_token_type') || 'Bearer';

  setHiddenToken(token, tokenType);

  if (!apiBaseUrl || !token) {
    logPlanError('No se pudo cargar planes: falta base URL o token.');
    return;
  }

  try {
    const response = await fetch(url, {
      headers: {
        Accept: 'application/json',
        Authorization: `${tokenType} ${token}`,
      },
    });

    const data = await getJson(response);

    if (!response.ok) {
      logPlanError('Error al cargar planes de préstamo.', {
        status: response.status,
        response: data,
      });
      return;
    }

    renderPlans(data?.data || []);
    updatePlanFields();
  } catch (error) {
    logPlanError('Error inesperado al cargar planes de préstamo.', error);
  }
};

if (loanForm && plansSelect) {
  loadPlans();
  plansSelect.addEventListener('change', updatePlanFields);
  updatePlanFields();
  updateAvalMode();
  bindWizardNavigation();
}

if (avalToggles.length > 0) {
  avalToggles.forEach((toggle) => {
    toggle.addEventListener('change', updateAvalMode);
  });
}
