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
			$this->lConf['inAnimation']  = $this->getFlexformData('general', 'inAnimation');
			$this->lConf['content']      = $this->getFlexformData('general', 'content');
			$this->lConf['contentWidth'] = $this->getFlexformData('general', 'contentWidth');

			$this->lConf['inDelay']              = $this->getFlexformData('inAnimation', 'inDelay');
			$this->lConf['inTransition']         = $this->getFlexformData('inAnimation', 'inTransition');
			$this->lConf['inTransitiondir']      = $this->getFlexformData('inAnimation', 'inTransitiondir');
			$this->lConf['inTransitionduration'] = $this->getFlexformData('inAnimation', 'inTransitionduration');

			$this->lConf['outDelay']              = $this->getFlexformData('outAnimation', 'outDelay');
			$this->lConf['outTransition']         = $this->getFlexformData('outAnimation', 'outTransition');
			$this->lConf['outTransitiondir']      = $this->getFlexformData('outAnimation', 'outTransitiondir');
			$this->lConf['outTransitionduration'] = $this->getFlexformData('outAnimation', 'outTransitionduration');

			$this->lConf['options']         = $this->getFlexformData('special', 'options');
			$this->lConf['optionsOverride'] = $this->getFlexformData('special', 'optionsOverride');

			// Override the config with flexform data
			if ($this->lConf['inAnimation']) {
				$this->conf['config.']['inAnimation'] = $this->lConf['inAnimation'];
			}
			if ($this->lConf['content']) {
				$this->conf['config.']['content'] = $this->lConf['content'];
			}
			if ($this->lConf['contentWidth']) {
				$this->conf['config.']['contentWidth'] = $this->lConf['contentWidth'];
			}

			// IN
			if ($this->lConf['inDelay']) {
				$this->conf['config.']['inDelay'] = $this->lConf['inDelay'];
			}
			if ($this->lConf['inTransition']) {
				$this->conf['config.']['inTransition'] = $this->lConf['inTransition'];
			}
			if ($this->lConf['inTransition']) {
				$this->conf['config.']['inTransitiondir'] = $this->lConf['inTransitiondir'];
			}
			if ($this->lConf['inTransitionduration']) {
				$this->conf['config.']['inTransitionduration'] = $this->lConf['inTransitionduration'];
			}

			// OUT
			if ($this->lConf['outDelay']) {
				$this->conf['config.']['outDelay'] = $this->lConf['outDelay'];
			}
			if ($this->lConf['outTransition']) {
				$this->conf['config.']['outTransition'] = $this->lConf['outTransition'];
			}
			if ($this->lConf['outTransition']) {
				$this->conf['config.']['outTransitiondir'] = $this->lConf['outTransitiondir'];
			}
			if ($this->lConf['outTransitionduration']) {
				$this->conf['config.']['outTransitionduration'] = $this->lConf['outTransitionduration'];
			}

			// options
			if ($this->lConf['optionsOverride'] || trim($this->lConf['options'])) {
				$this->conf['config.']['options'] = $this->lConf['options'];
				$this->conf['config.']['optionsOverride'] = $this->lConf['optionsOverride'];
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

		$options = array();

		if ($this->conf['config.']['inAnimation']) {
			$options['inAnimation'] = "inAnimation: '{$this->conf['config.']['inAnimation']}'";
		}
		if ($this->conf['config.']['contentWidth']) {
			$this->pagerenderer->addCSS(
"#{$this->getContentKey()} { 
	width: {$this->conf['config.']['contentWidth']};
}");
		}

		if (is_numeric($this->conf['config.']['inDelay'])) {
			$options['inDelay'] = "inDelay: '{$this->conf['config.']['inDelay']}'";
		}
		if (in_array($this->conf['config.']['inTransition'], array('linear', 'swing'))) {
			$options['inTransition'] = "inTransition: '{$this->conf['config.']['inTransition']}'";
		} elseif ($this->conf['config.']['inTransitiondir'] && $this->conf['config.']['inTransition']) {
			$options['inTransition'] = "inTransition: 'ease{$this->conf['config.']['inTransitiondir']}{$this->conf['config.']['inTransition']}'";
		}
		if ($this->conf['config.']['inTransitionduration'] > 0) {
			$options['inDuration'] = "inDuration: '{$this->conf['config.']['inTransitionduration']}'";
		}

		if (is_numeric($this->conf['config.']['outDelay'])) {
			$options['outDelay'] = "outDelay: '{$this->conf['config.']['outDelay']}'";
		}
		if (in_array($this->conf['config.']['outTransition'], array('linear', 'swing'))) {
			$options['outTransition'] = "outTransition: '{$this->conf['config.']['outTransition']}'";
		} elseif ($this->conf['config.']['outTransitiondir'] && $this->conf['config.']['outTransition']) {
			$options['outTransition'] = "outTransition: 'ease{$this->conf['config.']['outTransitiondir']}{$this->conf['config.']['outTransition']}'";
		}
		if ($this->conf['config.']['outTransitionduration'] > 0) {
			$options['outDuration'] = "outDuration: '{$this->conf['config.']['outTransitionduration']}'";
		}

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
		$markerArray["KEY"]     = $this->getContentKey();
		$markerArray["OPTIONS"] = implode(",\n		", $options);
		// get the template
		if (! $templateCode = trim($this->cObj->getSubpart($this->templateFileJS, "###TEMPLATE_JS###"))) {
			$templateCode = $this->outputError("Template TEMPLATE_JS is missing", TRUE);
		}

		$templateCode = $this->cObj->substituteMarkerArray($templateCode, $markerArray, '###|###', 0);

		$this->pagerenderer->addJS($jQueryNoConflict . $templateCode);

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
		// set the register:key for TS manipulation
		$GLOBALS['TSFE']->register['key']        = $this->getContentKey();
		$GLOBALS['TSFE']->register['content_id'] = $this->conf['config.']['content'];

		$content = $this->cObj->cObjGetSingle($this->conf['table.']['tt_content.']['content'], $this->conf['table.']['tt_content.']['content.']);
		$return_string = $this->cObj->stdWrap($content, $this->conf['contentWrap.']);

		return $return_string;
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