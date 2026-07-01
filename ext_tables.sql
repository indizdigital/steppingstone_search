#
# Table structure for table 'tx_phiindexedsearch_domain_model_fulltext'
#
CREATE TABLE tx_phiindexedsearch_domain_model_fulltext (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
	subtitle varchar(255) DEFAULT '' NOT NULL,
	alltext text NOT NULL,
	pagelink text NOT NULL,
	taskuid varchar(255) DEFAULT '' NOT NULL,
	chdate int(11) unsigned DEFAULT '0' NOT NULL,
	recorduid int(11) unsigned DEFAULT '0' NOT NULL,
	recordpid int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	fe_group varchar(255) DEFAULT '' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),

 KEY language (l10n_parent,sys_language_uid)

);
#
# Table structure for table 'tx_phiindexedsearch_domain_model_wordlist'
#
CREATE TABLE tx_phiindexedsearch_domain_model_searchwordlist (
	word varchar(255) DEFAULT '' NOT NULL,

	PRIMARY KEY (word)

);
