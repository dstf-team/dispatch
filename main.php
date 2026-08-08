
<style>
.card-text-limit{
    display:block;
    overflow:hidden;
    max-height:60px; /* kira-kira 3 baris teks */
}
</style>
<style>
.default-posts-left,
.default-posts-left p,
.default-posts-left div,
.default-posts-left span {
    color:#000 !important;
}



.pagination{
    flex-wrap: wrap;
    gap:5px;
}

.pagination .page-link{
    min-width:40px;
    text-align:center;
}
</style>

<?php

include "admin/koneksi.php";
$_GET['module'] = isset($_GET['module']) ? $_GET['module'] : '';

/* ---------------- Pagination Helper ---------------- */
function paginate($page, $limit, $total, $module){
    $jumlah_halaman = ceil($total / $limit);
    if ($jumlah_halaman <= 1) return "";

    $html = '<nav><ul class="pagination justify-content-center mt-4">';

    // Prev
    $prev = ($page > 1) ? $page - 1 : 1;
    $html .= '<li class="page-item '.($page==1?'disabled':'').'">
                <a class="page-link" href="?module='.$module.'&page='.$prev.'">«</a>
              </li>';

    // Numbers
// Numbers (SMART PAGINATION)
$range = 10;

// halaman awal
if ($page > 3) {
    $html .= '<li class="page-item">
                <a class="page-link" href="?module='.$module.'&page=1">1</a>
              </li>';
    $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
}

// range tengah
for ($i = max(1, $page - $range); $i <= min($jumlah_halaman, $page + $range); $i++) {

    $html .= '<li class="page-item '.($page==$i?'active':'').'">
                <a class="page-link" href="?module='.$module.'&page='.$i.'">'.$i.'</a>
              </li>';
}

// halaman akhir
if ($page < $jumlah_halaman - 2) {
    $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
    $html .= '<li class="page-item">
                <a class="page-link" href="?module='.$module.'&page='.$jumlah_halaman.'">'.$jumlah_halaman.'</a>
              </li>';
}

    // Next
    $next = ($page < $jumlah_halaman) ? $page + 1 : $jumlah_halaman;
    $html .= '<li class="page-item '.($page==$jumlah_halaman?'disabled':'').'">
                <a class="page-link" href="?module='.$module.'&page='.$next.'">»</a>
              </li>';

    $html .= '</ul></nav>';
    return $html;
}

/* =================================================
   FITUR SEARCH
================================================= */
if (isset($_GET['search']) && $_GET['search'] != '') {

    // 🔑 PAKSA SEARCH GLOBAL (abaikan module)
    $_GET['module'] = '';

    $search = mysqli_real_escape_string($koneksi, $_GET['search']);

    echo "<h3 class='mb-3'>Hasil pencarian: <b>".htmlspecialchars($search)."</b></h3>";

    $sql = "SELECT b.*, u.username
            FROM berita b
            LEFT JOIN user u ON b.id_user = u.id
            WHERE b.judul LIKE '%$search%'
               OR b.isi_berita LIKE '%$search%'
               OR b.tgl_posting LIKE '%$search%'
            ORDER BY b.id_berita DESC";

    $hasil = mysqli_query($koneksi, $sql);

    if (mysqli_num_rows($hasil) == 0) {
        echo "<div class='alert alert-warning'>Tidak ada berita ditemukan.</div>";
    } else {
        echo '<div class="row">';
        while ($t = mysqli_fetch_assoc($hasil)) {

            $username = !empty($t['username']) ? $t['username'] : "Admin";

            $isi = strip_tags($t['isi_berita']);
            $isi = substr($isi, 0, 150);
            if (strlen($isi) >= 150) {
                $isi = substr($isi, 0, strrpos($isi, " "));
            }

            echo '
            <div class="col-md-4 col-sm-6 col-12 mb-4">
                <div class="card" style="border-radius:12px; overflow:hidden;">
                    '.(!empty($t['gambar'])
                        ? '<img src="admin/foto_berita/'.htmlspecialchars($t['gambar']).'" class="card-img-top" style="height:200px;object-fit:cover;">'
                        : ''
                    ).'
                    <div class="card-body">
                        <h4 class="card-title" style="font-size:18px;font-weight:bold;">
                            <a href="?module=detailberita&id='.intval($t['id_berita']).'">'
                                .htmlspecialchars($t['judul']).'
                            </a>
                        </h4>
                        <p style="font-size:12px;color:gray;">
                            '.htmlspecialchars($t['tgl_posting']).' | by '.htmlspecialchars($username).'
                        </p>
                        <p class="card-text-limit">'.htmlspecialchars($isi).'</p>
                        <a href="?module=detailberita&id='.intval($t['id_berita']).'"
                           class="btn btn-primary btn-sm">Selengkapnya</a>
                    </div>
                </div>
            </div>';
        }
        echo '</div>';
    }

    exit(); // 
}

