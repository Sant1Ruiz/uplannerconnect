<?php
/**
 * @package     uPlannerConnect
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_uplannerconnect\infrastructure\api\exception;

defined('MOODLE_INTERNAL') || die;

/**
 * Thrown when the uPlanner message status API request fails.
 */
class uplanner_messages_status_api_exception extends \moodle_exception
{
    /**
     * @param string $endpoint Request URL (messages_host + query).
     * @param int $httpcode HTTP status code.
     * @param string $detail Optional response body or API message.
     */
    public function __construct(string $endpoint, int $httpcode, string $detail = '')
    {
        $message = 'uPlanner status API failed (HTTP ' . $httpcode . ') at endpoint: ' . $endpoint;
        if ($detail !== '') {
            $message .= '. ' . $detail;
        }

        parent::__construct('error', 'local_uplannerconnect', '', null, $message);
    }
}
