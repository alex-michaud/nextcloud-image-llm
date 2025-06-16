window.addEventListener('DOMContentLoaded', function() {
	const saveButton = document.getElementById('saveSettings')
	if (saveButton) {
		saveButton.addEventListener('click', function() {
			const apiKey = document.getElementById('apiKey').value
			const apiUrl = document.getElementById('apiUrl').value
			const ollamaModel = document.getElementById('ollamaModel').value
			document.getElementById('settingsMsg').textContent = 'Saving...'
			fetch(OC.generateUrl('/apps/archives_analyzer/settings/save'), {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					requesttoken: OC.requestToken,
				},
				body: JSON.stringify({ apiKey, apiUrl, ollamaModel }),
			})
				.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok ' + response.statusText)
					}
					return response.json()
				})
				.then(data => {
					document.getElementById('settingsMsg').textContent = t('archives_analyzer', 'Settings saved!')
				})
				.catch(error => {
					document.getElementById('settingsMsg').textContent = 'Failed to save settings: ' + error.message
				})
		})
	}
})
