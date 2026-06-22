<?php
/**
 * @package     local_uplannerconnect
 * @copyright   Cristian Machado <cristian.machado@correounivalle.edu.co>
 * @copyright   Daniel Eduardo Dorado <doradodaniel14@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

namespace local_uplannerconnect\application\service;

use local_uplannerconnect\application\repository\course_data_repository;
use moodle_exception;

/**
 *  Validaciones de los eventos
*/
class event_access_validator
{
    const EVENT_DATA = 'dataEvent';
    const EVENT_KEY = 'key';
    const EVENT_METHOD_NAME = 'methodName';
    const DATA_VERIFY_EVENT = 'typeEvent';
    // Facultade a evaluar
    private $courseDataRepository;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->courseDataRepository = new course_data_repository();
    }

    /**
     *  Valida si no repite multiples veces el mismo evento
     *  con el mismo dato
     *
     * @param array $data $data
     * @return bool
     */
    public function validateTypeEvent(array $data) : bool
    {
        $boolean = false;
        try {
            if (empty($data) ||
                !isset($data[self::EVENT_DATA]) ||
                !isset($data[self::EVENT_METHOD_NAME]) ||
                !isset($data[self::EVENT_KEY]) ||
                !isset($data[self::DATA_VERIFY_EVENT])
            ) { return false; }

            // Obtener el nombre del método.
            $methodName = $data[self::EVENT_METHOD_NAME];
            // Obtener la data del evento.
            $dataEvent = ($data[self::EVENT_DATA])->$methodName();
            // Obtener la llave del evento.
            $eventExecute = $dataEvent[$data[self::EVENT_KEY]];
            // valida.
            $boolean = in_array($eventExecute,$data[self::DATA_VERIFY_EVENT]);
        }
        catch (moodle_exception $e) {
          error_log('Excepción capturada: '. $e->getMessage(). "\n");
        }
        return $boolean;
    }

    /**
     *  Validate if the course is active
     *
    */
    public function validateAccessByFaculty($courseid) : bool
    {
        $result = false;
        try {
            if (!empty($courseid)) {
                //Obtener el shortname del curso
                $shortname = $this->courseDataRepository->getCourseShortname($courseid);
                //verifica si tiene algun dato en la posición 4
                if (isset($shortname)) {
                    $patron = '/^\d{2}-\d{6}[A-Za-z]-\d{2}-\d{9}$/';
                    $patron2 = '/^\d{2}-\d{6}-\d{2}-\d{9}$/';
                    $patron3 = '/^\d{2}-[A-Za-z]\d{5}[A-Za-z]-\d{2}-\d{9}$/';
                    $result = preg_match($patron, $shortname) || preg_match($patron2, $shortname) || preg_match($patron3, $shortname);
                }
            }
        }
        catch (moodle_exception $e) {
          error_log('Excepción capturada: '. $e->getMessage(). "\n");
        }
        return $result;
    }
}