<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MslController extends Controller
{
    public function getMatches(Request $request)
    {
        // 1. Ambil parameter filter dari user (default: upcoming)
        // Options: 'upcoming', 'this_week', 'all'
        $type = $request->query('type', 'upcoming');
        
        // 2. Konfigurasi Dasar API
        $url = env('MSL_SUPABASE_URL') . '/match';
        $apiKey = env('MSL_SUPABASE_KEY');
        
        // Parameter wajib (select relationship & competition_id)
        // Note: Kita decode URL-nya agar lebih mudah dibaca, Http Client akan encode otomatis nanti.
        $nowWib = Carbon::now('Asia/Jakarta');

        // Format tanggal Z (UTC)
        $formatDate = function ($carbonDate) {
            return $carbonDate->setTimezone('UTC')->format('Y-m-d\TH:i:s.v\Z');
        };

        // 1. Susun Query String secara MANUAL (String biasa)
        // PENTING: Jangan di-encode karakter (*), (,), (!)
        $queryString = "select=*,home_club:club!home_club_id(*),away_club:club!away_club_id(*)";
        $queryString .= "&competition_id=eq.6";
        $queryString .= "&order=matchday_at.asc";

        // 2. Tambahkan Logika Filter Waktu
        switch ($type) {
            case 'upcoming':
                $utcDate = $formatDate($nowWib);
                $queryString .= "&matchday_at=gte.{$utcDate}";
                break;

            case 'this_week':
                $startWeek = $formatDate($nowWib->copy()->startOfWeek());
                $endWeek = $formatDate($nowWib->copy()->endOfWeek());
                
                // Tempel dua parameter matchday_at
                $queryString .= "&matchday_at=gte.{$startWeek}&matchday_at=lte.{$endWeek}";
                break;

            case 'all':
                // Tidak ada filter tambahan
                break;
                
            default:
                 return response()->json(['message' => 'Filter type invalid'], 400);
        }

        try {
            // 3. Kirim Request dengan URL yang sudah jadi string utuh
            // Laravel Http Client tidak akan meng-encode ulang jika kita kirim full URL di parameter pertama
            $fullUrl = "{$url}?{$queryString}";

            $response = Http::withHeaders([
                'apikey' => $apiKey,
                'Authorization' => 'Bearer ' . $apiKey,
            ])->get($fullUrl);

            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'type' => $type,
                    'details_url' => $fullUrl,
                    'data' => $response->json()
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Fetch failed',
                'debug_url' => $fullUrl, // Cek URL di sini, harusnya bersih tanpa %28 %29
                'supabase_response' => $response->json()
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
