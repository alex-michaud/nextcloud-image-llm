window.addEventListener('DOMContentLoaded', function() {
	$('#saveSettings').click(function() {
		const apiKey = $('#apiKey').val()
		const apiUrl = $('#apiUrl').val()
		$('#settingsMsg').text('Saving...')
		$.post(
			OC.generateUrl('/apps/archives_analyzer/settings/save'),
			{ apiKey, apiUrl },
		).done(function(data) {
			$('#settingsMsg').text('Settings saved!')
		}).fail(function(jqXHR) {
			$('#settingsMsg').text('Failed to save settings: ' + jqXHR.responseText)
		})
	})
})