/* =================================================
   HALAMAN UTAMAX (LIST BERITA)
================================================= */
if ($_GET['module'] == '') {

    $limit = 9;
    $page  = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $start = ($page - 1) * $limit;


  $sql_total = "SELECT COUNT(id_berita) AS total FROM berita";
$res_total = mysqli_query($koneksi, $sql_total);
$total = intval(mysqli_fetch_assoc($res_total)['total']);

$sql = "SELECT b.*, u.username
        FROM berita b
        LEFT JOIN user u ON b.id_user = u.id
        ORDER BY b.tgl_posting DESC
        LIMIT $start, $limit";
$terkini = mysqli_query($koneksi, $sql);


    echo '<div class="row">';
    while ($t = mysqli_fetch_assoc($terkini)) {

        $username = !empty($t['username']) ? $t['username'] : "Admin";

        $isi = strip_tags($t['isi_berita']);
        $isi = substr($isi, 0, 150);
        if (strlen($isi) >= 150) {
            $isi = substr($isi, 0, strrpos($isi, " "));
        }

        echo '
        <div class="col-md-4 col-sm-6 col-12 mb-4">
            <div class="card" style="border-radius:12px; overflow:hidden;">
               '.(
    !empty($t['gambar'])
    ? '<img src="admin/foto_berita/'.htmlspecialchars($t['gambar']).'" class="card-img-top" style="height:200px;object-fit:cover;">'
    : '<div style="height:200px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#888;">
         Tidak ada gambar :(
       </div>'
).'

                <div class="card-body">
                    <h5 class="card-title" style="font-size:18px;font-weight:bold;">
                        <a href="?module=detailberita&id='.intval($t['id_berita']).'">'.htmlspecialchars($t['judul']).'</a>
                    </h5>
                    <p style="font-size:12px;color:gray;">'.htmlspecialchars($t['tgl_posting']).' | by: '.htmlspecialchars($username).'</p>
                    <p>'.htmlspecialchars($isi).'...</p>
                    <a href="?module=detailberita&id='.intval($t['id_berita']).'" class="btn btn-primary btn-sm">Selengkapnya</a>
                </div>
            </div>
        </div>';
    }
    echo '</div>';

    echo paginate($page, $limit, $total, "");
}

/* =================================================
   HALAMAN DETAIL BERITA
================================================= */
elseif ($_GET['module'] == 'detailberita') {

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) die("<div class='alert alert-warning'>ID berita tidak valid.</div>");

    $sql = "SELECT b.*, u.username FROM berita b LEFT JOIN user u ON b.id_user=u.id WHERE b.id_berita=$id LIMIT 1";
    $detail = mysqli_query($koneksi, $sql);
    $d = mysqli_fetch_assoc($detail);
    if (!$d) die("<div class='alert alert-warning'>Berita tidak ditemukan.</div>");

    $username = !empty($d['username']) ? $d['username'] : "Admin";

    echo '<div class="detail-berita">';

    // Gambar utama
    if (!empty($d['gambar'])) {
        $img_path = 'admin/foto_berita/'.htmlspecialchars($d['gambar']);
        echo '<div class="mb-3 text-center">';
        echo '<img src="'.$img_path.'" class="img-fluid gallery-img" style="max-width:100%; max-height:400px; object-fit:cover; border-radius:12px; cursor:pointer;" data-bs-toggle="modal" data-bs-target="#imageModal" data-bs-img="'.$img_path.'">';
        echo '</div>';
    }
    echo '<br/>';

    

    echo '<h2>'.htmlspecialchars($d['judul']).'</h2>';
    echo '<p class="text-muted">'.htmlspecialchars($d['tgl_posting']).' | by: '.htmlspecialchars($username).'</p>';
    echo '<div class="text-justify" style="color:#000;">'.$d['isi_berita'].'</div>';

    echo '</Br>';
    echo '<h4>DOKUMENTASI:<h4>';
