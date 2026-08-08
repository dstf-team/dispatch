<?php
ob_start(); // cegah output bocor

require 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

include "koneksi.php";

$id = intval($_GET['id']);

// ============================
// AMBIL DATA BERITA
// ============================
$q = mysqli_query($koneksi, "SELECT * FROM berita WHERE id_berita='$id'");
if (!$q) {
    die("Query Error (berita): " . mysqli_error($koneksi));
}
$data = mysqli_fetch_assoc($q);

// ============================
// FUNGSI FORMAT TANGGAL
// ============================
function tgl_indo_short($tanggal) {
    $hari = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];
    $ts = strtotime($tanggal);
    return $hari[date('l', $ts)] . ', ' . date('d/m/Y', $ts);
}
function tgl_indo($tanggal) {
    $bulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    $ts = strtotime($tanggal);
    $tgl = date('d', $ts);
    $bln = (int)date('m', $ts);
    $thn = date('Y', $ts);

    return $tgl . ' ' . $bulan[$bln] . ' ' . $thn;
}


// ============================
// FUNGSI IMAGE BASE64 (INI FIX UTAMA)
// ============================
function img_base64($path) {
    if (!file_exists($path)) return '';
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    return 'data:image/'.$type.';base64,'.base64_encode($data);
}

// ============================
// AMBIL FOTO DOKUMENTASI
// ============================
$gm = mysqli_query($koneksi, "SELECT * FROM berita_foto WHERE id_berita='$id'");

// ============================
// PATH LOGO (STRUKTUR TIDAK DIUBAH)
// ============================
$logo_kiri  = img_base64(__DIR__ . '/LOGO/HPAL.png');
$logo_kanan = img_base64(__DIR__ . '/LOGO/HARITA.png');
?>

<!DOCTYPE html>
<html>
<head>

    <style>
p{
    margin: 4px 0;      /* atur sesuai selera, default dompdf bisa ~12px */
    line-height: 1.4;   /* opsional */
}
</style>

<meta charset="UTF-8">
<style>
body { font-family: Arial; font-size: 12px; margin: 30px; }
.header-kop { border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 20px; }
.header-kop-table { width: 100%; }
.header-kop-table td { vertical-align: top; }
.judul-kop { font-size: 16px; font-weight: bold; text-align: center; }
.sub-kop { font-size: 11px; text-align: center; }
.section-title {
    font-size: 13px; font-weight: bold; margin-top: 15px;
    border-left: 4px solid #333; padding-left: 10px;
}
p { text-align: justify; line-height: 1.5; }
.img-wrapper {
    width: 22%;
    height: 120px;
    border: 1px solid #666;
    padding: 3px;
    margin: 5px;
    display: inline-block;
    vertical-align: top;
    text-align: center;
}

.img-wrapper img {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
}
.table-foto {
    width: 100%;
    border-collapse: collapse;
}

.table-foto td {
    width: 33.33%;
    padding: 6px;
    text-align: center;
    vertical-align: top;
}

.foto-box {
    width: 180px;
    height: 130px;
    border: 1px solid #666;
    padding: 3px;
    margin: auto;
}

.foto-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}


</style>
</head>

<body>

<div class="header-kop">
<table class="header-kop-table">
<tr>
    <td width="80">
        <img src="<?= $logo_kiri ?>" style="width:80px;">
    </td>

    <td>
        <div class="judul-kop">PT. HALMAHERA PERSADA LYGEND</div>
        <div class="sub-kop">
            Gedung Panin Senayan Lantai 3, Jl. Jendral Sudirman, Kav. 1, Jakarta Pusat 10270
        </div>
        <div class="sub-kop">
            Phone : (021) 5735432, Fax : (021) 5735442 email : management@hpalnickel.com
        </div>
    </td>

    <td width="80" style="text-align:right;">
        <img src="<?= $logo_kanan ?>" style="width:80px;">
    </td>
</tr>
</table>
</div>

<h3 style="text-align:center;text-decoration:underline;margin-top:-10px;">
    LAPORAN BERITA KEGIATAN
</h3>

<b>Judul:</b> <?= htmlspecialchars($data['judul']); ?><br>
<b>Tanggal:</b> <?= tgl_indo($data['tgl_posting']); ?><br><br>


<div class="section-title">Isi Laporan</div>
 <?= $data['isi_berita']; ?>

</br>
</br>

<div class="section-title">Dokumentasi</div><br>

<table class="table-foto">
<tr>
<?php
$ada = false;
$kolom = 0;

while ($g = mysqli_fetch_assoc($gm)) {
    $foto = __DIR__ . '/foto_berita/' . $g['foto'];

    if (file_exists($foto)) {
        echo '<td>
                <div class="foto-box">
                    <img src="'.img_base64($foto).'">
                </div>
              </td>';
        $kolom++;
        $ada = true;

        if ($kolom % 3 == 0) {
            echo '</tr><tr>';
        }
    }
}

if (!$ada) {
    echo '<td colspan="3">Tidak ada foto dokumentasi.</td>';
}
?>
</tr>
</table>


</body>
</html>

<?php
// ============================
// GENERATE PDF
// ============================
$html = ob_get_clean();

$dompdf = new Dompdf();
$dompdf->set_option('isRemoteEnabled', true);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Laporan_Berita_".$data['id_berita'].".pdf", ["Attachment" => false]);
exit;
