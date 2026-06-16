<?php
/**
 * @package     uPlannerConnect
 * @copyright   cristian machado mosquera <cristian.machado@correounivalle.edu.co>
 * @copyright   Daniel Dorado <doradodaniel14@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die();

use local_uplannerconnect\plugin_config\plugin_config;

$pluginName = plugin_config::PLUGIN_NAME_LOCAL;
$pluginNameLocal = plugin_config::PLUGIN_NAME;

if ($hassiteconfig) {
    $ADMIN->add(
        'localplugins',
        new admin_category(
            'local_uplannerconnect_settings',
            new lang_string('pluginname', 'local_uplannerconnect')
        )
    );
    $settings = new admin_settingpage(
        'managelocaluplannerconnect',
        new lang_string('manage', 'local_uplannerconnect')
    );
    if ($ADMIN->fulltree) {
        $settings->add( new admin_setting_heading(
                'generalsettingsheading',
                new lang_string('generalsettingsheading', 'local_uplannerconnect'),
                new lang_string('generalsettingsheading_desc', 'local_uplannerconnect')
            )
        );
        // uPlanner key
        $settings->add(new admin_setting_heading(
                $pluginName . '_key',
                get_string('key_heading', $pluginName),
                'Uplanner key'
            )
        );
        $settings->add(new admin_setting_configtext(
                $pluginName . '/key',
                get_string('key', $pluginName),
                get_string('key_desc', $pluginName),
                'uPlanner key',
                PARAM_TEXT
            )
        );
        // uPlanner token
        $settings->add(new admin_setting_heading(
                $pluginName . '_token',
                get_string('token_endpoint_heading', $pluginName),
                'uPlanner token url'
            )
        );
        $settings->add(new admin_setting_configtext(
                $pluginName . '/token_endpoint',
                get_string('token_endpoint', $pluginName),
                get_string('token_endpoint_desc', $pluginName),
                'https://univalle-qa-api.u-planner.com/api/auth/signature',
                PARAM_URL
            )
        );
        // uPlanner base url
        $settings->add(new admin_setting_heading(
                $pluginName . '_base_url',
                get_string('base_url_heading', $pluginName),
                'uPlanner base url (messages)'
            )
        );
        $settings->add(new admin_setting_configtext(
                $pluginName . '/base_url',
                get_string('base_url', $pluginName),
                get_string('base_url_desc', $pluginName),
                'https://u-plannerplus.servicebus.windows.net/topic/messages',
                PARAM_URL
            )
        );
        // uPlanner materials
        $settings->add(new admin_setting_heading(
                $pluginName . '_materials',
                get_string('materials_endpoint_heading', $pluginName),
                'uPlanner materials'
            )
        );
        $settings->add(new admin_setting_configtext(
                $pluginName . '/materials_endpoint',
                get_string('materials_endpoint', $pluginName),
                get_string('materials_endpoint_desc', $pluginName),
                'univalle/materials',
                PARAM_URL
            )
        );
        // uPlanner announcements
        $settings->add(new admin_setting_heading(
                $pluginName . '_announcements',
                get_string('announcements_endpoint_heading', $pluginName),
                'uPlanner announcements'
            )
        );
        $settings->add(new admin_setting_configtext(
                $pluginName . '/announcements_endpoint',
                get_string('announcements_endpoint', $pluginName),
                get_string('announcements_endpoint_desc', $pluginName),
                'univalle/announcements',
                PARAM_URL
            )
        );
        // uPlanner evaluation_structure
        $settings->add(new admin_setting_heading(
                $pluginName . '_evaluation_structure',
                get_string('evaluation_structure_endpoint_heading', $pluginName),
                'uPlanner evaluation structure'
            )
        );
        $settings->add(new admin_setting_configtext(
                $pluginName . '/evaluation_structure_endpoint',
                get_string('evaluation_structure_endpoint', $pluginName),
                get_string('evaluation_structure_endpoint_desc', $pluginName),
                'univalle/evaluationStructure',
                PARAM_URL
            )
        );
        // uPlanner grades
        $settings->add(new admin_setting_heading(
                $pluginName . '_grades',
                get_string('grades_endpoint_heading', $pluginName),
                'uPlanner grades'
            )
        );
        $settings->add(new admin_setting_configtext(
                $pluginName . '/grades_endpoint',
                get_string('grades_endpoint', $pluginName),
                get_string('grades_endpoint_desc', $pluginName),
                'univalle/grades',
                PARAM_URL
            )
        );
        // uPlanner messages host
        $settings->add(new admin_setting_heading(
                $pluginName . '_messages_host',
                get_string('messages_host_heading', $pluginName),
                'uPlanner messages host'
            )
        );
        $settings->add(new admin_setting_configtext(
                $pluginName . '/messages_host',
                get_string('messages_host', $pluginName),
                get_string('messages_host_desc', $pluginName),
                '',
                PARAM_TEXT
            )
        );
        // uPlanner messages database
        $settings->add(new admin_setting_heading(
                $pluginName . '_messages_database',
                get_string('messages_database_heading', $pluginName),
                'uPlanner messages database'
            )
        );
        $settings->add(new admin_setting_configtext(
                $pluginName . '/messages_database',
                get_string('messages_database', $pluginName),
                get_string('messages_database_desc', $pluginName),
                '',
                PARAM_TEXT
            )
        );
        // uPlanner messages user
        $settings->add(new admin_setting_heading(
                $pluginName . '_messages_user',
                get_string('messages_user_heading', $pluginName),
                'uPlanner messages user'
            )
        );
        $settings->add(new admin_setting_configtext(
                $pluginName . '/messages_user',
                get_string('messages_user', $pluginName),
                get_string('messages_user_desc', $pluginName),
                '',
                PARAM_TEXT
            )
        );
        // uPlanner messages password
        $settings->add(new admin_setting_heading(
                $pluginName . '_messages_password',
                get_string('messages_password_heading', $pluginName),
                'uPlanner messages password'
            )
        );
        $settings->add(new admin_setting_configtext(
                $pluginName . '/messages_password',
                get_string('messages_password', $pluginName),
                get_string('messages_password_desc', $pluginName),
                '',
                PARAM_TEXT
            )
        );
        // uPlanner messages port
        $settings->add(new admin_setting_heading(
                $pluginName . '_messages_port',
                get_string('messages_port_heading', $pluginName),
                'uPlanner messages port'
            )
        );
        $settings->add(new admin_setting_configtext(
                $pluginName . '/messages_port',
                get_string('messages_port', $pluginName),
                get_string('messages_port_desc', $pluginName),
                '',
                PARAM_INT
            )
        );

        $settings->add(new admin_setting_configtext(
                $pluginName . '/task_runtime_primary',
                get_string('task_rutine_primary', $pluginName),
                get_string('task_rutine_primary_desc', $pluginName),
                5,
                PARAM_INT
            )
        );

        $settings->add(new admin_setting_heading(
                $pluginName . '_notification_emails',
                get_string('notification_emails_heading', $pluginName),
                get_string('notification_emails_heading_desc', $pluginName)
            )
        );
        $settings->add(new admin_setting_configcheckbox(
                $pluginName . '/enable_notification_emails',
                get_string('enable_notification_emails', $pluginName),
                get_string('enable_notification_emails_desc', $pluginName),
                0
            )
        );
        $settings->add(new admin_setting_configtext(
                $pluginName . '/notification_email',
                get_string('notification_email', $pluginName),
                get_string('notification_email_desc', $pluginName),
                'notifications@example.com',
                PARAM_EMAIL
            )
        );
    }

    // Agrega la página de configuración al árbol
    $ADMIN->add('localplugins', $settings);

    $ADMIN->add('reports', 
      new admin_category($pluginNameLocal, 
      new lang_string('pluginname', $pluginName)));

    $ADMIN->add($pluginNameLocal,
        new admin_externalpage('uplanerIndex', new lang_string('reports', $pluginName),
            new moodle_url('/local/'.$pluginNameLocal.'/index.php'), 'moodle/site:configview'
        )
    );
    
    
}