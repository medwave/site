# Google Reviews Filter

A lightweight WordPress plugin that pulls in your Google Business Profile reviews and lets visitors filter them by **Good** or **Bad**.

## Installation

1. Zip the `google-reviews-filter` folder (or download the release zip).
2. In WordPress admin, go to **Plugins → Add New → Upload Plugin**.
3. Upload the zip and click **Activate**.

## Setup

1. **Get a Google Places API key**
   - Go to the [Google Cloud Console](https://console.cloud.google.com/).
   - Create (or select) a project, then enable the **Places API**.
   - Create an API key under **APIs & Services → Credentials**.
   - Restrict the key to the Places API and, ideally, to your server's IP for security.
2. **Find your Google Place ID**
   - Use Google's [Place ID Finder](https://developers.google.com/maps/documentation/places/web-service/place-id) and search for your business.
3. In WordPress, go to **Settings → Google Reviews** and enter your API key and Place ID.
4. Choose the star rating that should count as "Good" (default: 4+ stars). Anything below is labeled "Bad".

## Usage

Add the shortcode to any page, post, or widget:

```
[google_reviews]
```

Optional attributes:

- `limit="10"` — maximum number of reviews to display.
- `default_filter="good"` — which filter (`all`, `good`, or `bad`) is active when the page loads.

Example:

```
[google_reviews default_filter="good" limit="5"]
```

Visitors see filter buttons (All / Good / Bad) above the review list and can toggle between them instantly — no page reload.

## Notes & limitations

- Google's Places API returns a **maximum of 5 reviews** per business, chosen by Google's own algorithm — not necessarily the newest, highest, or lowest rated. This is a Google API limitation, not something this plugin can change.
- Reviews are cached (default: 12 hours, configurable) to avoid unnecessary API calls and stay within Google's usage quota/billing.
- Use **Settings → Google Reviews → Clear Cached Reviews Now** to force an immediate refresh.
- The Places API is a paid Google Cloud service; Google provides a monthly free credit that comfortably covers typical small-business traffic.
