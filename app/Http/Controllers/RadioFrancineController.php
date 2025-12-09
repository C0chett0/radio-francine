<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class RadioFrancineController extends Controller
{
    public function nowPlaying()
    {
        $baseUrl = rtrim(Config::get('services.azuracast.base_url'), '/');
        $shortcode = Config::get('services.azuracast.stations.francine.shortcode', 'francine');

        try {
            $response = Http::timeout(5)
                ->get("{$baseUrl}/api/nowplaying/{$shortcode}");

            $response->throw();
        } catch (RequestException $e) {
            return response()->json([
                'message' => 'Impossible de récupérer les informations depuis AzuraCast.',
            ], 502);
        }

        $data = $response->json();

        // Format standard AzuraCast: station, now_playing, song_history, ...
        $stationName = data_get($data, 'station.name', 'Radio Francine');

        $np = data_get($data, 'now_playing', null);
        $npSong = $np ? data_get($np, 'song', []) : [];

        $nowPlaying = $np ? [
            'artist'      => data_get($npSong, 'artist'),
            'title'       => data_get($npSong, 'title'),
            'artwork_url' => data_get($npSong, 'art'),
            'duration'    => data_get($np, 'duration'),   // en secondes
            'elapsed'     => data_get($np, 'elapsed'),    // en secondes
            'played_at'   => data_get($np, 'played_at'),  // timestamp UNIX
        ] : null;

        $history = collect(data_get($data, 'song_history', []))
            ->map(function (array $entry) {
                $song = data_get($entry, 'song', []);

                return [
                    'artist'      => data_get($song, 'artist'),
                    'title'       => data_get($song, 'title'),
                    'artwork_url' => data_get($song, 'art'),
                    'played_at'   => data_get($entry, 'played_at'), // UNIX
                ];
            })
            ->values();

        return response()->json([
            'station_name' => $stationName,
            'stream' => [
                'mp3' => 'https://cast.cochet.cloud/listen/francine/radio.mp3',
                'hls' => 'https://cast.cochet.cloud/hls/francine/live.m3u8',
            ],
            'now_playing' => $nowPlaying,
            'history'     => $history,
        ]);
    }
}
