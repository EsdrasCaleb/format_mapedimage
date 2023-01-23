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
    const ADD_NUM_ITEMS = 3;
    const MAX_GROUPS = 8;
    const START_NUM_ITEMS = 6;


    public function __construct($submiturl, $context,$sections,$data ,$formeditable = true) {
        global $DB;

        $this->context = $context;
        $this->data = $data;
        $this->sections = $sections;
        $this->editoroptions = array('subdirs' => 0, 'maxfiles' => 1,
                'context' => $this->context);
        $this->fileoptions = array('subdirs' => 0, 'maxfiles' => 1, 'maxbytes' => -1,'accepted_types' => array('gif', 'jpe', 'jpeg', 'jpg', 'png'));
        $mform = $this->_form;

        parent::__construct($submiturl, null, null, '', ['data-qtype' => $this->qtype()], $formeditable);
    }

    public function qtype() {
        return 'ddimageortext';
    }

    protected function definition() {
        global $DB, $PAGE;

        $mform = $this->_form;

        // Standard fields at the start of the form.
        $mform->addElement('header', 'previewareaheader',
                            get_string('previewareaheader', 'qtype_'.$this->qtype()));
        $mform->setExpanded('previewareaheader');
        $mform->addElement('static', 'previewarea', '',
                            get_string('previewareamessage', 'qtype_'.$this->qtype()));

        $mform->registerNoSubmitButton('refresh');
        $mform->addElement('submit', 'refresh', get_string('refresh', 'qtype_'.$this->qtype()));

        $mform->addElement('filepicker', 'bgimage', "Imagem de navegação",
        null, $this->fileoptions);
        $mform->closeHeaderBefore('dropzoneheader');

        $itemrepeatsatstart = self::ADD_NUM_ITEMS;
        $mform->addElement('header', 'draggableitemheader',
                                get_string('draggableitems', 'qtype_ddimageortext'));
        $mform->addElement('advcheckbox', 'shuffleanswers', get_string('shuffleimages', 'qtype_'.$this->qtype()));
        $mform->setDefault('shuffleanswers',  0);
        $this->repeat_elements($this->draggable_item($mform), $itemrepeatsatstart,
                $this->draggable_items_repeated_options(),
                'noitems', 'additems', self::ADD_NUM_ITEMS,
                get_string('addmoreimages', 'qtype_ddimageortext'), true);
        $this->repeat_elements($this->drop_zone($mform), $itemrepeatsatstart,
                $this->drop_zones_repeated_options(),
                'nodropzone', 'adddropzone', self::ADD_NUM_ITEMS,
                "Adicionar mais 3 zonas na imagem", false);

        $mform->addGroup($buttonarray, 'updatebuttonar', '', array(' '), false);
        $mform->closeHeaderBefore('updatebuttonar');

        $this->add_action_buttons(true, get_string('savechanges'));
    }

    protected function draggable_items_repeated_options() {
        $repeatedoptions = array();
        $repeatedoptions['draggroup']['default'] = '1';
        return $repeatedoptions;
    }

    protected function draggable_item($mform) {
        $draggableimageitem = array();

        $grouparray = array();
        $dragitemtypes = array('image' => get_string('draggableimage', 'qtype_ddimageortext'),
                                'word' => get_string('draggableword', 'qtype_ddimageortext'));
        $grouparray[] = $mform->createElement('select', 'dragitemtype',
                                            get_string('draggableitemtype', 'qtype_ddimageortext'),
                                            $dragitemtypes,
                                            array('class' => 'dragitemtype'));
        $options = array();
        for ($i = 1; $i <= self::MAX_GROUPS; $i += 1) {
            $options[$i] = question_utils::int_to_letter($i);
        }
        $grouparray[] = $mform->createElement('select', 'draggroup',
                                                get_string('group', 'qtype_gapselect'),
                                                $options,
                                                array('class' => 'draggroup'));
        $grouparray[] = $mform->createElement('advcheckbox', 'infinite', get_string('infinite', 'qtype_ddimageortext'));
        $draggableimageitem[] = $mform->createElement('group', 'drags',
                get_string('draggableitemheader', 'qtype_ddimageortext', '{no}'), $grouparray);

        $draggableimageitem[] = $mform->createElement('filepicker', 'dragitem', '', null,
        $this->fileoptions);

        $draggableimageitem[] = $mform->createElement('text', 'draglabel',
                                                get_string('label', 'qtype_ddimageortext'),
                                                array('size' => 30, 'class' => 'tweakcss draglabel'));
        $mform->setType('draglabel', PARAM_RAW); // These are validated manually.
        return $draggableimageitem;
    }


    protected function drop_zones_repeated_options() {
        $repeatedoptions = array();
        // The next two are PARAM_RAW becuase we need to distinguish 0 and ''.
        // We do the necessary validation in the validation method.
        $repeatedoptions['drops[xleft]']['type']     = PARAM_RAW;
        $repeatedoptions['drops[ytop]']['type']      = PARAM_RAW;
        $repeatedoptions['drops[droplabel]']['type'] = PARAM_RAW;
        $repeatedoptions['choice']['default'] = '0';
        return $repeatedoptions;
    }

    protected function drop_zone($mform) {
        $dropzoneitem = array();

        $grouparray = array();
        $grouparray[] = $mform->createElement('text', 'xleft',
                                                'left',
                                                array('size' => 5, 'class' => 'tweakcss'));
        $grouparray[] = $mform->createElement('text', 'ytop',
                                                'top',
                                                array('size' => 5, 'class' => 'tweakcss'));
        $options = array();

        $options[0] = 'Nenhum';
        $i =0;
        foreach($this->sections as $section) {
            $i++;
            $options[$i] = $section->name??'Seção '.$i;
        }
        $options[count($this->sections)] = "Link" ;
        $grouparray[] = $mform->createElement('select', 'choice',
                                    'Seção', $options);
        $grouparray[] = $mform->createElement('text', 'droplabel',
                                                'URL',
                                                array('size' => 10, 'class' => 'tweakcss'));
        $mform->setType('droplabel', PARAM_NOTAGS);
        
        $dropzone = $mform->createElement('group', 'drops',
                get_string('dropzone', 'qtype_ddimageortext', '{no}'), $grouparray);
        return array($dropzone);
    }

    public function set_data($data) {
        $data = $this->data_preprocessing($data);
        parent::set_data($data);
    }

    public function data_preprocessing($data) {

        $dragids = array(); // Drag no -> dragid.
        if (!empty($data->options)) {
            $data->shuffleanswers = $data->options->shuffleanswers;
            $data->drags = array();
            foreach ($data->options->drags as $drag) {
                $dragindex = $drag->no - 1;
                $data->drags[$dragindex] = array();
                $data->draglabel[$dragindex] = $drag->label;
                $data->drags[$dragindex]['infinite'] = $drag->infinite;
                $data->drags[$dragindex]['draggroup'] = $drag->draggroup;
                $dragids[$dragindex] = $drag->id;
            }
            $data->drops = array();
            foreach ($data->options->drops as $drop) {
                $data->drops[$drop->no - 1] = array();
                $data->drops[$drop->no - 1]['choice'] = $drop->choice;
                $data->drops[$drop->no - 1]['droplabel'] = $drop->label;
                $data->drops[$drop->no - 1]['xleft'] = $drop->xleft;
                $data->drops[$drop->no - 1]['ytop'] = $drop->ytop;
            }
        }
        // Initialise file picker for bgimage.
        $draftitemid = file_get_submitted_draft_itemid('bgimage');

        file_prepare_draft_area($draftitemid, $this->context->id, 'qtype_ddimageortext',
                                'bgimage', !empty($data->id) ? (int) $data->id : null,
                                $this->fileoptions);
        $data->bgimage = $draftitemid;

        // Initialise file picker for dragimages.
        list(, $imagerepeats) = $this->get_drag_item_repeats();
        $draftitemids = optional_param_array('dragitem', array(), PARAM_INT);
        for ($imageindex = 0; $imageindex < $imagerepeats; $imageindex++) {
            $draftitemid = isset($draftitemids[$imageindex]) ? $draftitemids[$imageindex] : 0;
            // Numbers not allowed in filearea name.
            $itemid = isset($dragids[$imageindex]) ? $dragids[$imageindex] : null;
            file_prepare_draft_area($draftitemid, $this->context->id, 'qtype_ddimageortext',
                                'dragimage', $itemid, $this->fileoptions);
            $data->dragitem[$imageindex] = $draftitemid;
        }
        if (!empty($data->options)) {
            foreach ($data->options->drags as $drag) {
                $dragindex = $drag->no - 1;
                if (!isset($data->dragitem[$dragindex])) {
                    $fileexists = false;
                } else {
                    $fileexists = self::file_uploaded($data->dragitem[$dragindex]);
                }
                $labelexists = (trim($data->draglabel[$dragindex]) != '');
                if ($labelexists && !$fileexists) {
                    $data->drags[$dragindex]['dragitemtype'] = 'word';
                } else {
                    $data->drags[$dragindex]['dragitemtype'] = 'image';
                }
            }
        }
    
        $this->js_call();

        return $data;
    }

    protected function get_drag_item_repeats() {
        $countimages = 0;
        if (isset($this->data->id)) {
            foreach ($this->data->options->drags as $drag) {
                $countimages = max($countimages, $drag->no);
            }
        }

        if (!$countimages) {
            $countimages = self::START_NUM_ITEMS;
        }
        $itemrepeatsatstart = $countimages;

        $imagerepeats = optional_param('noitems', $itemrepeatsatstart, PARAM_INT);
        $addfields = optional_param('additems', false, PARAM_BOOL);
        if ($addfields) {
            $imagerepeats += self::ADD_NUM_ITEMS;
        }
        return array($itemrepeatsatstart, $imagerepeats);
    }

    public function js_call() {
        global $PAGE;
        $PAGE->requires->js_call_amd('qtype_ddimageortext/form', 'init');
    }

}
?>
