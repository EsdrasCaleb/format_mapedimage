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
 * Mapedimage Format - A topics based format that uses user selectable image maped top to popup a light box of the section.
 *
 * @package    format_mapedimage
 * @copyright  &copy; 2019 Jose Wilson  in respect to modifications of grid format.
 * @copyright  &copy; 2012 G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - {@link http://about.me/gjbarnard} and
 *                           {@link http://moodle.org/user/profile.php?id=442195}
 * @copyright  &copy; 2022    Paul Krix and Julian Ridden. trail format
 *@author     Esdras Caleb - {@link http://github.com/esdrasCaleb/} 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * TODO ERRO SECTION name
 */

defined('MOODLE_INTERNAL') || die();

// Plugin version.
$plugin->version = 2022070215;

// Required Moodle version.
$plugin->requires  = 2022041900.00; // Moodle 4.0 (Build: 20220419).

// Full name of the plugin.
$plugin->component = 'format_mapedimage';

// Software maturity level.
$plugin->maturity = MATURITY_BETA;

// User-friendly version number.
$plugin->release = '1.0.0.1';
$plugin->dependencies = [
    'format_trail' => ANY_VERSION,
];