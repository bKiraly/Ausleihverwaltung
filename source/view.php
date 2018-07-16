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
 * Prints a particular instance of apeinsdrei
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_apeinsdrei
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace apeinsdrei with the name of your module and remove this line.

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... apeinsdrei instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('apeinsdrei', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $apeinsdrei  = $DB->get_record('apeinsdrei', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $apeinsdrei  = $DB->get_record('apeinsdrei', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $apeinsdrei->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('apeinsdrei', $apeinsdrei->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$event = \mod_apeinsdrei\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $apeinsdrei);
$event->trigger();


// Um Tabelle >>resources<< zu belegen
/*
$record = new stdClass();
$record->name         = 'handy';
$record->description = 'dasd';
$record->serialnumber        = 'serial12';
$record->inventorynumber = 'invent123';
$record->comment        = 'Comment this Comment thisComment thisComment thisComment thisComment thisComment thisComment thisComment thisComment thisComment thisComment thisComment thisComment thisComment this';
$record->status = 0;
$record->amount         = 2;
$record->type = 0;
$record->maincategory    = "Handy";
$record->subcategory = "sub";

$DB->insert_record('resources', $record, $returnid=false, $bulk=false);
*/

/* PAGE belegen*/
$PAGE->set_url('/mod/apeinsdrei/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($apeinsdrei->name));
$PAGE->set_heading(format_string($course->fullname));

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('apeinsdrei-'.$somevar);
 */

// Hier beginnt die Ausgabe
echo $OUTPUT->header();

// Conditions to show the intro can change to look for own settings or whatever.
if ($apeinsdrei->intro) {
    echo $OUTPUT->box(format_module_intro('apeinsdrei', $apeinsdrei, $cm->id), 'generalbox mod_introbox', 'apeinsdreiintro');
}

$strName = "Ressource anlegen";
echo $OUTPUT->heading($strName);

require_once(dirname(__FILE__).'/forms/newresourceform.php');
$mform = new newresourcehtml_form(null);

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
 } else if ($fromform = $mform->get_data()) {
    
    //dynamisches Auslesen der eingegebenen Daten
    $ressourcename = $fromform->name;

    switch ($fromform->category) {
        case 0:
            $category = 'Handy';
        break;
        case 1:
            $category = 'Tablet';
        break;
        case 2:
            $category = 'Laptop';
        break;
        case 3:
            $category = 'Computer';
        break;
        case 4:
            $category = 'Software';
        break;
        case 5:
            $category = 'Drucker';
        break;
        case 6:
            $category = 'Kabel';
        break;
    };

    switch ($fromform->resourcetype) {
        case 0:
            $type = 0;
        break;
        case 1:
            $type = 1;
        break;
    };

    $tags = '';
    if(isset($fromform->iPhone) & $fromform->iPhone == 1){
        $tags = $tags + 'iPhone';
    }
    if(isset($fromform->Convertible) & $fromform->Convertible == 1){
        $tags = $tags + 'Convertible';
    }
    if(isset($fromform->Mac) & $fromform->Mac == 1){
        $tags = $tags + 'Mac';
    }
    if(isset($fromform->Huawai) & $fromform->Huawai == 1){
        $tags = $tags + 'Huawai';
    }
    if(isset($fromform->Samsung) & $fromform->Samsung == 1){
        $tags = $tags + 'Samsung';
    }
    if(isset($fromform->Nexus) & $fromform->Nexus == 1){
        $tags = $tags + 'Nexus';
    }
    if(isset($fromform->LTE) & $fromform->LTE == 1){
        $tags = $tags + 'LTE';
    }
    
    redirect(new moodle_url('../apeinsdrei/newressource.php', array('id' => $cm->id, 'ressourcename' => $ressourcename, 'category' => $category, 'tags' => $tags, 'type' => $type)));
 
 } else {
  // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
  // or on the first display of the form.
 
  // Set default data (if any)
  // Required for module not to crash as a course id is always needed
  $formdata = array('id' => $id);
  $mform->set_data($formdata);
  //displays the form
  $mform->display();
 
 }

// Finish the page.
echo $OUTPUT->footer();

