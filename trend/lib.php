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
 * Theme functions.
 *
 * @package    theme_trend
 * @copyright 2018 Allan Levitt || http://www.alevitt.co.za
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();



/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_trend_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM) {
        $theme = theme_config::load('trend');
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}

/**
     * Creates the SCSS file
     *
     * @return string
     */
function theme_trend_get_main_scss_content($theme) {                                                                                
    global $CFG;                                                                                                                    
 
    $scss ='';                                                                                                                       
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;                                                 
    $fs = get_file_storage();                                                                                                       
 
    $context = context_system::instance();                                                                                          
    if ($filename == 'default.scss') {                                                                                              
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.                      
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');                                        
    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_trend', 'preset', 0, '/', $filename))) {              
        // This preset file was fetched from the file area for theme_trend.                
        $scss .= $presetfile->get_content();                                                                                        
    } else {                                                                                                                        
        // Safety fallback - maybe new installs etc.                                                                                
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');                                        
    }                                                                                                                                     
 
    // Pre CSS - this is loaded AFTER any prescss from the setting but before the main scss.                                        
    $pre = file_get_contents($CFG->dirroot . '/theme/trend/scss/pre.scss');
    // Trend CSS - this is loaded AFTER the main scss but before the Post CSS.                                        
    $trendcss = file_get_contents($CFG->dirroot . '/theme/trend/scss/trend.scss');   
    // Post CSS - this is loaded AFTER the trend scss but before the extra scss from the setting.                                    
    $post = file_get_contents($CFG->dirroot . '/theme/trend/scss/post.scss');                                                       
 
    // Combine them together.                                                                                                       
    return $scss . "\n" . $pre . "\n" . $trendcss . "\n" . $post;                                                                                                                  
}

/**
     * Uploads the image from settings (not required)
     *
     * @return string
     */
function theme_trend_update_settings_images($settingname) {                                                                         
    global $CFG;                                                                                                                    
                                                                                          
    $parts = explode('_', $settingname);                                                                            
    $settingname = end($parts);                                                                                                     
                                                                                
    $syscontext = context_system::instance();                                                                        
    $component = 'theme_trend';                                                                                                     
                                     
    $filename = get_config($component, $settingname);                                                               
    $extension = substr($filename, strrpos($filename, '.') + 1);                                                                    
                                                                  
    $fullpath = "/{$syscontext->id}/{$component}/{$settingname}/0{$filename}";                                                                               
    $fs = get_file_storage();                                                        
    if ($file = $fs->get_file_by_hash(sha1($fullpath))) {                                
        $pathname = $CFG->dataroot . '/pix_plugins/theme/trend/' . $settingname . '.' . $extension;                                 
                                        
        $pathpattern = $CFG->dataroot . '/pix_plugins/theme/trend/' . $settingname . '.*';                                          
                                                                                 
        @mkdir($CFG->dataroot . '/pix_plugins/theme/trend/', $CFG->directorypermissions, true);                                      
                                                                          
        foreach (glob($pathpattern) as $filename) {                                                                                 
            @unlink($filename);                                                                                                     
        }                                                                                                                           

        $file->copy_content_to($pathname);                                                                                          
    }                                                                                                                               
                                                               
    theme_reset_all_caches();                                                                                                       
}

/**
     * Returns the total number of categories on the site
     * For use in the settings page. Can be used elsewhere if needed.
     *
     * @return integer
     */
    function trend_catnum() {
        global $CFG;
        require_once($CFG->libdir. '/coursecatlib.php');
                
        $chelper = new coursecat_helper();
        
        $num = coursecat::count_all();
        
        return $num;
    } 

/**
     * Returns the NAME of the category based on the order in which they appear on the site.
     * For use in the settings page ONLY. (Although can have limited use in FOR loops).
     *
     * @return string
     */
    function trend_catname($number) {
        global $CFG;
        require_once($CFG->libdir. '/coursecatlib.php');
                        
        $chelper = new coursecat_helper();
        
        $entries = coursecat::make_categories_list();
        $count = 0;
        $namearray = array();
        
        foreach ($entries as $num => $entry) {
            $count ++;
            $namearray[] = $entry;
        }
        
        if ($number != null) {
            $name = $namearray[$number - 1];
        } else {
            $name = '';
        }
        return $name;
    }

/**
     * Returns the ID of the category based on the order in which they appear on the site.
     * For use in the settings page ONLY. (Although can have limited use in FOR loops).
     *
     * @return string
     */
    function trend_catid($number) {
        global $CFG;
        require_once($CFG->libdir. '/coursecatlib.php');
                        
        $chelper = new coursecat_helper();
        
        $entries = coursecat::make_categories_list();
        $count = 0;
        $numarray = array();
        
        foreach ($entries as $num => $entry) {
            $count ++;
            $numarray[] = $num;
        }
        
        if ($number != null) {
            $id = $numarray[$number - 1];
        } else {
            $id = '';
        }
        return $id;
    }
    
    /**
     * Returns the image linked to the category that was uploaded in the settings page as a URL.
     * 
     *
     * @return URL
     */
    function trend_catimage($catid) {
        static $theme;
        if (empty($theme)) {
            $theme = theme_config::load('trend');
        }
        
        $catimgsetting = 'catimage_'.$catid;
        
        if (!empty($theme->settings->$catimgsetting)) {
            $url = $theme->setting_file_url($catimgsetting, $catimgsetting);
        }
        
        return $url;
        
    }