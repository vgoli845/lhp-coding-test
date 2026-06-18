<?php

namespace App\Support;

/**
 * Offline reverse geocoder: turns a latitude/longitude into a human-readable
 * "City, Country" by snapping to the nearest known city anchor.
 *
 * Distance uses a cheap equirectangular approximation, which is more than
 * accurate enough at city scale and avoids the cost of a full haversine for
 * every row on a page.
 */
final class ReverseGeocoder
{
    /**
     * @return array{city: string, country: string, label: string}|null
     */
    public static function resolve(?float $lat, ?float $lng): ?array
    {
        if ($lat === null || $lng === null) {
            return null;
        }

        $nearest = null;
        $nearestDistance = INF;

        foreach (CityDirectory::all() as $city) {
            $dLat = $lat - $city['lat'];
            // Scale longitude by the latitude band so degrees compare fairly.
            $dLng = ($lng - $city['lng']) * cos(deg2rad($lat));
            $distance = ($dLat * $dLat) + ($dLng * $dLng);

            if ($distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearest = $city;
            }
        }

        if ($nearest === null) {
            return null;
        }

        return [
            'city' => $nearest['city'],
            'country' => $nearest['country'],
            'label' => "{$nearest['city']}, {$nearest['country']}",
        ];
    }
}
