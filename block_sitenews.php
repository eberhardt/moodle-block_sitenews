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
 * @package    block_sitenews
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v4 or later
 * @author     Jan Eberhardt <eberhardt@math.tu-berlin.de>
 */

require_once($CFG->dirroot.'/blocks/sitenews/locallib.php');

class block_sitenews extends block_base {

	public $items;

	/**
	 * Initialization
	 */
	public function init()
	{
		$this->title = get_string("pluginname", "block_sitenews");
	}

	/**
	 * In the next section restrit user's ability to modify this block
	 */
	public function instance_allow_config() {
		return true;
	}

	public function instance_allow_multiple() {
		return false;
	}

	public function applicable_formats() {
		return array('site' => true, 'my-index' => true);
	}

	/**
	 * Show content
	 *
	 * @see block_base::get_content()
	 */
	public function get_content()
	{
		global $SITE;

		if (! $newsforum = forum_get_course_forum($SITE->id, "news")) {
			print_error("cannotfindorcreateforum", "forum");
		}
		$newsforumcm = get_coursemodule_from_instance('forum', $newsforum->id, $SITE->id, false, MUST_EXIST);

		$updatemynumber = optional_param("mynewsitems", -1, PARAM_INT);
		if ($updatemynumber >= 0 && $updatemynumber < 11)
		{
			block_sitenews_update_itemsnumber($updatemynumber);
			$items = $updatemynumber;
		}
		else
			$items = block_sitenews_get_itemsnumber();

		if ($items == 0) // setting is "preset"
			$items = $SITE->newsitems;

		$this->content = new stdClass();
		$this->content->text = "";
		$this->content->footer = "";

		$renderer = $this->page->get_renderer("block_sitenews");

		if ($this->page->user_is_editing())
			$this->content->text .= $renderer->editing_bar_head($items);

		if ($items == 0 || forum_get_discussions_count($newsforumcm) == 0) // admin disabled news or just nothing to display
			$this->content->text .= $renderer->nothing_to_display_message();
		else
			$this->content->text .= $renderer->sitenews($newsforum, $items);

		return $this->content;
	}

}

?>