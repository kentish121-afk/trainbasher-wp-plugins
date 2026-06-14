# Collection Stats Dashboard Plugin

**Version:** 1.0.0  
**GitHub Issue:** [#4](https://github.com/kentish121-afk/trainbasher-wp-plugins/issues/4)

The **Collection Stats Dashboard** provides beautiful, shareable insights into the scale and activity of your bus photography archive.

## Features

- **Key Metrics Cards**
  - Total Sightings
  - Unique Buses (deduplicated by registration)
  - Operators Covered
  - Sightings This Month

- **Top Operators Leaderboard** — Table showing the most photographed operators.

- **Monthly Activity Chart** — Interactive line/bar chart of sightings over the last 12 months (powered by Chart.js).

- **Admin Dashboard** — Full page under **Tools → TrainBasher Stats**.

- **Dashboard Widget** — Quick overview on the WordPress admin homepage.

- **Public Shortcode** — `[trainbasher_stats]` — Embed the full stats display on any page.

## Installation & Setup

1. Upload the `stats-dashboard` folder to `wp-content/plugins/`.
2. Activate the plugin.
3. View stats immediately in **Tools → TrainBasher Stats** or the dashboard widget.
4. (Recommended) Add the shortcode to a public page:
   ```
   [trainbasher_stats]
   ```

## How the Data Works

The plugin reads from the same custom meta fields used by the **Bus Search Pro** plugin (`_tb_operator`, `_tb_reg`, `_tb_spotted_date`, etc.).

For the richest and most accurate stats:
- Run the **Migration Tool** in the Bus Search plugin first.
- Fill in the meta box when creating new posts.

## Public Display

Add the shortcode to any page or post to show visitors impressive numbers and charts. Great for an "About the Archive" or "Stats" page.

## Caching

Stats are cached for 6 hours for performance. They update automatically as you publish new sightings.

## Tips

- Combine with the search plugin for the best experience.
- The monthly chart is a nice visual way to show consistent activity.
- Great content for social media ("We've now photographed X unique buses!").

## Future Enhancements

Possible future additions (tracked on GitHub):
- More detailed breakdowns (by region, vehicle type)
- Year-over-year comparisons
- "Milestones" achievements

See the GitHub Issue for the latest discussion.