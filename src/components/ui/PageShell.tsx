export function LoadingScreen() {
  return (
    <div className="loading-screen">
      <div className="spinner" />
      <p>Đang tải dữ liệu học tiếng Trung...</p>
    </div>
  );
}

export function PageShell({ title, desc, children }: { title: string; desc?: string; children: React.ReactNode }) {
  return (
    <section className="page active">
      <div className="container">
        <header className="section-head" style={{ textAlign: 'left', marginBottom: 24 }}>
          <h1>{title}</h1>
          {desc && <p>{desc}</p>}
        </header>
        {children}
      </div>
    </section>
  );
}
