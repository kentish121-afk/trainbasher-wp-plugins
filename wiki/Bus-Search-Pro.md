# Bus Search Pro Plugin

**Version:** 1.0.0  
**GitHub Issue:** [#3](https://github.com/kentish121-afk/trainbasher-wp-plugins/issues/3)

The **Bus Search Pro** plugin turns your large collection of bus photography posts into a fast, filterable database that visitors (and you) will love.

## Features

- **Structured Data** — Custom meta fields for Operator, Fleet Number, Registration, Spotted Date, and Location.
- **Powerful Search Form** — AJAX-powered filters including partial matches on registration and fleet numbers.
- **Migration Tool** — One-click tool that parses existing post titles and populates the structured fields automatically (batched for safety).
- **Editor Meta Box** — Easy sidebar form when editing posts for new sightings.
- **Shortcode** — `[trainbasher_bus_search]` — Embed the full search experience anywhere.
- **Related Sightings** — Automatically shows "Other sightings of this bus" on single post pages (when registration matches).
- **Responsive & Fast** — Clean grid results with thumbnails.

## Installation & Setup

1. Upload the `bus-search` folder to `wp-content/plugins/`.
2. Activate the plugin.
3. Go to **Settings → TrainBasher Search**.
4. Click **Start Migration** (recommended — processes your existing posts in batches).
5. Create a new page (e.g. "Find a Bus") and add the shortcode:
   ```
   [trainbasher_bus_search]
   ```
6. (Optional) Add the page to your navigation menu.

## Using the Search Form

Visitors can filter by:
- Operator (partial match)
- Fleet Number
- Registration (very useful for exact or partial plate searches)
- Date From / To
- General keyword search

Results appear instantly with thumbnails, key details, and links to the full post.

## Migration Tool

The migration tool uses smart regex patterns to extract data from common title formats like:
- `National Express West Midlands · 4773 BV57XKT · Jan 30, 2026`

It safely updates post meta without overwriting manual entries.

You can run it multiple times safely.

## Shortcode

```markdown
[trainbasher_bus_search]
```

Place this on any page or post.

## Tips for Best Results

- Fill in the **Bus Sighting Details** meta box when creating new posts.
- Run the migration tool after bulk importing old content.
- The plugin works alongside your existing categories (region/operator).

## Future Ideas (tracked in Issues)

- More advanced same-bus history linking
- Export results to CSV
- Integration with Flickr

See the main GitHub Issue for updates and feedback.