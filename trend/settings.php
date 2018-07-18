<?php
// This file is part of Ranking block for Moodle - http://moodle.org/
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
 * Theme Trend block settings file
 *
 * @package    theme_trend
 * @copyright  2018 Allan Levitt || http://www.alevitt.co.za/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

// Need to manually include the lib file as this no longer loads in on its own.
require_once($CFG->dirroot . '/theme/trend/lib.php');
                                                                               
if ($ADMIN->fulltree) {                                                                                                             
                      
    $settings = new theme_boost_admin_settingspage_tabs('themesettingtrend', get_string('configtitle', 'theme_trend'));             
                                                           
    // General settings || Boost default
    $page = new admin_settingpage('theme_trend_general', get_string('generalsettings', 'theme_trend'));                             
 
    $name = 'theme_trend/logo';
    $title = get_string('logo', 'theme_trend');
    $description = get_string('logodesc', 'theme_trend');
    $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'));
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo', 0, $opts);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
                                                                                  
    $name = 'theme_trend/preset';                                                                                                   
    $title = get_string('preset', 'theme_trend');                                                                                   
    $description = get_string('preset_desc', 'theme_trend');                                                                        
    $default = 'default.scss';                                                                                                      
                                                                              
    $context = context_system::instance();                                                                                          
    $fs = get_file_storage();                                                                                                       
    $files = $fs->get_area_files($context->id, 'theme_trend', 'preset', 0, 'itemid, filepath, filename', false);                    
 
    $choices = [];                                                                                                                  
    foreach ($files as $file) {                                                                                                     
        $choices[$file->get_filename()] = $file->get_filename();                                                                    
    }                                                                                                                                                                      
    $choices['default.scss'] = 'default.scss';                                                                                                 
 
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);                                     
    $setting->set_updatedcallback('theme_reset_all_caches');                                                                        
    $page->add($setting);                                                                                                           
                                                                                               
    $name = 'theme_trend/presetfiles';                                                                                              
    $title = get_string('presetfiles','theme_trend');                                                                               
    $description = get_string('presetfiles_desc', 'theme_trend');                                                                   
 
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'preset', 0,                                         
        array('maxfiles' => 20, 'accepted_types' => array('.scss')));                                                               
    $page->add($setting);     
                                                                                               
    $name = 'theme_trend/loginbackgroundimage';                                                                                     
    $title = get_string('loginbackgroundimage', 'theme_trend');                                                                     
    $description = get_string('loginbackgroundimage_desc', 'theme_trend');           
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'loginbackgroundimage');                                                  
    $setting->set_updatedcallback('theme_trend_update_settings_images');                                                               
    $page->add($setting);   
                                  
    $name = 'theme_trend/brandcolor';                                                                                               
    $title = get_string('brandcolor', 'theme_trend');                                                                               
    $description = get_string('brandcolor_desc', 'theme_trend');                                                                    
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');                                               
    $setting->set_updatedcallback('theme_reset_all_caches');                                                                        
    $page->add($setting);               
                                  
    $name = 'theme_trend/brandsecondcolor';                                                                                               
    $title = get_string('brandsecondcolor', 'theme_trend');                                                                               
    $description = get_string('brandsecondcolor_desc', 'theme_trend');                                                                    
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');                                               
    $setting->set_updatedcallback('theme_reset_all_caches');                                                                        
    $page->add($setting);                                                                                                  
                                                              
    $settings->add($page);                                                                                                          
 
    // Advanced settings
    $page = new admin_settingpage('theme_trend_advanced', get_string('advancedsettings', 'theme_trend'));

    /* Footer settings
     * 
     * All settings under this footer section should be named 'footer[setting]' in order for the trend_footer_info()
     * function to pull the correct information. (footermail, footerphone, footertwitter, etc.)
     * 
     */
    
    $name = 'theme_trend/footertitle';
    $setting = new admin_setting_heading($name, 'Footer settings','');
    $page->add($setting);
    
    $name = 'theme_trend/footermail';
    $title =get_string('footermailtitle', 'theme_trend');
    $description = get_string('footermaildesc', 'theme_trend');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    $name = 'theme_trend/footerphone';
    $title =get_string('footerphonetitle', 'theme_trend');
    $description = get_string('footerphonedesc', 'theme_trend');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    $name = 'theme_trend/footercopy';
    $title =get_string('footercopytitle', 'theme_trend');
    $description = get_string('footercopydesc', 'theme_trend');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
 
    $settings->add($page);
    
    // Category settings 
    $page = new admin_settingpage('theme_trend_addimages', get_string('addimages', 'theme_trend'));  
    
    $catnum = trend_catno();
    
    for ($i = 1; $i <= $catnum; $i++) {
        // Category Section generator.    
        $catname = trend_catid($i, 'name');
        $catid =  trend_catid($i);

        $name = 'theme_trend/catname' . $catid;
        $setting = new admin_setting_heading($name, $catname,'');
        $page->add($setting);
        
        $name = 'theme_trend/catview' . $catid;
        $title = $catname . ' category visibility';
        $description = 'Set whether or not the ' . $catname . ' category will be visible to users who are not enrolled in any courses within the category.';
        $default = '0';
        $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        $name = 'theme_trend/catcourseview' . $catid;
        $title = $catname . ' course visibility';
        $description = 'Set whether or not courses in the ' . $catname . ' category will be visible to users who are not enrolled in those courses.';
        $default = '0';
        $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        $name = 'theme_trend/catimage_' . $catid;
        $title = $catname . ' category image';
        $description = 'Upload an image that will be used for the ' . $catname . ' category';
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'catimage_' . $catid);
        $setting->set_updatedcallback('theme_trend_update_settings_images');
        $page->add($setting);
    }
                                                                      
    $settings->add($page);     
}