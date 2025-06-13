import { generateUrl } from '@nextcloud/router'
import { showError, showInfo } from '@nextcloud/dialogs'
import { emit } from '@nextcloud/event-bus'
import { getLogger } from '@nextcloud/logger'

const logger = getLogger()

export const AnalyzeFile = function(file) {
	return fetch(
		generateUrl('/apps/archives_analyzer/analyze/markdown') + '?file=' + encodeURIComponent(file.path),
		{
			method: 'GET',
			headers: {
				Accept: 'application/json',
				requesttoken: OC.requestToken, // for CSRF protection if needed
			},
			credentials: 'same-origin',
		},
	)
		.then(response => response.json())
		.then(data => {
			logger.debug('AnalyzeController response', data)
			// Handle the response (show result, error, etc.)
			if (data.error) {
				showError('Analyze error: ' + data.error)
			} else {
				showInfo('Analysis complete :' + data.fileid)
				// refresh the file list or update UI as needed
				emit('files:node:updated', { fileid: parseInt(data.parentid) || null })
			}
		})
		.catch(error => {
			logger.error('AnalyzeController fetch error:', error)
			showError('Failed to analyze file: ' + error.message)
		})
}
