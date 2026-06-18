/**
 * Event timestamps arrive as UTC ISO-8601 strings. We render them in the
 * viewer's own timezone (the browser default) so "global" events read sensibly
 * for whoever is looking, and label them as local time where it matters.
 */

const dateFmt = new Intl.DateTimeFormat(undefined, {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: 'numeric',
});

const timeFmt = new Intl.DateTimeFormat(undefined, {
    hour: '2-digit',
    minute: '2-digit',
});

const dayHeadingFmt = new Intl.DateTimeFormat(undefined, {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
});

const relativeFmt = new Intl.RelativeTimeFormat(undefined, { numeric: 'auto' });

export function formatDate(iso: string): string {
    return dateFmt.format(new Date(iso));
}

export function formatTime(iso: string): string {
    return timeFmt.format(new Date(iso));
}

export function formatDateTime(iso: string): string {
    return `${formatDate(iso)} · ${formatTime(iso)}`;
}

export function formatDayHeading(iso: string): string {
    return dayHeadingFmt.format(new Date(iso));
}

/** Stable per-day key in the viewer's local timezone (YYYY-MM-DD). */
export function localDayKey(iso: string): string {
    const d = new Date(iso);
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');

    return `${y}-${m}-${day}`;
}

/** Human-friendly distance to the event, e.g. "in 3 days" / "tomorrow". */
export function relativeToNow(iso: string): string {
    const diffMs = new Date(iso).getTime() - Date.now();
    const diffDays = Math.round(diffMs / 86_400_000);

    if (Math.abs(diffDays) >= 1) {
        return relativeFmt.format(diffDays, 'day');
    }

    const diffHours = Math.round(diffMs / 3_600_000);

    if (Math.abs(diffHours) >= 1) {
        return relativeFmt.format(diffHours, 'hour');
    }

    const diffMinutes = Math.round(diffMs / 60_000);

    return relativeFmt.format(diffMinutes, 'minute');
}

export function formatPrice(currency: string, min: number | null): string {
    if (min === null) {
        return '—';
    }

    if (min === 0) {
        return 'Free';
    }

    try {
        return new Intl.NumberFormat(undefined, {
            style: 'currency',
            currency,
        }).format(min);
    } catch {
        return `${currency} ${min.toFixed(2)}`;
    }
}
