=== MeetingSchedule.app ===
Contributors: californiasteve
Tags: bmlt, meetings, schedule, print, narcotics anonymous
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Build a printable meeting schedule from a BMLT server in wp-admin and let visitors download it as PDF, Word or Excel.

== Description ==

MeetingSchedule.app pulls meetings from a BMLT server and turns them into a print-ready schedule: trifolds, quad folds, booklets and wall lists, with your own cover, sections, languages and formatting.

* Build the schedule under Meeting Schedule in wp-admin, then click Save for downloads.
* Add `[meeting_schedule]` to any page for PDF, Word and Excel buttons.
* `[meeting_schedule format="pdf"]`, `format="word"` or `format="excel"` shows a single button. Add `label="..."` to change its text.
* Visitors always get the latest meetings: the file is made in their browser from your saved layout and the meetings on the BMLT server at that moment.
* PDF opens the print dialog with the finished schedule, where visitors choose Save as PDF or print it.

Online accounts and share links are part of meetingschedule.app and are not included in the plugin.

== Installation ==

1. Upload the `meeting-schedule` folder to `/wp-content/plugins/`, or upload the zip under Plugins > Add New > Upload Plugin.
2. Activate the plugin.
3. Open Meeting Schedule in the admin menu, build your schedule and click Save for downloads.
4. Add `[meeting_schedule]` to a page.

== Frequently Asked Questions ==

= I changed the schedule but the downloads didn't change =

The builder keeps your work in your own browser until you click Save for downloads. Meeting changes on the BMLT server show up on their own.

= I'm on a different computer and the builder is empty =

If this browser has no schedule yet, the builder opens the saved one. Otherwise click Open the saved schedule.

= Does it send data anywhere? =

Everything runs in the browser. It reads meetings from your BMLT server, and loads the Word and Excel libraries from cdnjs.cloudflare.com only when someone downloads those files.

== Changelog ==

= 1.1.0 =
* The builder moves to wp-admin. The shortcode now shows PDF, Word and Excel download buttons for the saved schedule (format="pdf|word|excel").
* Settings from 1.0 are no longer used.

= 1.0.0 =
* First release.
