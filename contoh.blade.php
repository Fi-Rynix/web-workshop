<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
@page {
    size: 210mm 165mm;
    margin: 0;
}

* {
    box-sizing: border-box;
}

body {
    font-family: sans-serif;
    margin: 0;
    padding: 0;
    width: 210mm;
    height: 165mm;
}

.label-wrapper {
    position: relative;
    width: 210mm;
    height: 165mm;
    overflow: hidden;
}

.label-box {
    position: absolute;
    width: 38mm;
    height: 18mm;
    /* border: 1px dashed #ccc; */
    text-align: center;
    overflow: hidden;
    box-sizing: border-box;
    /* Hapus semua flex & padding, ganti dengan ini: */
    display: table;
}

/* Tambah class baru ini */
.label-content {
    display: table-cell;
    vertical-align: middle;
    text-align: center;
    width: 38mm;
    height: 18mm;
}

.nama-barang {
    font-size: 11px;
    font-weight: bold;
    display: block;
    margin-bottom: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 36mm;
}

.harga-barang {
    font-size: 12px;
    font-weight: bold;
    display: block;
}
</style>
</head>
<body>

@php
    $labelWidth  = 38; // mm
    $labelHeight = 18; // mm
    $gapX        = 3;  // jarak antar label horizontal
    $gapY        = 2;  // jarak antar label vertikal
    $cols        = 5;
    $rows        = 8;

    // Hitung margin agar label center di kertas
    // Total lebar konten: (5 × 38) + (4 × 3) = 190 + 12 = 202mm
    // Sisa: 210 - 202 = 8mm → margin kiri & kanan masing-masing 4mm
    $marginLeft  = 4;

    // Total tinggi konten: (8 × 18) + (7 × 2) = 144 + 14 = 158mm
    // Sisa: 165 - 158 = 7mm → margin atas ~3.5mm, bulatkan 3mm
    $marginTop   = 3.5;
@endphp

<div class="label-wrapper">
    @for ($row = 0; $row < $rows; $row++)
        @for ($col = 0; $col < $cols; $col++)
            @php
                $index = ($row * $cols) + $col;
                $left  = $marginLeft + ($col * ($labelWidth + $gapX));
                $top   = $marginTop  + ($row * ($labelHeight + $gapY));
            @endphp
            <div class="label-box" style="left: {{ $left }}mm; top: {{ $top }}mm;">
                @if(isset($labels[$index]) && $labels[$index])
                <div class="label-content">
                    <span class="nama-barang">{{ $labels[$index]->nama_barang }}</span>
                    <span class="harga-barang">Rp {{ number_format($labels[$index]->harga) }}</span>
                </div>
                @endif
            </div>
        @endfor
    @endfor
</div>

</body>
</html>