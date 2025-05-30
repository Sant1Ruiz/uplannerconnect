<?php
/**
 * @package     uPlannerConnect
 * @copyright   Cristian Machado Mosquera <cristian.machado@correounivalle.edu.co>
 * @copyright   Daniel Eduardo Dorado <doradodaniel14@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

namespace local_uplannerconnect\infrastructure\api;

use Exception;
use local_uplannerconnect\application\repository\repository_type;
use local_uplannerconnect\infrastructure\api\client\abstract_uplanner_client;
use local_uplannerconnect\infrastructure\api\factory\uplanner_client_factory;
use local_uplannerconnect\application\repository\messages_status_repository;
use local_uplannerconnect\infrastructure\email\email;
use local_uplannerconnect\infrastructure\file;
use local_uplannerconnect\infrastructure\log;

defined('MOODLE_INTERNAL') || die;


/**
 * Class handle_send_uplanner_task, send data to uPlanner
 */
class handle_send_uplanner_task
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
     * @var bool
     */
    private $send_emails;

    /**
     * @var messages_status_repository
     */
    private $message_repository;

    /**
     * Construct
     *
     * @param $tasks_id
     * @param $send_emails
     */
    public function __construct(
        $tasks_id,
        $send_emails = true
    ) {
        $this->task_id = $tasks_id;
        $this->send_emails = $send_emails;
        $this->prefix = $this->task_id . '_';
        $this->current_date = date("F j, Y, g:i:s a");
        $this->uplanner_client_factory = new uplanner_client_factory();
        $this->message_repository = new messages_status_repository();
        $this->email = new email();
    }

    /**
     * Handle process send information to uPlanner
     *
     * @param int $state
     * @param int $num_request_by_endpoint
     * @param int $num_rows MAXVALUE: 100
     * @return void
     */
    public function process(
        $state = 0,
        $num_request_by_endpoint = 1,
        $num_rows = 100
    ) {
        $this->create_log($this->prefix . $this->task_id . '_log');
        $this->log->add_line('------------------------------------------  UPLANNER - PROCESS START - FOREACH REPOSITORIES ------------------------------------------');
        # Se valida el estado 2, pero pasa a resending los estado 3
        if ($state == repository_type::STATE_ERROR) {
            $this->send_error_per_repository($page_size = 1000);
            # En este se pone el estado de resending con estado 0
            # Entonces el estado que debe utilizarse a continuacion para enviar deberia de ser los 0
        }
        # Recorre todas las 4 tablas con el estado declarado en los parametros
        foreach (repository_type::ACTIVE_REPOSITORY_TYPES as $type => $repository_class) {
            $this->log->add_line('------- CREATE REPOSITORY OBJECT: ' . $type . ' - ' . $repository_class);
            $repository = new $repository_class();
            $uplanner_client = $this->uplanner_client_factory->create($type);
            $this->process_request(
                $uplanner_client,
                $repository,
                $state,
                $num_request_by_endpoint,
                $num_rows
            );
        }
        $this->log->add_line('------------------------------------------            UPLANNER - PROCESS FINISHED             ------------------------------------------');
        $this->send_email(
            $this->prefix . $this->task_id . '_log',
            $this->log
        );
        $this->log->reset_log();
    }

    /**
     * Process request
     *
     * @param $uplanner_client
     * @param $repository
     * @param $state
     * @param $num_request_by_endpoint
     * @param $num_rows
     * @return void
     */
    public function process_request(
        $uplanner_client,
        $repository,
        $state,
        $num_request_by_endpoint,
        $num_rows
    ) {
        if ($num_request_by_endpoint <= 0 || $num_rows <= 0 ) {
            return;
        }
        $index_row = 0;
        $offset = 0;
        $this->log->add_line('********** UPLANNER - PROCESS REQUEST - WHILE: ');
        while ($index_row < $num_request_by_endpoint) {
            $dataQuery = [
                'state' => $state,
                'limit' => $num_rows,
                'offset' => $offset
            ];
            $this->log->add_line('UPLANNER - DATA QUERY: ' . json_encode($dataQuery));
            $rows = $repository->getDataBD($dataQuery);
            $this->log->add_line('UPLANNER - DATA ROWS: ' . json_encode($rows));
            if (!$rows) {
                break;
            }
            $response = $this->request($uplanner_client, $rows);
            $this->log->add_line('UPLANNER - RESPONSE: ' . json_encode($response));
            $status = (empty($response) || array_key_exists('error', $response))
                ? repository_type::STATE_ERROR : repository_type::STATE_SEND;
            $fileCreated = $this->create_file($this->prefix . $uplanner_client->get_file_name(), $rows, $status);
            $this->log->add_line('UPLANNER - FILE WAS CREATED: ' . $fileCreated);
            if ($fileCreated) {
                $this->send_email($this->prefix . $uplanner_client->get_email_subject(), $this->file);
            }
            $numRows = 0;
            $this->log->add_line('UPLANNER - UPDATE REGISTER STATUS: ' . $status);
            foreach ($rows as $row) {
                $dataQuery = [
                    'response' => $response,
                    'success' => $status,
                    'id' => $row->id
                ];
                $repository->updateDataBD($dataQuery);
                if ($status == $state) {
                    $numRows++;
                }
            }
            if ($fileCreated) {
                $this->file->reset_csv(abstract_uplanner_client::FILE_HEADERS);
            }
            $index_row++;
            $offset += $numRows;
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
        $this->file = new file($this->task_id, $file_name, $this->send_emails);
        $fileCreated = $this->file->create_csv(abstract_uplanner_client::FILE_HEADERS);
        if ($fileCreated) {
            foreach ($rows as $row) {
                $data = [
                    $row->json,
                    $status
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
        $this->log = new log($this->task_id, $file_name . '_date', $this->send_emails);
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
        if ($this->send_emails) {
            $recipient_email = 'samuel.ramirez@correounivalle.edu.co';
            return $this->email->send(
                $recipient_email,
                $subject,
                $this->current_date,
                $file->get_path_file(),
                $file->get_virtual_name()
            );
        }

        return false;
    }

    /**
     * Send error json to uplanner
     *
     * @return void
     */
    private function send_error_per_repository($page_size)
    {
        foreach (repository_type::ACTIVE_REPOSITORY_TYPES as $type => $repository_class) {
            $repository = new $repository_class($type);
            $this->start_process_error_send(
                $repository,
                $page_size
            );
        }
    }

    /**
     * Send error json to uplanner
     *
     * @param $subject
     * @param $file
     * @return void
     */
    private function start_process_error_send(
        $repository,
        $page_size
    ) {
        try {
            if ($page_size <= 0) {
                return;
            }
            $offset = 0;
            # En esto no veo la condicion de parada, pero si hay limite a enviarse
            # osea esto puede provocar que hayan mas resending que los que realmente se enviaran
            # porque el envio esta limitado a 100 registros y se ejecuta 1000 veces por repositorio
            # este while busca de a 1000 registros sin condicion de parada
            # ARREGLAR ESTO SINCRONIZANDO CON EL NUMERO DE REQUEST POR ENDPOINT PARA QUE SE LIMITE A LO MISMO
            # LO QUE ENVIA DEBE DE SER IGUAL A LO QUE PREPARA PAR ENVIAR YA QUE ESTOS QUEDAN INACCESIBLES 
            # PARA SER ENVIADO NUEVAMENTE PORQUE SU ESTADO PASA A 0, SIN SIQUIERA HABERSE INTENTADO
            while (true) {
                $data = [
                    # Esto esta mal? esto es estado 3, pero el condicional anterior para llegar aqui es estado 2
                    'state' => repository_type::STATE_UP_ERROR,
                    'limit' => $page_size,
                    'offset' => $offset,
                ];

                $rows = $repository->getDataBD($data);
                if (!$rows) {
                    break;
                }
                
                $this->message_repository->process_error_state($repository, $rows);
                $rows = $repository->getDataBD($data);
                $offset += count($rows);
            }
        } catch (moodle_exception $e) {
            error_log('handle_re_send_fail_uplanner_task - process: ' . $e->getMessage() . PHP_EOL);
        }
    }
}