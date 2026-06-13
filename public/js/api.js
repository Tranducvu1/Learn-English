/**
 * HanViet API client — Laravel backend (same-origin /api/v1)
 */
const HanVietAPI = {
  TOKEN_KEY: 'hanviet_token',

  get token() {
    try {
      return localStorage.getItem(this.TOKEN_KEY) || '';
    } catch {
      return '';
    }
  },

  setToken(value) {
    if (value) localStorage.setItem(this.TOKEN_KEY, value);
    else localStorage.removeItem(this.TOKEN_KEY);
  },

  apiRoot() {
    if (window.HANVIET_CONFIG?.apiUrl) {
      return String(window.HANVIET_CONFIG.apiUrl).replace(/\/$/, '');
    }
    if (window.HANVIET_API_BASE) {
      return String(window.HANVIET_API_BASE).replace(/\/$/, '');
    }
    const meta = document.querySelector('meta[name="hanviet-api"]');
    if (meta) {
      const c = (meta.content || '').trim();
      if (c.startsWith('http')) return c.replace(/\/$/, '');
      if (c.startsWith('/')) return c.replace(/\/$/, '');
      return '/api';
    }
    const { hostname, port } = window.location;
    if ((hostname === 'localhost' || hostname === '127.0.0.1') && port === '8000') {
      return '/api';
    }
    return null;
  },

  base() {
    const root = this.apiRoot();
    return root ? `${root}/v1` : null;
  },

  enabled() {
    return !!this.apiRoot();
  },

  /** Web bắt buộc Laravel BE khi có meta hanviet-api hoặc HANVIET_CONFIG */
  requiresBackend() {
    if (window.HANVIET_CONFIG?.requiresBackend) return true;
    return !!document.querySelector('meta[name="hanviet-api"]');
  },

  async request(path, options = {}) {
    const base = this.base();
    if (!base) throw new Error('API not configured');

    const headers = { Accept: 'application/json', ...(options.headers || {}) };
    if (this.token) headers.Authorization = `Bearer ${this.token}`;

    let body = options.body;
    if (body && !(body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
      body = JSON.stringify(body);
    }

    const res = await fetch(`${base}${path}`, { ...options, headers, body });
    const data = await res.json().catch(() => ({}));

    if (!res.ok) {
      const err = new Error(data.message || `API ${res.status}`);
      err.status = res.status;
      err.code = data.code;
      err.data = data;
      throw err;
    }

    return data;
  },

  async health() {
    const root = this.apiRoot();
    const res = await fetch(`${root}/health`);
    if (!res.ok) throw new Error('API health check failed');
    return res.json();
  },

  async loadContentBundle() {
    return this.request('/bootstrap');
  },

  async fetchProgress() {
    return this.request('/me/progress');
  },

  async syncProgress(state) {
    return this.request('/me/progress/sync', { method: 'POST', body: state });
  },

  async me() {
    return this.request('/auth/me');
  },

  formatError(err) {
    const data = err.data || {};
    if (data.errors) {
      const first = Object.values(data.errors).flat()[0];
      if (first) return first;
    }
    return data.message || err.message || 'Có lỗi xảy ra';
  },

  async login(email, password) {
    const data = await this.request('/auth/login', {
      method: 'POST',
      body: { email, password },
    });
    this.setToken(data.token);
    return data;
  },

  async register(payload) {
    const data = await this.request('/auth/register', {
      method: 'POST',
      body: payload,
    });
    this.setToken(data.token);
    return data;
  },

  async logout() {
    try {
      await this.request('/auth/logout', { method: 'POST' });
    } finally {
      this.setToken('');
    }
  },

  async aiChat(message, sessionId) {
    return this.request('/ai/tutor/chat', {
      method: 'POST',
      body: { message, session_id: sessionId || null },
    });
  },

  async demoPremium() {
    return this.request('/premium/demo', { method: 'POST' });
  },
};

window.HanVietAPI = HanVietAPI;
