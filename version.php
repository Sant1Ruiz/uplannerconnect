<?php
/**
 * @package     uPlannerConnect
 * @copyright   cristian machado mosquera <cristian.machado@correounivalle.edu.co>
 * @copyright   Daniel Dorado <doradodaniel14@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2026060300;
$plugin->component = 'local_uplannerconnect';

$plugin->requires = 2022010100; 
// $plugin->maturity = MATURITY_ALPHA;

$plugin->release = '1.0.0';
$plugin->release = '1.0 (Build: 2023090300)';
$plugin->author = 'Samuel Ramirez & Cristian Machado Mosquera & Daniel Dorado & Isabela Rosero Obando';
$plugin->authorcontact = 'samuel.ramirez@correounivalle.edu.co & cristian.machado@correounivalle.edu.co & doradodaniel14@gmail.com & isabela.rosero@correounivalle.edu.co';

$plugin->license = 'GNU GPL v3 or later';
$plugin->description = 'Este plugin se utiliza para enviar información a uPlanner.';

$plugin->dependencies = [
    'local_iracv' => 2022042701,
];