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

ini_set('max_execution_time', '-1');
set_time_limit(-1);

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
        global $COURSE;
        $elements = parent::create_edit_form_elements($mform, $forsection);

        if (!(empty($COURSE->id) || $COURSE->id == SITEID)) {
            $elements[] = $mform->addElement('header', 'previewareaheader',"Maped Image");
            $mform->setExpanded('previewareaheader');
            $elements[] = $mform->addElement('static', 'previewarea', '',
                        "Preview");
            $courseconfig = get_config('moodlecourse');
            $mform->registerNoSubmitButton('refresh');
            $max = (int)$courseconfig->maxsections;
            $elements[] = $mform->addElement('select', 'numsections', "Numero de Seções", range(0, $max ?: 52));
            $mform->setType('numsections', PARAM_INT);
            $sections = $mform->getElementValue('numsections')[0];
            if (is_null($sections)) {
                $mform->setDefault('numsections', $courseconfig->numsections);
                $sections = $courseconfig->numsections;
            }
            
            $elements[] = $mform->addElement('submit', 'refresh', "Recarregar");
            $elements[] = $mform->addElement('filepicker', 'bgimage', "Image",
                                                        null, self::file_picker_options());
            $mform->closeHeaderBefore('dropzoneheader');

            $elements[] = $mform->addElement('header', 'dropzoneheader', "Zona da Imagem");

            for($i=0;$i<=$sections;$i++){
                $elements[] = $mform->addElement('text', 'sec'.$i, "Secao ".$i);
                $mform->setType('sec'.$i, PARAM_INT);
            }
            

        }

        return $elements;
    }
    
    public static function file_picker_options() {
        $filepickeroptions = array();
        $filepickeroptions['accepted_types'] = array('web_image');
        $filepickeroptions['maxbytes'] = 0;
        $filepickeroptions['maxfiles'] = 1;
        $filepickeroptions['subdirs'] = 0;
        return $filepickeroptions;
    }

    public function get_settings() {
        if (empty($this->settings) == true) {
            $this->settings = $this->get_format_options();
        }
        return $this->settings;
    }

}
