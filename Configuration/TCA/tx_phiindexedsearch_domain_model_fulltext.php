<?php
return array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:phi_indexedsearch/Resources/Private/Language/locallang_db.xlf:tx_phiindexedsearch_domain_model_fulltext',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,

		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'title,subtitle,alltext,pagelink,',
		'security' => [
			'ignorePageTypeRestriction' => true
		],
		'iconfile' => 'phi_indexedsearch/Resources/Public/Icons/tx_phiindexedsearch_domain_model_fulltext.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, title, subtitle, alltext, pagelink,recorduid,recordpid,taskuid,chdate,fe_group',
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, title, subtitle, alltext, pagelink,recorduid,recordpid,taskuid,chdate,fe_group --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, starttime, endtime'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(

		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0)
				),
			),
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_phiindexedsearch_domain_model_fulltext',
				'foreign_table_where' => 'AND tx_phiindexedsearch_domain_model_fulltext.pid=###CURRENT_PID### AND tx_phiindexedsearch_domain_model_fulltext.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),

		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),

		'title' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:phi_indexedsearch/Resources/Private/Language/locallang_db.xlf:tx_phiindexedsearch_domain_model_fulltext.title',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'subtitle' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:phi_indexedsearch/Resources/Private/Language/locallang_db.xlf:tx_phiindexedsearch_domain_model_fulltext.subtitle',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'alltext' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:phi_indexedsearch/Resources/Private/Language/locallang_db.xlf:tx_phiindexedsearch_domain_model_fulltext.alltext',
			'config' => array(
				'type' => 'text',
				'cols' => 40,
				'rows' => 15,
				'eval' => 'trim'
			)
		),
		'pagelink' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:phi_indexedsearch/Resources/Private/Language/locallang_db.xlf:tx_phiindexedsearch_domain_model_fulltext.pagelink',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'recorduid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:phi_indexedsearch/Resources/Private/Language/locallang_db.xlf:tx_phiindexedsearch_domain_model_fulltext.recorduid',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'taskuid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:phi_indexedsearch/Resources/Private/Language/locallang_db.xlf:tx_phiindexedsearch_domain_model_fulltext.taskuid',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'recordpid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:phi_indexedsearch/Resources/Private/Language/locallang_db.xlf:tx_phiindexedsearch_domain_model_fulltext.recordpid',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'chdate' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:phi_indexedsearch/Resources/Private/Language/locallang_db.xlf:tx_phiindexedsearch_domain_model_fulltext.tstamp',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'readOnly' => 1,
			),
		),
		'fe_group' => [
				'exclude' => true,
				'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.fe_group',
				'config' => [
						'type' => 'select',
						'renderType' => 'selectMultipleSideBySide',
						'size' => 7,
						'maxitems' => 20,
						'items' => [
								[
										'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.hide_at_login',
										-1
								],
								[
										'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.any_login',
										-2
								],
								[
										'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.usergroups',
										'--div--'
								]
						],
						'exclusiveKeys' => '-1,-2',
						'foreign_table' => 'fe_groups',
						'foreign_table_where' => 'ORDER BY fe_groups.title',
						'enableMultiSelectFilterTextfield' => true
				]
		],

	),
);
