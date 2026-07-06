<?php

// =========================
// DISKON OTOMATIS (KUIS)
// =========================
if (!function_exists('hitung_diskon')) {

    function hitung_diskon($total)
    {
        $total = (float) $total;

        $persen = 0;

        if ($total >= 50000000) {
            $persen = 20;
        } elseif ($total >= 25000000) {
            $persen = 12;
        } elseif ($total >= 15000000) {
            $persen = 7;
        } elseif ($total >= 5000000) {
            $persen = 3;
        }

        $diskon = ($persen / 100) * $total;

        return [
            'persen' => $persen,
            'diskon' => (float) $diskon
        ];
    }
}


// =========================
// DISKON VOUCHER
// =========================
if (!function_exists('hitung_diskon_voucher')) {

    function hitung_diskon_voucher($subtotal, $code)
    {
        $subtotal = (float) $subtotal;
        $code = strtoupper(trim($code));

        $voucher = [
            'PROMO2025'  => 10000,
            'PROMO2026'  => 15000,
            'AKHIRTAHUN' => 25000,
        ];

        $diskon = $voucher[$code] ?? 0;

        // OPTIONAL: kalau mau aman, jangan sampai diskon > subtotal
        if ($diskon > $subtotal) {
            $diskon = $subtotal;
        }

        return (float) $diskon;
    }
}


// =========================
// BIAYA JASA
// =========================
if (!function_exists('hitung_biaya_jasa')) {

    function hitung_biaya_jasa($subtotal)
    {
        $subtotal = (float) $subtotal;

        if ($subtotal >= 10000000) {
            return 50000;
        } elseif ($subtotal >= 5000000) {
            return 25000;
        }

        return 10000;
    }
}


// =========================
// FREE MOUSE PROMO
// =========================
if (!function_exists('hitung_free_mouse')) {

    function hitung_free_mouse($subtotal)
    {
        $subtotal = (float) $subtotal;

        if ($subtotal >= 20000000) {
            return 50000;
        }

        return 0;
    }
}