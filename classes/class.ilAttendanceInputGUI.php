<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2014 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/
require_once('Services/Form/classes/class.ilFormPropertyGUI.php');

/**
 * Class ilAttendanceInputGUI
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilAttendanceInputGUI extends ilFormPropertyGUI {


	/**
	 * @var array
	 */
	protected $rows;
	/**
	 * @var array
	 */
	protected $columns;
	/**
	 * @var array
	 */
	protected $value;
	/**
	 * @var int
	 */
	protected $label_length = 100;


	/**
	 * @param array $columns
	 */
	public function setColumns($columns) {
		$this->columns = $columns;
	}


	/**
	 * @return array
	 */
	public function getColumns() {
		return $this->columns;
	}


	/**
	 * @param array $rows
	 */
	public function setRows($rows) {
		$this->rows = $rows;
	}


	/**
	 * @return array
	 */
	public function getRows() {
		return $this->rows;
	}


	public function checkInput() {
		if (is_array($_POST[$this->getPostVar()])) {
			foreach ($_POST[$this->getPostVar()] as $columns) {
				$columns = ilUtil::stripSlashesArray($columns);

				foreach ($columns as $key => $column) {
					if ($key == 'label' AND trim($column) == '') {
						$plugin = new ilPCAttendancePlugin();
						$this->setAlert($plugin->txt('msg_label_required'));
						return FALSE;
					}

					if ($key != 'label' && $column == '1' && ($key < 0 || count($this->getColumns()) <= $key)) {
						return FALSE;
					}
				}
			}
		}

		return TRUE;
	}


	/**
	 * Set value by array
	 *
	 * @param	array	$a_values	value array
	 */
	public function setValueByArray($a_values) {
		$this->setValue($a_values[$this->getPostVar()]);
	}


	/**
	 * Set Value.
	 *
	 * @param	array	$value	Value
	 */
	function setValue($value) {
		$this->value = array();
		// update rows and set column values
		if (is_array($value)) {
			$rows = array();
			foreach ($value as $selected_columns) {
				$rows[] = $selected_columns['label'];
				unset($selected_columns['label']);
				$this->value[] = $selected_columns;
			}
			$this->setRows($rows);
		} else {
			for ($i = 0; $i < count($this->getRows()); $i++) {
				$this->value[$i] = array();
			}
		}
	}


	/**
	 * Get Value.
	 *
	 * @return	array	Value
	 */
	function getValue()	{
		return $this->value;
	}


	/**
	 * @param string $a_mode
	 *
	 * @return string
	 */
	function render($a_mode = '') {
		global $lng;

		// include jQuery files in standard template
		include_once('./Services/jQuery/classes/class.iljQueryUtil.php');
		iljQueryUtil::initjQuery();

		$tpl = new ilTemplate('Customizing/global/plugins/Services/COPage/PageComponent/PCAttendance/templates/tpl.attendance_input.html', TRUE, TRUE);
		$tpl->setVariable('LABEL_HEADER', $lng->txt('time_segment'));

		foreach ($this->getColumns() as $column) {
			$tpl->setCurrentBlock('column_header');
			$tpl->setVariable('COLUMN_HEADER', $column);
			$tpl->parseCurrentBlock();
		}

		$rows = $this->getRows();
		for ($i = 0; $i < count($this->getRows()); $i++) {
			if (is_array($this->getValue()) AND array_key_exists($i, $this->getValue())) {
				$selected_columns = $this->value[$i];
			} else  {
				$selected_columns = array();
			}

			for ($j = 0; $j < count($this->getColumns()); $j++) {
				$tpl->setCurrentBlock('column');
				$tpl->setVariable('POSTVAR', $this->getPostVar());
				$tpl->setVariable('ROW_ID', $i);
				$tpl->setVariable('VALUE', 1);
				$tpl->setVariable('COLUMN_ID', $j);

				if (array_key_exists((string)$j, $selected_columns)) {
					$tpl->touchBlock('checked');
				}

				$tpl->setCurrentBlock('column');
				$tpl->parseCurrentBlock();
			}

			$tpl->setCurrentBlock('row');
			$tpl->setVariable('POSTVAR', $this->getPostVar());
			$tpl->setVariable('ROW_ID', $i);
			$tpl->setVariable('LABEL_VALUE', $rows[$i]);
			$tpl->setVariable('LABEL_LENGTH', $this->getLabelLength());
			$tpl->setVariable('IMG_DEL', ilUtil::getImagePath('edit_remove.png'));
			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable('IMG_ADD', ilUtil::getImagePath('edit_add.png'));

		return $tpl->get();
	}

	/**
	 * @param ilTemplate $a_tpl
	 */
	function insert(&$a_tpl) {
		global $tpl;
		$tpl->addCss('Customizing/global/plugins/Services/COPage/PageComponent/PCAttendance/css/attendance_input.css');
		$tpl->addJavaScript('Customizing/global/plugins/Services/COPage/PageComponent/PCAttendance/js/attendance_input.js');
		$html = $this->render();

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $html);
		$a_tpl->parseCurrentBlock();
	}


	/**
	 * @param int $label_length
	 */
	public function setLabelLength($label_length) {
		$this->label_length = $label_length;
	}


	/**
	 * @return int
	 */
	public function getLabelLength() {
		return $this->label_length;
	}
}