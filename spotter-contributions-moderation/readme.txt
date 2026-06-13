=== Spotter Contributions & Moderation (GDPR & OSA Compliant) ===
Contributors: trainbasher.com
Tags: user-generated-content, moderation, gdpr, online-safety-act, bus-photography, community
Requires at least: 6.0
Tested up to: 6.5
Stable tag: 1.1.0
License: GPLv2 or later

== Description ==

v1.1.0 - Full-featured, compliance-first plugin for spotter submissions on bus/vehicle photography sites.

New in v1.1.0:
- AJAX autocomplete search for linking to existing Vehicle/Bus posts (your custom CPTs)
- Auto-attach submissions to vehicle posts OR auto-create new vehicle posts on approval
- Self-service user dashboard shortcode with GDPR data export (JSON) and deletion request forms
- Advanced flagging with categories (Spam, Inappropriate, Harmful/Illegal, etc.) + basic banned-word content filtering
- Public [top_spotters_leaderboard] shortcode
- Integration hooks & filters for Post Views Counter, Related Posts, importers, and your other plugins

Core features retained from v1.0: Consent logging, age declaration, moderation queue, reputation system.

== Installation ==
1. Upload folder to wp-content/plugins/
2. Activate
3. Use shortcodes on pages
4. Configure in Settings & Compliance

== Shortcodes ==
- [spotter_submission_form] — Public submission form with AJAX vehicle search
- [spotter_user_dashboard] — Logged-in users: view submissions + GDPR tools
- [top_spotters_leaderboard limit="10"] — Public top contributors

== Changelog ==
= 1.1.0 =
* Major enhancements as requested: AJAX search, auto-attach/create, user GDPR dashboard, advanced flagging + filtering, leaderboard shortcode, integration hooks.
= 1.0.0 =
* Initial release with core compliance and moderation features.