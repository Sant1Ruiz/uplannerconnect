<?php
/**
 * @package     uPlannerConnect
 * @copyright   Daniel Eduardo Dorado <doradodaniel14@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_uplannerconnect\infrastructure\api;

use Exception;
use local_uplannerconnect\application\repository\general_repository;
use local_uplannerconnect\application\repository\messages_status_repository;
use local_uplannerconnect\application\repository\repository_type;
use local_uplannerconnect\infrastructure\api\client\abstract_uplanner_client;
use local_uplannerconnect\infrastructure\api\factory\uplanner_client_factory;
use local_uplannerconnect\infrastructure\email\email;
use local_uplannerconnect\infrastructure\file;
use local_uplannerconnect\infrastructure\log;
use local_uplannerconnect\plugin_config\notification_settings;

defined('MOODLE_INTERNAL') || die;

/**
 * Class handle_send_uplanner_edit, send data to uPlanner
 */
class handle_send_uplanner_edit
{
    /**
     * @var $uplanner_client_factory
     */
    private $uplanner_client_factory;

    /**
     * @var $file
     */
    private $file;

    /**
     * @var $log
     */
    private $log;

    /**
     * @var $email
     */
    private $email;

    /**
     * @var $task_di
     */
    private $task_id;

    /**
     * @var $current_date
     */
    private $current_date;

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var messages_status_repository
     */
    private messages_status_repository $message_repository;

    /**
     * @var general_repository
     */
    private general_repository $general_repository;

    /**
     * Construct
     *
     * @param $tasks_id
     */
    public function __construct(
        $tasks_id
    ) {
        $this->task_id = $tasks_id;
        $this->prefix = $this->task_id . '_';
        $this->current_date = date("F j, Y, g:i:s a");
        $this->uplanner_client_factory = new uplanner_client_factory();
        $this->email = new email();
        $this->message_repository = new messages_status_repository();
        $this->general_repository = new general_repository();
    }

    /**
     * Handle process send information to uPlanner
     *
     * @param $data
     * @return mixed
     */
    public function process(
        $data
    ){
        $this->create_log($this->prefix . $this->task_id . '_log');
        $this->log->add_line('------------------------------------------  UPLANNER - PROCESS START - RESEND LOG ------------------------------------------');
        $this->log->add_line('--- UPLANNER DATA REQUEST: ' . json_encode($data));
        $log = $this->getDataRequest($data);
        $this->log->add_line('--- UPLANNER LOG DATA: ' . json_encode($log));
        $row = $this->process_request($log);
        if (intval($row->success) === repository_type::STATE_UP_ERROR && intval($row->is_sucessful) === 1) {
            $this->log->add_line('--- UPLANNER REMOVE LOG AFTER QUERY: ' . json_encode($log));
            $condition = [
                'id' => $row->id,
            ];
            $this->general_repository->delete_rows($log->repository::TABLE, $condition);
        }
        $this->log->add_line('------------------------------------------            UPLANNER - PROCESS FINISHED             ------------------------------------------');
        $this->send_email(
            $this->prefix . $this->task_id . '_log',
            $this->log
        );
        $this->log->add_line('------------------------------------------            UPLANNER - RESET LOG             ------------------------------------------');
        $this->log->reset_log();

        return $row;
    }

    /**
     * Get data request
     *
     * @param $data
     * @return object
     */
    public function getDataRequest($data)
    {
        $this->log->add_line(' ++++++++++++++++  GET DATA REQUEST');
        $log = [];
        foreach ($data as $index => $input) {
            switch ($input['name']) {
                case 'type':
                    $repositoryClass =  repository_type::getClass($input['value']);
                    $this->log->add_line('UPLANNER CREATE REPOSITORY: ' . $input['value']);
                    $log['repository'] = new $repositoryClass();
                    $this->log->add_line('UPLANNER CREATE CLIENT: ' . $input['value']);
                    $log['uplanner_client'] = $this->uplanner_client_factory->create($input['value']);
                    break;
                case 'json':
                    $this->log->add_line('UPLANNER JSON: ' . $input['value']);
                    $log['json'] = $this->getJson($input['value']);
                    break;
                case 'id':
                    $this->log->add_line('UPLANNER ID: ' . $input['value']);
                    $log['id'] = $input['value'];
                    break;
                default:
            }
        }

        return (object) $log;
    }

