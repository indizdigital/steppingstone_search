<?php
namespace Phi\PhiIndexedsearch\Task;

class SearchCrawlerTaskAdditionalFieldProvider implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface{

		public $extTableFields = array("table","recordStoragePid","pageUid","paramPrefix","controller","action","baseUrl");

		/**
		 * This method is used to define new fields for adding or editing a task
		 * In this case, it adds an conf field
		 *
		 * @param array $taskInfo Reference to the array containing the info used in the add/edit form
		 * @param AbstractTask|NULL $task When editing, reference to the current task. NULL when adding.
		 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
		 * @return array Array containing all the information pertaining to the additional fields
		 */

		public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {

			// Initialize extra field value
			if (empty($taskInfo['conf'])) {
				$cmd = $parentObject->getCurrentAction();
				if ($cmd === 'add') {
				} elseif ($cmd === 'edit') {
					// In case of edit, and editing a test task, set to internal value if not data was submitted already
					$taskInfo['conf'] = $task->conf;
				} elseif($task && $task->conf) {
					// Otherwise set an empty value, as it will not be used anyway
					$taskInfo['conf'] = $task->conf;
				}
			}
			// Write the code for the field
			$fieldID = 'phiindexedsearch_config';
            $textAreaConf = isset($taskInfo['conf'])?htmlspecialchars($taskInfo['conf']):"";
			$fieldCode = '<textarea class="form-control" name="tx_scheduler[conf]" id="' . $fieldID . '">' . $textAreaConf . '</textarea>';
			$additionalFields = array();
			$additionalFields[$fieldID] = array(
				'code' => $fieldCode,
				'label' => 'LLL:EXT:phi_indexedsearch/Resources/Private/Language/locallang.xlf:tx_phiindexedsearch.taskconfig',
				'cshKey' => '_MOD_system_txphiindexedsearchM1',
				'cshLabel' => $fieldID
			);
			return $additionalFields;
        }

		/**
		 * This method checks any additional data that is relevant to the specific task
		 * If the task class is not relevant, the method is expected to return TRUE
		 *
		 * @param array	 $submittedData Reference to the array containing the data submitted by the user
		 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
		 * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
		 */
        public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {

			return true;
        }

		/**
		 * This method is used to save any additional input into the current task object
		 * if the task class matches
		 *
		 * @param array $submittedData Array containing the data submitted by the user
		 * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task Reference to the current task object
		 * @return void
		 */
        public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task) {
        	$task->conf = $submittedData['conf'];
        }


		/**
		* @param array $conf
		* @return void
		*/
		public function checkValues($conf){
			foreach($this->extTableFields as $fieldname){
				$this->checkValue($conf,$fieldname);
			}
		}
		/**
		* @param array $conf
		* @param string $val
		* @return void
		*/
		public function checkValue($conf,$val){

			if(!isset($conf[$val]) || strlen($conf[$val]) == 0){
				$this->confErrors[] = "Please specify the value '".$val."' to your conf!";
			}
		}
}
