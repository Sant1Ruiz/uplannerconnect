<?php
/**
 * @package     uPlannerConnect
 * @copyright   Cristian Machado Mosquera <cristian.machado@correounivalle.edu.co>
 * @copyright   Daniel Eduardo Dorado <doradodaniel14@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_uplannerconnect\infrastructure\email;

use coding_exception;
use local_uplannerconnect\plugin_config\notification_settings;
use stdClass;

/**
 * Class email, send email with uPlaner info
 */
class email
{
    /**
     * Send email with uPlanner info
     *
     * @param string $subject Lang string key for the subject (local_uplannerconnect).
     * @param string $currentdate Date string appended to the subject.
     * @param string $attachmentpath Path to the attachment file.
     * @param string $attachmentname Attachment filename for the recipient.
     * @return bool
     */
    public function send(
        $subject,
        $currentdate,
        $attachmentpath,
        $attachmentname
    ): bool {
        if (!notification_settings::should_send()) {
            return false;
        }

        try {
            $recipientemail = notification_settings::get_recipient_email();
            $user = new stdClass();
            $user->email = $recipientemail;
            $user->id = '000001';
            $user->username = 'univalle';
            $admin = get_admin();
            $subject = get_string($subject, 'local_uplannerconnect');

            $body = 'Dear administrator,

            We are attaching information regarding the changes towards uPlanner.
    
            Best regards,
            Univalle';

            return email_to_user(
                $user,
                $admin,
                $subject . ' - ' . $currentdate,
                $body,
                '',
                $attachmentpath,
                $attachmentname
            );
        } catch (coding_exception $e) {
            error_log('send: ' . $e->getMessage() . PHP_EOL);
        }

        return false;
    }
}
