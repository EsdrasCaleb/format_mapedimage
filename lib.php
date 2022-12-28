<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Mapedimage Format - A topics based format that uses user selectable image maped top to popup a light box of the section.
 *
 * @package    format_mapedimage
 * @copyright  &copy; 2019 Jose Wilson  in respect to modifications of grid format.
 * @copyright  &copy; 2012 G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - {@link http://about.me/gjbarnard} and
 *                           {@link http://moodle.org/user/profile.php?id=442195}
 * @copyright  &copy; 2022    Paul Krix and Julian Ridden. trail format
 *@author     Esdras Caleb - {@link http://github.com/esdrasCaleb/} 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

use core_courseformat\base as course_format;

require_once($CFG->dirroot . '/course/format/lib.php'); // For format_base.

/**
 * format_trail class
 *
 * @package    format_trail
 * @copyright  &copy; 2019 Jose Wilson  in respect to modifications of grid format.
 * @author     &copy; 2012 G J Barnard in respect to modifications of standard topics format.
 */
class format_mapedimage extends course_format {

    public function create_edit_form_elements(&$mform, $forsection = false) {
        if (!$forsection && (empty($COURSE->id) || $COURSE->id == SITEID)) {
        }
    }
}
