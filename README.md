# Trainbasher.com WP Plugins

Custom WordPress plugins built for [trainbasher.com](https://trainbasher.com/) — the excellent bus photography archive.

These plugins add powerful search/filtering and collection statistics to turn your large sighting archive into a more usable, engaging, and data-rich experience for bus enthusiasts.

## Plugins Included

### 1. Bus Search Pro (`bus-search/`)

**Advanced searchable database for bus sightings.**

- Structured meta fields for Operator, Fleet Number, Registration, Spotted Date, Location
- One-click migration tool to structure your existing hundreds/thousands of posts from title patterns
- Beautiful AJAX-powered public search form with filters (Operator, Fleet, Reg, Date range, keyword)
- Meta box on post editor for easy structured data entry on new sightings
- "Other sightings of this bus" hints (basic)
- Shortcode: `[trainbasher_bus_search]`

**Why it's great for trainbasher.com**: Makes it easy for visitors to find specific buses by registration or fleet number without endless pagination.

### 2. Collection Stats Dashboard (`stats-dashboard/`)

**Beautiful stats and insights into your bus photography collection.**

- Total sightings, unique buses, operators covered
- Top operators leaderboard
- Monthly activity chart
- Sightings this month / year
- Public shortcode `[trainbasher_stats]` for a nice stats page or homepage section
- Admin dashboard widget + full admin page

**Why it's great**: Showcases the impressive scale of your work, provides fun insights, and creates shareable content.

## Installation

1. Download or clone this repository.
2. Upload the desired plugin folder(s) to your `wp-content/plugins/` directory (or use WP's upload plugin feature after zipping the folder).
3. Activate the plugin(s) in WordPress admin.
4. For **Bus Search**:
   - Go to **Settings → TrainBasher Search**
   - Click **Migrate Existing Posts** (recommended first step — it will parse your existing post titles and create structured meta data)
   - Create a new Page (e.g. "Bus Search") and add the shortcode `[trainbasher_bus_search]`
5. For **Stats Dashboard**:
   - Go to **Tools → TrainBasher Stats** (or view the widget on Dashboard)
   - Optionally add `[trainbasher_stats]` to any page

## Requirements
- WordPress 6.0+
- PHP 7.4+
- The plugins are designed to work alongside the Newsup theme (and most others)

## Development Notes
- Both plugins use the same custom post meta keys for compatibility:
  - `_tb_operator`
  - `_tb_fleet_no`
  - `_tb_reg`
  - `_tb_spotted_date`
  - `_tb_location`
- They are intentionally lightweight and follow WordPress coding standards.
- Future versions may add more features (advanced same-bus linking, interactive map, Flickr sync, etc.)

## License
GPLv2 or later

## Author
Built by Grok (xAI) for the owner of trainbasher.com

---

**Want more plugins?** Ideas like Interactive Map, Vehicle History Tracker, Quick Sighting Logger, or Flickr Sync are ready to be built next. Just let me know!

Repository created and maintained for kentish121-afk / trainbasher.com