    /**
     * Process request
     *
     * @param $log
     * @return mixed
     */
    public function process_request($log)
    {
        $this->log->add_line('********** UPLANNER - PROCESS REQUEST ');
        $this->log->add_line('UPLANNER - UPDATE JSON ');
        if ($log->json){
            $dataQuery = [
                'json' => $log->json,
                'id' => $log->id
            ];
            $log->repository->updateDataBD($dataQuery);
            $row = $log->repository->get_data_by_id($log->id);
            $this->log->add_line('UPLANNER - REQUEST: ' . json_encode($row));
            $response = $this->request($log->uplanner_client, $row);
            $this->log->add_line('UPLANNER - RESPONSE: ' . json_encode($response));
            $status = (empty($response) || array_key_exists('error', $response))
                ? repository_type::STATE_UP_ERROR : repository_type::STATE_SEND;
            $this->log->add_line('UPLANNER - UPDATE REGISTER STATUS: ' . $status);
            $dataQuery = [
                'response' => $response,
                'id' => $log->id
            ];
            $this->log->add_line('UPLANNER - UPDATE RESPONSE: ' . $status);
            $log->repository->updateDataBD($dataQuery);
            $row = $log->repository->get_data_by_id($log->id);
            $fileCreated = $this->create_file($this->prefix . $log->uplanner_client->get_file_name(),
                $row,
                $status
            );
            $this->log->add_line('UPLANNER - FILE WAS CREATED: ' . $fileCreated);
            $this->log->add_line('UPLANNER - READ UPLANNER LOG' );
            $this->message_repository->process($log->repository, $row, $this->log, false);
            $row = $log->repository->get_data_by_id($log->id);
            $this->log->add_line('UPLANNER - UPDATED LOG AFTER QUERY: ' . json_encode($row) );
            $row = reset($row);
        } else {
            $this->log->add_line('UPLANNER - DATA NO VALID: ' . json_encode($log) );
            $row = $log->repository->get_data_by_id($log->id);
            $fileCreated = $this->create_file(
                $this->prefix . $log->uplanner_client->get_file_name(),
                $row,
                repository_type::STATE_UP_ERROR
            );
        }
        if ($fileCreated) {
            $data = [
                $row->json,
                $row->success,
                $row->response,
                $row->is_sucessful,
                $row->ds_error
            ];
            $this->file->add_row($data);
            $this->log->add_line('UPLANNER - SEND EMAIL: ' . $fileCreated);
            $this->send_email($this->prefix . $log->uplanner_client->get_email_subject(), $this->file);
            $this->file->reset_csv(abstract_uplanner_client::FILE_HEADERS);
        }

        return $row;
    }

    /**
     * Is valid json
     *
     * @param string $json
     * @return bool|null
     */
    function getJson($json)
    {
        try {
            $object = json_decode($json, null, JSON_THROW_ON_ERROR);
            return is_object($object) ? $object : null;
        } catch  (Exception $e) {
            return null;
        }
    }

    /**
     * @param $uplanner_client
     * @param $rows
     * @return mixed
     */
    public function request($uplanner_client, $rows)
    {
        $response = $json = [];
        try {
            foreach ($rows as $row) {
                $json[] = json_decode($row->json, true);
            }
            $response = $uplanner_client->request($json);
        } catch (Exception $e) {
            error_log('handle_send_uplanner_task - request: ' . $e->getMessage() . PHP_EOL);
        }

        return $response ?? [];
    }

    /**
     * Create and add rows in file
     *
     * @param $file_name
     * @param $rows
     * @param $status
     * @return bool
     */
    private function create_file($file_name, $rows, $status)
    {
        $this->file = new file($this->task_id, $file_name, notification_settings::emails_enabled());
        $headers = abstract_uplanner_client::FILE_HEADERS;
        $headers[] = 'response';
        $headers[] = 'is_sucessful';
        $headers[] = 'ds_error';
        $fileCreated = $this->file->create_csv($headers);
        if ($fileCreated) {
            foreach ($rows as $row) {
                $data = [
                    $row->json,
                    $status,
                    $row->response,
                    $row->is_sucessful,
                    $row->ds_error

                ];
                $this->file->add_row($data);
            }
        }

        return $fileCreated;
    }

    /**
     * Create and add log file
     *
     * @param $file_name
     * @return void
     */
    private function create_log($file_name)
    {
        $this->log = new log($this->task_id, $file_name . '_date', notification_settings::emails_enabled());
        $this->log->create_log(
            'TASK ' . strtoupper($this->task_id) . ' ' . $this->current_date
        );
    }

    /**
     * Send email
     *
     * @param $subject
     * @param $file
     * @return bool
     */
    private function send_email($subject, $file)
    {
        return $this->email->send(
            $subject,
            $this->current_date,
            $file->get_path_file(),
            $file->get_virtual_name()
        );
    }
}