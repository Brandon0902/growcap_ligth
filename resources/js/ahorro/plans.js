const savingsForm = document.querySelector('[data-savings-form]');
const plansSelect = document.querySelector('[data-savings-plan-select]');
const yieldInput = document.querySelector('[data-savings-plan-yield]');
const minMonthsInput = document.querySelector('[data-savings-plan-min-months]');
const frequencySelect = document.querySelector('[data-savings-frequency]');
const cuotaInput = document.querySelector('[data-savings-cuota]');
const minimumLabel = document.querySelector('[data-savings-minimum]');
const fechaFinWrapper = document.querySelector('[data-savings-fecha-fin-wrapper]');
const fechaFinInput = document.querySelector('[data-savings-fecha-fin]');
const submitButton = savingsForm?.querySelector('button[type="submit"]');

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
  if (!savingsForm) return;
  const tokenInput = savingsForm.querySelector('input[name="auth_token"]');
  const tokenTypeInput = savingsForm.querySelector('input[name="auth_token_type"]');

  if (tokenInput) tokenInput.value = token || '';
  if (tokenTypeInput) tokenTypeInput.value = tokenType || 'Bearer';
};

const formatRendimiento = (value) => {
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

const minimumByFrequency = (monthlyMin, frequency) => {
  const minValue = Number(monthlyMin) || 0;
  if (frequency === 'Semanal') return Math.round((minValue / 4) * 100) / 100;
  if (frequency === 'Quincenal') return Math.round((minValue / 2) * 100) / 100;
  return Math.round(minValue * 100) / 100;
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
    option.dataset.rendimiento = plan.rendimiento ?? '';
    option.dataset.minMensual = plan.monto_min ?? plan.monto_minimo ?? '';
    option.dataset.minMonths = plan.meses_minimos ?? '';
    option.dataset.temporada = plan.is_temporada ? '1' : '0';
    option.textContent = `${plan.label ?? plan.nombre ?? 'Plan sin nombre'}`;
    plansSelect.appendChild(option);
  });
};

const updateMinimum = () => {
  if (!plansSelect || !minimumLabel || !frequencySelect) return;
  const selected = plansSelect.options[plansSelect.selectedIndex];
  const minMensual = selected?.dataset?.minMensual ?? '';
  const frequency = frequencySelect.value || 'Mensual';

  if (!minMensual) {
    minimumLabel.textContent = 'Selecciona un plan para conocer la cuota mínima.';
    if (cuotaInput) cuotaInput.removeAttribute('min');
    return;
  }

  const minValue = minimumByFrequency(minMensual, frequency);
  minimumLabel.textContent = `Cuota mínima para ${frequency}: ${formatCurrency(minValue)}.`;

  if (cuotaInput && !Number.isNaN(minValue)) {
    cuotaInput.min = String(minValue);
  }
};

const updatePlanFields = () => {
  if (!plansSelect) return;
  const selected = plansSelect.options[plansSelect.selectedIndex];
  const rendimiento = selected?.dataset?.rendimiento ?? '';
  const minMonths = selected?.dataset?.minMonths ?? '';
  const isTemporada = selected?.dataset?.temporada === '1';

  if (yieldInput) {
    yieldInput.value = rendimiento !== '' ? formatRendimiento(rendimiento) : '';
  }

  if (minMonthsInput) {
    minMonthsInput.value = minMonths !== '' ? String(minMonths) : '';
  }

  if (fechaFinWrapper) {
    if (isTemporada) {
      fechaFinWrapper.hidden = false;
      if (fechaFinInput) {
        fechaFinInput.required = true;
      }
    } else {
      fechaFinWrapper.hidden = true;
      if (fechaFinInput) {
        fechaFinInput.required = false;
        fechaFinInput.value = '';
      }
    }
  }

  updateMinimum();
};

const extractAhorroPayload = (payload) => {
  if (!payload || typeof payload !== 'object') return {};
  return payload.ahorro || payload.data?.ahorro || payload.data || {};
};

const startStripeCheckout = async ({ apiBaseUrl, token, tokenType, ahorroId, body, returnUrl }) => {
  const endpointTemplate =
    savingsForm?.getAttribute('data-savings-stripe-endpoint-template') ||
    '/api/ahorros/{id}/stripe/checkout';

  const endpoint = endpointTemplate.replace('{id}', ahorroId);
  const payload = { ...body };

  if (returnUrl) {
    payload.return_url = returnUrl;
  }

  const response = await fetch(buildApiUrl(apiBaseUrl, endpoint), {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      Authorization: `${tokenType} ${token}`,
    },
    body: JSON.stringify(payload),
  });

  const data = await getJson(response);

  if (!response.ok || (!data?.checkout_url && !data?.url)) {
    const message = data?.error || data?.message || 'No se pudo iniciar el pago con Stripe.';
    throw new Error(message);
  }

  window.location.assign(data.checkout_url || data.url);
};

