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
 * Course renderer.
 *
 * @package    theme_trend
 * @copyright  2018 Allan Levitt || http://www.alevitt.co.za/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// namespace theme_trend\output\core;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/renderer.php');
require_once($CFG->dirroot . '/course/format/topics/renderer.php');
require_once($CFG->dirroot . '/course/format/weeks/renderer.php');
require_once($CFG->libdir . '/coursecatlib.php');

class theme_trend_core_course_renderer extends core_course_renderer {
        
    /** DONE
     * @TODO: grayed-out block for users who are not enrolled that sends an email to site support.
     * 
     * Displays one course in the list of courses.
     *
     * This is an internal function, to display an information about just one course
     * please use {@link core_course_renderer::course_info_box()}
     *
     * @param coursecat_helper $chelper various display options
     * @param course_in_list|stdClass $course
     * @param string $additionalclasses additional classes to add to the main <div> tag (usually
     *    depend on the course position in list - first/last/even/odd)
     * @return string
     */
    protected function coursecat_coursebox(coursecat_helper $chelper, $course, $additionalclasses = '') {
        global $CFG;
        global $USER;
        if (!isset($this->strings->summary)) {
            $this->strings->summary = get_string('summary');
        }
        if ($chelper->get_show_courses() <= self::COURSECAT_SHOW_COURSES_COUNT) {
            return '';
        }
        if ($course instanceof stdClass) {
            require_once($CFG->libdir. '/coursecatlib.php');
            $course = new course_in_list($course);
        }
        $content = '';
        $classes = trim('coursebox clearfix '. $additionalclasses);
        $nametag = 'h3';
        $coursename = $chelper->get_course_formatted_name($course);
        $courselink = $CFG->wwwroot.'/course/view.php?id='.$course->id;

        // .coursebox || Changed to a link so the entire box is the link.
        $content .= html_writer::start_tag('a', array(
            'class' => $classes,
            'data-courseid' => $course->id,
            'data-type' => self::COURSECAT_TYPE_COURSE,
            'href' => $courselink,
        ));
        
        // Image & Course name
        $content .= html_writer::start_tag('div', array('class' => 'courseinfo'));

        // display course overview files
        $contentimages = $contentfiles = '';
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                    '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                    $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
            if ($isimage) {
                $contentimages .= html_writer::tag('div',
                        html_writer::empty_tag('img', array('src' => $url)),
                        array('class' => 'courseimage'));
            }
        }
        $content .= $contentimages;

        // course name
        $content .= html_writer::tag($nametag, $coursename, array('class' => 'coursename'));
        
        //End courseinfo
        $content .=html_writer::end_tag('div');

        // print enrolmenticons
        if ($icons = enrol_get_course_info_icons($course)) {
            $content .= html_writer::start_tag('div', array('class' => 'enrolmenticons'));
            foreach ($icons as $pix_icon) {
                $content .= $this->render($pix_icon);
            }
            $content .= html_writer::end_tag('div'); // .enrolmenticons
        }

        $content .= html_writer::end_tag('a'); // .coursebox
        
        // If a user is not enrolled in a course, do not display that course
        $catid = $course->category;
        $context = context_course::instance($course->id);
        if (!is_enrolled($context)) {
            if (trend_catview($catid, 'course') == '1' || is_siteadmin()) {
                $content .= '';
            } else {
                $content = '';
            }
        }
        
