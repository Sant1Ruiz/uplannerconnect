<?php
/**
 * @package     uPlannerConnect
 * @copyright   Cristian Machado Mosquera <cristian.machado@correounivalle.edu.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

// Plugin Settings.
$string['manage'] = 'Settings for the uPlannerConnect plugin';
$string['generalsettingsheading'] = 'General settings';
$string['generalsettingsheading_desc'] = 'General settings for the plugin';

$string['notification_emails_heading'] = 'Email notifications';
$string['notification_emails_heading_desc'] = 'Settings for CSV and log attachments sent after uPlanner sync tasks.';
$string['enable_notification_emails'] = 'Enable email notifications';
$string['enable_notification_emails_desc'] = 'When enabled, scheduled tasks and manual resends can email reports to the address below. Requires Moodle outgoing mail to be configured.';
$string['notification_email'] = 'Notification recipient email';
$string['notification_email_desc'] = 'Email address that receives uPlanner task reports (CSV and log files).';

// Access.
$string['reports'] = 'Reports for the UplannerConnect plugin';

// Email
$string['send_uplanner_email_subject_grades'] = 'Send uPlanner Grades';
$string['send_uplanner_email_subject_evaluations'] = 'Send uPlanner Evaluations';
$string['send_uplanner_email_subject_anounciente'] = 'Send uPlanner Anounciente';
$string['send_uplanner_email_subject_materials'] = 'Send uPlanner Materials';

$string['error_uplanner_email_subject_grades'] = 'Re Send uPlanner Grades';
$string['error_uplanner_email_subject_evaluations'] = 'Re Send uPlanner Evaluations';
$string['error_uplanner_email_subject_anounciente'] = 'Re Send uPlanner Anounciente';
$string['error_uplanner_email_subject_materials'] = 'Re Send uPlanner Materials';

$string['clean_uplanner_email_subject_grades'] = 'Clean uPlanner Grades';
$string['clean_uplanner_email_subject_evaluations'] = 'Clean uPlanner Evaluations';
$string['clean_uplanner_email_subject_anounciente'] = 'Clean uPlanner Anounciente';
$string['clean_uplanner_email_subject_materials'] = 'Clean uPlanner Materials';

$string['send_send_log'] = 'UPLANNER TASK SEND LOG';
$string['clean_clean_log'] = 'UPLANNER TASK CLEAN LOG';
$string['error_error_log'] = 'UPLANNER TASK ERROR LOG';
$string['resend_resend_log'] = 'UPLANNER TASK RESEND LOG';

// Tasks.
$string['syncsendtuplannertask'] = 'Send uPlanner Task';
$string['synccleanuplannertask'] = 'Clean Log uPlanner Task';
$string['syncerroruplannertask'] = 'Send registers no processing in uPlanner Task';

// Register
$string['uplanner_log_processing'] = ' Registration processed successfully';
$string['uplanner_log_dont_processing'] = 'Error in message sent to uPlanner';

// Module
$string['pluginname'] = 'uPlannerConnect';
$string['welcome'] = 'Welcome';
$string['description'] = 'It aims to extract information from the campus and send it to uPlanner.';
$string['title'] = 'uPlannerConnect';
$string['uplannerconnect:index'] = 'uPlannerConnect';

// uPlanner key field
$string['key_heading'] = 'uPlanner key configuration';
$string['key'] = 'uPlanner key';
$string['key_desc'] = 'Enter the uPlanner key';

// uPlanner base url field
$string['base_url_heading'] = 'uPlanner base url configuration';
$string['base_url'] = 'uPlanner base url';
$string['base_url_desc'] = 'Enter the uPlanner base url';

// uPlanner token field
$string['token_endpoint_heading'] = 'Token url configuration';
$string['token_endpoint'] = 'Token url';
$string['token_endpoint_desc'] = 'Enter the url for the token.';

// uPlanner materials field
$string['materials_endpoint_heading'] = 'Materials topic configuration';
$string['materials_endpoint'] = 'Materials topic';
$string['materials_endpoint_desc'] = 'Enter the topic for the materials endpoint.';

// uPlanner announcements field
$string['announcements_endpoint_heading'] = 'Announcements topic configuration';
$string['announcements_endpoint'] = 'Announcements topic';
$string['announcements_endpoint_desc'] = 'Enter the topic for the announcements endpoint.';

// uPlanner evaluation_structure field
$string['evaluation_structure_endpoint_heading'] = 'Evaluation structure topic configuration';
$string['evaluation_structure_endpoint'] = 'Evaluation structure topic';
$string['evaluation_structure_endpoint_desc'] = 'Enter the topic for the evaluation structure endpoint.';

// uPlanner grades field
$string['grades_endpoint_heading'] = 'Grades topic configuration';
$string['grades_endpoint'] = 'Grades topic';
$string['grades_endpoint_desc'] = 'Enter the topic for the grades endpoint.';

// uPlanner messages host
$string['messages_host_heading'] = 'Messages host configuration';
$string['messages_host'] = 'Messages host';
$string['messages_host_desc'] = 'Enter teh messages host.';

// uPlanner messages database
$string['messages_database_heading'] = 'Messages database configuration';
$string['messages_database'] = 'Messages database';
$string['messages_database_desc'] = 'Enter teh messages database.';

// uPlanner messages user
$string['messages_user_heading'] = 'Messages user configuration';
$string['messages_user'] = 'Messages user';
$string['messages_user_desc'] = 'Enter teh messages user.';

// uPlanner messages password
$string['messages_password_heading'] = 'Messages password configuration';
$string['messages_password'] = 'Messages password';
$string['messages_password_desc'] = 'Enter teh messages password.';

// uPlanner messages port
$string['messages_port_heading'] = 'Messages port configuration';
$string['messages_port'] = 'Messages port';
$string['messages_port_desc'] = 'Enter teh messages port.';

// uPlanner task rutine field
$string['task_rutine_primary'] = 'Primary Task Runtime';
$string['task_rutine_primary_desc'] = 'Enter the runtime in seconds for the primary task.';

// Events.
$string['uplannerconnect:uplanner_dispatch_tmp'] = "Module Files Uploaded";