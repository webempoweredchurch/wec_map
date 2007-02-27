<?php

/***************************************************************
* Copyright notice
*
* (c) 2007 Foundation For Evangelism (info@evangelize.org)
* All rights reserved
*
* This file is part of the Web-Empowered Church (WEC)
* (http://webempoweredchurch.org) ministry of the Foundation for Evangelism
* (http://evangelize.org). The WEC is developing TYPO3-based
* (http://typo3.org) free software for churches around the world. Our desire
* is to use the Internet to help offer new life through Jesus Christ. Please
* see http://WebEmpoweredChurch.org/Jesus.
*
* You can redistribute this file and/or modify it under the terms of the
* GNU General Public License as published by the Free Software Foundation;
* either version 2 of the License, or (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This file is distributed in the hope that it will be useful for ministry,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the file!
***************************************************************/

class tx_wecmap_recordhandler {
	
	var $itemsPerPage = 75;
	var $count;
	
	/**
	 * PHP4 constructor
	 *
	 * @return void
	 **/
	function tx_wecmap_recordhandler($count) {
		$this->__construct($count);
	}
	
	/**
	 * PHP5 constructor
	 *
	 * @return void
	 **/
	function __construct($count) {
		$this->count = $count;
	}
	
	/**
	 * Displays the table with cache records
	 *
	 * @return String
	 **/
	function displayTable($page) {
		
		if($this->count == 0) {
			$content = $this->getTotalCountHeader(0).'<br />';
			$content .= 'No Records Found.';
			return $content;
		}
		
		global $LANG;
		
		// $limit = $this->getPageLimit($page, $this->itemsPerPage);
		$limit = null;
		// Select rows:
		$displayRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*','tx_wecmap_cache','', 'address', 'address', $limit);
		
		// $pager = $this->makePagination($page);

		foreach($displayRows as $row) {				

			// Add icon/title and ID:
			$cells = array();
			$cells[] = '<td class="editButton"><a href="#" onclick="editRecord(\''. $row['address_hash'] .'\'); return false;"><img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->getLL('editAddress').'" alt="'.$LANG->getLL('editAddress').'" /></a></td>';
			$cells[] = '<td class="deleteButton"><a href="#" onclick="deleteRecord(\''. $row['address_hash'] .'\'); return false;")"><img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('deleteAddress').'" alt="'.$LANG->getLL('deleteAddress').'" /></a></td>';
			
			$cells[] = '<td class="address">'.$row['address'].'</td>';
				
			if ($row['address_hash'] == $uid && $cmd = 'edit') {
				$cells[] = '<td><input name="latitude" value="'.$row['latitude'].'" size="8"/></td>';
				$cells[] = '<td><input name="longitude" value="'.$row['longitude'].'" size="8"/></td>';
				$cells[] = '<td><input type="submit" value="'.$LANG->getLL('updateAddress').'" /></td>';
			} else {
				$cells[] = '<td class="latitude">'.$row['latitude'].'</td>';
				$cells[] = '<td class="longitude">'.$row['longitude'].'</td>';
				$cells[] = '<td class="recordEditButtons"></td>';
			}
										
			// Compile Row:
			$output.= '
				<tr id="'. $row['address_hash'] .'" class="bgColor'.($cc%2 ? '-20':'-10').'">
					'.implode('
					',$cells).'
				</tr>';
			$cc++;

			$this->countDisplayed++;
		}

		// Create header:
		$headerCells = array();
		$headerCells[] = '<th>&nbsp;</th>';
		$headerCells[] = '<th>&nbsp;</th>';
		$headerCells[] = '<th>'.$LANG->getLL('address').'</th>';
		$headerCells[] = '<th>'.$LANG->getLL('latitude').'</th>';
		$headerCells[] = '<th>'.$LANG->getLL('longitude').'</th>';
		$headerCells[] = '<th>&nbsp;</th>';
		
		$output = '
			<thead class="bgColor5 tableheader"><tr>
				'.implode('
				',$headerCells).'
			</tr></thead>'.$output;
		
		$output = $this->getTotalCountHeader($this->count).
		'<br /><div id="recordTable">'.
		// $pager.
		'<br/>'.
		'<table border="0" cellspacing="1" cellpadding="3" id="tx-wecmap-cache" class="sortable">'.$output.'</table></div>';
		
		return $output;
	}
	
