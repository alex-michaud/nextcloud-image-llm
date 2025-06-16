import { translate as t } from '@nextcloud/l10n'
import { registerFileAction, FileAction } from '@nextcloud/files'
// import { emit } from '@nextcloud/event-bus'
// import { generateUrl } from '@nextcloud/router'
import { showError, showInfo } from '@nextcloud/dialogs'
import { getLogger } from '@nextcloud/logger'
// import svgBrain from '@mdi/svg/svg/brain.svg'
// import textBox from '@mdi/svg/svg/text-box.svg'
import headCheck from '@mdi/svg/svg/head-check.svg'
import headQuestion from '@mdi/svg/svg/head-question.svg'
import { AnalyzeFile } from './AnalyzeFile.js' // Assuming you have this function in a separate file

const logger = getLogger()

const convertToMarkdownAction = new FileAction({
	id: 'convert-to-markdown',
	displayName: () => t('archives_analyzer', 'Convert to Markdown'),
	iconSvgInline: () => {
		return headCheck
	},
	order: 1,
	enabled: (files) => {
		logger.debug('Checking if action is enabled', { filesCount: files.length })
		return files.length === 1 && files[0].mime.startsWith('image/')
		// return true
	},

	// The action to perform
	exec: (file) => {
		logger.debug('Executing action for file', {
			filePath: file.path,
			fileMime: file.mime,
		})
		const analyzeMessage = document.createElement('div')
		analyzeMessage.innerHTML = `<span class="icon-loading-small"></span><span>Analyzing file: ${file.path}</span>`
		const toastAnalyze = showInfo(analyzeMessage, {
			timeout: -1,
			isHTML: true,
		})

		AnalyzeFile(file).finally(() => {
			toastAnalyze.hideToast()
		})
	},

	execBatch: (fileList) => {
		if (fileList.length === 0) {
			showError(t('archives_analyzer', 'No files selected for analysis.'))
			return
		}
		for (const file of fileList) {
			// Check if the file is an image
			if (!file.mime.startsWith('image/')) {
				showError(t('archives_analyzer', 'Only image files can be analyzed.'))
				continue
			}
			const analyzeMessage = document.createElement('div')
			analyzeMessage.innerHTML = `<span class="icon-loading-small"></span><span>Analyzing file: ${file.path}</span>`
			const toastAnalyze = showInfo(analyzeMessage, {
				timeout: -1,
				isHTML: true,
			})

			AnalyzeFile(file).finally(() => {
				toastAnalyze.hideToast()
			})
		}
	},
})

const promptThenConvertToMarkdownAction = new FileAction({
	id: 'prompt-then-convert-to-markdown',
	displayName: () => t('archives_analyzer', 'Prompt and Convert to Markdown'),
	iconSvgInline: () => {
		return headQuestion
	},
	order: 2,
	enabled: (files) => {
		logger.debug('Checking if action is enabled', { filesCount: files.length })
		return files.length === 1 && files[0].mime.startsWith('image/')
	},

	exec: (file) => {
		const actionPrompt = prompt('Please enter your prompt for the AI analysis:', 'Describe the image content here...')
		if (!actionPrompt) {
			showError(t('archives_analyzer', 'No prompt provided. Action cancelled.'))
			return
		}

		logger.debug('Executing prompt then convert action for file', {
			filePath: file.path,
			fileMime: file.mime,
		})
		const analyzeMessage = document.createElement('div')
		analyzeMessage.innerHTML = `<span class="icon-loading-small"></span><span>Analyzing file with prompt: ${file.path}</span>`
		const toastAnalyze = showInfo(analyzeMessage, {
			timeout: -1,
			isHTML: true,
		})

		// Here you would implement the logic to prompt the user for input
		// and then call AnalyzeFile with that input.
		// For now, we just call AnalyzeFile directly.
		AnalyzeFile(file, actionPrompt).finally(() => {
			toastAnalyze.hideToast()
		})
	},
})

try {
	logger.debug('Creating file action')

	registerFileAction(convertToMarkdownAction)
	registerFileAction(promptThenConvertToMarkdownAction)

	logger.debug('--File action registered')
} catch (error) {
	logger.error('Archives Analyzer: Error registering file action:', error)
	showError('Failed to register file action: ' + error.message)
}
