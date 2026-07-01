<?php
namespace Phi\PhiIndexedsearch\Task;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Extbase\Annotation\Inject;
use \TYPO3\CMS\Extbase\Object\ObjectManager;
use \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

class SearchCrawlerTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask {

    /**
     * An additional indexing config
     *
     * @var string $conf
     */
    public $conf;

    /**
     * Associated Array of Config
     *
     * @var string $conf
     */
    public $assocConf;

    /**
     * Internal Linebreak Tag
     *
     * @var string $lineBreak
     */
    public $lineBreak = "#BR#";

    /**
     * Internal EndOfTag-Tag
     *
     * @var string $endOfTag
     */
    public $endOfTag = "#ET#";

    /**
     * Fulltext Repository
     *
     * @var \Phi\PhiIndexedsearch\Domain\Repository\FulltextRepository $fulltextRepository
     */
    public $fulltextRepository;

    /**
     * $pidList
     *
     * @var \array $pidList
     */
    public $pidList;

    /**
     * rootline
     *
     * @var \array rootline
     */
    public $rootline;

    /**
     * $replaceVars
     *
     * @var \array $replaceVars
     */
    public $replaceVars;

    /**
     * $languageKeys
     *
     * @var \array $languageKeys
     */
    public $languageKeys;

    /**
     * $languageLabels
     *
     * @var \array $languageLabels
     */
    public $languageLabels;

    /**
    * $fulltextTable
    *
    * @var \string $fulltextTable
    */
    public $fulltextTable = "tx_phiindexedsearch_domain_model_fulltext";


    /**
     * cObj
     *
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj
     */
    public $cObj;

	public function execute() {
			//...
			$this->parseConfig();
			$this->loadLanguages();

		//	$this->loadCObject();
			$this->eraseData();
			$this->loadAndWriteData();
			return true;
	}

	/**
	* parse the config string into an array with key=>values
	*
	* @return void
	*/
	public function parseConfig(){

		$config = explode(PHP_EOL,trim($this->conf));

		foreach($config as $c){
			$key = trim(substr($c,0,strpos($c,"=")));
			$val = trim(substr($c,strpos($c,"=")+1));
			if(strpos($key,"#") === false){
				if(in_array($key,array("pidMap","field2LanguageLabelMapper"))){
					$keyArray = explode(",",$val);
					foreach($keyArray as $v){
						$map = explode(":",$v);
						$this->assocConf[$key][$map[0]] = $map[1];

					}
				}else{
					$this->assocConf[$key] = $val;
				}
			}
		}
	}

