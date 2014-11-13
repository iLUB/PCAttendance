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

require_once('./Services/COPage/classes/class.ilPageComponentPlugin.php');

/**
 * Class ilPCAttendancePlugin
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilPCAttendancePlugin extends ilPageComponentPlugin {

	/**
	 * Determines the resources that allow to include the
	 * new content component.
	 *
	 * @param    string $a_type Parent type (e.g. "cat", "lm", "glo", "wiki", ...)
	 *
	 * @return    boolean        true/false if the resource type allows
	 */
	function isValidParentType($a_type) {
		if (in_array($a_type, array('prtf'))) { // Only allow this page component on the portfolio page
			return TRUE;
		} else {
			return FALSE;
		}
	}


	/**
	 * Get Plugin Name. Must be same as in class name il<Name>Plugin
	 * and must correspond to plugins subdirectory name.
	 *
	 * Must be overwritten in plugin class of plugin
	 * (and should be made final)
	 *
	 * @return    string    Plugin Name
	 */
	final function getPluginName() {
		return 'PCAttendance';
	}


	/**
	 * Get Javascript files
	 */
	function getJavascriptFiles() {
		return array();
	}


	/**
	 * Get css files
	 */
	function getCssFiles() {
		return array('css/content.css');
	}
}