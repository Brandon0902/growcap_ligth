const loginForm = document.querySelector('[data-login-form]');

const isDebugEnabled = () => localStorage.getItem('gc_debug') === '1';
const logDebug = (...args) => {
  if (isDebugEnabled()) {
    console.info('[Growcap login]', ...args);
  }
};

const updateStatus = (statusEl, message, variant = 'info') => {
  if (!statusEl) return;

  statusEl.textContent = message;
  statusEl.classList.remove(
    'hidden',
    'border-red-200',
    'bg-red-50',
    'text-red-700',
    'border-green-200',
    'bg-green-50',
    'text-green-700',
    'border-purple-200',
    'bg-purple-50',
    'text-purple-700',
  );

  if (variant === 'error') {
    statusEl.classList.add('border-red-200', 'bg-red-50', 'text-red-700');
  } else if (variant === 'success') {
    statusEl.classList.add('border-green-200', 'bg-green-50', 'text-green-700');
  } else {
    statusEl.classList.add('border-purple-200', 'bg-purple-50', 'text-purple-700');
  }
};

if (loginForm) {
  logDebug('Formulario de login detectado, inicializando listeners.');
  const statusEl = document.querySelector('[data-login-status]');
  const submitButton = document.querySelector('[data-login-submit]');

  const apiBaseUrl = (document.querySelector('[data-api-base-url]')?.getAttribute('data-api-base-url') || '')
    .replace(/\/$/, '');
  const loginEndpoint = apiBaseUrl ? `${apiBaseUrl}/auth/login` : '/api/auth/login';
  const redirectUrl = loginForm.getAttribute('data-redirect-url') || '/';

  loginForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    logDebug('Submit de login interceptado.');

    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = 'Ingresando...';
    }

    updateStatus(statusEl, 'Validando credenciales...', 'info');

    const formData = new FormData(loginForm);
    const payload = {
      email: formData.get('login'),
      password: formData.get('password'),
      device: 'cliente-web',
      single: formData.get('single') === 'on',
    };

    try {
      logDebug('POST login', { endpoint: loginEndpoint, payload });

      const response = await fetch(loginEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify(payload),
      });

      let data = null;

      try {
        data = await response.json();
      } catch (error) {
        data = null;
      }

      logDebug('Respuesta login', { status: response.status, data });

      if (!response.ok) {
        const message =
          data?.message || data?.errors?.email?.[0] || 'No pudimos iniciar sesión. Revisa tus datos.';
        updateStatus(statusEl, message, 'error');
        return;
      }

      if (data?.access_token) {
        localStorage.setItem('gc_token_type', data.token_type || 'Bearer');
        localStorage.setItem('gc_access_token', data.access_token);
      }

      if (data?.user) {
        localStorage.setItem('gc_user', JSON.stringify(data.user));
      }

      updateStatus(statusEl, '¡Listo! Redirigiendo al panel...', 'success');
      setTimeout(() => {
        const targetUrl = data?.redirect_url || redirectUrl;
        logDebug('Redirigiendo a', targetUrl);
        window.location.href = targetUrl;
      }, 800);
    } catch (error) {
      logDebug('Error en login', error);
      updateStatus(statusEl, 'No se pudo conectar con el servidor. Intenta de nuevo.', 'error');
    } finally {
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = 'Iniciar sesión';
      }
    }
  });
}
