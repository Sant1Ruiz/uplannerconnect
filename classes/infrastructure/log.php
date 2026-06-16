<?php
/**
 * @package     uPlannerConnect
 * @copyright   Cristian Machado Mosquera <cristian.machado@correounivalle.edu.co>
 * @copyright   Daniel Eduardo Dorado <doradodaniel14@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_uplannerconnect\infrastructure;

use Exception;

/**
 * Class log file manager
 */
class log
{
    const BASE_NAME = '%s_uplanner_template_log.txt';

    /**
     * Directory path files
     *
     * @var string $filename
     */
    private string $directory = __DIR__ . '/email/files/';

    /**
     * File name
     *
     * @var string $filename
     */
    private string $filename;

    /**
     * Virtual name
     *
     * @var string $virtual_name
     */
    private $virtual_name;

    /**
     * Create file or no
     *
     * @var bool $create_file
     */
    private $create_file;

    /**
     * Construct
     *
     * @param $filename
     * @param $virtual_name
     * @param $create_file
     */
    public function __construct(
        $filename,
        $virtual_name,
        $create_file = true
    ) {
        $this->create_file = $create_file;
        $millis = round(microtime(true) * 1000);
        $this->filename = sprintf(self::BASE_NAME, $filename);
        $this->virtual_name = str_replace("date", $millis, $virtual_name);

    }

    /**
     * Get virtual name
     *
     * @return string
     */
    public function get_virtual_name()
    {
        return $this->virtual_name;
    }

    /**
     * Get path file
     *
     * @return string
     */
    public function get_path_file()
    {
        return $this->directory . $this->filename;
    }

    /**
     * Create log file
     *
     * @param $title
     * @return bool
     */
    public function create_log($title)
    {
        if ($this->create_file) {
            try {
                if (!file_exists($this->directory)) {
                    mkdir($this->directory, 0755, true);
                }
                $text_file = $this->get_path_file();
                file_put_contents($text_file, PHP_EOL . $title . PHP_EOL);
                return true;
            } catch (Exception $e) {
                error_log('create_log: ' . $e->getMessage() . PHP_EOL);
            }
        }

        return false;
    }

    /**
     * Add row
     *
     * @param $line
     * @return bool
     */
    public function add_line($line)
    {
        if ($this->create_file) {
            try {
                $text_file = $this->get_path_file();
                file_put_contents($text_file, $line . PHP_EOL, FILE_APPEND);
                return true;
            } catch (Exception $e) {
                error_log('add_line: ' . $e->getMessage() . PHP_EOL);
            }
        }


        return false;
    }

    /**
     * Add multiple lines to the text file
     *
     * @param array $lines
     * @return bool
     */
    public function add_lines(array $lines)
    {
        if ($this->create_file) {
            try {
                $text_file = $this->get_path_file();
                file_put_contents($text_file, implode(PHP_EOL, $lines) . PHP_EOL, FILE_APPEND);
                return true;
            } catch (Exception $e) {
                error_log('add_lines: ' . $e->getMessage() . PHP_EOL);
            }
        }

        return false;
    }

    /**
     * Reset text file (clear content)
     *
     * @return bool
     */
    public function reset_log()
    {
        if ($this->create_file) {
            try {
                $text_file = $this->get_path_file();
                file_put_contents($text_file, '');
                return true;
            } catch (Exception $e) {
                error_log('reset_log: ' . $e->getMessage() . PHP_EOL);
            }
        }

        return false;
    }

    /**
     * Delete text file
     *
     * @return void
     */
    public function delete_log()
    {
        if ($this->create_file) {
            try {
                $text_file = $this->get_path_file();
                if (file_exists($text_file)) {
                    unlink($text_file);
                }
            } catch (Exception $e) {
                error_log('delete_log: ' . $e->getMessage() . PHP_EOL);
            }
        }
    }
}
