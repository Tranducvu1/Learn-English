/**
 * Review Code SSE Client — lắng nghe events từ Laravel backend
 *
 * Usage:
 *   HANVIET_API_URL=http://localhost:8000 npm run listen
 */
const API_BASE = (process.env.HANVIET_API_URL || 'http://localhost:8000').replace(/\/$/, '');
const STREAM_URL = `${API_BASE}/api/events/stream`;
const LAST_ID = parseInt(process.env.LAST_ID || '0', 10);

console.log(`[review-client] Connecting to ${STREAM_URL}?last_id=${LAST_ID}\n`);

const es = new EventSource(`${STREAM_URL}?last_id=${LAST_ID}`);

es.addEventListener('review', (event) => {
  try {
    const data = JSON.parse(event.data);
    const time = new Date().toLocaleTimeString('vi-VN');
    console.log(`\n── [${time}] ${data.type} (#${data.id}) ──`);

    if (data.payload?.findings?.length) {
      for (const f of data.payload.findings) {
        const icon = { critical: '🔴', error: '❌', warning: '⚠️', info: 'ℹ️' }[f.severity] || '•';
        console.log(`  ${icon} [${f.severity}] ${f.file}${f.line ? ':' + f.line : ''} — ${f.message}`);
      }
    } else if (data.payload?.passed !== undefined) {
      console.log(`  ${data.payload.passed ? '✅ PASSED' : '❌ FAILED'} — ${data.payload.findings_count ?? 0} findings`);
    } else {
      console.log(' ', JSON.stringify(data.payload, null, 2));
    }
  } catch (err) {
    console.warn('[review-client] parse error:', err.message);
  }
});

es.onopen = () => {
  console.log('[review-client] Connected. Waiting for review events...\n');
  console.log('Trigger review: curl -X POST http://localhost:8000/api/v1/review/trigger\n');
};

es.onerror = () => {
  console.warn('[review-client] Connection lost — EventSource will retry');
};

process.on('SIGINT', () => {
  es.close();
  console.log('\n[review-client] Disconnected.');
  process.exit(0);
});
