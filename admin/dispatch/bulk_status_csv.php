<?php
?>

<div class="d-flex left-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <a href="index.php">Home</a> / Import CSV Status Unit
</div>

<style>
.table td,
.table th{
    color:#000 !important;
}
</style>

<h2>Import CSV Status Unit</h2>

<div class="alert alert-info">

    <b>Format CSV:</b>

<pre style="margin:0;">
Tanggal,Shift,Equipment,Unit Code,Lokasi,Status Unit,Keterangan
2026-04-30,day,Dump Truck,HDT-HO-01,ROM-A,READY,
2026-04-30,day,Excavator,HX-SA-02,PIT-1,BREAKDOWN,Engine Overheat
</pre>

</div>

<form method="POST"
      action="dispatch/proses_import_csv_status.php"
      enctype="multipart/form-data">

    <div class="form-group">

        <label>Upload File CSV</label>

        <input type="file"
               name="file_csv"
               accept=".csv"
               class="form-control"
               required>

    </div>

    <br>

    <button type="submit"
            class="btn btn-primary">

        <i class="fas fa-file-upload"></i>
        Import CSV

    </button>

</form>