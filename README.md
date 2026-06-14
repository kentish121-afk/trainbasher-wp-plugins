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

---

## Non-Standard / Custom Plugins from Live Site

The following plugins are **installed and active** on the live trainbasher.com WordPress site but are **not from standard or well-known publishers** (Automattic, Yoast, Google, dFactory, ShortPixel, WPDeveloper, BracketSpace, Viper007Bond, Xylus Themes, AdminColumns.com, WebFactory Ltd, CookieYes, etc.).

They are custom / site-specific plugins (authors include "Your Name", "trainbasher.com", "Grok", "Grok-assisted", "Royal Plugins"). These have been prepared with placeholder entry-point files in this monorepo for **version control, backup, and easy management** (e.g. via the installed Git Updater plugin).

**Full original source code lives in `wp-content/plugins/[slug]/` on the production server.** The placeholders below are starting points — sync the complete folders from the live site to have the real functionality under version control.

| Plugin Name | Version | Author | Suggested Slug / Dir | GitHub Location | Status |
|-------------|---------|--------|----------------------|-----------------|--------|
| Auto Livery Post Starter | 1.0.0 | Your Name | auto-livery-post-starter | auto-livery-post-starter/ | Placeholder added |
| BusTimes.org Vehicle Importer | 2.1.8 | trainbasher.com | bustimes-org-vehicle-importer | bustimes-org-vehicle-importer/ | Placeholder added |
| Bus Types Importer (as Tags) | 0.3.0 | Your Name | bus-types-importer-as-tags | bus-types-importer-as-tags/ | Placeholder added |
| Bus Vehicle Info Pro + Live Journeys | 1.0.2 | trainbasher.com | bus-vehicle-info-pro-live-journeys | bus-vehicle-info-pro-live-journeys/ | Placeholder added |
| European Vehicle Info | 2.4.0 | Your Name | european-vehicle-info | european-vehicle-info/ | Placeholder added |
| Mass Undo Last Revision | 1.1 | Grok-assisted | mass-undo-last-revision | mass-undo-last-revision/ | Placeholder added |
| Multiple Permalinks + Disambiguation | 1.3.0 | trainbasher.com | multiple-permalinks-disambiguation | multiple-permalinks-disambiguation/ | Placeholder added |
| Royal MCP – Secure AI Connector for Claude, ChatGPT & Gemini | 1.4.27 | Royal Plugins | royal-mcp | royal-mcp/ | Placeholder added |
| Smart Related Posts | 1.0.0 | Grok | smart-related-posts | smart-related-posts/ | Placeholder added |

**Already in this repo (may provide overlapping or additional custom functionality):** `bulk-data-sync-reconciliation/`, `bus-search/`, `seo-schema-enhancer-vehicles/`, `spotter-contributions-moderation/`, `stats-dashboard/`, `vehicle-data-dashboard/`

### How to Complete the Full Export / Sync

1. **Clone the repo locally**:
   ```bash
   git clone https://github.com/kentish121-afk/trainbasher-wp-plugins.git
   cd trainbasher-wp-plugins
   ```
2. **Copy the real plugin code from the live site** (using SFTP, SSH/rsync, or your hosting file manager):
   - Locate each plugin in `wp-content/plugins/[slug]/` on trainbasher.com
   - Copy the entire folder (including all .php, assets/, includes/, etc.) into the matching subdirectory here.
3. **Commit and push the full code**:
   ```bash
   git add .
   git commit -m "Export full custom plugin code from live trainbasher.com site (vX.Y.Z)"
   git push origin main
   ```
4. **(Optional but recommended)** Tag the release to match the live version for history:
   ```bash
   git tag v2.1.8-bustimes-org-vehicle-importer
   git push origin v2.1.8-bustimes-org-vehicle-importer
   ```
5. On the live site, you can now point **Git Updater** to these GitHub paths for seamless updates without manual uploads.

This process ensures all your custom development work is safely backed up, versioned, auditable, and maintainable on GitHub!

*Exported with assistance from Grok on 2026-06-14. Full code sync recommended for production fidelity.*