        return $content;
    }
    
    /** DONE
     * Returns HTML to display a course category as a part of a tree
     *
     * This is an internal function, to display a particular category and all its contents
     * use {@link core_course_renderer::course_category()}
     *
     * @param coursecat_helper $chelper various display options
     * @param coursecat $coursecat
     * @param int $depth depth of this category in the current tree
     * @return string
     */
    protected function coursecat_category(coursecat_helper $chelper, $coursecat, $depth) {        
        global $CFG;
        $chelper = new coursecat_helper();
        $catid = $coursecat->id;
        
        // open category tag
        $classes = array('category');
        if (empty($coursecat->visible)) {
            $classes[] = 'dimmed_category';
        }
        
        // If a user is enrolled in a course within the category, then create the category block.
        if ((trend_catenrolled($catid) == true || trend_catview($catid) == '1') || is_siteadmin()) {
        
            // Converted the div tag to a link so the entire box is a link.
            $content = html_writer::start_tag('a', array(
                'class' => join(' ', $classes),
                'data-categoryid' => $coursecat->id,
                'data-depth' => $depth,
                'data-showcourses' => $chelper->get_show_courses(),
                'data-type' => self::COURSECAT_TYPE_CATEGORY,
                'href' => $CFG->wwwroot . '/course/index.php?categoryid=' . $coursecat->id,
            ));

            // Category details
            $categoryname = $coursecat->get_formatted_name();
            $categorydesc = $chelper->get_category_formatted_description($coursecat);

            // Get the image URL that was uploaded in the settings page.
            $catimg = trend_catimage($catid);

            // Add the image to the output
            $content .= html_writer::start_tag('div', array('class' => 'image'));
            $content .= html_writer::empty_tag('img', array('src' => $catimg));
            $content .= html_writer::end_tag('div');

            // This adds the Category name
            $content .= html_writer::start_tag('div', array('class' => 'info'));

            $content .= html_writer::tag(($depth > 1) ? 'h4' : 'h3', $categoryname, array('class' => 'categoryname'));

            $content .= html_writer::end_tag('div'); // .info

            // Add category description to the output
            $content .= html_writer::tag('div', $categorydesc, array('class' => 'content'));

            $content .= html_writer::end_tag('a'); // .category
        
        // If the user is not enrolled in a course within the category, don't create the category block.
        } else {
            $content = '';
        }
        
        // Return the course category tree HTML
        return $content;
    }

    /** DONE
     * Returns HTML to display a tree of subcategories and courses in the given category
     *
     * @param coursecat_helper $chelper various display options
     * @param coursecat $coursecat top category (this category's name and description will NOT be added to the tree)
     * @return string
     */
    protected function coursecat_tree(coursecat_helper $chelper, $coursecat) {
        global $CFG;
        // Reset the category expanded flag for this course category tree first.
        $this->categoryexpandedonload = false;
        $categorycontent = $this->coursecat_category_content($chelper, $coursecat, 0);
        if (empty($categorycontent)) {
            return '';
        }

        // Start content generation
        $content = '';
        $attributes = $chelper->get_and_erase_attributes('course_category_tree clearfix');
        $content .= html_writer::start_tag('div', $attributes);

        $content .= html_writer::tag('div', $categorycontent, array('class' => 'content'));

        $content .= html_writer::end_tag('div'); // .course_category_tree

        return $content;
    }

    /** DONE
     * Renders HTML to display particular course category - list of it's subcategories and courses
     *
     * Invoked from /course/index.php
     *
     * @param int|stdClass|coursecat $category
     */
    public function course_category($category) {
        global $CFG;
        require_once($CFG->libdir. '/coursecatlib.php');
        $coursecat = coursecat::get(is_object($category) ? $category->id : $category);
        $site = get_site();
        $output = '';

        if (can_edit_in_category($coursecat->id)) {
            // Add 'Manage' button if user has permissions to edit this category.
            $managebutton = $this->single_button(new moodle_url('/course/management.php',
                array('categoryid' => $coursecat->id)), get_string('managecourses'), 'get');
            $this->page->set_button($managebutton);
        }
        if (!$coursecat->id) {
            if (coursecat::count_all() == 1) {
                // There exists only one category in the system, do not display link to it
                $coursecat = coursecat::get_default();
                $strfulllistofcourses = get_string('fulllistofcourses');
                $this->page->set_title("$site->shortname: $strfulllistofcourses");
            } else {
                $strcategories = get_string('categories');
                $this->page->set_title("$site->shortname: $strcategories");
            }
        } else {
            $title = $site->shortname;
            if (coursecat::count_all() > 1) {
                $title .= ": ". $coursecat->get_formatted_name();
            }
            $this->page->set_title($title);
        }                

        // Print current category name & description
        $chelper = new coursecat_helper();
        if ($description = $chelper->get_category_formatted_description($coursecat)) {
            $catname = $coursecat->get_formatted_name();
            $output .= html_writer::tag('h1',$catname, array('class' => 'catlist-name'));
            $output .= $this->box($description, array('class' => 'generalbox info'));
        }

        // Prepare parameters for courses and categories lists in the tree
        $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_AUTO)
                ->set_attributes(array('class' => 'category-browse category-browse-'.$coursecat->id));

        $coursedisplayoptions = array();
        $catdisplayoptions = array();
        $browse = optional_param('browse', null, PARAM_ALPHA);
        $perpage = optional_param('perpage', $CFG->coursesperpage, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $baseurl = new moodle_url('/course/index.php');
        if ($coursecat->id) {
            $baseurl->param('categoryid', $coursecat->id);
        }
        if ($perpage != $CFG->coursesperpage) {
            $baseurl->param('perpage', $perpage);
        }
        $coursedisplayoptions['limit'] = $perpage;
        $catdisplayoptions['limit'] = $perpage;
        if ($browse === 'courses' || !$coursecat->has_children()) {
            $coursedisplayoptions['offset'] = $page * $perpage;
            $coursedisplayoptions['paginationurl'] = new moodle_url($baseurl, array('browse' => 'courses'));
            $catdisplayoptions['nodisplay'] = true;
            $catdisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'categories'));
            $catdisplayoptions['viewmoretext'] = new lang_string('viewallsubcategories');
        } else if ($browse === 'categories' || !$coursecat->has_courses()) {
            $coursedisplayoptions['nodisplay'] = true;
            $catdisplayoptions['offset'] = $page * $perpage;
            $catdisplayoptions['paginationurl'] = new moodle_url($baseurl, array('browse' => 'categories'));
            $coursedisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'courses'));
            $coursedisplayoptions['viewmoretext'] = new lang_string('viewallcourses');
        } else {
            // we have a category that has both subcategories and courses, display pagination separately
            $coursedisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'courses', 'page' => 1));
            $catdisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'categories', 'page' => 1));
        }
        $chelper->set_courses_display_options($coursedisplayoptions)->set_categories_display_options($catdisplayoptions);
        
        // Add course search form.
        // This is removed for now. Perhaps will be needed in future.
