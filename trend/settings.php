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

// This is used for performance, we don't need to know about these settings on every page in Moodle, only when                      
// we are looking at the admin settings pages.                                                                                      
if ($ADMIN->fulltree) {                                                                                                             
 
    // Boost provides a nice setting page which splits settings onto separate tabs. We want to use it here.                         
    $settings = new theme_boost_admin_settingspage_tabs('themesettingtrend', get_string('configtitle', 'theme_trend'));             
 
    // Each page is a tab - the first is the "General" tab.                                                                         
    $page = new admin_settingpage('theme_trend_general', get_string('generalsettings', 'theme_trend'));                             
 
    // Logo file setting.
    $name = 'theme_trend/logo';
    $title = get_string('logo', 'theme_trend');
    $description = get_string('logodesc', 'theme_trend');
    $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'));
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo', 0, $opts);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    // Replicate the preset setting from boost.                                                                                     
    $name = 'theme_trend/preset';                                                                                                   
    $title = get_string('preset', 'theme_trend');                                                                                   
    $description = get_string('preset_desc', 'theme_trend');                                                                        
    $default = 'default.scss';                                                                                                      
 
    // We list files in our own file area to add to the drop down. We will provide our own function to                              
    // load all the presets from the correct paths.                                                                                 
    $context = context_system::instance();                                                                                          
    $fs = get_file_storage();                                                                                                       
    $files = $fs->get_area_files($context->id, 'theme_trend', 'preset', 0, 'itemid, filepath, filename', false);                    
 
    $choices = [];                                                                                                                  
    foreach ($files as $file) {                                                                                                     
        $choices[$file->get_filename()] = $file->get_filename();                                                                    
    }                                                                                                                               
    // These are the built in presets from Boost.                                                                                   
    $choices['default.scss'] = 'default.scss';                                                                                                 
 
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);                                     
    $setting->set_updatedcallback('theme_reset_all_caches');                                                                        
    $page->add($setting);                                                                                                           
 
    // Preset files setting.                                                                                                        
    $name = 'theme_trend/presetfiles';                                                                                              
    $title = get_string('presetfiles','theme_trend');                                                                               
    $description = get_string('presetfiles_desc', 'theme_trend');                                                                   
 
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'preset', 0,                                         
        array('maxfiles' => 20, 'accepted_types' => array('.scss')));                                                               
    $page->add($setting);     
    
    // Login page background setting.                                                                                               
    $name = 'theme_trend/loginbackgroundimage';                                                                                     
    $title = get_string('loginbackgroundimage', 'theme_trend');                                                                     
    $description = get_string('loginbackgroundimage_desc', 'theme_trend');           
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'loginbackgroundimage');                             
    // This function will copy the image into the data_root location it can be served from.                                
    $setting->set_updatedcallback('theme_trend_update_settings_images');                                                               
    $page->add($setting);   
 
    // Variable $brand-color.                                                                                                       
    // We use an empty default value because the default colour should come from the preset.                                        
    $name = 'theme_trend/brandcolor';                                                                                               
    $title = get_string('brandcolor', 'theme_trend');                                                                               
    $description = get_string('brandcolor_desc', 'theme_trend');                                                                    
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');                                               
    $setting->set_updatedcallback('theme_reset_all_caches');                                                                        
    $page->add($setting);                                                                                                           
 
    // Must add the page after definiting all the settings!                                                                         
    $settings->add($page);                                                                                                          
 
    // Advanced settings.                                                                                                           
    $page = new admin_settingpage('theme_trend_advanced', get_string('advancedsettings', 'theme_trend'));                           
 
    // Raw SCSS to include before the content.                                                                                      
    $setting = new admin_setting_configtextarea('theme_trend/scsspre',                                                              
        get_string('rawscsspre', 'theme_trend'), get_string('rawscsspre_desc', 'theme_trend'), '', PARAM_RAW);                      
    $setting->set_updatedcallback('theme_reset_all_caches');                                                                        
    $page->add($setting);                                                                                                           
 
    // Raw SCSS to include after the content.                                                                                       
    $setting = new admin_setting_configtextarea('theme_trend/scss', get_string('rawscss', 'theme_trend'),                           
        get_string('rawscss_desc', 'theme_trend'), '', PARAM_RAW);                                                                  
    $setting->set_updatedcallback('theme_reset_all_caches');                                                                        
    $page->add($setting);                                                                                                        
 
    $settings->add($page);       
    
    // This is the Category Images tab. 
    // This is the first custom tab for Trend
    $page = new admin_settingpage('theme_trend_addimages', get_string('addimages', 'theme_trend'));  
    
    $catnum = trend_catnum();
    
    for ($i = 1; $i <= $catnum; $i++) {
        // Category image uploader.    
        $catname = trend_catname($i);
        $catnospace = str_replace(' ', '', $catname);
        $catformatname = strtolower($catnospace);
        
        $name = 'theme_trend/catimage_'.$catformatname;
        $title = $catname.' category image';
        $description = 'Upload an image that will be used for the '.$catname.' category';
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'catimage_'.$catformatname);
        $setting->set_updatedcallback('theme_trend_update_settings_images');
        $page->add($setting);
    }
    
    // Must add the page after definiting all the settings!                                                                         
    $settings->add($page);     
}





/*
 * This is to select the course category. Maybe useful if I want to make category specific colour changes
 * $setting = new admin_settings_coursecat_select($name, $visiblename, $description, $defaultsetting)
 *  */