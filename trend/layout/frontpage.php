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
 * Moodle frontpage.
 *
 * @package    core
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

foreach (explode(',', $frontpagelayout) as $v) {
    switch ($v) {
        // Display the main part of the front page.
        case FRONTPAGENEWS:
            if ($SITE->newsitems) {
                // Print forums only when needed.
                require_once($CFG->dirroot .'/mod/forum/lib.php');

                if (! $newsforum = forum_get_course_forum($SITE->id, 'news')) {
                    print_error('cannotfindorcreateforum', 'forum');
                }

                // Fetch news forum context for proper filtering to happen.
                $newsforumcm = get_coursemodule_from_instance('forum', $newsforum->id, $SITE->id, false, MUST_EXIST);
                $newsforumcontext = context_module::instance($newsforumcm->id, MUST_EXIST);

                $forumname = format_string($newsforum->name, true, array('context' => $newsforumcontext));
                echo html_writer::link('#skipsitenews',
                    get_string('skipa', 'access', core_text::strtolower(strip_tags($forumname))),
                    array('class' => 'skip-block skip'));

                // Wraps site news forum in div container.
                echo html_writer::start_tag('div', array('id' => 'site-news-forum'));

                if (isloggedin()) {
                    $SESSION->fromdiscussion = $CFG->wwwroot;
                    $subtext = '';
                    if (\mod_forum\subscriptions::is_subscribed($USER->id, $newsforum)) {
                        if (!\mod_forum\subscriptions::is_forcesubscribed($newsforum)) {
                            $subtext = get_string('unsubscribe', 'forum');
                        }
                    } else {
                        $subtext = get_string('subscribe', 'forum');
                    }
                    echo $OUTPUT->heading($forumname);
                    $suburl = new moodle_url('/mod/forum/subscribe.php', array('id' => $newsforum->id, 'sesskey' => sesskey()));
                    echo html_writer::tag('div', html_writer::link($suburl, $subtext), array('class' => 'subscribelink'));
                } else {
                    echo $OUTPUT->heading($forumname);
                }

                forum_print_latest_discussions($SITE, $newsforum, $SITE->newsitems, 'plain', 'p.modified DESC');

                // End site news forum div container.
                echo html_writer::end_tag('div');

                echo html_writer::tag('span', '', array('class' => 'skip-block-to', 'id' => 'skipsitenews'));
            }
        break;

        case FRONTPAGEENROLLEDCOURSELIST:
            $mycourseshtml = $courserenderer->frontpage_my_courses();
            if (!empty($mycourseshtml)) {
                echo html_writer::link('#skipmycourses',
                    get_string('skipa', 'access', core_text::strtolower(get_string('mycourses'))),
                    array('class' => 'skip skip-block'));

                // Wrap frontpage course list in div container.
                echo html_writer::start_tag('div', array('id' => 'frontpage-course-list'));

                echo $OUTPUT->heading(get_string('mycourses'));
                echo $mycourseshtml;

                // End frontpage course list div container.
                echo html_writer::end_tag('div');

                echo html_writer::tag('span', '', array('class' => 'skip-block-to', 'id' => 'skipmycourses'));
                break;
            }
            // No "break" here. If there are no enrolled courses - continue to 'Available courses'.

        case FRONTPAGEALLCOURSELIST:
            $availablecourseshtml = $courserenderer->frontpage_available_courses();
            if (!empty($availablecourseshtml)) {
                echo html_writer::link('#skipavailablecourses',
                    get_string('skipa', 'access', core_text::strtolower(get_string('availablecourses'))),
                    array('class' => 'skip skip-block'));

                // Wrap frontpage course list in div container.
                echo html_writer::start_tag('div', array('id' => 'frontpage-course-list'));

                echo $OUTPUT->heading(get_string('availablecourses'));
                echo $availablecourseshtml;

                // End frontpage course list div container.
                echo html_writer::end_tag('div');

                echo html_writer::tag('span', '', array('class' => 'skip-block-to', 'id' => 'skipavailablecourses'));
            }
        break;

        case FRONTPAGECATEGORYNAMES:
            echo html_writer::link('#skipcategories',
                get_string('skipa', 'access', core_text::strtolower(get_string('categories'))),
                array('class' => 'skip skip-block'));

            // Wrap frontpage category names in div container.
            echo html_writer::start_tag('div', array('id' => 'frontpage-category-names'));

            //echo $OUTPUT->heading(get_string('categories'));
            echo $courserenderer->frontpage_categories_list();

            // End frontpage category names div container.
            echo html_writer::end_tag('div');

            echo html_writer::tag('span', '', array('class' => 'skip-block-to', 'id' => 'skipcategories'));
        break;

        case FRONTPAGECATEGORYCOMBO:
            echo html_writer::link('#skipcourses',
                get_string('skipa', 'access', core_text::strtolower(get_string('courses'))),
                array('class' => 'skip skip-block'));

            // Wrap frontpage category combo in div container.
            echo html_writer::start_tag('div', array('id' => 'frontpage-category-combo'));

            echo $OUTPUT->heading(get_string('courses'));
            echo $courserenderer->frontpage_combo_list();

            // End frontpage category combo div container.
            echo html_writer::end_tag('div');

            echo html_writer::tag('span', '', array('class' => 'skip-block-to', 'id' => 'skipcourses'));
        break;

        case FRONTPAGECOURSESEARCH:
            echo $OUTPUT->box($courserenderer->course_search_form('', 'short'), 'mdl-align');
        break;

    }
    echo '<br />';
}
if ($editing && has_capability('moodle/course:create', context_system::instance())) {
    echo $courserenderer->add_new_course_button();
}
