/**
 * Đường dẫn Laravel monolith — repo root = Laravel app
 */
const path = require('path');

const root = path.join(__dirname, '..');
const dataDir = path.join(root, 'database', 'data');
const resourcesJs = path.join(root, 'resources', 'js');
const resourcesCss = path.join(root, 'resources', 'css');

module.exports = { root, dataDir, resourcesJs, resourcesCss };