// Galeri tambahan
    $foto_query = mysqli_query($koneksi, "SELECT * FROM berita_foto WHERE id_berita=$id");
    if ($foto_query && mysqli_num_rows($foto_query) > 0) {
        echo '<div class="berita-galeri row mb-3">';
        while ($f = mysqli_fetch_assoc($foto_query)) {
            $img_path = 'admin/foto_berita/'.htmlspecialchars($f['foto']);
            echo '<div class="col-6 col-md-3 mb-2">';
            echo '<img src="'.$img_path.'" class="img-fluid gallery-img" style="width:100%; height:150px; object-fit:cover; border-radius:8px; cursor:pointer;" data-bs-toggle="modal" data-bs-target="#imageModal" data-bs-img="'.$img_path.'">';
            echo '</div>';
        }
        echo '</div>';
    }

    echo '<hr>';

  
  // Form komentar
echo '<h3>Komentar</h3>';

$namaLogin = "";
if(isset($_SESSION['user'])){
    // potong jadi max 2 kata biar rapi
    $parts = explode(" ", trim($_SESSION['user']));
    $namaLogin = implode(" ", array_slice($parts,0,5));
}

echo '<form method="POST">';

// Jika login → nama otomatis & hidden input
if(!empty($namaLogin)){
    echo '
    <div class="form-group mb-2">
        <label>Nama</label>
        <input type="text" class="form-control" value="'.htmlspecialchars($namaLogin).'" disabled>
        <input type="hidden" name="nama" value="'.htmlspecialchars($namaLogin).'">
    </div>';
}else{
    // Jika belum login tetap manual
    echo '
    <div class="form-group mb-2">
        <label>Nama</label>
        <input type="text" name="nama" class="form-control" required>
    </div>';
}

echo '
    <div class="form-group mb-2">
        <label>Komentar</label>
        <textarea name="komentar" class="form-control" rows="4" required></textarea>
    </div>

    <button type="submit" name="btnsubmit" class="btn btn-primary">Kirim</button>
</form><hr>';


    // Tampilkan komentar
    $komen = mysqli_query($koneksi, "SELECT * FROM komentar WHERE id_berita=$id ORDER BY id_komentar DESC");
    if ($komen) {
        echo "<div class='mt-3'>";
        while ($k = mysqli_fetch_assoc($komen)) {
            echo "<div class='mb-3'><br><strong>".htmlspecialchars($k['ip_address'])." : </strong><strong>".htmlspecialchars($k['nama'])."</strong><br><span>".htmlspecialchars($k['isi_komentar'])."</span></div>";
        }
        echo "</div>";
    }

    // Simpan komentar
    if (isset($_POST['btnsubmit'])) {

    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $komentar = mysqli_real_escape_string($koneksi, $_POST['komentar']);

    // Ambil IP Address
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    mysqli_query($koneksi, "INSERT INTO komentar 
        (id_berita, nama, ip_address, isi_komentar) 
        VALUES 
        ($id,'$nama','$ip','$komentar')");

    echo "<script>location.replace('?module=detailberita&id=$id');</script>";
    exit;
}


    echo '<a href="javascript:history.back()" class="btn btn-secondary mt-3">Kembali</a>';
    echo '</div>';

    // Modal & script
    echo '<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-body p-0">
            <img src="" id="modalImg" class="img-fluid" style="width:100%; object-fit:contain;">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          </div>
        </div>
      </div>
    </div>
    <script>
    document.querySelectorAll(".gallery-img").forEach(item => {
        item.addEventListener("click", function(){
            document.getElementById("modalImg").setAttribute("src", this.getAttribute("data-bs-img"));
        });
    });
    </script>';
    echo' <div id="imgOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); justify-content:center; align-items:center; z-index:9999;">
    <img id="overlayImg" src="" style="max-width:90%; max-height:90%; border-radius:8px;">
</div>

<script>
document.querySelectorAll(".gallery-img").forEach(img => {
    img.addEventListener("click", function() {
        const overlay = document.getElementById("imgOverlay");
        const overlayImg = document.getElementById("overlayImg");
        overlayImg.src = this.src;
        overlay.style.display = "flex";
    });
});

document.getElementById("imgOverlay").addEventListener("click", function() {
    this.style.display = "none";
});
</script>';

}

