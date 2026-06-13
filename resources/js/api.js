/**
 * HanViet API client — Laravel backend (same-origin /api/v1)
 */
const HanVietAPI = {
  TOKEN_KEY: 'hanviet_token',
  RETRY_STATUSES: [502, 503, 504],
  MAX_RETRIES: 4,

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

  requiresBackend() {
    if (window.HANVIET_CONFIG?.requiresBackend) return true;
    return !!document.querySelector('meta[name="hanviet-api"]');
  },

  sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
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

    const url = `${base}${path}`;
    let lastError = null;

    for (let attempt = 0; attempt < this.MAX_RETRIES; attempt++) {
      try {
        const res = await fetch(url, { ...options, headers, body });
        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
          if (this.RETRY_STATUSES.includes(res.status) && attempt < this.MAX_RETRIES - 1) {
            await this.sleep(1500 * (attempt + 1));
            continue;
          }
          const err = new Error(data.message || `API ${res.status}`);
          err.status = res.status;
          err.code = data.code;
          err.data = data;
          throw err;
        }

        return data;
      } catch (err) {
        lastError = err;
        if (err.status && !this.RETRY_STATUSES.includes(err.status)) {
          throw err;
        }
        if (attempt < this.MAX_RETRIES - 1) {
          await this.sleep(1500 * (attempt + 1));
          continue;
        }
        if (!err.status) {
          err.status = 0;
          err.message = err.message || 'Không kết nối được server';
        }
        throw err;
      }
    }

    throw lastError || new Error('API request failed');
  },

  async health() {
    const root = this.apiRoot();
    let lastError = null;

    for (let attempt = 0; attempt < this.MAX_RETRIES; attempt++) {
      try {
        const res = await fetch(`${root}/health`);
        if (res.ok) return res.json();
        if (this.RETRY_STATUSES.includes(res.status) && attempt < this.MAX_RETRIES - 1) {
          await this.sleep(2000 * (attempt + 1));
          continue;
        }
        throw new Error(`API health check failed (${res.status})`);
      } catch (err) {
        lastError = err;
        if (attempt < this.MAX_RETRIES - 1) {
          await this.sleep(2000 * (attempt + 1));
          continue;
        }
      }
    }

    throw lastError || new Error('API health check failed');
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
    if (err.status === 502 || err.status === 503 || err.status === 504) {
      return 'Server đang khởi động (có thể mất ~30 giây trên Render free). Đợi rồi thử lại, hoặc chuyển sang tab Đăng ký.';
    }
    if (err.status === 0) {
      return 'Không kết nối được server. Kiểm tra mạng hoặc thử lại sau.';
    }
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

  async aiChat(message, sessionId, options = {}) {
    return this.request('/ai/tutor/chat', {
      method: 'POST',
      body: {
        message,
        session_id: sessionId || null,
        mode: options.mode || 'tutor',
        scenario_id: options.scenario_id || null,
        hsk_level: options.hsk_level || null,
      },
    });
  },

  async checkoutPremium(plan, method = 'sandbox') {
    return this.request('/premium/checkout', {
      method: 'POST',
      body: { plan, method },
    });
  },

  async demoPremium() {
    return this.request('/premium/demo', { method: 'POST' });
  },

  async scoreSpeech(targetText, audioBlob) {
    const form = new FormData();
    form.append('target_text', targetText);
    form.append('audio', audioBlob, 'recording.webm');
    return this.request('/speech/score', { method: 'POST', body: form });
  },
};

window.HanVietAPI = HanVietAPI;
