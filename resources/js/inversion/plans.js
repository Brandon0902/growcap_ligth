const investmentForm = document.querySelector('[data-investment-form]');
const plansSelect = document.querySelector('[data-investment-plan-select]');
const tokenDebug = document.querySelector('[data-investment-token-debug]');
const periodInput = document.querySelector('[data-investment-plan-period]');
const yieldInput = document.querySelector('[data-investment-plan-yield]');

const setHiddenToken = (token, tokenType = 'Bearer') => {
  if (!investmentForm) return;
  const tokenInput = investmentForm.querySelector('input[name="auth_token"]');
  const tokenTypeInput = investmentForm.querySelector('input[name="auth_token_type"]');

  if (tokenInput) tokenInput.value = token || '';
  if (tokenTypeInput) tokenTypeInput.value = tokenType || 'Bearer';
};

const updateTokenDebug = (token, tokenType, apiBaseUrl) => {
  if (!tokenDebug) return;

  if (!token) {
    tokenDebug.textContent = 'Token: no encontrado en localStorage (gc_access_token).';
    return;
  }

  const preview = token.length > 10 ? `${token.slice(0, 6)}...${token.slice(-4)}` : token;
  tokenDebug.textContent = `Token: ${preview} | tipo: ${tokenType} | base: ${apiBaseUrl || 'sin BACKEND_API_URL'}`;
};

const renderPlans = (plans) => {
  if (!plansSelect) return;
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

  if (periodInput) {
    periodInput.value = periodo !== '' ? String(periodo) : '';
  }

  if (yieldInput) {
    yieldInput.value = rendimiento !== '' ? formatRendimiento(rendimiento) : '';
  }
};

const loadPlans = async () => {
  if (!investmentForm) return;

  const apiBaseUrl = (investmentForm.getAttribute('data-api-base-url') || '').replace(/\/$/, '');
  const endpoint = investmentForm.getAttribute('data-investment-plans-endpoint') || '/inversiones/planes';

  const token = localStorage.getItem('gc_access_token');
  const tokenType = localStorage.getItem('gc_token_type') || 'Bearer';

  setHiddenToken(token, tokenType);
  updateTokenDebug(token, tokenType, apiBaseUrl);

  if (!apiBaseUrl || !token) return;

  try {
    const response = await fetch(`${apiBaseUrl}${endpoint}`, {
      headers: {
        Accept: 'application/json',
        Authorization: `${tokenType} ${token}`,
      },
    });

    const data = await response.json();

    if (response.ok) {
      renderPlans(data?.data || []);
      updatePlanFields();
    }
  } catch (error) {
    // Silent failure: server-side renders error message if needed.
  }
};

if (investmentForm && plansSelect) {
  loadPlans();
  plansSelect.addEventListener('change', updatePlanFields);
  updatePlanFields();
}
