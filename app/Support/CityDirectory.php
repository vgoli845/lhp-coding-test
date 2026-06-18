<?php

namespace App\Support;

/**
 * A small, offline directory of the city anchors the seeded dataset is
 * generated around (see EventSeeder::CITY_ANCHORS). Each seeded event is
 * jittered by at most ~0.5° from one of these points, so a nearest-anchor
 * lookup reliably recovers a human-readable location with no external API.
 *
 * The same list powers the location filter on the browsing pages.
 */
final class CityDirectory
{
    /**
     * @var list<array{slug: string, city: string, country: string, lat: float, lng: float}>|null
     */
    private static ?array $cities = null;

    /**
     * @return list<array{slug: string, city: string, country: string, lat: float, lng: float}>
     */
    public static function all(): array
    {
        if (self::$cities !== null) {
            return self::$cities;
        }

        $raw = [
            // United States
            ['New York', 'United States', 40.7128, -74.0060],
            ['Los Angeles', 'United States', 34.0522, -118.2437],
            ['Chicago', 'United States', 41.8781, -87.6298],
            ['Houston', 'United States', 29.7604, -95.3698],
            ['Phoenix', 'United States', 33.4484, -112.0740],
            ['Philadelphia', 'United States', 39.9526, -75.1652],
            ['San Antonio', 'United States', 29.4241, -98.4936],
            ['San Diego', 'United States', 32.7157, -117.1611],
            ['Dallas', 'United States', 32.7767, -96.7970],
            ['San Jose', 'United States', 37.3382, -121.8863],
            ['Austin', 'United States', 30.2672, -97.7431],
            ['San Francisco', 'United States', 37.7749, -122.4194],
            ['Seattle', 'United States', 47.6062, -122.3321],
            ['Denver', 'United States', 39.7392, -104.9903],
            ['Boston', 'United States', 42.3601, -71.0589],
            ['Las Vegas', 'United States', 36.1699, -115.1398],
            ['Miami', 'United States', 25.7617, -80.1918],
            ['Atlanta', 'United States', 33.7490, -84.3880],
            ['Washington', 'United States', 38.9072, -77.0369],
            ['Nashville', 'United States', 36.1627, -86.7816],
            ['Portland', 'United States', 45.5152, -122.6784],
            ['New Orleans', 'United States', 29.9511, -90.0715],
            // Canada
            ['Toronto', 'Canada', 43.6532, -79.3832],
            ['Montreal', 'Canada', 45.5019, -73.5674],
            ['Vancouver', 'Canada', 49.2827, -123.1207],
            ['Calgary', 'Canada', 51.0447, -114.0719],
            ['Ottawa', 'Canada', 45.4215, -75.6972],
            ['Edmonton', 'Canada', 53.5461, -113.4938],
            ['Quebec City', 'Canada', 46.8139, -71.2080],
            ['Winnipeg', 'Canada', 49.8951, -97.1384],
            // Mexico
            ['Mexico City', 'Mexico', 19.4326, -99.1332],
            ['Guadalajara', 'Mexico', 20.6597, -103.3496],
            ['Monterrey', 'Mexico', 25.6866, -100.3161],
            ['Puebla', 'Mexico', 19.0414, -98.2063],
            ['Tijuana', 'Mexico', 32.5149, -117.0382],
            ['Cancún', 'Mexico', 21.1619, -86.8515],
            ['Mérida', 'Mexico', 20.9674, -89.5926],
            // Europe
            ['London', 'United Kingdom', 51.5074, -0.1278],
            ['Paris', 'France', 48.8566, 2.3522],
            ['Berlin', 'Germany', 52.5200, 13.4050],
            ['Madrid', 'Spain', 40.4168, -3.7038],
            ['Rome', 'Italy', 41.9028, 12.4964],
            ['Amsterdam', 'Netherlands', 52.3676, 4.9041],
            ['Barcelona', 'Spain', 41.3851, 2.1734],
            ['Munich', 'Germany', 48.1351, 11.5820],
            ['Milan', 'Italy', 45.4642, 9.1900],
            ['Vienna', 'Austria', 48.2082, 16.3738],
            ['Prague', 'Czechia', 50.0755, 14.4378],
            ['Lisbon', 'Portugal', 38.7223, -9.1393],
            ['Dublin', 'Ireland', 53.3498, -6.2603],
            ['Copenhagen', 'Denmark', 55.6761, 12.5683],
            ['Stockholm', 'Sweden', 59.3293, 18.0686],
            ['Oslo', 'Norway', 59.9139, 10.7522],
            ['Helsinki', 'Finland', 60.1699, 24.9384],
            ['Brussels', 'Belgium', 50.8503, 4.3517],
            ['Zurich', 'Switzerland', 47.3769, 8.5417],
            ['Warsaw', 'Poland', 52.2297, 21.0122],
            ['Budapest', 'Hungary', 47.4979, 19.0402],
            ['Athens', 'Greece', 37.9838, 23.7275],
            ['Lyon', 'France', 45.7640, 4.8357],
            ['Hamburg', 'Germany', 53.5511, 9.9937],
            ['Manchester', 'United Kingdom', 53.4808, -2.2426],
            ['Edinburgh', 'United Kingdom', 55.9533, -3.1883],
            ['Frankfurt', 'Germany', 50.1109, 8.6821],
            ['Kraków', 'Poland', 50.0647, 19.9450],
            ['Porto', 'Portugal', 41.1579, -8.6291],
            ['Naples', 'Italy', 40.8518, 14.2681],
            // Global hubs
            ['Tokyo', 'Japan', 35.6762, 139.6503],
            ['Seoul', 'South Korea', 37.5665, 126.9780],
            ['Singapore', 'Singapore', 1.3521, 103.8198],
            ['Sydney', 'Australia', -33.8688, 151.2093],
            ['Melbourne', 'Australia', -37.8136, 144.9631],
            ['Dubai', 'United Arab Emirates', 25.2048, 55.2708],
            ['São Paulo', 'Brazil', -23.5505, -46.6333],
            ['Buenos Aires', 'Argentina', -34.6037, -58.3816],
        ];

        $cities = [];
        foreach ($raw as [$city, $country, $lat, $lng]) {
            $cities[] = [
                'slug' => self::slug($city),
                'city' => $city,
                'country' => $country,
                'lat' => $lat,
                'lng' => $lng,
            ];
        }

        return self::$cities = $cities;
    }

    /**
     * Find a city in the directory by its slug.
     *
     * @return array{slug: string, city: string, country: string, lat: float, lng: float}|null
     */
    public static function find(string $slug): ?array
    {
        foreach (self::all() as $city) {
            if ($city['slug'] === $slug) {
                return $city;
            }
        }

        return null;
    }

    private static function slug(string $city): string
    {
        $ascii = (string) preg_replace('/[^a-z0-9]+/', '-', strtolower(self::ascii($city)));

        return trim($ascii, '-');
    }

    private static function ascii(string $value): string
    {
        return strtr($value, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ñ' => 'n', 'ã' => 'a', 'â' => 'a', 'ô' => 'o', 'ü' => 'u',
        ]);
    }
}
