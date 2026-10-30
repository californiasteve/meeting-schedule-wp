/* Meeting Schedule admin page: saves the builder's settings to WordPress for the download shortcodes. */
(function () {
	var S = window.meetingScheduleAdmin, frame = document.getElementById('ms-builder'), status = document.getElementById('ms-status');
	var app = function () { return frame.contentWindow && frame.contentWindow.MeetingSchedule; };
	var say = function (text) { status.textContent = text; };
	var openSaved = function () { if (S.config && app()) { app().setConfig(JSON.parse(S.config)); say(S.text.opened); } };

	// The builder keeps its settings in this browser. In a browser that has none yet, start from the saved schedule.
	var fresh = true;
	try { fresh = !localStorage.getItem('sp-config'); } catch (e) {}
	frame.addEventListener('load', function () { if (fresh) openSaved(); });

	document.getElementById('ms-open').addEventListener('click', openSaved);
	document.getElementById('ms-save').addEventListener('click', function () {
		var btn = this;
		if (!app()) { say(S.text.failed); return; }
		var body = new URLSearchParams();
		body.append('action', 'meeting_schedule_save');
		body.append('_ajax_nonce', S.nonce);
		body.append('config', JSON.stringify(app().getConfig()));
		btn.disabled = true; say(S.text.saving);
		fetch(S.ajax, { method: 'POST', credentials: 'same-origin', body: body })
			.then(function (r) { return r.json(); })
			.then(function (j) {
				if (!j || !j.success) throw new Error();
				S.config = body.get('config');
				document.getElementById('ms-open').hidden = false;
				say(S.text.saved);
			})
			.catch(function () { say(S.text.failed); })
			.then(function () { btn.disabled = false; });
	});
})();
