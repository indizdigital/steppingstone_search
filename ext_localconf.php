<?php

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'PhiIndexedsearch',
	'Indexedsearch',
	array(
		Phi\PhiIndexedsearch\Controller\FulltextController::class => 'search'

	),
	// non-cacheable actions
	array(
		Phi\PhiIndexedsearch\Controller\FulltextController::class => 'search',
	)
);

// Add scheduler
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Phi\PhiIndexedsearch\Task\SearchCrawlerTask::class] = array(
        'extension' => 'phi_indexedsearch',
        'title' => 'LLL:EXT:phi_indexedsearch/Resources/Private/Language/locallang.xlf:tx_phiindexedsearch.tasktitle',
        'description' => 'LLL:EXT:phi_indexedsearch/Resources/Private/Language/locallang.xlf:tx_phiindexedsearch.taskdescription',
        'additionalFields' => \Phi\PhiIndexedsearch\Task\SearchCrawlerTaskAdditionalFieldProvider::class
);

?>
