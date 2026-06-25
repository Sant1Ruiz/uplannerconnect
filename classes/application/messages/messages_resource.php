<?php
/**
 * @package     local_uplannerconnec
 * @copyright   Daniel Eduardo Dorado <doradodaniel14@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_uplannerconnect\application\messages;

use local_uplannerconnect\infrastructure\api\client\uplanner_messages_status_client;
use local_uplannerconnect\infrastructure\api\exception\uplanner_messages_status_api_exception;

defined('MOODLE_INTERNAL') || die;

/**
 * Class messages_resource
 */
class messages_resource
{
    /**
     * @var uplanner_messages_status_client
     */
    private uplanner_messages_status_client $statusclient;

    public function __construct()
    {
        $this->statusclient = new uplanner_messages_status_client();
    }

    /**
     * Get messages status from uPlanner API.
     *
     * @param string $transactions Comma-separated transaction IDs.
     * @return array
     * @throws uplanner_messages_status_api_exception
     */
    public function get_messages($transactions)
    {
        if (!$this->statusclient->is_configured()) {
            error_log('get_messages: messages_host, key or token_endpoint not configured' . PHP_EOL);
            return [];
        }

        $transactionids = $this->parse_transaction_ids($transactions);
        if (empty($transactionids)) {
            return [];
        }

        $allitems = [];
        $chunks = array_chunk($transactionids, uplanner_messages_status_client::MAX_TRANSACTION_IDS);
        foreach ($chunks as $chunk) {
            $allitems = array_merge($allitems, $this->statusclient->fetch_items($chunk));
        }

        $deduped = $this->dedupe_by_transaction($allitems);

        return $this->normalize_items($deduped);
    }

    /**
     * @param string $transactions
     * @return int[]
     */
    private function parse_transaction_ids(string $transactions): array
    {
        if ($transactions === '') {
            return [];
        }

        $parts = explode(',', $transactions);
        $ids = [];
        foreach ($parts as $part) {
            $id = intval(trim($part));
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Keep the item with the latest processedAt per transactionId.
     *
     * @param array $items
     * @return array
     */
    private function dedupe_by_transaction(array $items): array
    {
        $bytransaction = [];

        foreach ($items as $item) {
            if (!isset($item['transactionId'])) {
                continue;
            }
            $tid = (string) $item['transactionId'];
            if (
                !isset($bytransaction[$tid]) ||
                $this->processed_at_timestamp($item['processedAt'] ?? '') >
                $this->processed_at_timestamp($bytransaction[$tid]['processedAt'] ?? '')
            ) {
                $bytransaction[$tid] = $item;
            }
        }

        return array_values($bytransaction);
    }

    /**
     * @param string $processedat
     * @return int
     */
    private function processed_at_timestamp(string $processedat): int
    {
        if ($processedat === '') {
            return 0;
        }

        $timestamp = strtotime($processedat);
        return $timestamp !== false ? $timestamp : 0;
    }

    /**
     * Map API items to legacy fields expected by messages_status_repository.
     *
     * @param array $items
     * @return array
     */
    private function normalize_items(array $items): array
    {
        $messages = [];

        foreach ($items as $item) {
            $messages[] = [
                'id_transaction' => (int) ($item['transactionId'] ?? 0),
                'is_successful' => !empty($item['isSuccessful']) ? 1 : 0,
                'ds_error' => $item['error'] ?? '',
            ];
        }

        return $messages;
    }
}
