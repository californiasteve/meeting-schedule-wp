# MeetingSchedule.app for WordPress

A WordPress plugin for [MeetingSchedule.app](https://meetingschedule.app/). The builder runs in wp-admin, and a shortcode gives visitors the schedule as a PDF, Word or Excel file, with live meetings from your BMLT server.

## Install

1. Download `meeting-schedule.zip` from the latest release, or build it (below).
2. In WordPress, go to **Plugins > Add New > Upload Plugin**, choose the zip and activate it.
3. Open **Meeting Schedule** in the admin menu, build the schedule and click **Save for downloads**.
4. Add a shortcode to a page:

```
[meeting_schedule]                    PDF, Word and Excel buttons
[meeting_schedule format="pdf"]       just one button: pdf, word or excel
[meeting_schedule format="word" label="Download the meeting list"]
```

## How it works

The builder is the same single-page app as meetingschedule.app (`meeting-schedule/app/index.html`), opened with `?embed=1` in a frame on the admin page. **Save for downloads** stores its settings in the `meeting_schedule_schedule` option through admin-ajax (nonce plus `manage_options`).

On the public side, `assets/public.js` opens the same app off screen when someone clicks a button. It calls `MeetingSchedule.output(settings, format)`, which loads live meetings without touching the visitor's own saved settings. PDF opens the print dialog with the finished schedule. Word and Excel come back as files and are downloaded. This needs app version 1.1.0 or later (SaaS-Meeting-Schedule PR #28).

## Updating the app

The app comes from the main repository, [SaaS-Meeting-Schedule](https://github.com/californiasteve/SaaS-Meeting-Schedule). To pick up its latest version and build the zip:

```
./build.sh ../SaaS-Meeting-Schedule
```

Raise the version in `meeting-schedule/meeting-schedule.php` (header and `MEETING_SCHEDULE_VERSION`) and `readme.txt` (`Stable tag`) when you release, so browsers load the new app instead of a cached one.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