	/**
	* erase the search Index
	*
	* @return void
	*/
	public function eraseData(){

    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->fulltextTable);
    $queryBuilder
      ->delete($this->fulltextTable)
       ->where(
          $queryBuilder->expr()->eq('taskuid', $queryBuilder->createNamedParameter($this->getTaskUid()))
       )
       ->executeStatement();
	}

	/**
	* loadLanguages
	*
	* @return void
	*/
	public function loadLanguages(){
		//languages fixe
		$available_languages = [1=>"fr",2=>"en",3=>"it"];
    

		$languageFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Localization\LocalizationFactory::class);


		$this->languageKeys[0] = "default";
		$lang = $languageFactory->getParsedData('EXT:phi_indexedsearch/Resources/Private/Language/locallang.xlf',"default");
		$this->languageLabels["default"] = $lang["default"];

		foreach($available_languages as $uid=>$langcode){
			$this->languageKeys[$uid] = $langcode;

			if(isset($this->assocConf["field2LanguageLabelMapper"])){
				if(!isset($this->assocConf["extensionName"])){
					echo "Please set 'extensionName' in the config to load the language Files for the 'field2LanguageLabelMapper' option";
					exit;
				}
				$lang = $languageFactory->getParsedData('EXT:phi_indexedsearch/Resources/Private/Language/'.$langcode.'.locallang.xlf',$langcode);

				$this->languageLabels[$langcode] = $lang[$langcode];
			}
		}

	}

	/**
	* loadPid
	*
	* @param \int $uid
	* @param \array $excludePages
	* @return void
	*/
	public function loadPid($uid,$excludePages){
    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');

	$constrains = [
		$queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($uid))
	];
	if(!empty($excludePages)){
		$constrains[] = $queryBuilder->expr()->notIn('uid', $queryBuilder->createNamedParameter($excludePages,Connection::PARAM_INT_ARRAY));
	}
    $queryBuilder = $queryBuilder
       ->select('uid')
       ->from('pages')
       ->where(
          ...$constrains
       );

    $statement = $queryBuilder->executeQuery();
		//$addWhere = $this->getEnableFields();
		//$res = $GLOBALS['TYPO3_DB']->sql_query("SELECT uid FROM pages WHERE ". $addWhere . " AND pid = " . $uid);
		$uids = array($this->assocConf["rootPageId"]);
		while($row = $statement->fetchAssociative()){
			$this->pidList[] = $row["uid"];
			$this->loadPid($row["uid"],$excludePages);
		}
	}

	/**
	* overloadMappedFields
	*
	* @param \array $row
	* @return void
	*/
	public function overloadMappedFields(&$row){
		if(isset($this->assocConf["field2LanguageLabelMapper"])){
      die("field2LanguageLabelMapper  is deprecated");
			foreach($this->assocConf["field2LanguageLabelMapper"] as $condition=>$key){
				$keyset = $this->languageLabels[$this->languageKeys[$row["sys_language_uid"]]][$key];
				$mappedVal = "";
				if(isset($keyset[0])){
					$mappedVal = $keyset[0]["target"];
				}elseif(isset($this->languageLabels["default"][$key][0])){
					$mappedVal = $this->languageLabels["default"][$key][0]["target"];
				}else{
					echo "key '" . $key . "' is not set in the language file of the extension " . $this->assocConf["extensionName"];exit;
				}
				list($field,$value) = explode("=",$condition);

				if($row[$field] == $value){
					$row[$field] = $mappedVal;
				}elseif($row[$field] == '0'){
					$row[$field] = '';
				}

			}

		}
	}

	/**
	* parse the config string into an array with key=>values
	*
	* @return void
	*/
	public function loadAndWriteData(){

		$searchTitle = $this->assocConf["titleField"];
		$excludePages = $this->checkConfValue("excludePageIds")?explode(",",$this->assocConf["excludePageIds"]):[];

		$addWhere = $this->getEnableFields();

		if(isset($this->assocConf["rootPageId"])){
			$this->pidList[] = $this->assocConf["rootPageId"];
			$this->loadPid($this->assocConf["rootPageId"],$excludePages);

		}

		//load all records
		//$res = $GLOBALS['TYPO3_DB']->sql_query("SELECT * FROM " . $this->assocConf["table"] ." WHERE ". $addWhere);
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->assocConf["table"]);
		$queryBuilder
         ->select('*')
         ->from($this->assocConf["table"])
         ->where(
            $queryBuilder->expr()->in('pid', $queryBuilder->createNamedParameter($this->pidList,Connection::PARAM_INT_ARRAY))
         );
      	if($this->checkConfValue("addWhere")){
           $andWhereClauses = explode("AND",$this->assocConf["addWhere"]);
           $queryClauses = [];
           foreach($andWhereClauses as $whereClause){
             $clause = explode(":",$whereClause);
             switch(strtolower($clause[0])){
                case 'in':
                  $queryClauses[] = $queryBuilder->expr()->in($clause[1], $queryBuilder->createNamedParameter(GeneralUtility::intExplode(',', $clause[2], true),Connection::PARAM_INT_ARRAY));
                  break;
                case 'eq':
                  $queryClauses[] = $queryBuilder->expr()->eq($clause[1], $queryBuilder->createNamedParameter($clause[2]));
                  break;

             }
           }

           $queryBuilder = $queryBuilder->andWhere(...$queryClauses);
         }

        $fields = explode(",",$this->assocConf["fields"]);

         $objectfields = (isset($this->assocConf["objectfields"]) && strlen($this->assocConf["objectfields"]))?explode(",",$this->assocConf["objectfields"]):[];
         $multivalObjectfields = (isset($this->assocConf["multivalObjectfields"]) && strlen($this->assocConf["multivalObjectfields"]))?explode(",",$this->assocConf["multivalObjectfields"]):[];

        if($this->checkConfValue("dataMapper")){


           $dataMapper = GeneralUtility::makeInstance(ObjectManager::class)->get(DataMapper::class);
           $rows = $dataMapper->map(
              $this->assocConf["dataMapper"],
               $queryBuilder->executeQuery()->fetchAllAssociative()
           );
           foreach($rows as $row){
             $valArray = $this->getObjectsTexts($row,$fields,$objectfields,$multivalObjectfields);

             $this->proceedObjectRow($valArray,$row);
           }
        }else{
           $statement = $queryBuilder->executeQuery();
       			while($row = $statement->fetchAssociative()){
                $this->proceedRow($row,$fields);
         		}
        }


	}

  public function proceedRow($row,$fields){

       $alltext = "";
       $langUid = isset($row["sys_language_uid"])?$row["sys_language_uid"]:0;
	   
      $page = $this->getPage($row["pid"],$langUid);
		if(!is_array($page)){
			return;
		}
      $this->overloadMappedFields($row);
      //$this->loadReplaceVars();

      if(isset($row["CType"]) && $row["CType"] == "shortcut" ){
        $alltext = $this->loadShortCuts($fields,$row["records"]);
      }else{
        $alltext = $this->getTexts($fields,$row);
      }

      if(isset($this->assocConf["relationContentPage"]) && $this->assocConf["relationContentPage"] == '1'){
        $this->loadRelations($row);
      }else{
		$searchTitleVal = $this->getTitle($page,$row,$langUid);
		
        $plinkConfArray = $this->getPageLinkConfArray($row["uid"],$row["pid"],$row);
		 //if no link possible, dont add the row to the results
		if(empty($plinkConfArray)){
			return;
		}
		
        if($page["doktype"] == 3){
          if(!isset($page["url"]) || !isset($page["url"]) || !isset($page["target"])){
            die("Please add 'urltype,url,target' to [FE][addRootLineFields] in the install tool");
          }

          if(strlen($page["target"])){
              $plinkConfArray["target"] = $page["target"];
          }
        }
        $fe_group = isset($row["fe_group"])?$row["fe_group"]:"";

        $insertVals = array(
          "pid"=>$this->assocConf["pid"],
          "alltext"=>$alltext,
          "title"=>$searchTitleVal,
          "pagelink"=>json_encode($plinkConfArray),
          "sys_language_uid"=>$langUid,
          "recorduid"=>$row["uid"],
          "recordpid"=>$row["pid"],
          "taskuid"=>$this->getTaskUid(),
          "chdate"=>$row["tstamp"],
          "fe_group"=>$fe_group
        );
		

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->fulltextTable);
        $affectedRows = $queryBuilder
           ->insert($this->fulltextTable)
           ->values(
             $insertVals
            )
           ->executeStatement();
          if($affectedRows == 0){
            echo "SQL Error in Search Crawler Task";
            exit;
          }

      }
  }

  public function getTitle($page,$row,$langUid){
	$searchTitle = $this->assocConf["titleField"];
	$searchTitleVal = "";
	//if titleFieldForce is set get the entity which is related
	if($this->checkConfValue("titleFieldForce")){
		$row = $this->getEntityUid($row,$this->assocConf["titleFieldForce"],false);
	}

	//first condition is title of the pid of the record
	//second is an attribute of the object
	//third is the tile of the pidMap'ed page
	if($searchTitle == '{pagetitle}' && is_array($page)){
		$searchTitleVal = $page["title"];
	}elseif(is_array($row) && isset($row[$searchTitle])){
		$searchTitleVal = $row[$searchTitle];
	}elseif($searchTitle == '{pidMap}'){
		$title_page = $this->getPage($this->assocConf["pidMap"][$row["pid"]],$langUid);
		$searchTitleVal = $title_page["title"];
	}
	return $searchTitleVal;
  }

  public function proceedObjectRow($valArray,$object){
      $fe_group = 0;
      if($object->getFegroup()){
        $fe_group = $object->getFegroup()->getUid();
      }
      eval('$searchTitleVal = $object->get'.$this->assocConf["titleField"].'();');

      $alltext = implode(" ",$valArray);

      $sys_language_uid = 0;
      if(is_callable([$object,"getSysLanguageUid"])){
          $sys_language_uid = $object->getSysLanguageUid();
      }

      $plinkConfArray = $this->getPageLinkConfArray($object->getUid(),$object->getPid(),[]);
	  //if no link possible, dont add the row to the results
	  if(empty($plinkConfArray)){
		return;
	  }
        $insertVals = array(
          "pid"=>$this->assocConf["pid"],
          "alltext"=>$alltext,
          "title"=>$searchTitleVal,
          "pagelink"=>json_encode($plinkConfArray),
          "sys_language_uid"=>$sys_language_uid,
          "recorduid"=>$object->getUid(),
          "recordpid"=>$object->getPid(),
          "taskuid"=>$this->getTaskUid(),
          "chdate"=>$object->getTstamp(),
          "fe_group"=>$fe_group
        );

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->fulltextTable);
        $affectedRows = $queryBuilder
           ->insert($this->fulltextTable)
           ->values(
             $insertVals
            )
           ->executeStatement();
          if($affectedRows == 0){
            echo "SQL Error in Search Crawler Task";
            exit;
          }

  }

	/**
	* loads the custom vars from the abstract of the page
	*
	* @return void
	*/
	public function loadReplaceVars(){
			$sr = array();
			$vars = array();
			sort($this->rootline);
			foreach($this->rootline as $page){
				$vars = array_merge($vars,explode(PHP_EOL,$page["abstract"]));
			}
			$search = $replace = array();
			foreach($vars as $line){
				$s = trim(substr($line,0,strpos($line,"=")));
				$r =trim(substr($line,strpos($line,"=")+1));
				$sr[$s] =$r;
			}
			$this->replaceVars["search"] = array_keys($sr);
			$this->replaceVars["replace"] = array_values($sr);

	}


  /**
  * gets the text from an objected Row
  *
  * @param \array $row
  * @param \array $fields
  * @param \array $objectfields
  * @param \array $multivalObjectfields
  * @return void
  */
  public function getObjectsTexts($row,$fields,$objectfields,$multivalObjectfields){
    $indexedVals = [];
    foreach($fields as $field){

     eval ('$val = $row->get' . ucfirst($field)."();");
     $indexedVals[] = $val;

    }
    foreach($objectfields as $oField){

     preg_match("/(.+)?\[(.+)??\]/",$oField,$matches);
     $attrs = explode("|",$matches[2]);
     eval ('$object = $row->get' . $matches[1]."();");
     if($object){
       foreach($attrs as $attr){
         eval ('$val = $object->get' . $attr."();");
         $indexedVals[] = $val;
       }
     }

    }
    foreach($multivalObjectfields as $oField){

     preg_match("/(.+)?\[(.+)??\]/",$oField,$matches);
     $attrs = explode("|",$matches[2]);
     eval ('$storageObject = $row->get' . $matches[1]."();");
     foreach($storageObject as $object){

       foreach($attrs as $attr){
         eval ('$val = $object->get' . $attr."();");
         $indexedVals[] = $val;
       }
     }

    }
    return $indexedVals;
  }
	/**
	* gets the text from a record
	*
	* @param \array $fields
	* @param \array $row
	* @return void
	*/
	public function getTexts($fields,$row){

		$alltext = "";
		foreach($fields as $field){
			$v = $row[$field];
			if(strpos($field,":") !== false){
				$v = "";
				$fieldObj = explode(":",$field);
				$field = $fieldObj[0];
				$subfields = explode("|",$fieldObj[1]);

				foreach($subfields as $subfield){
					if(strpos($subfield,"{") === false){
						$startfield = '<field index="'.$subfield.'">';
						if(strpos($row[$field],$startfield) !== false){
							$st = strpos($row[$field],$startfield) + strlen($startfield);

							$end = strpos($row[$field],'</field>',($st < strlen($row[$field])?$st:0));
							$l = $end - $st;

							$v .= trim(substr($row[$field],$st,$l)) ." ";
						}
					}else{
						$subfieldsstart = strpos($subfield,"{") + 1;
						$subfieldsend = strpos($subfield,"}");
						$subsubfields = substr($subfield,$subfieldsstart,$subfieldsend - $subfieldsstart);
						$subsubfields = explode(";",$subsubfields);
						$subsubParts = array();
						foreach($subsubfields as $subsubfield){
							$substrOffset = 0;
							$startfield = '<field index="'.$subsubfield.'">';
							while(strpos($row[$field],$startfield,$substrOffset) !== false){
								$st = strpos($row[$field],$startfield,$substrOffset) + strlen($startfield);
								$end = strpos($row[$field],'</field>',($st < strlen($row[$field])?$st:0));
								$l = $end - $st;
								$subsubParts[$subsubfield][] = trim(substr($row[$field],$st,$l));

								$substrOffset = $end;
							}
						}
						$indexArray = array_shift($subsubParts);
						for($in = 0; $in < count($indexArray); $in++){
							$v .= $indexArray[$in] . " ";
							foreach($subsubParts as $subsubfieldArray){
								$v .= $subsubfieldArray[$in] . " ";
							}
						}
					}
				}

			}
			$p = trim(strip_tags(html_entity_decode($v)));
			if(strlen($p)){
				$alltext .= $p . $this->lineBreak;
			}
		}
		return $alltext;

	}

	/**
	* loads the current page title from the pages table > or page overlay!!
	*
	* @param \array $fields
	* @param \string $records
	* @return void
	*/
	public function loadShortCuts($fields,$records){

		if($this->assocConf["table"] == "tt_content"){
			$records = str_replace("tt_content_","",$records);
		}else{
			echo "Insert records is not yet implemented in the Search Crawler Task for table ".$this->assocConf["table"];exit;
		}

    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->assocConf["table"]);

    $statement = $queryBuilder
       ->select('*')
       ->from($this->assocConf["table"])
       ->where(
          $queryBuilder->expr()->in('uid', $queryBuilder->createNamedParameter(
            GeneralUtility::intExplode(',', $records, true),
            Connection::PARAM_INT_ARRAY)
          )
       )
       ->executeQuery();

		//$res = WHERE uid IN (". $records .")");
		$alltext = "";

		while($row = $statement->fetchAssociative()){

			if($row["CType"] == "shortcut"){
				$alltext .= $this->loadShortCuts($fields,$row["records"]);
			}else{
				$alltext .= $this->getTexts($fields,$row);
			}
		}
		return $alltext;
	}

	/**
	* loads the current page title from the pages table > or page overlay!!
	*
	* @param \int $pid
	* @param \int $lang
	* @return void
	*/
	public function getPage($pid,$lang){
    $pagesTable = "pages";
    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($pagesTable);
    $statement = $queryBuilder
       ->select('*')
       ->from($pagesTable)
       ->where(
          $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pid)),
		  $queryBuilder->expr()->or(
		  		$queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter($lang)),
				$queryBuilder->expr()->eq('doktype', $queryBuilder->createNamedParameter(254))

		  ),
         
       )->executeQuery();

    $row = [];
 		if($row = $statement->fetchAssociative()){
    }
		return $row;
	}

	/**
	* loads the current page title from the pages table > or page overlay!!
	*
	* @param \string $key
	* @param \string $val
	* @return void
	*/
	public function checkConfValue($key,$val = ""){
		if(isset($this->assocConf[$key]) && strlen($val) == 0){
			return true;
		}elseif(isset($this->assocConf[$key]) && $this->assocConf[$key] == $val){
			return true;
		}else{
			return false;
		}
	}

	/**
	* parse the config string into an array with key=>values
	*
	* @param \string $field
	* @param \string $fields
	* @param \string $table
	* @param \string $type
	* @param \string $additionalWhere
	* @return void
	*/
	public function loadContent($field,$fields,$table,$type,$additionalWhere){
		$relationContent = '';
		$replaceTags = array(
			"</p>" =>$this->endOfTag,
			"</li>" =>$this->endOfTag,
			"<br>"=>$this->lineBreak,
			"<br/>"=>$this->lineBreak,
			"<br />"=>$this->lineBreak
		);



			$addWhere = $this->getEnableFields();

			if($type == 'commaseparated'){
				$addWhere .= " AND uid IN (".$field.")";
			}
			if(strlen($additionalWhere)){
				$addWhere .= " AND " . $additionalWhere;
			}
      echo $addWhere;exit;
			//load all records
      $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
      $statement = $queryBuilder
         ->select('*')
         ->from($table)
         /*->where(
            $queryBuilder->expr()->eq('bodytext', $queryBuilder->createNamedParameter('klaus'))
         )*/
         ->executeQuery();
			//$res = $GLOBALS['TYPO3_DB']->sql_query("SELECT * FROM " . $table ." WHERE ". $addWhere);

			$relationtablefields = explode(",",$fields);
			while($relationrow = $statement->fetchAssociative()){

				foreach($relationtablefields as $field){
					$paragraphs = trim(str_replace(array_keys($replaceTags),array_values($replaceTags),$relationrow[$field]));
					if(strlen($paragraphs)){
						$relationContent .= strip_tags($paragraphs) . " ";
					}
				}

			}

		return $relationContent;
	}
 

	/**
	* generates the page link
	*
	* @return \string
	*/
	public function getEnableFields(){
		$time = time();
		//render conditional fieldstring which switches a database record on/off
		return " hidden = 0 AND deleted = 0 AND (starttime = 0 OR starttime < " . $time . ") AND (endtime = 0 OR endtime > " . $time .")";
	}

	public function getEntityUid($item,$entityUidConf,$returnUid = true){
		
		$ent_conf = explode(">",$entityUidConf);
		
		$foreignField = array_shift($ent_conf);
		if(isset($item[$foreignField])){
			
			$foreignUid = $item[$foreignField];
		}

		while($conf = array_shift($ent_conf)){
			
			$conf_array = explode(":",$conf);
			$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($conf_array[0]);
			
			$res = $queryBuilder
			->select("*")
			->from($conf_array[0])
			->where(
          		$queryBuilder->expr()->eq($conf_array[1], $queryBuilder->createNamedParameter($foreignUid))
			)
			->executeQuery()->fetchAssociative();
			if($res){
				$foreignUid = $res["uid"];
			}else{
				return 0;
			}
			
		}
		return $returnUid?$foreignUid:$res;
		
	}
	/**
	* generates the page link
	*
	* @param \int $itemid
	* @param \int $pid
	* @param \array $row
	* @return \array
	*/
	public function getPageLinkConfArray($itemid,$pid,$row){
		$urlParams = array();

		if($this->checkConfValue("extPrefix")){

			if($this->checkConfValue("action")){
				$urlParams["additionalParams"][] = $this->assocConf["extPrefix"] . '[action]'. "=" .$this->assocConf["action"];
			}
			if($this->checkConfValue("controller")){
				$urlParams["additionalParams"][] = $this->assocConf["extPrefix"] . '[controller]'. "=" .$this->assocConf["controller"];
			}
			if($this->checkConfValue("entity")){
				if($this->checkConfValue("entityUid")){
					$itemid = $this->getEntityUid($row,$this->assocConf["entityUid"]);
					if(!$itemid){
						return [];
					}
				}
				$urlParams["additionalParams"][] = $this->assocConf["extPrefix"] . '['.$this->assocConf["entity"].']'. "=" .$itemid;
			}
      		$urlParams["additionalParams"] = implode("&",$urlParams["additionalParams"]);
		}


		if($this->checkConfValue("languageSupport","1")){
			if(isset($row["sys_language_uid"])){
					$urlParams["L"] = $row["sys_language_uid"];
			}else{
				$urlParams["L"] = 0;
			}
		}
		if($this->checkConfValue("additionalParams")){
			$replace_array = [
				"{uid}" => $row["uid"]
			];
			if($this->checkConfValue("additionalParamsConfig")){
					$add_param = $this->parseParams($row["uid"]);
					$replace_array["{".$add_param[0]."}"] = $add_param[1];
			}
			$urlParams["additionalParams"] .= "&" . str_replace(array_keys($replace_array),array_values($replace_array),$this->assocConf["additionalParams"]);
		}
		$urlParams['parameter'] = $pid;

		if(isset($this->assocConf["pidMap"][$pid])){
			$urlParams["parameter"] = $this->assocConf["pidMap"][$pid];
		}

		return $urlParams;
	}


	/**
	 * Resolves DB-backed placeholders in additionalParams.
	 *
	 * Config format (multiple entries separated by |):
	 *   {placeholder}:{table}:{field}
	 *
	 * For each entry: SELECT {field} FROM {table} WHERE uid = $uid
	 * and replaces {placeholder} in the additionalParams string.
	 *
	 * @param int    $uid     UID used as the WHERE uid = $uid lookup value
	 * @return array         additionalParams string with all placeholders resolved
	 */
	public function parseParams(int $uid): array
	{
		
			$entry = trim($this->assocConf['additionalParamsConfig']);

			$parts = explode(':', $entry);

			[$placeholder, $table, $valuefield,$field] = $parts;

			$qb = GeneralUtility::makeInstance(ConnectionPool::class)
				->getQueryBuilderForTable($table);

			$result = $qb
				->select($valuefield)
				->from($table)
				->where(
					$qb->expr()->eq($field, $qb->createNamedParameter($uid))
				)
				->orderBy("sorting","ASC")
				->executeQuery()
				->fetchOne();


		return [$placeholder,$result];
	}



}
