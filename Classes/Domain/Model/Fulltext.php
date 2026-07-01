<?php
namespace Phi\PhiIndexedsearch\Domain\Model;

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
 use TYPO3\CMS\Core\Database\ConnectionPool;
/**
 * Fulltext
 */
class Fulltext extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * title
     *
     * @var string
     */
    protected $title = '';

    /**
     * subtitle
     *
     * @var string
     */
    protected $subtitle = '';

    /**
     * alltext
     *
     * @var string
     */
    protected $alltext = '';

    /**
     * pagelink
     *
     * @var string
     */
    protected $pagelink = '';

    /**
     * recorduid
     *
     * @var string
     */
    protected $recorduid = '';

    /**
     * recordpid
     *
     * @var string
     */
    protected $recordpid = '';

    /**
     * taskuid
     *
     * @var string
     */
    protected $taskuid = '';

    /**
     * rate
     *
     * @var int
     */
    protected $rate = 0;

    /**
     * chdate
     *
     * @var int
     */
    protected $chdate = 0;

    /**
     * fe_group
     *
     * @var string
     */
    protected $fe_group;

    /**
     * taskConfs
     *
     * @var array
     */
    protected $taskConfs;

    /**
     * foundWords
     *
     * @var int
     */
    protected $foundWords = array();

    /**
     * Returns the title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns the subtitle
     *
     * @return string $subtitle
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * Sets the subtitle
     *
     * @param string $subtitle
     * @return void
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
    }

    /**
     * Returns the pagelink
     *
     * @return string $pagelink
     */
    public function getPagelink()
    {
        return json_decode($this->pagelink,true);
    }

    /**
     * Returns the pagetarget
     *
     * @return string $pagetarget
     */
    public function getPagetarget()
    {
        $chunks = explode(" ",$this->pagelink);
        return isset($chunks[1])?$chunks[1]:"";
    }

    /**
     * Returns the getExtPagelinkConf
     *
     * @return string $pagelink
     */
    public function getExtPagelinkConf()
    {
      echo "deprecated";exit;
      /*  if(strpos($this->getPagelink(),"?") !== false){
          if(empty($this->taskConfs)){
            $this->loadTaskConfs();
          }
          return $this->taskConfs[$this->getTaskuid()];

        }else{
          return "";
        }*/

    }

    /**
     * Returns the loadTaskConfs
     *
     * @return string $pagelink
     */
    public function loadTaskConfs()
    {
      echo "deprecated";exit;
      
    }

    /**
     * Sets the pagelink
     *
     * @param string $pagelink
     * @return void
     */
    public function setPagelink($pagelink)
    {
        $this->pagelink = $pagelink;
    }

    /**
     * Returns the alltext
     *
     * @return string alltext
     */
    public function getAlltext()
    {
        return $this->alltext;
    }

    /**
     * Sets the alltext
     *
     * @param string $alltext
     * @return void
     */
    public function setAlltext($alltext)
    {
        $this->alltext = $alltext;
    }

    /**
     * Returns the rate
     *
     * @return int rate
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Sets the rate
     *
     * @param int $rate
     * @return void
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
    }

    /**
     * Returns the chdate
     *
     * @return int chdate
     */
    public function getChdate()
    {
        return $this->chdate;
    }

    /**
     * Sets the chdate
     *
     * @param int $chdate
     * @return void
     */
    public function setChdate($chdate)
    {
        $this->chdate = $chdate;
    }

    /**
     * Returns the foundWords
     *
     * @return array foundWords
     */
    public function getFoundWords()
    {
        return $this->foundWords;
    }

    /**
     * Sets the foundWords
     *
     * @param int $foundWords
     * @return void
     */
    public function setFoundWords($foundWords)
    {
        $this->foundWords = $foundWords;
    }

    /**
     * Returns the recorduid
     *
     * @return array recorduid
     */
    public function getRecorduid()
    {
        return $this->recorduid;
    }

    /**
     * Sets the recorduid
     *
     * @param int $recorduid
     * @return void
     */
    public function setRecorduid($recorduid)
    {
        $this->recorduid = $recorduid;
    }

    /**
     * Returns the recordpid
     *
     * @return array recordpid
     */
    public function getRecordpid()
    {
        return $this->recordpid;
    }

    /**
     * Sets the recordpid
     *
     * @param int $recordpid
     * @return void
     */
    public function setRecordpid($recordpid)
    {
        $this->recordpid = $recordpid;
    }

    /**
     * Returns the taskuid
     *
     * @return array taskuid
     */
    public function getTaskuid()
    {
        return $this->taskuid;
    }

    /**
     * Sets the taskuid
     *
     * @param int $taskuid
     * @return void
     */
    public function setTaskuid($taskuid)
    {
        $this->taskuid = $taskuid;
    }

    /**
     * Returns the fe_group
     *
     * @return string $fe_group
     */
    public function getFeGroup()
    {
        return $this->fe_group;
    }

    /**
     * Sets the title
     *
     * @param string $fe_group
     * @return void
     */
    public function setFeGroup($fe_group)
    {
        $this->fe_group = $fe_group;
    }

}
