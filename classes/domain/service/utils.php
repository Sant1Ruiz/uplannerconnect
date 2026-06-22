<?php
/**
 * @package     local_uplannerconnect
 * @copyright   Cristian Machado <cristian.machado@correounivalle.edu.co>
 * @copyright   daniel eduardo dorado <doradodaniel14@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

namespace local_uplannerconnect\domain\service; 

use local_uplannerconnect\application\repository\moodle_query_handler;
use moodle_exception;

/**
 *  Utils class
 */
class utils
{
    const IDENTIFICATION_USER = "SELECT data FROM {user_info_data} WHERE userid = :userid AND fieldid = 11 ORDER BY id DESC LIMIT 1";
    const GET_TEACHER_USERNAME = "SELECT username 
                                  FROM {user} AS t1 
                                  INNER JOIN {role_assignments} AS t2 
                                  ON t1.id = t2.userid 
                                  INNER JOIN {context} AS t3 
                                  ON t2.contextid = t3.id 
                                  WHERE t3.instanceid = :courseid
                                  AND t2.roleid IN (2,3,4,30)
                                  ORDER BY t2.id DESC LIMIT 1";

    private $moodle_query_handler;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->moodle_query_handler = new moodle_query_handler();
    }

    /**
     * Convert format Uplanner
     *
     * @param string $originalString
     * @return string
     */
    public function convertFormatUplanner($originalString) 
    {
        $pattern = '/^(\d{2})-(\d{6}[A-Za-z])-(\d{2})-(\d{9})$/';
        $pattern2 = '/^(\d{2})-(\d{6})-(\d{2})-(\d{9})$/';
        $pattern3 = '/^(\d{2})-([A-Za-z]\d{5}[A-Za-z])-(\d{2})-(\d{9})$/';
        $newString = $originalString;
        
        if (preg_match($pattern, $originalString, $matches) || preg_match($pattern2, $originalString, $matches) || preg_match($pattern3, $originalString, $matches)) {
            
            $part1 = $matches[1];
            $part2 = $matches[2];
            $part3 = $matches[3];
            $part4 = $matches[4];

            // Build the new string in the desired format
            $newString = "$part1-$part4-$part2-$part3";
        }

        return $newString;
    }

    /**
     * Get Identification
     *
     * @param string $userid
     * @return string
     */
    public function getIdentificationUser($userId) : string
    {
        $userIdentification = '';
        if (!empty($userId)) {
            $result = $this->executeQueryOnResult([
                'query' => self::IDENTIFICATION_USER,
                'params' => [
                    'userid' => $userId
                ]
            ]);
            if (!empty($result)) {$userIdentification = strval($result->data);}
        }
        return $userIdentification;
    }

    /**
     * Get usermane Teacher of course
     *
     * @param string $userid
     * @return string
     */
    public function getUserNameTeacher($courseId) : string
    {
        $userIdentification = '';
        if (!empty($courseId)) {

            $result = $this->executeQueryOnResult([
                'query' => self::GET_TEACHER_USERNAME,
                'params' => [
                    'courseid' => $courseId
                ]
            ]);

            if (!empty($result)) {$userIdentification = strval($result->username);}
        }
        return $userIdentification;
    }

    /**
     * Return result one result of query
     */
    private function executeQueryOnResult(array $data)
    {
        $response = null;
        try {
            if (!empty($data)) {
                $query = $data['query'];
                $params = $data['params'];
                $result = $this->moodle_query_handler->executeQuery(
                    $query,
                    $params
                );
                
                if (!empty($result) && !empty(reset($result))) {
                    $response = reset($result);
                }
            }
        } catch (moodle_exception $e) {
            error_log('Excepción capturada: ' . $e->getMessage() . "\n");
        }
        return $response;
    }

    /**
     * Data Format send to uPlanner
     * 
     * @param array $data
     * @return array
     */
    public function send_data_uplanner(array $data) : array
    {
        $arraySend = [
            'data' => [],
            'typeEvent' => '',
        ]; 

        try {
            if (!empty($data)) {
                if (is_array($data['data'])) {               
                    $arraySend = [
                        'data' => $data['data'],
                        'typeEvent' => $data['typeEvent'],
                    ];
                }
            }
        }
        catch (moodle_exception $e) {
            error_log('Excepción capturada: '. $e->getMessage(). "\n");
        }
        return $arraySend;
    }
}