//        $output .= $this->course_search_form();

        // Display course category tree.
        $output .= $this->coursecat_tree($chelper, $coursecat);

        // Add action buttons
        $output .= $this->container_start('buttons');
        $context = get_category_or_system_context($coursecat->id);
        if (has_capability('moodle/course:create', $context)) {
            // Print link to create a new course, for the current category.
            if ($coursecat->id) {
                $url = new moodle_url('/course/edit.php', array('category' => $coursecat->id, 'returnto' => 'category'));
                $output .= $this->single_button($url, get_string('addnewcourse'), 'get');
            }
        }
        ob_start();
        if (coursecat::count_all() == 1) {
            print_course_request_buttons(context_system::instance());
        } else {
            print_course_request_buttons($context);
        }
        $output .= ob_get_contents();
        ob_end_clean();
        $output .= $this->container_end();

        return $output;
    }

    /** DONE
     * Returns HTML to print tree with course categories for the frontpage. No courses should display.
     *
     * @return string
     */
    public function frontpage_combo_list() {
        global $CFG;
        require_once($CFG->libdir. '/coursecatlib.php');
        $chelper = new coursecat_helper();
        $chelper->set_subcat_depth($CFG->maxcategorydepth)->
            set_categories_display_options(array(
                'limit' => $CFG->coursesperpage,
                'viewmoreurl' => new moodle_url('/course/index.php',
                        array('browse' => 'categories', 'page' => 1))
            ))->
            set_attributes(array('class' => 'frontpage-category-names'));
        return $this->coursecat_tree($chelper, coursecat::get(0));
    }

    /** DONE
     * Returns HTML to print tree of course categories for the frontpage
     *
     * @return string
     */
    public function frontpage_categories_list() {
        global $CFG;
        require_once($CFG->libdir. '/coursecatlib.php');
        $chelper = new coursecat_helper();
        $chelper->set_subcat_depth($CFG->maxcategorydepth)->
                set_categories_display_options(array(
                    'limit' => $CFG->coursesperpage,
                    'viewmoreurl' => new moodle_url('/course/index.php',
                            array('browse' => 'categories', 'page' => 1))
                ))->
                set_attributes(array('class' => 'frontpage-category-names'));
        return $this->coursecat_tree($chelper, coursecat::get(0));
    }
    
} // End of Class

class theme_trend_format_topics_renderer extends format_topics_renderer { 
    
