# TrainBasher WP Plugins

Custom WordPress plugins built for [trainbasher.com](https://trainbasher.com/) — the excellent bus photography archive.

These plugins add powerful search/filtering and collection statistics to turn your large sighting archive into a more usable, engaging, and data-rich experience for bus enthusiasts.

## Documentation

**Full Wiki documentation is available here:**

- [Wiki Home](https://github.com/kentish121-afk/trainbasher-wp-plugins/wiki)
- [Bus Search Pro](https://github.com/kentish121-afk/trainbasher-wp-plugins/wiki/Bus-Search-Pro)
- [Collection Stats Dashboard](https://github.com/kentish121-afk/trainbasher-wp-plugins/wiki/Collection-Stats-Dashboard)

> **Tip:** The `wiki/` folder in this repo contains the source Markdown files. Copy them into the GitHub Wiki editor for the best experience.

## Plugins Included

### 1. Bus Search Pro (`bus-search/`)

**Advanced searchable database for bus sightings.**

- Structured meta fields for Operator, Fleet Number, Registration, Spotted Date, Location
- One-click migration tool to structure your existing hundreds/thousands of posts from title patterns
- Beautiful AJAX-powered public search form with filters
- Meta box on post editor
- Shortcode: `[trainbasher_bus_search]`

### 2. Collection Stats Dashboard (`stats-dashboard/`)

**Beautiful stats and insights into your bus photography collection.**

- Total sightings, unique buses, operators covered
- Top operators leaderboard
- Monthly activity chart
- Public shortcode `[trainbasher_stats]`

## Installation

1. Download or clone this repository.
2. Upload the desired plugin folder(s) to your `wp-content/plugins/` directory.
3. Activate the plugin(s) in WordPress admin.
4. For **Bus Search**:
   - Go to **Settings → TrainBasher Search**
   - Click **Migrate Existing Posts**
   - Create a page and add `[trainbasher_bus_search]`
5. For **Stats**:
   - View in **Tools → TrainBasher Stats** or use the shortcode

## Requirements
- WordPress 6.0+
- PHP 7.4+

## License
GPLv2 or later

## Author
Built by Grok (xAI) for the owner of trainbasher.com