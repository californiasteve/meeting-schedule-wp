# MeetingSchedule.app for WordPress

A WordPress plugin that puts the [MeetingSchedule.app](https://meetingschedule.app/) builder on any page. It pulls meetings from a BMLT server and turns them into print-ready schedules.

## Install

1. Download `meeting-schedule.zip` from the latest release, or build it (below).
2. In WordPress, go to **Plugins → Add New → Upload Plugin**, choose the zip and activate it.
3. Optional: under **Settings → Meeting Schedule**, enter the BMLT server and service body IDs that first-time visitors start with.
4. Add `[meeting_schedule]` to a page.

Each shortcode can override the settings:

```
[meeting_schedule server="https://example.org/main_server/" services="12,34" height="900px"]
```

## How it works

The builder is the same single-page app as meetingschedule.app (`meeting-schedule/app/index.html`). The shortcode shows it in a frame on your own site, so your theme's styles can't change it and printing prints only the schedule. It is opened with `?embed=1`, which hides online accounts (those live on meetingschedule.app) and points the Guide link to meetingschedule.app. Each visitor's settings are kept in their own browser.

## Updating the app

The app comes from the main repository, [SaaS-Meeting-Schedule](https://github.com/californiasteve/SaaS-Meeting-Schedule). To pick up its latest version and build the zip:

```
./build.sh ../SaaS-Meeting-Schedule
```

Raise the version in `meeting-schedule/meeting-schedule.php` (header and `MEETING_SCHEDULE_VERSION`) and `readme.txt` (`Stable tag`) when you release, so browsers load the new app instead of a cached one.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
