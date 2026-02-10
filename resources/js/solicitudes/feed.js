const feeds = document.querySelectorAll('[data-requests-feed]');

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

const normalizeList = (payload) => {
  if (Array.isArray(payload)) return payload;
  if (Array.isArray(payload?.data)) return payload.data;
  if (Array.isArray(payload?.data?.data)) return payload.data.data;
  if (Array.isArray(payload?.items)) return payload.items;
  return [];
};

const formatCurrency = (value) => {
  const numeric = Number(value);
  if (Number.isNaN(numeric)) return value ? String(value) : 'Sin monto';
  return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(numeric);
};

const formatDate = (value) => {
  if (!value) return 'Sin fecha';
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return String(value);
  return parsed.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
};

const getFirstValue = (...values) => values.find((value) => value !== undefined && value !== null && value !== '');

const getStatusLabel = (status) => {
  if (!status) return null;
  if (typeof status === 'string' || typeof status === 'number') return String(status);
  if (typeof status === 'object') {
    return getFirstValue(
      status?.label,
      status?.nombre,
      status?.name,
      status?.descripcion,
      status?.estado,
      status?.estatus
    );
  }
  return null;
};

const buildStatus = (status) => {
  const normalized = String(getStatusLabel(status) || 'En revisión');
  const key = normalized.toLowerCase();

  if (['aprobado', 'aprobada', 'activo'].some((value) => key.includes(value))) {
    return { label: normalized, classes: 'bg-emerald-50 text-emerald-700' };
  }
  if (['rechazado', 'rechazada', 'cancelado', 'cancelada'].some((value) => key.includes(value))) {
    return { label: normalized, classes: 'bg-rose-50 text-rose-700' };
  }
  if (['pendiente', 'revision', 'revisión', 'proceso'].some((value) => key.includes(value))) {
    return { label: normalized, classes: 'bg-amber-50 text-amber-700' };
  }

  return { label: normalized, classes: 'bg-gray-100 text-gray-600' };
};

const getPlanLabel = (plan) => {
  if (!plan) return null;
  if (typeof plan === 'string' || typeof plan === 'number') return String(plan);
  if (typeof plan === 'object') {
    return getFirstValue(plan?.label, plan?.nombre, plan?.name, plan?.titulo, plan?.plan, plan?.tipo);
  }
  return null;
};

const buildMeta = (item) => {
  const planRaw = getFirstValue(item?.plan, item?.activo, item?.producto, item?.tipo, item?.nombre_plan);
  const plan = getPlanLabel(planRaw);
  const plazo = getFirstValue(item?.plazo, item?.periodo, item?.tiempo, item?.meses);
  const frecuencia = getFirstValue(item?.frecuencia, item?.frecuencia_pago);

  const details = [];
  if (plan) details.push(`Plan: ${plan}`);
  if (plazo) details.push(`Plazo: ${plazo}`);
  if (frecuencia) details.push(`Frecuencia: ${frecuencia}`);

  return details;
};

const renderEmptyState = (listEl, message) => {
  listEl.innerHTML = `
    <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-4 py-6 text-center text-sm text-gray-500">
      ${message}
    </div>
  `;
};

const renderErrorState = (listEl, message) => {
  listEl.innerHTML = `
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-6 text-center text-sm text-rose-700">
      ${message}
    </div>
  `;
};

const renderItems = (listEl, items, typeLabel) => {
  listEl.innerHTML = '';

  items.forEach((item) => {
    const amount = getFirstValue(item?.monto, item?.cantidad, item?.monto_ahorro, item?.monto_solicitado);
    const statusValue = getFirstValue(
      item?.estado,
      item?.status,
      item?.estatus,
      item?.status_text,
      item?.estatus_texto,
      item?.estado_texto,
      item?.status_label
    );
    const status = buildStatus(statusValue);
    const dateValue = getFirstValue(item?.fecha, item?.created_at, item?.fecha_creacion, item?.fecha_solicitud);
    const meta = buildMeta(item);

    const card = document.createElement('div');
    card.className = 'relative pl-6';
    card.innerHTML = `
      <span class="absolute left-1.5 top-6 h-2.5 w-2.5 rounded-full bg-purple-500 shadow"></span>
      <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
          <div>
            <div class="text-sm text-gray-500">${typeLabel}</div>
            <div class="text-base font-semibold text-gray-900">${formatCurrency(amount)}</div>
          </div>
          <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ${status.classes}">
            ${status.label}
          </span>
        </div>
        <div class="mt-3 grid gap-1 text-xs text-gray-500">
          <div>Fecha: ${formatDate(dateValue)}</div>
          ${meta.map((detail) => `<div>${detail}</div>`).join('')}
        </div>
      </div>
    `;

    listEl.appendChild(card);
  });
};

const loadFeed = async (feed) => {
  const listEl = feed.querySelector('[data-requests-list]');
  const countEl = feed.querySelector('[data-requests-count]');
  const typeLabel = feed.getAttribute('data-requests-type') || 'Solicitud';
  const emptyMessage = feed.getAttribute('data-requests-empty') || 'No hay solicitudes para mostrar.';
  const limit = Number(feed.getAttribute('data-requests-limit')) || 4;

  if (!listEl) return;

  const apiBaseUrl = feed.getAttribute('data-api-base-url') || '';
  const endpoint = feed.getAttribute('data-requests-endpoint') || '';
  const token = localStorage.getItem('gc_access_token');
  const tokenType = localStorage.getItem('gc_token_type') || 'Bearer';

  if (!apiBaseUrl || !endpoint || !token) {
    renderEmptyState(listEl, emptyMessage);
    if (countEl) countEl.textContent = '0 solicitudes';
    return;
  }

  try {
    const response = await fetch(buildApiUrl(apiBaseUrl, endpoint), {
      headers: {
        Accept: 'application/json',
        Authorization: `${tokenType} ${token}`,
      },
    });

    const data = await getJson(response);
    if (!response.ok) {
      const message = data?.message || data?.error || 'No se pudieron cargar las solicitudes.';
      renderErrorState(listEl, message);
      return;
    }

    const items = normalizeList(data);
    if (items.length === 0) {
      renderEmptyState(listEl, emptyMessage);
      if (countEl) countEl.textContent = '0 solicitudes';
      return;
    }

    const slice = items.slice(0, limit);
    renderItems(listEl, slice, typeLabel);
    if (countEl) countEl.textContent = `${items.length} solicitudes`;
  } catch (error) {
    renderErrorState(listEl, 'No se pudo conectar con las solicitudes.');
  }
};

feeds.forEach((feed) => {
  loadFeed(feed);
});
