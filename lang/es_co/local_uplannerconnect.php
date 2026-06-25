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

$string['notification_emails_heading'] = 'Notificaciones por correo';
$string['notification_emails_heading_desc'] = 'Configuración de los informes (CSV y logs) enviados tras las tareas de sincronización con uPlanner.';
$string['enable_notification_emails'] = 'Activar envío de correos';
$string['enable_notification_emails_desc'] = 'Si está activo, las tareas programadas y los reenvíos manuales pueden enviar informes al correo indicado abajo. Requiere que el correo saliente de Moodle esté configurado.';
$string['notification_email'] = 'Correo destinatario de notificaciones';
$string['notification_email_desc'] = 'Dirección que recibe los informes de las tareas uPlanner (archivos CSV y de log).';

// Tasks.
$string['syncsendtuplannertask'] = 'Send uPlanner Task';
$string['synccleanuplannertask'] = 'Clean Log uPlanner Task';
$string['syncerroruplannertask'] = 'Send registers no processing in uPlanner Task';

// Email
$string['upllaner_email_subject_grades'] = 'uPlanner Grades';
$string['upllaner_email_subject_evaluations'] = 'uPlanner Evaluations';
$string['upllaner_email_subject_anounciente'] = 'uPlanner Anounciente';
$string['upllaner_email_subject_materials'] = 'uPlanner Materials';
$string['upllaner_email_subject_summary'] = 'uPlanner Summary';

$string['delete_upllaner_email_subject_grades'] = 'Delete uPlanner Grades';
$string['delete_upllaner_email_subject_evaluations'] = 'Delete uPlanner Evaluations';
$string['delete_upllaner_email_subject_anounciente'] = 'Delete uPlanner Anounciente';
$string['delete_upllaner_email_subject_materials'] = 'Delete uPlanner Materials';
$string['delete_upllaner_email_subject_summary'] = 'Delete uPlanner Summary';


$string['pluginname'] = 'uPlannerConnect';
$string['welcome'] = 'Bienvenido';
$string['description'] = 'Tiene como objectivo extraer informacion del campus y enviarla a uPlaner.';
$string['title'] = 'uPlannerConnect';
$string['uplannerconnect:index'] = 'uPlannerConnect';

// lang/en/local_uplannerconnect.php
$string['controllerpath'] = 'Ruta del controlador:';
$string['controllerpathdesc'] = 'Especifica la ruta del controlador.';

// uPlanner key field
$string['key_heading'] = 'Configuración de la key de uPlanner';
$string['key'] = 'uPlanner key';
$string['key_desc'] = 'Ingrese la key de uPlanner';

// uPlanner base url field
$string['base_url_heading'] = 'Configuración del base url de  uPlanner';
$string['base_url'] = 'Base url de uPlanner';
$string['base_url_desc'] = 'Ingrese el base url de uPlanner';

// uPlanner token field
$string['token_endpoint_heading'] = 'Configuración de la url para el token';
$string['token_endpoint'] = 'Url del token';
$string['token_endpoint_desc'] = 'Ingrese la url para el endpoint del token.';

// uPlanner materials field
$string['materials_endpoint_heading'] = 'Configuración del topic para materials';
$string['materials_endpoint'] = 'Topic de materials';
$string['materials_endpoint_desc'] = 'Ingrese el topic para el endpoint de materiales.';

// uPlanner announcements field
$string['announcements_endpoint_heading'] = 'Configuración del topic para anuncios';
$string['announcements_endpoint'] = 'Topic de anuncios';
$string['announcements_endpoint_desc'] = 'Ingrese el topic para el endpoint de anuncios.';

// uPlanner evaluation_structure field
$string['evaluation_structure_endpoint_heading'] = 'Configuración del topic para evaluaciones';
$string['evaluation_structure_endpoint'] = 'Topic de evaluaciones';
$string['evaluation_structure_endpoint_desc'] = 'Ingrese el topic para el endpoint de evaluaciones.';

// uPlanner grades field
$string['grades_endpoint_heading'] = 'Configuración del topic para notas';
$string['grades_endpoint'] = 'Topic de notas';
$string['grades_endpoint_desc'] = 'Ingrese el topic para el endpoint de notas.';

// uPlanner messages host
$string['messages_host_heading'] = 'Congiguración para el host de mensajes';
$string['messages_host'] = 'Host de mensajes';
$string['messages_host_desc'] = 'Ingrese el host de los densajes.';

// uPlanner messages database
$string['messages_database_heading'] = 'Configuración de la base de datos de mensajess';
$string['messages_database'] = 'Base de datos de mensajes';
$string['messages_database_desc'] = 'Ingrese la base de datos de los mensajes.';

// uPlanner messages user
$string['messages_user_heading'] = 'Configuración del usuario de mensajes';
$string['messages_user'] = 'Usuario de mensajes';
$string['messages_user_desc'] = 'Ingrese el usuario de los mensajes.';

// uPlanner messages password
$string['messages_password_heading'] = 'Configuración de la contraseña de mensajes';
$string['messages_password'] = 'Contraseña de mensajes';
$string['messages_password_desc'] = 'Ingrese la contraseña de los mensajes.';

// uPlanner messages port
$string['messages_port_heading'] = 'Configuración del puerto de mensajes';;
$string['messages_port'] = 'Puerto de mensajes';
$string['messages_port_desc'] = 'Ingrese el puerto de los mensajes.';

// uPlanner task rutine field
$string['task_rutine_primary'] = 'Tiempo de ejecución de la tarea principal';
$string['task_rutine_primary_desc'] = 'Ingrese el tiempo de ejecución en segundos para la tarea principal.';

// Events.
$string['uplannerconnect:uplanner_dispatch_tmp'] = "Module Files Uploaded";

// Register
$string['uplanner_log_processing'] = 'Registro procesado correctamente';
$string['uplanner_log_dont_processing'] = 'Error registrado en la base de datos de uplanner';