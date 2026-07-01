<?php
namespace Phi\PhiIndexedsearch\Controller;

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

/**
 * FulltextController
 */
class FulltextController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

      /**
       * fulltextRepository
       *
       * @var \Phi\PhiIndexedsearch\Domain\Repository\FulltextRepository
       */
      protected $fulltextRepository = NULL;


      /**
       * @param \Phi\PhiIndexedsearch\Domain\Repository\FulltextRepository $fulltextRepository
       */
      public function injectFulltextRepository(\Phi\PhiIndexedsearch\Domain\Repository\FulltextRepository $fulltextRepository)
      {
          $this->fulltextRepository = $fulltextRepository;
      }

    /**
     * action search
     *
     * @return void
     */
    public function searchAction()
    {

      $sword = "";
      if(isset($_POST["tx_phiindexedsearch_indexedsearch"]) && isset($_POST["tx_phiindexedsearch_indexedsearch"]["sword"])){
        $sword = $_POST["tx_phiindexedsearch_indexedsearch"]["sword"];
      }
  		if(strlen($sword)){
  			$this->renderResults($sword);
        $this->fulltextRepository->updateSearchWords($sword);
        $fulltexts = $this->fulltextRepository->findAll();
        $this->view->assign('fulltexts', $fulltexts);
      }
      return $this->htmlResponse();
    }

    /**
     * action result
     *
	 * @param \string $sword
     * @return void
     */
    public function renderResults($sword)
    {
		    $this->fulltextRepository->settings = $this->settings;
        $results = $this->fulltextRepository->findByAlltext($sword);
        $this->view->assign('results', $results);
        $this->view->assign('sword', $sword);
        return $this->htmlResponse();
    }

}
