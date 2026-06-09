<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sales Kunjungan Toko Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk fitur sales kunjungan toko. Threshold adalah batas
    | radius (dalam meter) yang digunakan untuk memvalidasi kunjungan sales.
    |
    | Rumus: threshold_efektif = jarak_aktual + accuracy_toko + accuracy_sales
    | Laporan diterima jika jarak_aktual <= threshold_efektif.
    |
    */

    'radius_threshold' => env('SALES_RADIUS_THRESHOLD', 500),
];