    /** DONE
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('div', array('class' => 'topics'));
    }

    /** DONE
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('div');
    }

    /** DONE
     * Generate the display of the header part of a section before
     * course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a single-section page
     * @param int $sectionreturn The section to return to after an action
     * @return string HTML to output.
     */
    protected function section_header($section, $course, $onsectionpage, $sectionreturn=null) {
        global $PAGE;
        $PAGE->requires->jquery();
        $o = '';
        $currenttext = '';
        $sectionstyle = '';

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            }
            if (course_get_format($course)->is_section_current($section)) {
                $sectionstyle = ' current';
            }
        }

        $o.= html_writer::start_tag('div', array('id' => 'section-'.$section->section,
            'class' => 'section mainclearfix'.$sectionstyle, 'role'=>'region',
            'aria-label'=> get_section_name($course, $section)));

        // When not on a section page, we display the section titles except the general section if null
        $hasnamenotsecpg = (!$onsectionpage && ($section->section != 0 || !is_null($section->name)));

        // When on a section page, we only display the general section title, if title is not the default one
        $hasnamesecpg = ($onsectionpage && ($section->section == 0 && !is_null($section->name)));

        $classes = ' accesshide title';
        if ($hasnamenotsecpg || $hasnamesecpg) {
            $classes = ' title';
        }
        
        // New section title to allow the accordian to function correctly
        $o .= html_writer::start_tag('div', array('class' => $classes, 'href' => '#section-'.$section->section));
        $o .= html_writer::tag('h3', $this->section_title($section, $course));
        $o .= html_writer::end_tag('div');

        // Create a span that contains the section title to be used to create the keyboard section move menu.
        $o .= html_writer::tag('span', get_section_name($course, $section), array('class' => 'hidden sectionname'));

        $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $leftcontent, array('class' => 'left side'));

        $rightcontent = $this->section_right_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        
        // This is to hide every section except the first by default. This is for the accordion.
        if ($section->section != 0) {
            $display = 'display:none;';
        }
        
        $o.= html_writer::start_tag('div', array('class' => 'content', 'style' => $display));

        $o .= $this->section_availability($section);

        $o .= html_writer::start_tag('div', array('class' => 'summary'));
        $o .= $this->format_summary_text($section);
        $o .= html_writer::end_tag('div');

        return $o;
    }

    /** DONE
     * Generate the display of the footer part of a section
     *
     * @return string HTML to output.
     */
    protected function section_footer() {
        $o = html_writer::end_tag('div'); // Content
        $o.= html_writer::end_tag('div'); // Section Main

        return $o;
    }
    
} // End of Class

class theme_trend_format_weeks_renderer extends format_weeks_renderer {
    
    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'topics'));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course) {
        $title = get_section_name($course, $section);
        $url = course_get_url($course, $section->section, array('navigation' => true));
        if ($url) {
            $title = html_writer::link($url, $title);
        }
        return $title;
    }

    /**
     * Generate the section title to be displayed on the section page, without a link
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        return get_section_name($course, $section);
    }

    /**
     * Generate the display of the header part of a section before
     * course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a single-section page
     * @param int $sectionreturn The section to return to after an action
     * @return string HTML to output.
     */
    protected function section_header($section, $course, $onsectionpage, $sectionreturn=null) {
        global $PAGE;

        $o = '';
        $currenttext = '';
        $sectionstyle = '';

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            }
            if (course_get_format($course)->is_section_current($section)) {
                $sectionstyle = ' current';
            }
        }

        $o.= html_writer::start_tag('li', array('id' => 'section-'.$section->section,
            'class' => 'section main clearfix'.$sectionstyle, 'role'=>'region',
            'aria-label'=> get_section_name($course, $section)));

        // Create a span that contains the section title to be used to create the keyboard section move menu.
        $o .= html_writer::tag('span', get_section_name($course, $section), array('class' => 'hidden sectionname'));

        $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $leftcontent, array('class' => 'left side'));

        $rightcontent = $this->section_right_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        $o.= html_writer::start_tag('div', array('class' => 'content'));

        // When not on a section page, we display the section titles except the general section if null
        $hasnamenotsecpg = (!$onsectionpage && ($section->section != 0 || !is_null($section->name)));

        // When on a section page, we only display the general section title, if title is not the default one
        $hasnamesecpg = ($onsectionpage && ($section->section == 0 && !is_null($section->name)));

        $classes = ' accesshide';
        if ($hasnamenotsecpg || $hasnamesecpg) {
            $classes = '';
        }
        $sectionname = html_writer::tag('span', $this->section_title($section, $course));
        $o.= $this->output->heading($sectionname, 3, 'sectionname' . $classes);

        $o .= $this->section_availability($section);

        $o .= html_writer::start_tag('div', array('class' => 'summary'));
        $o .= $this->format_summary_text($section);
        $o .= html_writer::end_tag('div');

        return $o;
    }

    /**
     * Generate the display of the footer part of a section
     *
     * @return string HTML to output.
     */
    protected function section_footer() {
        $o = html_writer::end_tag('div'); // Content
        $o.= html_writer::end_tag('li'); // Section Main

        return $o;
    }    
    
} // End of Class