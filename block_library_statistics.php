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
 * Online users block.
 *
 * @package    block_library_statistics
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * This block needs to be reworked.
 * The new roles system does away with the concepts of rigid student and
 * teacher roles.
 */
class block_library_statistics extends block_base
{
    public function init()
    {
        global $PAGE;

        $this->title = $PAGE->cm->name;
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    public function has_config()
    {
        return false;
    }
    
    public function get_content()
    {
        global $USER, $PAGE, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        }

        $content = '';
                
        if ($PAGE->cm && $PAGE->cm->module == 6 && $PAGE->cm->instance > 0) {
            $fieldName = 'Tipo';
            $field = $DB->get_record('data_fields', ['dataid' => $PAGE->cm->instance, 'name' => $fieldName]);
            $categories = $this->get_categories($PAGE->cm->instance, $field);

            $content .= '<div class="block_library_statistics">';

            $content .= '<div class="statistics">';
            $totalRecords = $this->get_count_records($PAGE->cm->instance);
            $content .= '<div class="total-records">
                            <span>Itens do acervo</span>
                            <span>'.$totalRecords.'</span>
                        </div>';

            $totalRecordsViewed = $this->get_count_records_viewed($PAGE->cm->instance, $USER->id);
            $content .= '<div class="total-records-viewed">
                            <span>Itens que acessei</span>
                            <span>'.$totalRecordsViewed.'</span>
                         </div>';

            $totalRecordsNotViewed = $totalRecords - $totalRecordsViewed;
            $content .= '<div class="total-records-not-viewed">
                            <span>Itens que não acessei</span>
                            <span>'.$totalRecordsNotViewed.'</span>
                         </div>';

            $content .= '</div>';

            $content .= '<div class="categories">
                            <span>Categorias do acervo</span>
                            <ul>';

            foreach ($categories as $category) {
                $content .= '<li>
                                <a href="/mod/data/view.php?d='.$PAGE->cm->instance.'&filter=1&advanced=1&f_'.$field->id.'='.$category->category.'">
                                    '.$category->category.' ('.$category->total.')'.'
                                </a>
                            </li>';
            }

            $content .= '</ul>
                        </div>
                    </div>';
        }
        $this->content->text = $content;
        
        return $this->content;
    }

    private function get_count_records($libraryId)
    {
        global $DB;

        $totalRecords = $DB->count_records('data_records', ['dataid' => $libraryId]);
        return $totalRecords;
    }

    private function get_count_records_viewed($libraryId, $userId)
    {
        global $DB;

        $totalRecordsViewed = $DB->count_records('block_library_sta_log_access', ['dataid' => $libraryId, 'userid' => $userId]);
        return $totalRecordsViewed;
    }

    private function get_categories($libraryId, $field)
    {
        global $DB;
        
        $categories = $DB->get_records_sql('SELECT c.content as category, COUNT(*) total 
                                FROM {data_content} c 
                                LEFT JOIN {data_records} r ON c.recordid = r.id
                                WHERE r.dataid = ? AND c.fieldid = ?
                                GROUP BY c.content', [$libraryId, $field->id]);

        
        return $categories;
    }

    public function _self_test()
    {
        return true;
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats()
    {
        return array(
                'site-index' => false,
                'course-view' => false,
                'course-view-social' => false,
                'mod-data' => true
    );
    }
}
