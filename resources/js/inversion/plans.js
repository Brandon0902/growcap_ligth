const investmentForm = document.querySelector('[data-investment-form]');
const investmentWizard = document.querySelector('[data-investment-wizard]');
const plansSelect = document.querySelector('[data-investment-plan-select]');
const periodInput = document.querySelector('[data-investment-plan-period]');
const yieldInput = document.querySelector('[data-investment-plan-yield]');
const submitButton = investmentForm?.querySelector('button[type="submit"]');

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

const setHiddenToken = (token, tokenType = 'Bearer') => {
  if (!investmentForm) return;
  const tokenInput = investmentForm.querySelector('input[name="auth_token"]');
  const tokenTypeInput = investmentForm.querySelector('input[name="auth_token_type"]');

  if (tokenInput) tokenInput.value = token || '';
  if (tokenTypeInput) tokenTypeInput.value = tokenType || 'Bearer';
};

const renderPlans = (plans) => {
  if (!plansSelect) return;

  const previousValue = plansSelect.value;
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
    option.dataset.periodo = plan.periodo ?? plan.tiempo ?? plan.plazo ?? '';
    option.dataset.rendimiento = plan.rendimiento ?? plan.tasa ?? '';
    option.textContent = `${plan.label ?? 'Plan sin nombre'}`;
    plansSelect.appendChild(option);
  });

  if (previousValue) {
    plansSelect.value = previousValue;
  }
};

const formatRendimiento = (value) => {
  if (value === null || value === undefined || value === '') return '';
  const numeric = Number(value);
  if (Number.isNaN(numeric)) return String(value);
  return `${numeric.toFixed(2).replace(/\.?0+$/, '')}%`;
};

const updatePlanFields = () => {
  if (!plansSelect) return;
  const selected = plansSelect.options[plansSelect.selectedIndex];
  const periodo = selected?.dataset?.periodo ?? '';
  const rendimiento = selected?.dataset?.rendimiento ?? '';

  if (periodInput) periodInput.value = periodo !== '' ? String(periodo) : '';
  if (yieldInput) yieldInput.value = rendimiento !== '' ? formatRendimiento(rendimiento) : '';
};

const extractInvestmentId = (payload) => {
  if (!payload || typeof payload !== 'object') return null;

  const directId = payload.id ?? payload.inversion_id ?? payload.inversionId ?? null;
  if (directId) return directId;

  const data = payload.data ?? payload.inversion ?? null;
  if (data && typeof data === 'object') {
    return (
      data.id ??
      data.inversion_id ??
      data.inversionId ??
      data.id_inversion ??
      data.folio ??
      null
    );
  }

  return null;
};

const showStep = (step) => {
  if (!investmentWizard) return;
  const boundedStep = Math.max(1, Math.min(TOTAL_STEPS, step));
  currentStep = boundedStep;

  const panels = investmentWizard.querySelectorAll('[data-step-panel]');
  panels.forEach((panel) => {
    const panelStep = Number(panel.getAttribute('data-step-panel'));
    panel.classList.toggle('hidden', panelStep !== currentStep);
    panel.classList.toggle('grid', panelStep === currentStep);
  });

  const progress = investmentWizard.querySelector('[data-investment-progress]');
  const currentStepLabel = investmentWizard.querySelector('[data-investment-current-step]');
  const currentTitleLabel = investmentWizard.querySelector('[data-investment-current-title]');

  if (progress) {
    progress.style.width = `${(currentStep / TOTAL_STEPS) * 100}%`;
  }

  if (currentStepLabel) {
    currentStepLabel.textContent = String(currentStep);
  }

  if (currentTitleLabel) {
    const titles = {
      1: 'Elige el plan',
      2: 'Monto a invertir',
      3: 'Método de pago',
      4: 'Confirmación',
    };
    currentTitleLabel.textContent = titles[currentStep] || 'Proceso';
  }
};

const validateCurrentStep = () => {
  if (!investmentForm) return false;

  if (currentStep === 1) {
    if (!plansSelect || !plansSelect.value) {
      window.alert('Primero elige un plan para continuar.');
      return false;
    }
  }

  if (currentStep === 2) {
    const amountInput = investmentForm.querySelector('input[name="cantidad"]');
    if (!amountInput || !amountInput.value || Number(amountInput.value) <= 0) {
      window.alert('Escribe una cantidad válida para continuar.');
      return false;
    }
  }

  return true;
};

