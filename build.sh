#!/bin/sh
# Copies the app from the main MeetingSchedule.app repository and builds meeting-schedule.zip.
# Usage: ./build.sh [path to the SaaS-Meeting-Schedule checkout]
set -e
SRC="${1:-../SaaS-Meeting-Schedule}"
cp "$SRC/app/index.html" meeting-schedule/app/index.html
rm -f meeting-schedule.zip
zip -qr meeting-schedule.zip meeting-schedule -x '*.DS_Store'
echo "Built meeting-schedule.zip from $SRC/app/index.html"