	/**
	 * Shows a search box to filter cache records
	 *
	 * @return String
	 **/
	function displaySearch() {
		$content = '<div><input id="recordSearchbox" type="text" value="Filter records..." size="20" onfocus="clearSearchbox()" onkeyup="filter()"/></div>';
		return $content;
	}
	
	/**
	 * Returns the JS functions for our AJAX stuff
	 *
	 * @return String
	 **/
	function getJS() {
		$js = '<script type="text/javascript" src="../contrib/prototype/prototype.js"></script>'.chr(10).
			  '<script type="text/javascript" src="../contrib/tablesort/fastinit.js"></script>'.chr(10).
			  '<script type="text/javascript" src="../contrib/tablesort/tablesort.js"></script>'.chr(10).
			  '<script type="text/javascript">
				SortableTable.setup({ rowEvenClass : \'bgColor-20\', rowOddClass : \'bgColor-10\'})

			  </script>'.chr(10).
			'<script>
			
				// -------------------------
				// 		search functions
				// -------------------------

				function clearSearchbox() {
					$(\'recordSearchbox\').clear();
				}
			
				function filter() {
					var sword = $F(\'recordSearchbox\');
					var addresses = $(\'recordTable\').getElementsByClassName(\'address\');
					var result = addresses.select(function(n, sword) { return n.innerHTML == sword});
					// alert(sword);
					alert(result);
					
				}
				
				// -------------------------
				// record handling functions
				// -------------------------
				
				function deleteAll() {
					// Setup the parameters and make the ajax call
					var pars = \'?cmd=deleteAll\';
				    var myAjax = new Ajax.Updater(\'deleteAllStatus\', \'tx_wecmap_recordhandler_ai.php\', 
				          {method: \'get\', parameters: pars, onComplete:clearTable});
				}

				function deleteRecord(id) {
					// Setup the parameters and make the ajax call
					var pars = \'?cmd=deleteSingle&record=\'+id;
				    var myAjax = new Ajax.Updater(\'deleteAllStatus\', \'tx_wecmap_recordhandler_ai.php\', 
				          {method: \'get\', parameters: pars, onComplete:clearRow(id)});
				}
				
				function editRecord(id) {
					var longitudes = $(id).getElementsByClassName(\'longitude\');
					var latitudes = $(id).getElementsByClassName(\'latitude\');
					var longitude = longitudes[0];
					var latitude = latitudes[0];
					var links = getLinks(id, latitude.innerHTML, longitude.innerHTML);
					latitude.update(\'<input class="latForm" type="text" size="10" value="\'+latitude.innerHTML+\'"/>\');
					longitude.update(\'<input class="longForm" type="text" size="10" value="\'+longitude.innerHTML+\'"/>\');
					var buttonElement = $(id).getElementsByClassName(\'recordEditButtons\');
					buttonElement[0].update(links);
				}
				
				function refreshRows() {
					var table = $(\'tx-wecmap-cache\');
					var rows = SortableTable.getBodyRows(table);
					rows.each(function(r,i) {
						SortableTable.addRowClass(r,i);
					});
				}	
				
				function addRowClass(r,i) {
					r = $(r)
					r.removeClassName(SortableTable.options.rowEvenClass);
					r.removeClassName(SortableTable.options.rowOddClass);
					r.addClassName(((i+1)%2 == 0 ? SortableTable.options.rowEvenClass : SortableTable.options.rowOddClass));
				}
				
				function saveRecord(id) {
					var long = $(id).getElementsByClassName(\'longForm\');
					var longValue = $F(long[0]);

					var lat = $(id).getElementsByClassName(\'latForm\');
					var latValue = $F(lat[0]);

					// Setup the parameters and make the ajax call
					var pars = \'?cmd=saveRecord&record=\'+id+\'&latitude=\'+latValue+\'&longitude=\'+longValue;
				    var myAjax = new Ajax.Updater(\'deleteAllStatus\', \'tx_wecmap_recordhandler_ai.php\', 
				          {method: \'get\', parameters: pars, onComplete:unEdit(id,longValue,latValue)});
				}
				
				function unEdit(id, long, lat) {
					var longitudes = $(id).getElementsByClassName(\'longitude\');
					var latitudes = $(id).getElementsByClassName(\'latitude\');
					var longitude = longitudes[0];
					var latitude = latitudes[0];
					$(id).getElementsByClassName(\'recordEditButtons\')[0].update(\'\');
					longitude.update(long);
					latitude.update(lat);
				}
				
				function getLinks(id, oldLat, oldLong) {
					var link = \'<a href="#" onclick="saveRecord(\\\'\'+id+\'\\\'); return false;">Save</a>&nbsp;<a href="#" onclick="unEdit(\\\'\'+id+\'\\\',\\\'\'+oldLong+\'\\\', \\\'\'+oldLat+\'\\\'); return false;">Cancel</a>\';
					return link;
				}
				
				function clearRow(id) {
					$(id).remove();
					var count = $(\'recordCount\');
					var number = count.innerHTML;

					if((number-1)%'. $this->itemsPerPage .' == 0) {
						var page = Math.floor(number/'. $this->itemsPerPage .');
						updatePagination(page);
					}

					$(\'recordCount\').update(number-1);
					//SortableTable.load();
					refreshRows();

				}

				function clearTable() {
					var count = $(\'recordCount\');
					count.update("0");
					var status = $(\'recordTable\');
					status.update("No Records Found.");
				}
				
				function updatePagination(page) {
					var count = $(\'recordCount\');
					var number = count.innerHTML;
					var pars = \'?cmd=updatePagination&page=\'+page+\'&itemsPerPage='. $this->itemsPerPage .'&count=\'+number;
				    var myAjax = new Ajax.Updater(\'pagination\', \'tx_wecmap_recordhandler_ai.php\', 
				          {method: \'get\', parameters: pars});
				}
			</script>';
		
		return $js;
	}
	
	/**
	 * Displays the pagination
	 *
	 * @return String
	 **/
	function makePagination($page) {
		$pages = ceil($this->count/$this->itemsPerPage);
		if($pages == 1) return null;
		
		$content = array();
		$content[] = '<div id="pagination">';
		
		if($page !== 1) {
			$content[] = '<a href="?page='. ($page-1) .'">Previous</a>';	
		} else {
			$content[] = '<span style="color: grey;">Previous</span>';	
		} 
		
		for ( $i=0; $i < $pages; $i++ ) { 
			if($page == ($i+1)) {
				$content[] = '<span style="color: grey;">'.($i+1).'</span>';
			} else {
				$content[] = '<a href="?page='. ($i+1) .'">'. ($i+1) .'</a>';				
			}
		}
		
		if($page !== $pages) {
			$content[] = '<a href="?page='. ($page+1) .'">Next</a>';	
		} else {
			$content[] = '<span style="color: grey;">Next</span>';	
		}
		
		$content[] = '</div>';
		return implode(' ', $content);
		
	}
	
	/**
	 * Returns the header part that allows to delete all records and shows the
	 * total number of records
	 *
	 * @return String
	 **/
	function getTotalCountHeader($count) {
		global $LANG;
		$content = $LANG->getLL('totalCachedAddresses') .
			': <strong><span id="recordCount">'.$this->count.'</span></strong> '.
			'<a href="#" onclick="deleteAll(); return false;">'.
			'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('deleteCache').'" alt="'.$LANG->getLL('deleteCache').'" />'.
			'</a>';
		
		return $content;
	}
	
	/**
	 * Get record limits for SQL query
	 *
	 * @return String
	 **/
	function getPageLimit($page, $itemsPerPage) {
		if($page == 1) {
			$start = 0;
			$end = $itemsPerPage;
		} else {
			$start = ($page-1)*$itemsPerPage;
			$end = $page*$itemsPerPage;
		}
		
		return $start.','.$end;
	}
	
	function linkSelf($addParams)	{
		return htmlspecialchars('index.php?id='.$this->pObj->id.'&showLanguage='.rawurlencode(t3lib_div::_GP('showLanguage')).$addParams);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod1/class.tx_wecmap_recordhandler.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod1/class.tx_wecmap_recordhandler.php']);
}

?>