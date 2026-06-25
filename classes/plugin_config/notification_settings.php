<?php
/**
 * @package     local_uplannerconnect
 * @copyright   Daniel Eduardo Dorado <doradodaniel14@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_uplannerconnect\plugin_config;

defined('MOODLE_INTERNAL') || die();

/**
 * Plugin notification email settings (admin configuration).
 */
class notification_settings
{
    const SETTING_ENABLE = 'enable_notification_emails';
    const SETTING_EMAIL = 'notification_email';

    /**
     * Whether notification emails are enabled in plugin settings.
     *
     * @return bool
     */
    public static function emails_enabled(): bool
    {
        return (bool) get_config(plugin_config::PLUGIN_NAME_LOCAL, self::SETTING_ENABLE);
    }

    /**
     * Configured recipient email address, or null if missing or invalid.
     *
     * @return string|null
     */
    public static function get_recipient_email(): ?string
    {
        $email = trim((string) get_config(plugin_config::PLUGIN_NAME_LOCAL, self::SETTING_EMAIL));
        if ($email === '' || !validate_email($email)) {
            return null;
        }

        return $email;
    }

    /**
     * Whether the plugin should send notification emails.
     *
     * @return bool
     */
    public static function should_send(): bool
    {
        return self::emails_enabled() && self::get_recipient_email() !== null;
    }
}
