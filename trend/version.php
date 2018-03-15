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
 * Trend.
 *
 * @package    theme_trend
 * @copyright  2018 Allan Levitt || http://www.alevitt.co.za/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

// Trend's date of release.
$plugin->version = 2018031501; // Year | Month | Day | Increment

// Trend requires Moodle 3.4 or higher.
$plugin->requires = 2017111300;

$plugin->component = 'theme_trend';

// This is the release number.
$plugin->release = '0.2.1'; // Main | Minor | Increment

// Trend's release maturity. Do not use ALPHA or BETA in production sites.
$plugin->maturity = MATURITY_ALPHA;

// Trend requires the Boost theme from 3.4 or higher.
$plugin->dependencies = [
    'theme_boost' => 2017111300
];
