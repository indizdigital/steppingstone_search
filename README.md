# SEARCH

## Scheduler Config		
table = tt_content
fields = bodytext
titleField = {pagetitle}
pid = 39
languageSupport = 1
groupByPid = 1
rootPageId = 1
field2LanguageLabelMapper = motherofpearldial=1:tx_phiwatchcollection_domain_model_item.mop,chronograph=1:tx_phiwatchcollection_domain_model_item.chronograph,moonphase=1:tx_phiwatchcollection_domain_model_item.moonphase
extensionName = phi_watchcollection
addWhere = pid IN (SELECT uid FROM pages WHERE pid=39)
#pidMap = 39:3,45:9
#extPrefix = tx_phicontentcontainer_contentcontainer
#action = show
#controller = item
#entity = item
#relationField = contents
#relationType = commaseparated
#relationTable = tt_content
#relationFields = bodytext
#relationTitleField = title
#relationAddWhere = datum < UNIX_TIMESTAMP()
#relationContentPage = 0
#relationContentRelationField = contents
#relationContentRelationType = commaseparated
#relationContentRelationTable = tt_content
#relationContentRelationFields = bodytext
#relationContentRelationAddWhere = 1
#relationAdditionalLinkParams = cardcontent = {uid}';