<?php

use App\Support\CityDirectory;
use App\Support\ReverseGeocoder;

it('snaps coordinates to the nearest known city', function () {
    // A point jittered slightly off the New York anchor.
    $result = ReverseGeocoder::resolve(40.73, -74.01);

    expect($result)->not->toBeNull()
        ->and($result['city'])->toBe('New York')
        ->and($result['country'])->toBe('United States')
        ->and($result['label'])->toBe('New York, United States');
});

it('resolves a southern-hemisphere city correctly', function () {
    $result = ReverseGeocoder::resolve(-33.87, 151.21);

    expect($result['city'])->toBe('Sydney');
});

it('returns null when coordinates are missing', function () {
    expect(ReverseGeocoder::resolve(null, null))->toBeNull();
});

it('exposes unique slugs for every city', function () {
    $slugs = array_column(CityDirectory::all(), 'slug');

    expect($slugs)->toHaveCount(count(array_unique($slugs)));
});
