<?php
namespace Phi\PhiIndexedsearch\Domain\Repository;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;
 use TYPO3\CMS\Core\Context\Context;
 use TYPO3\CMS\Core\Database\ConnectionPool;
 use TYPO3\CMS\Core\Database\Connection;
/**
 * The repository for Fulltexts
 */
class FulltextRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
	/**
	* @var percentageReferenceValue
	*/
	protected $percentageReferenceValue = 100;

	/**
	* @var rateTitleWordsFactor
	*/
	protected $rateTitleWordsFactor = 2;

	/**
	* @var settings
	*/
	public $settings = array();

	/**
	 * function findByAlltext
	 * load all text from the database
	 *
	 * @param \string $sword
	 * @return \array
	 */
	public function findByAlltext($sword) {

		$swords = explode(" ",trim($sword));

		$this->initRating(count($swords));

		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_phiindexedsearch_domain_model_fulltext');
		//$query = $this->createQuery();
		//why should this not be respected?? TODO or better to check ...
		//$query->getQuerySettings()->setRespectSysLanguage(FALSE);
		$statement = $queryBuilder->select('*')->from('tx_phiindexedsearch_domain_model_fulltext');
		foreach($swords as $word){
			if(strlen(trim($word))){
				$statement = $statement->where(
      		$queryBuilder->expr()->or(
						$queryBuilder->expr()->like('alltext', $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($word) . '%')),
      			$queryBuilder->expr()->like('title', $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($word) . '%'))
					)
   			);
			}
		}

		/*$groups = isset($GLOBALS["TSFE"]->fe_user->user["usergroup"])?explode(",",$GLOBALS["TSFE"]->fe_user->user["usergroup"]):[];

		$groupConstrains = [];
		//is the user allowed to see the records
		foreach($groups as $group){
			$groupConstrains[] = $queryBuilder->expr()->like('fe_group', $queryBuilder->createNamedParameter($group));
			$groupConstrains[] = $queryBuilder->expr()->like('fe_group', $queryBuilder->createNamedParameter("%,".$group));
			$groupConstrains[] = $queryBuilder->expr()->like('fe_group', $queryBuilder->createNamedParameter($group. ",%"));
			$groupConstrains[] = $queryBuilder->expr()->like('fe_group', $queryBuilder->createNamedParameter("%," . $group . ",%"));

		}

		$groupConstrains[] = $queryBuilder->expr()->eq('fe_group', $queryBuilder->createNamedParameter(""));
		$groupConstrains[] = $queryBuilder->expr()->eq('fe_group', $queryBuilder->createNamedParameter("0"));

		$statement = $statement->andWhere(
			$queryBuilder->expr()->or(
				...$groupConstrains
			)
		);*/
    $languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');
    $sys_language_uid = $languageAspect->getId();
		$statement = $statement->andWhere(
			$queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter($sys_language_uid))
		);
		//debug echo $queryBuilder->getSQL();exit;

		

		$res = $statement->executeQuery();

		return $this->prepareResults($res,$swords);
	}

	/**
	 * function initRating
	 * calculates the rating
	 *
	 * @param \int $swordCount
	 * @return \void
	 */
	public function initRating($swordCount) {
		$this->percentageReferenceValue = $swordCount + $this->rateTitleWordsFactor * $swordCount;
	}

	/**
	 * function cropResults
	 * mark the words, crop the text, sort the results by relevance
	 *
	 * @param \array $results
	 * @param \array $swords
	 * @return \array
	 */
	public function prepareResults($results,$swords) {
		$resultLength = $this->settings["textCrop"];

		$resultArray = [];
		if($this->settings["groupByPage"] == '1'){
			$shortenedResults = [];
			while($result = $results->fetchAssociative()){
				//$t = $result->getAlltext(); 
				$this->cropTextAndMarkResult($result,$swords);
				//$pagelinkParts = explode(" ",$result["pagelink"]);
				$link = implode("",preg_replace("/[^a-zA-Z0-9]/","",$result["pagelink"]));
        if(!isset($shortenedResults[$link]["alltext"])){
  				$shortendLinkText = $result["alltext"];
  				if(!isset($shortenedResults[$link]) || strlen(trim($shortendLinkText)) == 0 ){
  					$shortenedResults[$link] = $result;
  				}
        }
			}
			$resultArray = $shortenedResults;
		}else{
			while($result = $results->fetchAssociative()){

				$this->cropTextAndMarkResult($result,$swords);

				$resultArray[] = $result;
			}
		}
		//user sort of the results
		usort($resultArray,array($this,"compareResults"));

		return $resultArray;
	}

	/**
	 * function compareResults
	 * array sorting function (used by native usort)
	 *
	 * @param \Phi\PhiIndexedsearch\Domain\Model\Fulltext $resultA
	 * @param \Phi\PhiIndexedsearch\Domain\Model\Fulltext $resultB
	 * @return \int
	 */
	public function compareResults($resultA,$resultB) {
		//compare results by their rate
        if ($resultA["rate"] == $resultB["rate"]) {
			if ($resultA["chdate"] == $resultB["chdate"]) {
				return 0;
			}
			return ($resultA["chdate"] < $resultB["chdate"]) ? +1 : -1;
        }
        return ($resultA["rate"] < $resultB["rate"]) ? +1 : -1;
	}

	/**
	 * function cropTextAndMarkResult
	 *
	 * @param \Phi\PhiIndexedsearch\Domain\Model\Fulltext $result
	 * @param \array $swords
	 * @return \void
	 */
	public function cropTextAndMarkResult(&$result,$swords) {
		//get indexed Text
		$text = $result["alltext"];
		//get indexed Page Title
		$title = $result["title"];

		//seeking for search words in the text
		$foundWords = $this->foundWords($text,$swords);
		//seeking for search words in the title
		$foundTitleWords = $this->foundWords($title,$swords);

		//set the found Words into the Model to render it in the view later
		$result["foundWords"] = (array_unique(array_merge($foundTitleWords,$foundWords)));

		//sort the found Words by their first index in the text
		ksort($foundWords);
		//get the result length from the TS settings
		$resultLength = $this->settings["textCrop"];
		//calcualete the length of the start part of the result text
		$startLength = count($foundWords)?($resultLength / count($foundWords)) / 2:$resultLength /2;
		//init done to the handel the loop like a do while loop
		$done = false;
		//init the result Text String
		$resultText = '';


		foreach($foundWords as $index=>$fWord){
			//just do this once!
			if(!$done){
				//initial start position
				//$start = strpos($text,$fWord);
				$start = $index;
				//set the start position before the first word (set by textCrop / count(foundWords))
				$start = $start > $startLength?$start - $startLength:0;
				//get the text from the beginning of the next space (cut the rest before)
				$resultText = $this->getTextFromNextSpace($text,$start);

				//try to get the whole text into result
				$resultText = $this->getTextUntilNextSpace($resultText,$resultLength);

				//set done to true to abort the loop
				$done = true;
			}
		}

		//if the search words have been found just in the title add some result text
		if(strlen($resultText) == 0 || trim($resultText) == "..."){
			$resultText = $this->getTextUntilNextSpace($text,$resultLength);
		}

		//highlite words in text
		$wrapper = isset($this->settings["markTextClasses"])?$this->settings["markTextClasses"]:"";
		$resultText = $this->markWords($resultText,$foundWords,true,$wrapper);
		$result["alltext"] = $resultText;

		//highlight words in title
		$wrapper = "";
		$resultTitle = $this->markWords($title,$foundTitleWords,false,$wrapper,false);
		$result["title"] = $resultTitle;

		//rate the result by date
		$result["rate"] = (round((((count($foundTitleWords) * $this->rateTitleWordsFactor) + count($foundWords)) / $this->percentageReferenceValue) * 100));
    $result["pagelink"] = json_decode($result["pagelink"],true);

	}

	/**
	 * function increaseIndexToNextSpaceIndex
	 *
	 * @param \string $text
	 * @param \string $offset
	 * @return \int
	 */
	public function increaseIndexToNextSpaceIndex($text,$offset) {
		if(strlen($text) == 0){
			return 0;
		}
		//find next space index after $start  + $length
		if($offset >= strlen($text)){
			$pos = 0;
		}else{
			$pos = strpos($text," ",$offset);
		}
		if($pos === false){
			$pos = 0;
		}
		return $pos;
	}

	/**
	 * function getTextFromNextSpace
	 * returns the text (based on $text) with the first word uncut
	 *
	 * @param \string $text
	 * @param \int $start
	 * @param \int $length
	 * @return \array
	 */
	public function getTextFromNextSpace($text,$start) {
		if($start == 0){
			return $text;
		}
		$nextSpaceIndex = $this->increaseIndexToNextSpaceIndex($text,$start);


		//check if this index is the end
		if($nextSpaceIndex == 0){
			return $text;
		}else{
			return "... " . substr($text,$nextSpaceIndex);
		}
	}

	/**
	 * function getTextUntilNextSpace
	 * returns the text (based on $text) with the last word uncut
	 *
	 * @param \string $text
	 * @param \int $start
	 * @return \array
	 */
	public function getTextUntilNextSpace($text,$start) {
		$nextSpaceIndex = $this->increaseIndexToNextSpaceIndex($text,$start);
		//check if this index is the end

		if($nextSpaceIndex == 0){
			return $text;
		}else{
			return substr($text,0,$nextSpaceIndex) . " ...";
		}
	}

	/**
	 * function markWords
	 * mark the found words bold in the text and replace br- and endTag-Markers
	 *
	 * @param \string $text
	 * @param \array $fwords
	 * @param \array $handleBR
	 * @param \string $wrapper
	 * 
	 * @return \array
	 */
	public function markWords($text,$fwords,$handleBR,$wrapper,$mark = true) {
		if($mark && !empty($fwords)){
			$words = $fwords;
			usort($words, function($a,$b){ return strlen($b) - strlen($a); });
			$pattern = '/\b(' . implode('|', array_map(function($w){ return preg_quote($w,'/'); }, $words)) . ')\b/ui';
			$text = preg_replace_callback($pattern, function($matches) use ($wrapper){
				return '<span class="' . $wrapper . '">' . $matches[1] . '</span>';
			}, $text);
		}

		if($handleBR){
			$text = str_replace(array("#BR#","#ET#"), array($this->settings["handleBR"],$this->settings["handleEndTags"]), $text);
		}
		return $text;
	}

	/**
	 * function foundWords
	 * returns an array containing the words found in the text
	 *
	 * @param \string $text
	 * @param \array $swords
	 * @param \array $handleBR
	 * @return \array
	 */
	public function foundWords($text,$swords) {
		$foundWords = array();
		foreach($swords as $sword){
			//case insensitive because mysql does like this!
			$xpos = strpos(strtolower($text),strtolower($sword));
			if($xpos !== false){
				//$rate = substr_count($text,$sword);
				$foundWords[$xpos] = $sword;
			}

		}
		return $foundWords;
	}

	/**
	 * function updateSearchWords
	 *
	 * @param \string $sword
	 * @return \array
	 */
	public function updateSearchWords($sword) {
		$words = explode(" ",trim($sword));
		$inserts = [];
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_phiindexedsearch_domain_model_searchwordlist');
		$statement = $queryBuilder
		   ->select('word')
		   ->from('tx_phiindexedsearch_domain_model_searchwordlist');

		$index = 0;
		//dont want to have tooo many words... seems not natural
		$normalized = [];
		if(count($words) < 10){
			foreach($words as $word){
				$statement = $index == 0?
						$statement->where($queryBuilder->expr()->eq('word', $queryBuilder->createNamedParameter($word))):
						$statement->orWhere($queryBuilder->expr()->eq('word', $queryBuilder->createNamedParameter($word)));
				$index++;
				$normalized[] = strtolower($word);
			}
			$res = $statement->executeQuery();
		}
		$inserts = [];
		while ($row = $res->fetchAssociative()) {
			$key = array_search(strtolower($row["word"]),$normalized);
			unset($normalized[$key]);
		}
		foreach($normalized as $nWord){
			$inserts[] = [$nWord];
		}
		if(!empty($inserts)){
			$connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_phiindexedsearch_domain_model_searchwordlist');
			$connection->bulkInsert('tx_phiindexedsearch_domain_model_searchwordlist',$inserts,["word"],[Connection::PARAM_STR]);
		}


	}

}
