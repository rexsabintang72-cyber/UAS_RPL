import "./../styles/Berita.css";

export default function Berita() {
  return (
    <div className="berita-page">
      <div className="berita-container">
        <h1>Berita Terkini</h1>
        <p>Update terbaru kegiatan dan penyaluran bantuan</p>

        <div className="berita-grid">
          <div className="berita-card">
            <img src="https://images.unsplash.com/photo-1509099836639-18ba1795216d" />
            <h3>Bantuan Bencana Alam</h3>
            <p>Donasi telah disalurkan ke wilayah terdampak banjir.</p>
          </div>

          <div className="berita-card">
            <img src="https://images.unsplash.com/photo-1593113598332-cd288d649433" />
            <h3>Distribusi Sembako</h3>
            <p>Ribuan paket sembako dibagikan kepada warga.</p>
          </div>

          <div className="berita-card">
            <img src="https://images.unsplash.com/photo-1526256262350-7da7584cf5eb" />
            <h3>Relawan Bergerak</h3>
            <p>Relawan Harapan Mulia turun langsung ke lapangan.</p>
          </div>
        </div>
      </div>
    </div>
  );
}
