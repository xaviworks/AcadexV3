import axios from 'axios';

window.axios = axios;
window.axios.defaults.withCredentials = true;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const CSRF_TOKEN_SELECTOR = 'meta[name="csrf-token"]';
const CSRF_REFRESH_URL_SELECTOR = 'meta[name="csrf-refresh-url"]';
const SAFE_METHODS = new Set(['GET', 'HEAD', 'OPTIONS']);

function getCsrfToken() {
  return document.querySelector(CSRF_TOKEN_SELECTOR)?.getAttribute('content') || '';
}

function setCsrfToken(token) {
  if (!token) {
    return;
  }

  const tokenMeta = document.querySelector(CSRF_TOKEN_SELECTOR);
  if (tokenMeta) {
    tokenMeta.setAttribute('content', token);
  }

  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
}

function getCsrfRefreshUrl() {
  return document.querySelector(CSRF_REFRESH_URL_SELECTOR)?.getAttribute('content') || '/csrf-token';
}

function isMutationMethod(method) {
  return !SAFE_METHODS.has((method || 'GET').toUpperCase());
}

function isSameOrigin(url) {
  try {
    return new URL(url, window.location.href).origin === window.location.origin;
  } catch {
    return false;
  }
}

const initialToken = getCsrfToken();
if (initialToken) {
  setCsrfToken(initialToken);
}

const nativeFetch = window.fetch.bind(window);

async function refreshCsrfToken() {
  const refreshUrl = getCsrfRefreshUrl();

  const response = await nativeFetch(refreshUrl, {
    method: 'GET',
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });

  if (!response.ok) {
    return null;
  }

  const payload = await response.json().catch(() => null);
  const token = payload?.token || '';

  if (token) {
    setCsrfToken(token);
  }

  return token || null;
}

function prepareRequest(baseRequest) {
  const headers = new Headers(baseRequest.headers || {});
  const method = (baseRequest.method || 'GET').toUpperCase();

  if (!headers.has('X-Requested-With')) {
    headers.set('X-Requested-With', 'XMLHttpRequest');
  }

  if (isMutationMethod(method)) {
    const csrfToken = getCsrfToken();
    if (csrfToken) {
      headers.set('X-CSRF-TOKEN', csrfToken);
    }
  }

  return new Request(baseRequest, { headers });
}

window.refreshCsrfToken = refreshCsrfToken;
window.csrf = {
  getToken: getCsrfToken,
  setToken: setCsrfToken,
  refresh: refreshCsrfToken,
};

window.fetch = async function csrfAwareFetch(input, init) {
  const baseRequest = input instanceof Request ? input.clone() : new Request(input, init);
  const sameOrigin = isSameOrigin(baseRequest.url);
  const method = (baseRequest.method || 'GET').toUpperCase();
  const refreshUrl = getCsrfRefreshUrl();
  const refreshPathname = new URL(refreshUrl, window.location.href).pathname;
  const requestPathname = new URL(baseRequest.url, window.location.href).pathname;
  const isRefreshRequest = sameOrigin && requestPathname === refreshPathname;

  let response = await nativeFetch(sameOrigin ? prepareRequest(baseRequest.clone()) : baseRequest.clone());

  if (sameOrigin && isMutationMethod(method) && response.status === 419 && !isRefreshRequest) {
    const refreshedToken = await refreshCsrfToken().catch(() => null);

    if (refreshedToken) {
      response = await nativeFetch(prepareRequest(baseRequest.clone()));
    }
  }

  if (sameOrigin && isMutationMethod(method) && (response.status === 401 || response.status === 419)) {
    window.dispatchEvent(
      new CustomEvent('auth:session-expired', {
        detail: {
          status: response.status,
          url: baseRequest.url,
        },
      })
    );
  }

  return response;
};
