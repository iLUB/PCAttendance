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

require_once('./Services/COPage/classes/class.ilPageComponentPluginGUI.php');

/**
 * Class ilPCAttendancePluginGUI
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 * @ilCtrl_isCalledBy ilPCAttendancePluginGUI: ilPCPluggedGUI
 */
class ilPCAttendancePluginGUI extends ilPageComponentPluginGUI {

	const ATTENDANCE = 'attendance';
	const ALIGNMENT = 'alignment';
	const DISPLAY_WEEKENDS = 'display_weekends';

	function executeCommand() {
		/** @var ilCtrl $ilCtrl */
		global $ilCtrl;
		$cmd = $ilCtrl->getCmd();

		switch($cmd) {
			case 'create':
			case 'edit':
			case 'update':
			case 'cancel':
				$this->$cmd();
				break;
		}
	}


	/**
	 * Called when adding a new ilPCAttendancePlugin in the page editor.
	 * Displays the standard input form.
	 */
	function insert() {
		global $tpl;

		$form = $this->initForm(TRUE);
		$tpl->setContent($form->getHTML());
	}


	/**
	 * Called when saving a new ilPCAttendancePlugin.
	 * On success the data is saved and the input form closed. Otherwise the user is asked to correct the form.
	 */
	function create() {
		global $tpl, $lng;

		$form = $this->initForm(TRUE);
		if ($form->checkInput()) {
			if ($this->createElement($this->getPropertiesFromPost($form))) {
				ilUtil::sendSuccess($lng->txt('msg_obj_modified'), TRUE);
				$this->returnToParent();
			}
		}

		$form->setValuesByPost();
		$tpl->setContent($form->getHtml());
	}


	/**
	 * Called when modifying an existing ilPCAttendancePlugin.
	 * Displays the input form with previously set data.
	 */
	function edit() {
		global $tpl;

		$form = $this->initForm();
		$tpl->setContent($form->getHTML());
	}


	/**
	 * Called when saving a new ilPCAttendancePlugin.
	 * Saves the edited data or asks the user to correct the input form.
	 */
	function update() {
		global $tpl, $lng;

		$form = $this->initForm(TRUE);
		if ($form->checkInput()) {
			if ($this->updateElement($this->getPropertiesFromPost($form))) {
				ilUtil::sendSuccess($lng->txt('msg_obj_modified'), TRUE);
				$this->returnToParent();
			}
		}

		$form->setValuesByPost();
		$tpl->setContent($form->getHtml());
	}


	/**
	 * Cancel
	 */
	function cancel() {
		$this->returnToParent();
	}


