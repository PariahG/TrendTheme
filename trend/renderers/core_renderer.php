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
 * Core renderer.
 *
 * @package    theme_trend
 * @copyright  2018 Allan Levitt || http://www.alevitt.co.za/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// namespace theme_trend\output;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/course/renderer.php');

class theme_trend_core_renderer extends theme_boost\output\core_renderer {

    /** @var custom_menu_item language The language menu if created */
    protected $language = null;

    /**
     * Starting wrapper for header elements.
     * This is done in order to include the header blocks region in the correct location.
     *
     * @return string HTML to display the main header.
     */
    public function start_header() {
        global $PAGE;

        $html = html_writer::start_tag('header', array('id' => 'page-header', 'class' => 'row'));
        $html .= html_writer::start_div('col-xs-12 p-a-1');
        $html .= html_writer::div($this->context_header_settings_menu(), 'pull-xs-right context-header-settings-menu');
        $html .= html_writer::start_div('pull-xs-left');
        $html .= $this->context_header();
        $html .= html_writer::end_div();
        $html .= html_writer::tag('div', $this->course_header(), array('id' => 'course-header'));
        $html .= html_writer::end_div();
        return $html;
    }

    /**
     * Closing wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function end_header() {
        global $PAGE;
        
        $html = html_writer::end_tag('header');
        return $html;
    }
    
    /**
     * Breadcrumb nav for Trend.
     *
     * @return string HTML to display the main header.
     */
    public function nav_trend() {
        global $PAGE;
        
        $html = '';
        
        $pageheadingbutton = $this->page_heading_button();
        if (empty($PAGE->layout_options['nonavbar'])) {
            $html .= html_writer::start_div('clearfix w-100 pull-xs-left', array('id' => 'page-navbar'));
            $html .= html_writer::tag('div', $this->navbar(), array('class' => 'breadcrumb-nav'));
            $html .= html_writer::div($pageheadingbutton, 'breadcrumb-button pull-xs-right');
            $html .= html_writer::end_div();
        } else if ($pageheadingbutton) {
            $html .= html_writer::div($pageheadingbutton, 'breadcrumb-button nonavbar pull-xs-right');
        }
        
        return $html;
    }

    /**
     * Override.
     *
     * @param array $headerinfo The header info.
     * @param int $headinglevel What level the 'h' tag will be.
     * @return string HTML for the header bar.
     */
    public function context_header($headerinfo = null, $headinglevel = 1) {
        global $SITE;
        
        $sitename = format_string($SITE->shortname, true, array('context' => context_course::instance(SITEID)));
        if ($this->should_display_main_logo($headinglevel)) {
            return html_writer::div(html_writer::empty_tag('img', [
                'src' => $this->get_logo_url(null, 150), 'alt' => $sitename]), 'logo');
        } else {
            $shortname = html_writer::start_div('page-context-header');
            $shortname .= html_writer::start_div('page-header-headings');
            $shortname .= html_writer::tag('h1', $sitename, null);
            $shortname .= html_writer::end_div();
            $shortname .= html_writer::end_div();
            return $shortname;
        }

        return parent::context_header($headerinfo, $headinglevel);
    }

    /**
     * Prints a nice side block with an optional header.
     *
     * @param block_contents $bc HTML for the content
     * @param string $region the region the block is appearing in.
     * @return string the HTML to be output.
     */
    public function block(block_contents $bc, $region) {
        $bc = clone($bc); // Avoid messing up the object passed in.
        if (empty($bc->blockinstanceid) || !strip_tags($bc->title)) {
            $bc->collapsible = block_contents::NOT_HIDEABLE;
        }

        $id = !empty($bc->attributes['id']) ? $bc->attributes['id'] : uniqid('block-');
        $context = new stdClass();
        $context->skipid = $bc->skipid;
        $context->blockinstanceid = $bc->blockinstanceid;
        $context->dockable = $bc->dockable;
        $context->id = $id;
        $context->hidden = $bc->collapsible == block_contents::HIDDEN;
        $context->skiptitle = strip_tags($bc->title);
        $context->showskiplink = !empty($context->skiptitle);
        $context->arialabel = $bc->arialabel;
        $context->ariarole = !empty($bc->attributes['role']) ? $bc->attributes['role'] : 'complementary';
        $context->type = $bc->attributes['data-block'];
        $context->title = $bc->title;
        $context->content = $bc->content;
        $context->annotation = $bc->annotation;
        $context->footer = $bc->footer;
        $context->hascontrols = !empty($bc->controls);
        if ($context->hascontrols) {
            $context->controls = $this->block_controls($bc->controls, $id);
        }

        return $this->render_from_template('theme_trend/block', $context);
    }    
    
    /**
     *  Footer contact region
     *  
     * Calls the footer info from settings and renders the contact info.
     * 
     */
    public function footer_contact() {
        global $OUTPUT;
        
        // Get the image locations for the icons
        $mailicon = $OUTPUT->image_url('icons/mail', 'theme_trend');
        $phoneicon = $OUTPUT->image_url('icons/phone', 'theme_trend');
        
        // Retrieve the contact info
        $email = trend_footer_info(MAIL);
        $phone = trend_footer_info(PHONE);

        $html = html_writer::tag('h4', 'Contact Us', null);
        $html .= html_writer::start_tag('p');
        $html .= html_writer::empty_tag('img', array('src' => $mailicon, 'class' => 'footer-icon'));
        $html .= html_writer::tag('a', $email, array('href' => 'mailto:' . $email, 'class' => 'footer-link'));
        $html .= html_writer::end_tag('p');
        $html .= html_writer::start_tag('p');
        $html .= html_writer::empty_tag('img', array('src' => $phoneicon, 'class' => 'footer-icon'));
        $html .= html_writer::tag('span', $phone, array('class' => 'footer-link'));
        $html .= html_writer::end_tag('p');
        
        return $html;        
    }  
    
    /**
     *  Footer copyright info
     *  
     * Calls the copyright info from settings and renders the relevant footer area.
     * 
     */
    public function footer_copy() {
        global $OUTPUT;
        
        // Retrieve the copyright info
        $info = trend_footer_info(COPY);

        $html = html_writer::tag('div', $info, array('class' => 'footer-copyright'));
        
        return $html;        
    }

    /**
     * Render the login page template.
     * @param \core_auth\output\login $form
     * @return type|string
     */
    public function render_login(\core_auth\output\login $form) {
        global $CFG, $PAGE, $SITE, $OUTPUT;
        $context = $form->export_for_template($this);
        // Override because rendering is not supported in template yet.
        $context->cookieshelpiconformatted = $this->help_icon('cookiesenabled');
        $context->errorformatted = $this->error_text($context->error);
        $maincontent = $this->render_from_template('theme_trend/loginform', $context);
        return  $maincontent;
    }
    
} // End of Class
