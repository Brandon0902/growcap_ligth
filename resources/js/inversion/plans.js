const investmentForm = document.querySelector('[data-investment-form]');
const plansSelect = document.querySelector('[data-investment-plan-select]');
const tokenDebug = document.querySelector('[data-investment-token-debug]');

const setHiddenToken = (token, tokenType = 'Bearer') => {
  if (!investmentForm) return;
  const tokenInput = investmentForm.querySelector('input[name="auth_token"]');
  const tokenTypeInput = investmentForm.querySelector('input[name="auth_token_type"]');
  if (tokenInput) {
    tokenInput.value = token || '';
  }
  if (tokenTypeInput) {
    tokenTypeInput.value = tokenType || 'Bearer';
  }
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
    const rendimiento = plan.rendimiento ? ` (${Number(plan.rendimiento).toFixed(2).replace(/\.?0+$/, '')}% anual)` : '';
    option.textContent = `${plan.label ?? 'Plan sin nombre'}${rendimiento}`;
    plansSelect.appendChild(option);
  });
};

const loadPlans = async () => {
  if (!investmentForm) return;
  const apiBaseUrl = (investmentForm.getAttribute('data-api-base-url') || '').replace(/\/$/, '');
  const endpoint = investmentForm.getAttribute('data-investment-plans-endpoint') || '/inversiones/planes';
  const token = localStorage.getItem('gc_access_token');
  const tokenType = localStorage.getItem('gc_token_type') || 'Bearer';

  setHiddenToken(token, tokenType);
  updateTokenDebug(token, tokenType, apiBaseUrl);
  if (token) {
    console.info('[Inversiones] token encontrado', {
      tokenPreview: `${token.slice(0, 6)}...${token.slice(-4)}`,
      tokenType,
      apiBaseUrl,
    });
  } else {
    console.warn('[Inversiones] token no encontrado en localStorage (gc_access_token).');
  }

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
    }
  } catch (error) {
    // Silent failure: server-side renders error message if needed.
  }
};

if (investmentForm && plansSelect) {
  loadPlans();
}
