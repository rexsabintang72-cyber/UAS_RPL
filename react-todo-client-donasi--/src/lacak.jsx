import "./../styles/Lacak.css";

export default function Lacak() {
  return (
    <div className="lacak-page">
      <div className="lacak-container">
        <h1>Lacak Donasi</h1>
        <p>Masukkan kode donasi untuk melihat status penyaluran</p>

        <div className="lacak-box">
          <input
            type="text"
            placeholder="Contoh: DN-2025-001"
          />
          <button>Lacak Sekarang</button>
        </div>

        <div className="lacak-info">
          <h3>Status Donasi</h3>
          <ul>
            <li><span>✔</span> Donasi diterima</li>
            <li><span>✔</span> Sedang diproses</li>
            <li><span>⏳</span> Dalam penyaluran</li>
          </ul>
        </div>
      </div>
    </div>
  );
}
