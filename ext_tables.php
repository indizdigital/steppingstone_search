<?php
defined('TYPO3') || die('Access denied.');

call_user_func(
    function()
    {


			\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
				'PhiIndexedsearch',
				'Indexedsearch',
				'Phi Indexed Search'
			);

				\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('phi_indexedsearch', 'Configuration/TypoScript', 'Phi Indexed Search');

    }
);
