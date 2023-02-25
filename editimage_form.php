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


defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');


class mapedimage_image_form extends moodleform {

    protected function definition() {
        $context           = $this->_customdata['context'];
        $attachmentoptions = $this->_customdata['attachmentoptions'];
        $courseid       = $this->_customdata['courseid'];

        $mform = $this->_form;
        $mform->addElement('hidden', 'id', $courseid);
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->addElement('hidden', 'image', true);
        $mform->addElement('filemanager', 'bgimage_filemanager', "Imagem de navegação",null, $attachmentoptions);


        $this->add_action_buttons(true, get_string('savechanges'));
    }
}
?>