/* =================================================
   HALAMAN KATEGORI BERITA
================================================= */
elseif (in_array($_GET['module'], ['earthwork','geotech','hidro','ipal','plan'])) {

    $kategori = [
        'earthwork' => 1,
        'geotech'   => 2,
        'hidro'     => 6,
        'ipal'      => 7,
        'plan'      => 8
    ];

    $mod = $_GET['module'];
    $id_kat = $kategori[$mod];
    $view = isset($_GET['view']) ? $_GET['view'] : 'card';


    // limit beda antara card & list
$limit = ($view == 'list') ? 20 : 6;

    $page  = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $start = ($page - 1) * $limit;

    $sql_total = "SELECT COUNT(id_berita) AS total FROM berita WHERE id_kategori=$id_kat";
    $res_total = mysqli_query($koneksi, $sql_total);
    $total = intval(mysqli_fetch_assoc($res_total)['total']);

    $sql = "SELECT b.*, u.username
            FROM berita b
            LEFT JOIN user u ON b.id_user = u.id
            WHERE b.id_kategori=$id_kat
            ORDER BY b.id_berita DESC
            LIMIT $start, $limit";
    $terkini = mysqli_query($koneksi, $sql);


    echo '<h3 class="mb-3">
            BA Over Shift: '.ucfirst(htmlspecialchars($mod)).'
            <small class="text-muted">('.$total.' Post)</small>
          </h3>';

echo '<div class="mb-3">';
echo '<a href="?module='.$mod.'&view=card" 
        class="btn btn-sm '.($view=='card'?'btn-primary':'btn-outline-primary').' me-2">
        Card View
      </a>';
echo '<a href="?module='.$mod.'&view=list" 
        class="btn btn-sm '.($view=='list'?'btn-primary':'btn-outline-primary').'">
        List View
      </a>';
echo '</div>';

      
    echo '<br/>';
  echo '<div class="row">';

while ($t = mysqli_fetch_assoc($terkini)) {

    $username = !empty($t['username']) ? $t['username'] : "Admin";

    // ================= LIST VIEW (TANPA GAMBAR) =================
    if ($view == 'list') {

        echo '
        <div class="col-12 mb-2">
            <div style="padding:10px 12px;border-bottom:1px solid #ddd;">
                <div style="font-weight:600;">
                    • <a href="?module=detailberita&id='.intval($t['id_berita']).'">
                        '.htmlspecialchars($t['judul']).'
                      </a>
                </div>
                <div style="font-size:12px;color:#666;">
                    '.htmlspecialchars($t['tgl_posting']).' | by '.htmlspecialchars($username).'
                </div>
            </div>
        </div>';

    }
    // ================= CARD VIEW (LENGKAP + GAMBAR) =================
    else {

        $isi = strip_tags($t['isi_berita']);
        $isi = substr($isi, 0, 150);

        echo '
        <div class="col-md-4 col-sm-6 col-12 mb-4">
            <div class="card" style="border-radius:12px; overflow:hidden;">
                '.(!empty($t['gambar'])
                    ? '<img src="admin/foto_berita/'.htmlspecialchars($t['gambar']).'"
                           style="height:200px;object-fit:cover;">'
                    : '<div style="height:200px;background:#f0f0f0;
                                display:flex;align-items:center;
                                justify-content:center;color:#888;">
                        Tidak ada gambar
                       </div>'
                ).'
                <div class="card-body">
                    <h5 class="card-title">
                        <a href="?module=detailberita&id='.intval($t['id_berita']).'">
                            '.htmlspecialchars($t['judul']).'
                        </a>
                    </h5>
                    <p style="font-size:12px;color:gray;">
                        '.htmlspecialchars($t['tgl_posting']).' | by '.htmlspecialchars($username).'
                    </p>
                    <a href="?module=detailberita&id='.intval($t['id_berita']).'"
                       class="btn btn-primary btn-sm">Selengkapnya</a>
                </div>
            </div>
        </div>';
    }
}


echo '</div>'; // ⬅️ TUTUP ROW
echo paginate($page, $limit, $total, $mod); // ⬅️ PINDAH KE LUAR while
}
?>
