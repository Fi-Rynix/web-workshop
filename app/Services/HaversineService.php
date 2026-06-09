<?php

namespace App\Services;

/**
 * Service untuk menghitung jarak antara dua koordinat
 * di permukaan bumi menggunakan rumus Haversine.
 *
 * Rumus Haversine menghitung jarak great-circle antara dua titik
 * pada permukaan bola (bumi), dengan asumsi bumi berbentuk
 * bola sempurna dengan radius R = 6.371.000 meter.
 */
class HaversineService
{
    /**
     * Radius bumi dalam meter.
     */
    private const EARTH_RADIUS_M = 6371000;

    /**
     * Hitung jarak (dalam meter) antara dua koordinat lintang/bujur.
     *
     * @param float $lat1 Lintang titik pertama (derajat)
     * @param float $lng1 Bujur titik pertama (derajat)
     * @param float $lat2 Lintang titik kedua (derajat)
     * @param float $lng2 Bujur titik kedua (derajat)
     * @return float Jarak dalam meter
     */
    public static function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_M * $c;
    }
}