const bindWizardNavigation = () => {
  if (!investmentWizard) return;

  const nextButtons = investmentWizard.querySelectorAll('[data-step-next]');
  const prevButtons = investmentWizard.querySelectorAll('[data-step-prev]');

  nextButtons.forEach((button) => {
    button.addEventListener('click', () => {
      if (!validateCurrentStep()) return;
      showStep(currentStep + 1);
    });
  });

  prevButtons.forEach((button) => {
    button.addEventListener('click', () => {
      showStep(currentStep - 1);
    });
  });

  const isCompleted = investmentWizard.getAttribute('data-investment-completed') === '1';
  const hasErrors = investmentWizard.getAttribute('data-investment-has-errors') === '1';

  if (isCompleted) {
    showStep(4);
    return;
  }

  if (hasErrors) {
    const hasPlan = !!plansSelect?.value;
    const amountInput = investmentForm?.querySelector('input[name="cantidad"]');
    const hasAmount = !!amountInput?.value;
    showStep(hasPlan && hasAmount ? 3 : hasPlan ? 2 : 1);
    return;
  }

  showStep(1);
};

const startStripeCheckout = async ({ apiBaseUrl, token, tokenType, investmentId }) => {
  const endpointTemplate =
    investmentForm?.getAttribute('data-investment-stripe-endpoint-template') ||
    '/api/inversiones/{id}/stripe/checkout';

  const returnUrl =
    investmentForm?.getAttribute('data-investment-stripe-return-url') ||
    window.location.href;

  const endpoint = endpointTemplate.replace('{id}', investmentId);

  const response = await fetch(buildApiUrl(apiBaseUrl, endpoint), {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      Authorization: `${tokenType} ${token}`,
    },
    body: JSON.stringify({ return_url: returnUrl }),
  });

  const data = await getJson(response);

  if (!response.ok || !data?.url) {
    const message = data?.error || data?.message || 'No se pudo iniciar el pago con Stripe.';
    throw new Error(message);
  }

  window.location.assign(data.url);
};

const loadPlans = async () => {
  if (!investmentForm) return;

  const apiBaseUrl = (investmentForm.getAttribute('data-api-base-url') || '').replace(/\/$/, '');
  const endpoint = investmentForm.getAttribute('data-investment-plans-endpoint') || '/inversiones/planes';

  const token = localStorage.getItem('gc_access_token');
  const tokenType = localStorage.getItem('gc_token_type') || 'Bearer';

  setHiddenToken(token, tokenType);

  if (!apiBaseUrl || !token) return;

  try {
    const response = await fetch(buildApiUrl(apiBaseUrl, endpoint), {
      headers: {
        Accept: 'application/json',
        Authorization: `${tokenType} ${token}`,
      },
    });

    const data = await getJson(response);

    if (response.ok) {
      renderPlans(data?.data || []);
      updatePlanFields();
    }
  } catch (error) {
    // silent
  }
};

const handleInvestmentSubmit = () => {
  if (!investmentForm) return;

  investmentForm.addEventListener('submit', async (event) => {
    const selectedPayment = investmentForm.querySelector('input[name="payment_method"]:checked');

    if (!selectedPayment || selectedPayment.value !== 'stripe') {
      return;
    }

    const apiBaseUrl = (investmentForm.getAttribute('data-api-base-url') || '').replace(/\/$/, '');
    const requestEndpoint =
      investmentForm.getAttribute('data-investment-request-endpoint') || '/api/inversiones';

    const token = localStorage.getItem('gc_access_token');
    const tokenType = localStorage.getItem('gc_token_type') || 'Bearer';

    if (!apiBaseUrl || !token) return;

    event.preventDefault();

    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = 'Procesando...';
    }

    const formData = new FormData(investmentForm);
    const payload = Object.fromEntries(formData.entries());

    try {
      const response = await fetch(buildApiUrl(apiBaseUrl, requestEndpoint), {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          Authorization: `${tokenType} ${token}`,
        },
        body: JSON.stringify(payload),
      });

      const data = await getJson(response);

      if (!response.ok) {
        const message = data?.message || data?.error || 'No se pudo enviar la solicitud.';
        throw new Error(message);
      }

      const investmentId = extractInvestmentId(data);
      if (!investmentId) {
        throw new Error('No se encontró el ID de la inversión para iniciar el pago.');
      }

      await startStripeCheckout({ apiBaseUrl, token, tokenType, investmentId });
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Ocurrió un error al iniciar el pago.';
      window.alert(message);
    } finally {
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = 'Confirmar solicitud';
      }
    }
  });
};

if (investmentForm && plansSelect) {
  loadPlans();
  plansSelect.addEventListener('change', updatePlanFields);
  updatePlanFields();
  bindWizardNavigation();
  handleInvestmentSubmit();
}
