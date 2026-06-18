<?php

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function makeEvent(array $attributes = []): Event
{
    return Event::factory()->for(User::factory())->create($attributes);
}

it('returns enriched event cards', function () {
    makeEvent([
        'type' => 'concert',
        'status' => 'published',
        'created_time' => Carbon::parse('2031-01-02 19:00', 'UTC')->timestamp,
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'payload' => ['name' => 'Skyline Jazz Night'],
    ]);

    $this->getJson(route('events.browse'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [['id', 'name', 'starts_at', 'image', 'location', 'price' => ['currency', 'min']]],
            'current_page',
            'has_more',
            'stats' => ['ms', 'count'],
        ])
        ->assertJsonPath('data.0.name', 'Skyline Jazz Night')
        ->assertJsonPath('data.0.location', 'New York, United States')
        ->assertJsonPath('data.0.image', '/images/events/concert.svg');
});

it('hides draft events from the public browser', function () {
    makeEvent(['status' => 'published', 'payload' => ['name' => 'Public One']]);
    makeEvent(['status' => 'draft', 'payload' => ['name' => 'Hidden Draft']]);

    $this->getJson(route('events.browse'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Public One');
});

it('filters by city using a bounding box', function () {
    makeEvent(['status' => 'published', 'latitude' => 40.71, 'longitude' => -74.0, 'payload' => ['name' => 'NYC Event']]);
    makeEvent(['status' => 'published', 'latitude' => 35.68, 'longitude' => 139.65, 'payload' => ['name' => 'Tokyo Event']]);

    $this->getJson(route('events.browse', ['city' => 'new-york']))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'NYC Event');
});

it('filters by date range against the event start', function () {
    makeEvent(['status' => 'published', 'created_time' => Carbon::parse('2030-06-15', 'UTC')->timestamp, 'payload' => ['name' => 'In Range']]);
    makeEvent(['status' => 'published', 'created_time' => Carbon::parse('2030-09-01', 'UTC')->timestamp, 'payload' => ['name' => 'Out Of Range']]);

    $this->getJson(route('events.browse', ['from' => '2030-06-01', 'to' => '2030-06-30']))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'In Range');
});
