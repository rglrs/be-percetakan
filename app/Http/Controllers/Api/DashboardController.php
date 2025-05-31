<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        try {
            // Menghitung "Pesanan Hari Ini" (status: menunggu, dibuat hari ini)
            $pesananHariIni = Order::where('status', 'menunggu')
                ->whereDate('created_at', Carbon::today())
                ->count();

            // Menghitung "Dalam Produksi" (status: diproses)
            $dalamProduksi = Order::where('status', 'diproses')->count();

            // Menghitung "Siap Kirim" (status: selesai)
            $siapKirim = Order::where('status', 'selesai')->count();

            // Menyiapkan data untuk response
            $data = [
                'pesanan_hari_ini' => $pesananHariIni,
                'dalam_produksi'  => $dalamProduksi,
                'siap_kirim'      => $siapKirim,
            ];

            return response()->json([
                'success' => true,
                'data'    => $data,
                'message' => 'Statistik dashboard berhasil diambil.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil statistik.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
