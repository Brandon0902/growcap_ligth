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

const statusMap = {
  1: 'Pendiente',
  2: 'Activa',
  3: 'Terminada',
  4: 'Rechazada',
  5: 'Cancelada',
  6: 'Vencida',
};

const getStatusLabel = (status) => {
  if (!status) return null;
  if (typeof status === 'number' && statusMap[status]) return statusMap[status];
  if (typeof status === 'string') {
    const trimmed = status.trim();
    if (trimmed !== '' && statusMap[trimmed]) return statusMap[trimmed];
    return trimmed || null;
  }
  if (typeof status === 'object') {
    const directLabel = getFirstValue(
      status?.label,
      status?.nombre,
      status?.name,
      status?.descripcion,
      status?.estado,
      status?.estatus
    );
    if (directLabel) return directLabel;
    const codeValue = getFirstValue(status?.id, status?.code, status?.valor, status?.status);
    if (codeValue && statusMap[codeValue]) return statusMap[codeValue];
  }
  return null;
};

const buildStatus = (status) => {
  const normalized = String(getStatusLabel(status) || 'En revisión');
  const key = normalized.toLowerCase();

  if (['aprobado', 'aprobada', 'activo'].some((value) => key.includes(value))) {
    return {
      label: normalized,
      chipClasses: 'request-card__badge--success',
      toneClass: 'request-card--success',
      icon: '✅',
    };
  }
  if (['rechazado', 'rechazada', 'cancelado', 'cancelada'].some((value) => key.includes(value))) {
    return {
      label: normalized,
      chipClasses: 'request-card__badge--danger',
      toneClass: 'request-card--danger',
      icon: '⛔',
    };
  }
  if (['pendiente', 'revision', 'revisión', 'proceso'].some((value) => key.includes(value))) {
    return {
      label: normalized,
      chipClasses: 'request-card__badge--warning',
      toneClass: 'request-card--warning',
      icon: '🕒',
    };
  }

  return {
    label: normalized,
    chipClasses: 'request-card__badge--neutral',
    toneClass: 'request-card--neutral',
    icon: '📌',
  };
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
  if (plan) details.push({ label: 'Plan', value: plan });
  if (plazo) details.push({ label: 'Plazo', value: plazo });
  if (frecuencia) details.push({ label: 'Frecuencia', value: frecuencia });

  return details;
};

const renderEmptyState = (listEl, message) => {
  listEl.innerHTML = `
    <div class="request-state request-state--empty">
      <div class="request-state__icon">📭</div>
      <div>${message}</div>
    </div>
  `;
};

const renderErrorState = (listEl, message) => {
  listEl.innerHTML = `
    <div class="request-state request-state--error">
      <div class="request-state__icon">⚠️</div>
      <div>${message}</div>
    </div>
  `;
};

const renderItems = (listEl, items, typeLabel) => {
  listEl.innerHTML = '';

  items.forEach((item, index) => {
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

    const card = document.createElement('article');
    card.className = `request-card ${status.toneClass}`;
    card.style.animationDelay = `${index * 90}ms`;
    card.innerHTML = `
      <div class="request-card__glow" aria-hidden="true"></div>
      <div class="request-card__header">
        <div>
          <div class="request-card__type">${typeLabel}</div>
          <div class="request-card__amount">${formatCurrency(amount)}</div>
        </div>
        <span class="request-card__badge ${status.chipClasses}">
          <span aria-hidden="true">${status.icon}</span>
          ${status.label}
        </span>
      </div>
      <div class="request-card__meta-grid">
        <div class="request-card__meta-item">
          <span class="request-card__meta-label">Fecha</span>
          <span class="request-card__meta-value">${formatDate(dateValue)}</span>
        </div>
        ${meta
          .map(
            (detail) => `
              <div class="request-card__meta-item">
                <span class="request-card__meta-label">${detail.label}</span>
                <span class="request-card__meta-value">${detail.value}</span>
              </div>
            `
          )
          .join('')}
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
