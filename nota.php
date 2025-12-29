<?php
// nota.php
// =============================
// CONTOH DATA
// (sementara, nanti bisa ambil dari DB / session)

$nama_toko = "FOREST DESSERT";
$alamat    = "Semarang"; // BUKAN GPS
$tanggal   = date("d/m/Y H:i");

// contoh data transaksi
$items = [
    [
        'nama' => 'Chocolate Hazelnut',
        'qty'  => 1,
        'harga'=> 22000
    ]
];

$total  = 22000;
$bayar  = 50000;
$kembali = $bayar - $total;

function rupiah($angka) {
    return number_format($angka, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota</title>
    <style>
        body {
            font-family: monospace;
            font-size: 12px;
            width: 280px;
            margin: auto;
        }
        .center {
            text-align: center;
        }
        .line {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        table {
            width: 100%;
        }
        td {
            vertical-align: top;
        }
        .right {
            text-align: right;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div class="center">
    <strong><?= $nama_toko ?></strong><br>
    <?= $alamat ?><br>
    <?= $tanggal ?>
</div>

<div class="line"></div>

<?php foreach ($items as $item): ?>
    <div>
        <?= $item['nama'] ?><br>
        <?= $item['qty'] ?> x <?= rupiah($item['harga']) ?>
        <span style="float:right"><?= rupiah($item['qty'] * $item['harga']) ?></span>
    </div>
<?php endforeach; ?>

<div class="line"></div>

<table>
    <tr>
        <td>Total</td>
        <td class="right"><?= rupiah($total) ?></td>
    </tr>
    <tr>
        <td>Bayar</td>
        <td class="right"><?= rupiah($bayar) ?></td>
    </tr>
    <tr>
        <td>Kembali</td>
        <td class="right"><?= rupiah($kembali) ?></td>
    </tr>
</table>

<div class="line"></div>

<div class="center">
    Terima kasih 💜
</div>

<!-- AUTO PRINT & AUTO CLOSE -->
<script>
    window.onload = function () {
        setTimeout(() => {
            window.print();
        }, 300);
    };

    window.onafterprint = function () {
        window.close();
    };
</script>

</body>
</html>
