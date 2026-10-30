/* [meeting_schedule] download buttons. The schedule is made in the visitor's browser by the builder app, opened
   out of sight on this site with the settings saved in wp-admin and the meetings on the BMLT server right now. */
(function () {
	var S = window.meetingSchedule, frame = null, ready = null;

	function app() {
		if (!ready) {
			frame = document.createElement('iframe');
			frame.title = 'Meeting schedule';
			frame.setAttribute('aria-hidden', 'true');
			frame.tabIndex = -1;
			// printing needs a real page size, so it sits off screen rather than display:none
			frame.style.cssText = 'position:fixed;left:-10000px;top:0;width:1100px;height:850px;border:0';
			ready = new Promise(function (resolve, reject) {
				frame.onload = function () { frame.contentWindow.MeetingSchedule ? resolve(frame.contentWindow.MeetingSchedule) : reject(new Error()); };
				frame.onerror = reject;
			});
			frame.src = S.app;
			document.body.appendChild(frame);
		}
		return ready;
	}

	function save(file) {
		var a = document.createElement('a');
		a.href = URL.createObjectURL(file.blob); a.download = file.name;
		document.body.appendChild(a); a.click(); a.remove();
		setTimeout(function () { URL.revokeObjectURL(a.href); }, 10000);
	}

	document.addEventListener('click', function (e) {
		var btn = e.target.closest && e.target.closest('.meeting-schedule-download');
		if (!btn || btn.disabled) return;
		var box = btn.closest('.meeting-schedule-downloads'), status = box && box.querySelector('.meeting-schedule-status');
		var label = btn.textContent;
		btn.disabled = true; btn.textContent = S.text.preparing;
		if (status) status.textContent = '';
		app()
			.then(function (ms) { return ms.output(S.config, btn.getAttribute('data-format')); })
			.then(function (file) { if (file) save(file); })
			.catch(function (err) { if (status) status.textContent = (err && err.message) || S.text.failed; })
			.then(function () { btn.disabled = false; btn.textContent = label; });
	});
})();