const loadPlans = async () => {
  if (!savingsForm) return;

  const apiBaseUrl = (savingsForm.getAttribute('data-api-base-url') || '').replace(/\/$/, '');
  const endpoint = savingsForm.getAttribute('data-savings-plans-endpoint') || '/ahorros/planes';

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

const loadFrequency = async () => {
  if (!savingsForm || !frequencySelect) return;

  const apiBaseUrl = (savingsForm.getAttribute('data-api-base-url') || '').replace(/\/$/, '');
  const endpoint = savingsForm.getAttribute('data-savings-frequency-endpoint') || '/ahorros/frecuencia';

  const token = localStorage.getItem('gc_access_token');
  const tokenType = localStorage.getItem('gc_token_type') || 'Bearer';

  if (!apiBaseUrl || !token) return;

  try {
    const response = await fetch(buildApiUrl(apiBaseUrl, endpoint), {
      headers: {
        Accept: 'application/json',
        Authorization: `${tokenType} ${token}`,
      },
    });

    const data = await getJson(response);

    if (response.ok && data?.frecuencia && !frequencySelect.value) {
      frequencySelect.value = data.frecuencia;
    }
  } catch (error) {
    // silent
  }
};

const handleSavingsSubmit = () => {
  if (!savingsForm) return;

  savingsForm.addEventListener('submit', async (event) => {
    const selectedPayment = savingsForm.querySelector('input[name="payment_method"]:checked');

    if (!selectedPayment || selectedPayment.value !== 'stripe') {
      return;
    }

    const apiBaseUrl = (savingsForm.getAttribute('data-api-base-url') || '').replace(/\/$/, '');
    const requestEndpoint = savingsForm.getAttribute('data-savings-request-endpoint') || '/api/ahorros';
    const configuredReturnUrl = savingsForm.getAttribute('data-savings-stripe-return-url');
    const returnUrl = configuredReturnUrl || `${window.location.origin}/ahorro`;

    const token = localStorage.getItem('gc_access_token');
    const tokenType = localStorage.getItem('gc_token_type') || 'Bearer';

    if (!apiBaseUrl || !token) return;

    event.preventDefault();

    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = 'Procesando...';
    }

    const formData = new FormData(savingsForm);
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

      const action = data?.action || 'create';
      const ahorroPayload = extractAhorroPayload(data);
      const ahorroId = ahorroPayload?.id ?? data?.id ?? null;

      if (!ahorroId) {
        throw new Error('No se encontró el ID del ahorro para iniciar el pago.');
      }

      const montoInicial = Number(payload.monto_ahorro || 0);
      const cuota = Number(payload.cuota || 0);

      if (action === 'update') {
        const requested = data?.requested || {};
        const currentMonto = Number(ahorroPayload?.monto_actual ?? ahorroPayload?.monto_ahorro ?? 0);
        const currentCuota = Number(ahorroPayload?.cuota_actual ?? ahorroPayload?.cuota ?? 0);
        const nextMonto = Number(requested?.new_monto_inicial ?? montoInicial);
        const nextCuota = Number(requested?.new_cuota ?? cuota);
        const addMonto = Math.max(0, nextMonto - currentMonto);
        const addCuota = Math.max(0, nextCuota - currentCuota);
        const oldSubscriptionId = ahorroPayload?.stripe_subscription_id || data?.ahorro?.stripe_subscription_id;

        if (addMonto <= 0 && addCuota <= 0) {
          throw new Error('No hay incrementos para actualizar en Stripe.');
        }

        await startStripeCheckout({
          apiBaseUrl,
          token,
          tokenType,
          ahorroId,
          returnUrl,
          body: {
            action: 'update',
            add_monto: addMonto,
            add_cuota: addCuota,
            old_subscription_id: oldSubscriptionId || undefined,
            charge_cuota_now: true,
          },
        });
        return;
      }

      await startStripeCheckout({
        apiBaseUrl,
        token,
        tokenType,
        ahorroId,
        returnUrl,
        body: {
          action: 'create',
          monto_inicial: montoInicial,
          cuota,
          charge_monto_now: true,
        },
      });
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Ocurrió un error al iniciar el pago.';
      window.alert(message);
    } finally {
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = 'Enviar solicitud';
      }
    }
  });
};

if (savingsForm && plansSelect) {
  loadPlans();
  loadFrequency();
  plansSelect.addEventListener('change', updatePlanFields);
  frequencySelect?.addEventListener('change', updateMinimum);
  updatePlanFields();
  handleSavingsSubmit();
}
