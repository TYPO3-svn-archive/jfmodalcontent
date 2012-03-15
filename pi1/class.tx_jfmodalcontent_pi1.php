<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Juergen Furrer <juergen.furrer@gmail.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('jfmodalcontent').'lib/class.tx_jfmodalcontent_pagerenderer.php');


/**
 * Plugin 'Modal Content' for the 'jfmodalcontent' extension.
 *
 * @author	Juergen Furrer <juergen.furrer@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_jfmodalcontent
 */
class tx_jfmodalcontent_pi1 extends tslib_pibase
{
	public $prefixId      = 'tx_jfmodalcontent_pi1';
	public $scriptRelPath = 'pi1/class.tx_jfmodalcontent_pi1.php';
	public $extKey        = 'jfmodalcontent';
	public $pi_checkCHash = TRUE;
	public $conf = array();
	private $lConf = array();
	private $confArr = array();
	private $templateFile = NULL;
	private $templateFileJS = NULL;
	private $templatePart = NULL;
	private $additionalMarker = array();
	private $contentKey = NULL;
	private $contentCount = NULL;
	private $contentClass = array();
	private $classes = array();
	private $contentWrap = array();
	private $titles = array();
	private $attributes = array();
	private $cElements = array();
	private $rels = array();
	private $content_id = array();
	private $piFlexForm = array();
	private $pagerenderer = NULL;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	public function main($content, $conf) {
		$this->content = $content;
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		// get the config from EXT
		$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['jfmodalcontent']);

		$this->pagerenderer = t3lib_div::makeInstance('tx_jfmodalcontent_pagerenderer');
		$this->pagerenderer->setConf($this->conf);

		// Plugin or template?
		if ($this->cObj->data['list_type'] == $this->extKey.'_pi1') {
			// It's a content, all data from flexform
			$this->lConf['style'] = $this->getFlexformData('general', 'style');
			$this->lConf['columnOrder'] = $this->getFlexformData('general', 'columnOrder', in_array($this->lConf['style'], array('2column','3column','4column','5column')));
			$this->lConf['options']         = $this->getFlexformData('special', 'options');
			$this->lConf['optionsOverride'] = $this->getFlexformData('special', 'optionsOverride');

			// Override the config with flexform data
			if ($this->lConf['inTransition']) {
				$this->conf['config.']['inTransition'] = $this->lConf['inTransition'];
			}
			if ($this->lConf['inTransitiondir']) {
				$this->conf['config.']['inTransitiondir'] = $this->lConf['inTransitiondir'];
			}
			if ($this->lConf['inTransitionduration'] > 0) {
				$this->conf['config.']['inTransitionduration'] = $this->lConf['inTransitionduration'];
			}
			// options
			if ($this->lConf['optionsOverride'] || trim($this->lConf['options'])) {
				$this->conf['config.'][$this->lConf['style'].'Options'] = $this->lConf['options'];
				$this->conf['config.'][$this->lConf['style'].'OptionsOverride'] = $this->lConf['optionsOverride'];
			}

			// define the key of the element
			$this->setContentKey('jfmodalcontent_c' . $this->cObj->data['uid']);
		}

		// The template for JS
		if (! $this->templateFileJS = $this->cObj->fileResource($this->conf['templateFileJS'])) {
			$this->templateFileJS = $this->cObj->fileResource("EXT:jfmodalcontent/res/tx_jfmodalcontent_pi1.js");
		}

		// define the jQuery mode and function
		if ($this->conf['jQueryNoConflict']) {
			$jQueryNoConflict = "jQuery.noConflict();";
		} else {
			$jQueryNoConflict = "";
		}

		$this->pagerenderer->addJS($jQueryNoConflict);

		$options = array();




		// overwrite all options if set
		if ($this->conf['config.']['optionsOverride']) {
			$options = array($this->conf['config.']['options']);
		} else {
			if ($this->conf['config.']['options']) {
				$options['options'] = $this->conf['config.']['options'];
			}
		}

		// get the Template of the Javascript
		$markerArray = array();
		// get the template
		if (! $templateCode = trim($this->cObj->getSubpart($this->templateFileJS, "###TEMPLATE_TAB_JS###"))) {
			$templateCode = $this->outputError("Template TEMPLATE_TAB_JS is missing", TRUE);
		}

		// Add all CSS and JS files
		if (T3JQUERY === TRUE) {
			tx_t3jquery::addJqJS();
		} else {
			$this->pagerenderer->addJsFile($this->conf['jQueryLibrary'], TRUE);
			$this->pagerenderer->addJsFile($this->conf['jQueryEasing']);
		}
		$this->pagerenderer->addJsFile($this->conf['modalContentJS']);
		$this->pagerenderer->addCssFile($this->conf['modalContentCSS']);

		// Add the ressources
		$this->pagerenderer->addResources();

		// Render the Template
		$content = $this->renderTemplate();

		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Set the contentKey
	 * @param string $contentKey
	 */
	public function setContentKey($contentKey=NULL) {
		$this->contentKey = ($contentKey == NULL ? $this->extKey : $contentKey);
	}

	/**
	 * Get the contentKey
	 * @return string
	 */
	public function getContentKey() {
		return $this->contentKey;
	}

	/**
	 * Render the template with the defined contents
	 * 
	 * @return string
	 */
	public function renderTemplate() {
	}

	/**
	* Return a errormessage if needed
	* @param string $msg
	* @param boolean $js
	* @return string
	*/
	public function outputError($msg='', $js=FALSE) {
		t3lib_div::devLog($msg, $this->extKey, 3);
		if ($this->confArr['frontendErrorMsg'] || ! isset($this->confArr['frontendErrorMsg'])) {
			return ($js ? "alert(".t3lib_div::quoteJSvalue($msg).")" : "<p>{$msg}</p>");
		} else {
			return NULL;
		}
	}

	/**
	* Set the piFlexform data
	*
	* @return void
	*/
	protected function setFlexFormData()
	{
		if (! count($this->piFlexForm)) {
			$this->pi_initPIflexForm();
			$this->piFlexForm = $this->cObj->data['pi_flexform'];
		}
	}

	/**
	 * Extract the requested information from flexform
	 * @param string $sheet
	 * @param string $name
	 * @param boolean $devlog
	 * @return string
	 */
	protected function getFlexformData($sheet='', $name='', $devlog=TRUE)
	{
		$this->setFlexFormData();
		if (! isset($this->piFlexForm['data'])) {
			if ($devlog === TRUE) {
				t3lib_div::devLog("Flexform Data not set", $this->extKey, 1);
			}
			return NULL;
		}
		if (! isset($this->piFlexForm['data'][$sheet])) {
			if ($devlog === TRUE) {
				t3lib_div::devLog("Flexform sheet '{$sheet}' not defined", $this->extKey, 1);
			}
			return NULL;
		}
		if (! isset($this->piFlexForm['data'][$sheet]['lDEF'][$name])) {
			if ($devlog === TRUE) {
				t3lib_div::devLog("Flexform Data [{$sheet}][{$name}] does not exist", $this->extKey, 1);
			}
			return NULL;
		}
		if (isset($this->piFlexForm['data'][$sheet]['lDEF'][$name]['vDEF'])) {
			return $this->pi_getFFvalue($this->piFlexForm, $name, $sheet);
		} else {
			return $this->piFlexForm['data'][$sheet]['lDEF'][$name];
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jfmodalcontent/pi1/class.tx_jfmodalcontent_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jfmodalcontent/pi1/class.tx_jfmodalcontent_pi1.php']);
}

?>