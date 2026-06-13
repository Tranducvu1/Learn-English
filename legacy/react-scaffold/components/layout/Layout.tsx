import { NavLink, Outlet, useLocation } from 'react-router-dom';
import { useApp } from '../../context/AppContext';
import { SeoHead } from '../seo/SeoHead';
import { getSeoByPath } from '../../config/seo';

const NAV = [
  { to: '/', label: 'Trang chủ', end: true },
  { to: '/luyen-hsk', label: 'Luyện HSK' },
  { to: '/tu-vung-hsk', label: '1200+ Từ vựng' },
  { to: '/luyen-giong-noi', label: 'Luyện giọng' },
  { to: '/flashcard-tieng-trung', label: 'Flashcard' },
  { to: '/luyen-de-hsk', label: 'Luyện đề' },
  { to: '/tien-do-hoc-tap', label: 'Tiến độ' },
  { to: '/tu-dien-tieng-trung', label: 'Từ điển' },
  { to: '/video-bai-giang-hsk', label: 'Video' },
  { to: '/premium', label: 'Premium' },
];

export function Layout() {
  const { state, toggleDark, isPremium } = useApp();
  const { pathname } = useLocation();
  const seo = getSeoByPath(pathname);

  return (
    <>
      <SeoHead seo={seo} />
      <header className="site-header">
        <div className="header-inner">
          <NavLink to="/" className="brand" end>
            <span className="brand-han">汉越</span><span className="brand-viet">学堂</span>
          </NavLink>
          <nav className="header-nav">
            {NAV.map(n => (
              <NavLink
                key={n.to}
                to={n.to}
                end={n.end}
                className={({ isActive }) => `nav-item${isActive ? ' active' : ''}`}
              >
                {n.label}
              </NavLink>
            ))}
          </nav>
          <div className="header-actions">
            <div className="streak-pill">🔥 {state.streak} ngày</div>
            {isPremium && <span className="pro-badge">PRO</span>}
            <button type="button" className="icon-btn" onClick={toggleDark} title="Giao diện">
              {state.settings.darkMode ? '☀️' : '🌙'}
            </button>
            <NavLink to="/luyen-hsk" className="btn btn-primary btn-sm">Học ngay</NavLink>
          </div>
        </div>
      </header>

      <main className="app-main">
        <Outlet />
      </main>

      <footer className="site-footer container" style={{ padding: '32px 16px', textAlign: 'center' }}>
        <p><strong>汉越学堂</strong> — Học tiếng Trung · Luyện thi HSK online miễn phí</p>
      </footer>
    </>
  );
}
