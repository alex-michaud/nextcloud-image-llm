<?php
return [
	'routes' => [
		[
			'name' => 'settings#save',
			'url' => '/settings/save',
			'verb' => 'POST'
		],
		[
			'name' => 'analyze#markdown',
			'url' => '/analyze/markdown',
			'verb' => 'GET'
		]
	]
];
