export interface EventPrice {
    currency: string;
    min: number | null;
}

export interface EventCard {
    id: string;
    name: string;
    description: string;
    type: string;
    status: string;
    starts_at: string;
    ends_at: string;
    image: string | null;
    venue: string | null;
    city: string | null;
    country: string | null;
    location: string | null;
    latitude: number | null;
    longitude: number | null;
    price: EventPrice;
}

export interface EventDetail extends EventCard {
    images: string[];
    organizer: string | null;
    capacity: number | null;
    tags: string[];
    attendees_count: number;
}

export interface CityOption {
    slug: string;
    label: string;
}

export interface EventFilterOptions {
    cities: CityOption[];
    types: string[];
    statuses: string[];
}

export interface EventFilters {
    from: string;
    to: string;
    city: string;
    type: string;
    status: string;
}
