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

require_once (PATH_t3lib . 'class.t3lib_page.php');

/**
 * 'itemsProcFunc' for the 'jfmodalcontent' extension.
 *
 * @author     Juergen Furrer <juergen.furrer@gmail.com>
 * @package    TYPO3
 * @subpackage tx_jfmodalcontent
 */
class tx_jfmodalcontent_itemsProcFunc
{
	/**
	 * Get defined Class inner for dropdown
	 * @return array
	 */
	public function getAvailableClasses($config, $item)
	{
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['jfmodalcontent']);
		$availableClasses = t3lib_div::trimExplode(",", $confArr['availableClasses']);
		if (count($availableClasses) < 1 || ! $confArr['availableClasses']) {
			$availableClasses = array('','alert-error','alert-block','alert-success','alert-info');
		}
		$pageTS = t3lib_BEfunc::getPagesTSconfig($config['row']['pid']);
		$jfmodalcontentClasses = t3lib_div::trimExplode(",", $pageTS['mod.']['jfmodalcontent.']['availableClasses'], TRUE);
		$optionList = array();
		if (count($jfmodalcontentClasses) > 0) {
			foreach ($availableClasses as $key => $availableClass) {
				if (in_array(trim($availableClass), $jfmodalcontentClasses)) {
					$optionList[] = array(
						trim($availableClass),
						trim($availableClass),
					);
				}
			}
		} else {
			foreach ($availableClasses as $key => $availableClass) {
				$optionList[] = array(
					trim($availableClass),
					trim($availableClass),
				);
			}
		}
		$config['items'] = array_merge($config['items'], $optionList);
		return $config;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jfmodalcontent/lib/class.tx_jfmodalcontent_itemsProcFunc.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jfmodalcontent/lib/class.tx_jfmodalcontent_itemsProcFunc.php']);
}
?>