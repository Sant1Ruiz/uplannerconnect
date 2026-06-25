<?php
/**
 * @package     local_uplannerconnect
 * @copyright   Santiago Ruiz<santiago.ruiz.cortes@correounivalle.edu.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

namespace local_uplannerconnect\infrastructure\utils;

use local_uplannerconnect\application\repository\course_data_repository;
use moodle_exception;


/**
 * Validate if the academic period of the course is active
 */
class academic_period_checker
{
    // Facultade a evaluar
    private $courseDataRepository;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DB;
        $this->db = $DB;
        $this->courseDataRepository = new course_data_repository();
    }

    /**
     *  Validate the shortname and return the academic period
     *
    */
    public function getPeriodAcademic($courseid) : ?string
    {
        $result = null;
        try {
            if (!empty($courseid)) {
                //Obtener el shortname del curso
                $shortname = $this->courseDataRepository->getCourseShortname($courseid);
                //verifica si tiene algun dato en la posición 4
                if (!empty($shortname)) {
                    $patron = '/^(\d{2})-(\d{6}[A-Za-z])-(\d{2})-(\d{9})$/';
                    $patron2 = '/^(\d{2})-(\d{6})-(\d{2})-(\d{9})$/';
                    $patron3 = '/^(\d{2})-([A-Za-z]\d{5}[A-Za-z])-(\d{2})-(\d{9})$/';
                    if (preg_match($patron, $shortname, $matches) || preg_match($patron2, $shortname, $matches) || preg_match($patron3, $shortname, $matches)){
                        $result = $matches[4];
                    }
                }
            }
        }
        catch (moodle_exception $e) {
          error_log('Excepción capturada: '. $e->getMessage(). "\n");
        }
        return $result;
    }

    /**
     * Get the academics periods active from iracv plugin
     */
    public function getActivePeriods() : array
    {
        $data = [];
        try {
            $data = $this->db->get_records('iracv_academic_periods',['active' => '1'],'', 'code');
        } catch (moodle_exception $e) {
            error_log('Excepción capturada: '. $e->getMessage(). "\n");
        }
        return array_keys($data);
    }

    /**
     * validates that the academic period of the course is active
     */
    public function validateAcademicPeriod($courseid) : bool
    {
        $result = false;
        try {
            $period = $this->getPeriodAcademic($courseid);
            $activePeriods = $this->getActivePeriods();
            if($period !== null && in_array($period,$activePeriods)){
                $result = true;
            }

        } catch (moodle_exception $e) {
            error_log('Excepción capturada: '. $e->getMessage(). "\n");
        }
        return $result;
    }

}