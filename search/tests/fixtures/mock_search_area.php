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

namespace core_mocksearch\search;

/**
 * Component implementing search for testing purposes.
 *
 * @package   core_search
 * @category  phpunit
 * @copyright David Monllao {@link http://www.davidmonllao.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

class role_capabilities extends \core_search\area\base {

    /**
     * To make things easier, base class required config stuff.
     *
     * @return bool
     */
    public function is_enabled() {
        return true;
    }

    public function get_recordset_by_timestamp($modifiedfrom = 0) {
        global $DB;
        // Filter by capability as we want this quick.
        return $DB->get_recordset_sql("SELECT id, contextid, roleid, capability FROM {role_capabilities} where timemodified >= ? and capability = ?", array($modifiedfrom, 'moodle/course:renameroles'));
    }

    public function get_document($record, $options = array()) {
        global $USER;

        // Prepare associative array with data from DB.
        $doc = \core_search\document_factory::instance($record->id, $this->componentname, $this->areaname);
        $doc->set('title', $record->capability . ' roleid ' . $record->roleid);
        $doc->set('content', $record->capability . ' roleid ' . $record->roleid . ' message');
        $doc->set('contextid', $record->contextid);
        $doc->set('courseid', SITEID);
        $doc->set('userid', $USER->id);
        $doc->set('owneruserid', \core_search\manager::NO_OWNER_ID);
        $doc->set('modified', time());

        return $doc;
    }

    public function attach_files($document) {
        global $CFG;

        // Add the searchable file fixture.
        $syscontext = \context_system::instance();
        $filerecord = array(
            'contextid' => $syscontext->id,
            'component' => 'core',
            'filearea'  => 'unittest',
            'itemid'    => 0,
            'filepath'  => '/',
            'filename'  => 'searchfile'.$document->get('itemid').'.txt',
        );

        $fs = get_file_storage();
        $file = $fs->create_file_from_string($filerecord, 'File contents');

        $document->add_stored_file($file);
    }

    public function uses_file_indexing() {
        return true;
    }

    public function check_access($id) {
        global $DB;

        if ($DB->get_record('role_capabilities', array('id' => $id))) {
            return \core_search\manager::ACCESS_GRANTED;
        }
        return \core_search\manager::ACCESS_DELETED;
    }

    public function get_doc_url(\core_search\document $doc) {
        return new \moodle_url('/index.php');
    }

    public function get_context_url(\core_search\document $doc) {
        return new \moodle_url('/index.php');
    }
}
