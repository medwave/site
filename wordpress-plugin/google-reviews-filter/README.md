# Google Reviews Filter

A lightweight WordPress plugin that showcases your Google Business reviews and lets visitors filter them by **Good** or **Bad**.

Reviews are added manually — there's no Google API key, no Google Cloud project, and no billing account required. You copy each review's text, rating, and reviewer name from your Google Business Profile once, and it lives on your own site from then on.

## Installation

1. Zip the `google-reviews-filter` folder (or download the release zip).
2. In WordPress admin, go to **Plugins → Add New → Upload Plugin**.
3. Upload the zip and click **Activate**.

## Adding reviews

1. In WordPress admin, go to **Reviews → Add New** (look for the star icon in the left sidebar).
2. Set the **Title** to the reviewer's name.
3. Fill in the **Star Rating** and **Review Text** fields in the "Review Details" box.
4. Optionally set a **Featured Image** as their profile photo.
5. Optionally set the **Published** date (in the Publish box, top right) to match the actual review date — this controls the display order and the "X days ago" text.
6. Click **Publish**.

Repeat for each review you want to show. Go to **Settings → Google Reviews** to choose which star rating counts as "Good" (default: 4+ stars) — anything below is labeled "Bad".

## Usage

Add the shortcode to any page, post, or widget:

```
[google_reviews]
```

Optional attributes:

- `limit="10"` — maximum number of reviews to display.
- `default_filter="good"` — which filter (`all`, `good`, or `bad`) is active when the page loads. Defaults to `good` so visitors see your best reviews first.

Example:

```
[google_reviews default_filter="all" limit="5"]
```

Visitors see filter buttons (All / Good / Bad) above the review list and can toggle between them instantly — no page reload.

## Notes

- Deleting the plugin removes its one settings option but leaves your added reviews in place, so reinstalling later won't lose anything.
- To update a review later, edit it under **Reviews** in the admin sidebar like any other post.