	/**
	 * Displays an ilPCAttendancePlugin
	 *
	 * @param string $a_mode
	 * @param array  $a_properties
	 * @param string $plugin_version
	 *
	 * @return string
	 */
	function getElementHTML($a_mode, array $a_properties, $plugin_version) {
		/** @var ilPCAttendancePlugin $pl */
		$pl = $this->getPlugin();
		$tpl = $pl->getTemplate('tpl.content.html');

		$tpl->setVariable('ATTENDANCE', $pl->txt('attendance'));

		$attendance = $this->getAttendanceInputItem();

		// Align attendance display
		if ($a_properties[self::ALIGNMENT] == 'middle') {
			$tpl->setVariable('ALIGNMENT', 'middle');
		} else if ($a_properties[self::ALIGNMENT] == 'right') {
			$tpl->setVariable('ALIGNMENT', 'right');
		}
		unset($a_properties[self::ALIGNMENT]);

		// Remove Saturday and Sunday when requested
		if (!$a_properties[self::DISPLAY_WEEKENDS]) {
			$columns = $attendance->getColumns();
			array_pop($columns);
			array_pop($columns);
			$attendance->setColumns($columns);
		}
		unset($a_properties[self::DISPLAY_WEEKENDS]);


		foreach ($attendance->getColumns() as $col) {
			$tpl->setCurrentBlock('column_header');
			$tpl->setVariable('COLUMN_HEADER', $col);
			$tpl->parseCurrentBlock();
		}

		foreach ($a_properties as $row_label => $string) {
			$selected_columns = explode(',', $string);

			$tpl->setCurrentBlock('row');
			$tpl->setVariable('LABEL_VALUE', $row_label);

			foreach ($attendance->getColumns() as $col_key => $col_name) {
				$tpl->setCurrentBlock('column');
				if (in_array((string)$col_key, $selected_columns, TRUE)) {
					$tpl->setVariable('CLASS', 'available');
				} else {
					$tpl->setVariable('CLASS', 'unavailable');
				}
				$tpl->parseCurrentBlock();
			}

			$tpl->setCurrentBlock('row');
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}


	/**
	 * Setup of the attendance form including pre-set values.
	 * @param bool $create_mode
	 *
	 * @return ilPropertyFormGUI
	 */
	protected function initForm($create_mode = FALSE) {
		global $lng, $ilCtrl;

		require_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		$form = new ilPropertyFormGUI();

		$attendance = $this->getAttendanceInputItem();
		$form->addItem($attendance);
		$alignment = new ilRadioGroupInputGUI($this->getPlugin()->txt('alignment'), 'alignment');
		$alignment->addOption(new ilRadioOption($lng->txt('cont_ed_align_left'), 'left'));
		$alignment->addOption(new ilRadioOption($lng->txt('cont_ed_align_center'), 'middle'));
		$alignment->addOption(new ilRadioOption($lng->txt('cont_ed_align_right'), 'right'));
		$alignment->setInfo($this->getPlugin()->txt('alignment_info'));
		$form->addItem($alignment);

		$weekend = new ilCheckboxInputGUI($this->getPlugin()->txt('disp_weekends'), self::DISPLAY_WEEKENDS);
		$weekend->setInfo($this->getPlugin()->txt('disp_weekends_info'));
		$form->addItem($weekend);

		// Set form values
		if (!$create_mode) {
			// Load previously set values
			$prop = $this->getProperties();

			$alignment->setValue($prop[self::ALIGNMENT]);
			unset($prop[self::ALIGNMENT]);

			if ($prop[self::DISPLAY_WEEKENDS]) {
				$weekend->setChecked(TRUE);
			}
			unset($prop[self::DISPLAY_WEEKENDS]);

			// Convert to attendance input GUI array
			$attendance->setValue($this->convertPropertiesToPost($prop));

			$form->addCommandButton('update', $lng->txt('save'));
			$form->setTitle($this->getPlugin()->txt('edit'));
		} else {
			// Set default values
			$alignment->setValue('middle');
			$weekend->setChecked(TRUE);
			$std_attendance = array(array('label' => $this->getPlugin()->txt('morning'), '0', '1', '2', '3', '4'),
				array('label' => $this->getPlugin()->txt('afternoon'), '0', '1', '2', '3', '4'));
			$attendance->setValue($std_attendance);

			$this->addCreationButton($form);
			$form->setTitle($this->getPlugin()->txt('create'));
		}

		$form->addCommandButton('cancel', $lng->txt('cancel'));
		$form->setFormAction($ilCtrl->getFormAction($this));

		return $form;
	}


	/**
	 * @return ilAttendanceInputGUI
	 */
	protected function getAttendanceInputItem() {
		global $lng;
		$lng->loadLanguageModule('dateplaner');

		require_once('Customizing/global/plugins/Services/COPage/PageComponent/PCAttendance/classes/class.ilAttendanceInputGUI.php');
		$attendance = new ilAttendanceInputGUI($this->getPlugin()->txt('attendance_input_title'), self::ATTENDANCE);
		$attendance->setColumns(array(
			$lng->txt('Mo_short'),
			$lng->txt('Tu_short'),
			$lng->txt('We_short'),
			$lng->txt('Th_short'),
			$lng->txt('Fr_short'),
			$lng->txt('Sa_short'),
			$lng->txt('Su_short')
		));


		return $attendance;
	}


	/**
	 * @param ilPropertyFormGUI $form
	 *
	 * @return array
	 */
	protected function getPropertiesFromPost($form) {
		$attendance = $this->getAttendanceInputItem();
		/** @var array $values */
		$values = $form->getInput($attendance->getPostVar());

		$properties = array();
		foreach ($values as $columns) {
			$label = $columns['label'];
			unset($columns['label']);
			$properties[$label] = implode(',', array_keys($columns));
		}

		$properties[self::ALIGNMENT] = $form->getInput(self::ALIGNMENT);
		$properties[self::DISPLAY_WEEKENDS] = (bool)$form->getInput(self::DISPLAY_WEEKENDS);

		return $properties;
	}


	/**
	 * Converts the attendance data ilAttendanceInputGUI format.
	 * @param array $properties array ('label' => '1,2,4', 'label2' => '0,2,3,4')
	 *
	 * @return array (0 => array('label', '1', '2', '4'), 1 => array('label2', '0', '2', '3', '4')
	 */
	protected function convertPropertiesToPost($properties) {
		$values = array();
		foreach ($properties as $label => $selected_columns) {
			$column = array_fill_keys(explode(',', $selected_columns), TRUE);
			$column['label'] = $label;
			$values[] = $column;
		}

		return $values;
	}
}