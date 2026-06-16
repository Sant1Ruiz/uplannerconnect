<?php
/**
 * @package     local_uplannerconnec
 * @copyright   Daniel Eduardo Dorado P. <doradodaniel14@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_uplannerconnect\application\repository;

use Exception;
use local_uplannerconnect\application\messages\messages_resource;
use local_uplannerconnect\infrastructure\api\exception\uplanner_messages_status_api_exception;

/**
 * Loaded class to manipulate data in uplanner_esb_messages_status table
 */
class messages_status_repository
{
    /**
     * @var messages_resource
     */
    private $messages_resource;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->messages_resource = new messages_resource();
    }

    /**
     * get list by transactions
     *
     * @param $transactions_ids
     * @return array
     */
    public function get_list_by_transactions($transactions_ids)
    {
        return $this->messages_resource->get_messages(
            implode(',', $transactions_ids)
        );
    }

    /**
     * Compare message log in uPlanner
     *
     * @return void
     */
    public function process($repository, $rows, $log, $updateStatus = true)
    {
        try {
            $parsedrows = $this->parse_rows($rows);
            $this->report_skipped_rows($parsedrows['skipped'], $log);
            if ($updateStatus) {
                $this->mark_skipped_rows($repository, $parsedrows['skipped']);
            }
            $uv_transactions = $parsedrows['transaction_ids'];
            $log->add_line(' --- UV TRANSACTIONS: count=' . count($uv_transactions));
            $up_messages = $this->get_list_by_transactions(
                $uv_transactions
            );
            $log->add_line(' --- UP MESSAGES: ' . $this->summarize_up_messages($up_messages));
            $log->add_line(' ---------------- FOREACH - COMPARE LOGS: ');
            foreach ($parsedrows['valid'] as $parsedrow) {
                $row = $parsedrow['row'];
                $idtransaction = $parsedrow['transaction_id'];
                $filtered_messages = array_filter($up_messages, function ($message) use ($idtransaction) {
                    return $message['id_transaction'] == $idtransaction;
                });
                $message = reset($filtered_messages);
                $issuccessful = 0;
                $dserror = 'Not found in uPlanner database.';
                $state = $row->success;
                if ($message) {
                    if (($message['is_successful'] === 1 || $message['is_successful'] === '1')) {
                        $dserror = '';
                        $issuccessful = 1;
                    } else {
                        $dserror = $message['ds_error'];
                        $state = repository_type::STATE_UP_ERROR;
                    }
                }
                $log->add_line(sprintf(
                    ' --- row id=%s transactionId=%s is_sucessful=%s success=%s ds_error=%s',
                    $row->id,
                    $idtransaction,
                    $issuccessful,
                    $state,
                    $dserror
                ));
                $data = [
                    'is_sucessful' => $issuccessful,
                    'ds_error' => $dserror,
                    'id' => $row->id
                ];
                if ($updateStatus) {
                    $data['success'] = $state;
                }

                if ($updateStatus && !$message) {
                    $data['success'] = repository_type::STATE_UP_ERROR;
                }
                $repository->updateDataBD($data);
            }
        } catch (uplanner_messages_status_api_exception $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('messages_status_repository->process: ' . $e->getMessage() . PHP_EOL);
        }
    }

    /**
     * Re sending error menssages to upplaner
     *
     * @return void
     */
    public function process_error_state($repository,$rows)
    {
        try {
            foreach ($rows as $row) {
                $data = [
                    'is_sucessful' => 0,
                    'success' => 0,
                    "response" => "re sending to uplanner",
                    'ds_error' => "",
                    'id' => $row->id
                ];

                $repository->updateDataBD($data);
            }
        } catch (Exception $e) {
            error_log('messages_status_repository->process: ' . $e->getMessage() . PHP_EOL);
        }
    }

    /**
     * Split rows into valid (with transactionId) and skipped (invalid JSON).
     *
     * @param array $rows
     * @return array Keys valid, skipped, transaction_ids.
     */
    private function parse_rows(array $rows): array
    {
        $valid = [];
        $skipped = [];
        $transactionids = [];

        foreach ($rows as $row) {
            $classification = $this->classify_row($row);
            if ($classification['transaction_id'] > 0) {
                $valid[] = [
                    'row' => $row,
                    'transaction_id' => $classification['transaction_id'],
                ];
                $transactionids[] = $classification['transaction_id'];
                continue;
            }

            $skipped[] = $classification;
        }

        return [
            'valid' => $valid,
            'skipped' => $skipped,
            'transaction_ids' => $transactionids,
        ];
    }

    /**
     * @param $repository
     * @param array $skipped
     * @return void
     */
    private function mark_skipped_rows($repository, array $skipped): void
    {
        foreach ($skipped as $item) {
            $repository->updateDataBD([
                'id' => $item['row_id'],
                'ds_error' => $item['ds_error'],
                'success' => repository_type::STATE_UP_ERROR,
                'is_sucessful' => 0,
            ]);
        }
    }

    /**
     * @param array $skipped
     * @param $log
     * @return void
     */
    private function report_skipped_rows(array $skipped, $log): void
    {
        if (empty($skipped)) {
            return;
        }

        $rowids = array_map(function (array $item): int {
            return (int) $item['row_id'];
        }, $skipped);

        mtrace(sprintf(
            '[clean] skipped rows with invalid JSON: count=%d ids=%s' . PHP_EOL,
            count($skipped),
            implode(',', $rowids)
        ));

        $log->add_line(' --- SKIPPED ROWS (invalid JSON): count=' . count($skipped));
        foreach ($skipped as $item) {
            $log->add_line(' --- ' . $item['summary']);
        }
    }

    /**
     * @param \stdClass $row
     * @return array
     */
    private function classify_row($row): array
    {
        $rowid = (int) ($row->id ?? 0);

        if (empty($row->json)) {
            return [
                'row_id' => $rowid,
                'transaction_id' => 0,
                'reason' => 'empty_json',
                'summary' => sprintf('row id=%s reason=empty_json', $rowid),
                'ds_error' => 'Skipped: empty JSON payload.',
            ];
        }

        $json = json_decode($row->json, true);
        if (!is_array($json)) {
            return [
                'row_id' => $rowid,
                'transaction_id' => 0,
                'reason' => 'invalid_json',
                'summary' => sprintf(
                    'row id=%s reason=invalid_json preview=%s',
                    $rowid,
                    $this->truncate_json_preview((string) $row->json)
                ),
                'ds_error' => 'Skipped: invalid JSON payload.',
            ];
        }

        if (empty($json['transactionId'])) {
            $sectionid = isset($json['sectionId']) ? (string) $json['sectionId'] : '';
            return [
                'row_id' => $rowid,
                'transaction_id' => 0,
                'reason' => 'missing_transaction_id',
                'summary' => sprintf(
                    'row id=%s reason=missing_transactionId sectionId=%s',
                    $rowid,
                    $sectionid !== '' ? $sectionid : 'n/a'
                ),
                'ds_error' => 'Skipped: JSON missing transactionId.',
            ];
        }

        $transactionid = intval($json['transactionId']);
        if ($transactionid <= 0) {
            return [
                'row_id' => $rowid,
                'transaction_id' => 0,
                'reason' => 'invalid_transaction_id',
                'summary' => sprintf(
                    'row id=%s reason=invalid_transactionId value=%s',
                    $rowid,
                    (string) $json['transactionId']
                ),
                'ds_error' => 'Skipped: invalid transactionId.',
            ];
        }

        return [
            'row_id' => $rowid,
            'transaction_id' => $transactionid,
            'reason' => '',
            'summary' => '',
            'ds_error' => '',
        ];
    }

    /**
     * @param string $json
     * @param int $maxlength
     * @return string
     */
    private function truncate_json_preview(string $json, int $maxlength = 200): string
    {
        $json = preg_replace('/\s+/', ' ', trim($json));
        if ($json === '') {
            return '(empty)';
        }

        if (strlen($json) <= $maxlength) {
            return $json;
        }

        return substr($json, 0, $maxlength) . '...';
    }

    /**
     * @param array $messages
     * @return string
     */
    private function summarize_up_messages(array $messages): string
    {
        $successful = 0;
        $failed = 0;
        foreach ($messages as $message) {
            if (!empty($message['is_successful'])) {
                $successful++;
            } else {
                $failed++;
            }
        }

        return 'count=' . count($messages) . ' successful=' . $successful . ' failed=' . $failed;
    }
}
