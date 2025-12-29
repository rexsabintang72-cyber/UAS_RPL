import "./App.css";
import Lacak from "./lacak";

export default function App() {
  return (
    <div className="app">

      {/* NAVBAR */}
      <header className="navbar">
        <div className="nav-container">
          <div className="logo">Harapan Mulia</div>

          <ul className="nav-menu">
            <li>bencana</li> 
            <li>lacak</li>
            <li>berita terkini</li>
          </ul>

          <button className="btn-primary">Donasi Cepat</button>
        </div>
      </header>

      {/* HERO */}
      <section className="hero">
        <div className="hero-container">
          <h1>
            Bencana <span>Alam</span>
          </h1>
          <p>
            sedikit donasi anda banyak yang terbantu
          </p>
          <button className="btn-hero">Donasi Sekarang</button>
        </div>
      </section>

      {/* PAKET DONASI */}
      <section className="packages">
        <div className="packages-container">
          <h2>Pilihan Paket Donasi</h2>

          <div className="packages-grid">
            <div className="package-card">
              <h3>bencana aceh</h3>
              <p>Rp 100.000</p>
              <button>Donasi</button>
            </div>

            <div className="package-card">
              <h3>bencana sulawesi</h3>
              <p>Rp 250.000</p>
              <button>Donasi</button>
            </div>

            <div className="package-card">
              <h3>gunung berapi meletus</h3>
              <p>Rp 1.000.000</p>
              <button>Donasi</button>
            </div>
          </div>
        </div>
      </section>

    </div>
  );
}
