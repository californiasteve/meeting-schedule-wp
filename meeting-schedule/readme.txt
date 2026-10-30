=== MeetingSchedule.app ===
Contributors: californiasteve
Tags: bmlt, meetings, schedule, print, narcotics anonymous
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Build printable meeting schedules from a BMLT server on any page of your site.

== Description ==

MeetingSchedule.app pulls meetings from a BMLT server and turns them into a print-ready schedule: trifolds, quad folds, booklets and wall lists, with your own cover, sections, languages and formatting. This plugin puts the builder on your own site.

* Add `[meeting_schedule]` to any page or post.
* Under Settings → Meeting Schedule, choose the BMLT server and service bodies a first-time visitor starts with, so your area's meetings load straight away.
* Print, save as PDF, or export to Word and Excel, all in the visitor's browser.
* Each visitor's layout is remembered in their own browser.

Online accounts and share links are part of meetingschedule.app and are not included in the plugin.

== Installation ==

1. Upload the `meeting-schedule` folder to `/wp-content/plugins/`, or upload the zip under Plugins → Add New → Upload Plugin.
2. Activate the plugin.
3. Go to Settings → Meeting Schedule and enter your BMLT server and service body IDs (optional).
4. Add `[meeting_schedule]` to a page.

== Frequently Asked Questions ==

= Can one page use a different server or area? =

Yes. Each shortcode can override the settings: `[meeting_schedule server="https://example.org/main_server/" services="12,34" height="900px"]`.

= Where do I find service body IDs? =

In the builder, pick your service body on the Meetings tab, or ask your BMLT server administrator. The ID is the number BMLT uses for the area or region.

= Does it send data anywhere? =

The builder runs in the visitor's browser. It reads meetings from the BMLT server you choose, and loads the Word and Excel export libraries from cdnjs.cloudflare.com only when someone exports.

== Changelog ==

= 1.0.0 =
* First release.
