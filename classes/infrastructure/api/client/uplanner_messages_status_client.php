<?php
/**
 * @package     uPlannerConnect
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_uplannerconnect\infrastructure\api\client;

use local_uplannerconnect\infrastructure\api\curl_wrapper;
use local_uplannerconnect\infrastructure\api\exception\uplanner_messages_status_api_exception;
use local_uplannerconnect\plugin_config\plugin_config;

defined('MOODLE_INTERNAL') || die;

/**
 * HTTP client for uPlanner message status lookup (GET messages_host API).
 */
class uplanner_messages_status_client
{
    const MAX_TRANSACTION_IDS = 100;

    const DEFAULT_LIMIT = 500;

    /** Safety cap when hasNext never becomes false or page param is ignored. */
    const MAX_PAGES = 50;

    /**
     * Status API can be slow in QA (~7s); send client keeps curl_wrapper default (4s).
     */
    const CONNECT_TIMEOUT_SECONDS = 10;

    const REQUEST_TIMEOUT_SECONDS = 30;

    /**
     * @var curl_wrapper
     */
    private curl_wrapper $curlwrapper;

    /**
     * @var string
     */
    private string $key = '';

    /**
     * @var string
     */
    private string $tokenurl = '';

    /**
     * @var string
     */
    private string $statusurl = '';

    /**
     * @var string
     */
    private string $token = '';

    public function __construct()
    {
        $this->curlwrapper = new curl_wrapper();
        $this->load_config();
    }

    /**
     * Load plugin configuration.
     *
     * @return void
     */
    private function load_config(): void
    {
        try {
            $this->key = get_config(plugin_config::PLUGIN_NAME_LOCAL, 'key') ?? '';
            $this->tokenurl = get_config(plugin_config::PLUGIN_NAME_LOCAL, 'token_endpoint') ?? '';
            $this->statusurl = trim((string) (get_config(plugin_config::PLUGIN_NAME_LOCAL, 'messages_host') ?? ''));
        } catch (\dml_exception $e) {
            error_log('uplanner_messages_status_client - load_config: ' . $e->getMessage() . PHP_EOL);
        }
    }

    /**
     * Whether status API URL is configured.
     *
     * @return bool
     */
    public function is_configured(): bool
    {
        return $this->statusurl !== '' && $this->key !== '' && $this->tokenurl !== '';
    }

    /**
     * Fetch status items for up to 100 transaction IDs (handles API pagination).
     *
     * @param array $transactionids Numeric transaction IDs.
     * @return array Raw API items.
     * @throws uplanner_messages_status_api_exception
     */
    public function fetch_items(array $transactionids): array
    {
        if (!$this->is_configured() || empty($transactionids)) {
            return [];
        }

        $transactionids = array_slice(array_values($transactionids), 0, self::MAX_TRANSACTION_IDS);
        $transactionids = array_map('intval', $transactionids);
        $transactionids = array_filter($transactionids);

        if (empty($transactionids)) {
            return [];
        }

        if (!$this->get_token()) {
            throw new uplanner_messages_status_api_exception(
                $this->tokenurl,
                0,
                'Could not obtain API signature (token_endpoint).'
            );
        }

        $items = [];
        $page = 1;
        $hasnext = false;

        while ($page <= self::MAX_PAGES) {
            $pageresult = $this->fetch_page($transactionids, $page);
            $countbefore = count($items);
            $items = array_merge($items, $pageresult['items']);
            $hasnext = $pageresult['hasnext'];

            if (!$hasnext) {
                return $items;
            }

            if (count($items) === $countbefore) {
                error_log(
                    'uplanner_messages_status_client: pagination stopped at page ' . $page .
                    ' (no new items; page/limit may be ignored by API).' . PHP_EOL
                );
                return $items;
            }

            $responsepage = (int) ($pageresult['responsepage'] ?? $page);
            if ($page > 1 && $responsepage < $page) {
                error_log(
                    'uplanner_messages_status_client: pagination stopped at page ' . $page .
                    ' (API returned page ' . $responsepage . ').' . PHP_EOL
                );
                return $items;
            }

            $page++;
        }

        if ($hasnext) {
            $endpoint = $this->build_status_url($transactionids, self::MAX_PAGES);
            throw new uplanner_messages_status_api_exception(
                $endpoint,
                200,
                'Pagination safety limit reached (' . self::MAX_PAGES . ' pages); hasNext is still true.'
            );
        }

        return $items;
    }

    /**
     * Base URL without query string (avoids duplicate or conflicting params).
     *
     * @return string
     */
    private function get_status_base_url(): string
    {
        $url = $this->statusurl;
        $qpos = strpos($url, '?');
        if ($qpos !== false) {
            $url = substr($url, 0, $qpos);
        }

        return rtrim($url, '/');
    }

    /**
     * Build GET URL with unencoded commas in transactionIds (required by API).
     *
     * @param array $transactionids
     * @param int $page
     * @return string
     */
    private function build_status_url(array $transactionids, int $page): string
    {
        $ids = implode(',', array_map('intval', $transactionids));

        return $this->get_status_base_url()
            . '?transactionIds=' . $ids
            . '&limit=' . self::DEFAULT_LIMIT
            . '&page=' . $page;
    }

    /**
     * Request one page of status data.
     *
     * @param array $transactionids
     * @param int $page
     * @return array Keys items, hasnext, responsepage.
     * @throws uplanner_messages_status_api_exception
     */
    private function fetch_page(array $transactionids, int $page): array
    {
        $url = $this->build_status_url($transactionids, $page);

        $headers = [
            'Content-Type: application/json',
            'Authorization: ' . $this->token,
        ];

        $idcount = count($transactionids);
        $this->curlwrapper->set_header($headers);
        $this->apply_request_timeouts();
        $responsebody = $this->curlwrapper->get($url);
        $code = $this->curlwrapper->get_code();

        $decoded = json_decode($responsebody, true);

        if ($this->is_curl_transport_error($responsebody, $code)) {
            throw new uplanner_messages_status_api_exception(
                $url,
                0,
                (string) $responsebody . ' (timeout limit: ' . self::REQUEST_TIMEOUT_SECONDS . 's)'
            );
        }

        if ($code < 200 || $code >= 300) {
            $detail = is_string($responsebody) ? substr($responsebody, 0, 500) : '';
            throw new uplanner_messages_status_api_exception($url, $code, $detail);
        }

        if (!is_array($decoded)) {
            throw new uplanner_messages_status_api_exception(
                $url,
                $code,
                'Invalid JSON response from status API.'
            );
        }

        if (empty($decoded['status'])) {
            $detail = isset($decoded['message']) ? (string) $decoded['message'] : json_encode($decoded);
            throw new uplanner_messages_status_api_exception($url, $code, 'API returned status=false. ' . $detail);
        }

        if (!isset($decoded['data']['items']) || !is_array($decoded['data']['items'])) {
            throw new uplanner_messages_status_api_exception(
                $url,
                $code,
                'API response missing data.items array.'
            );
        }

        $itemcount = count($decoded['data']['items']);
        $hasnext = !empty($decoded['data']['hasNext']);
        $this->log_request_summary('GET', $code, $page, $itemcount, $hasnext, $idcount);

        return [
            'items' => $decoded['data']['items'],
            'hasnext' => $hasnext,
            'responsepage' => $decoded['data']['page'] ?? $page,
        ];
    }

    /**
     * Obtain API signature (no topicName).
     *
     * @return string
     */
    private function get_token(): string
    {
        if ($this->token !== '') {
            return $this->token;
        }

        if ($this->key === '' || $this->tokenurl === '') {
            return '';
        }

        $data = [
            'sharedAccessKey' => $this->key,
        ];
        $headers = [
            'Content-Type: application/json',
        ];

        $this->curlwrapper->set_header($headers);
        $this->apply_request_timeouts();
        $response = $this->curlwrapper->post($this->tokenurl, $data);
        $code = $this->curlwrapper->get_code();

        if ($this->is_curl_transport_error($response, $code)) {
            throw new uplanner_messages_status_api_exception(
                $this->tokenurl,
                0,
                (string) $response . ' (timeout limit: ' . self::REQUEST_TIMEOUT_SECONDS . 's)'
            );
        }

        if ($code === 201) {
            $decoded = json_decode($response, true);
            $this->token = $decoded['signature'] ?? '';
        } else {
            $this->token = '';
            error_log('uPlanner status token POST HTTP ' . $code . PHP_EOL);
        }

        return $this->token;
    }

    /**
     * Override curl_wrapper defaults (4s) for slow status/token endpoints in QA.
     *
     * @return void
     */
    private function apply_request_timeouts(): void
    {
        $this->curlwrapper->add_option(CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT_SECONDS);
        $this->curlwrapper->add_option(CURLOPT_TIMEOUT, self::REQUEST_TIMEOUT_SECONDS);
    }

    /**
     * @param mixed $responsebody
     * @param int $code
     * @return bool
     */
    private function is_curl_transport_error($responsebody, int $code): bool
    {
        if (!is_string($responsebody) || $responsebody === '') {
            return false;
        }

        if ($code >= 200 && $code < 300) {
            return false;
        }

        return stripos($responsebody, 'timed out') !== false
            || stripos($responsebody, 'could not resolve host') !== false
            || stripos($responsebody, 'failed to connect') !== false;
    }

    /**
     * @param string $method
     * @param int $code
     * @param int $page
     * @param int $itemcount
     * @param bool $hasnext
     * @param int $idcount
     * @return void
     */
    private function log_request_summary(
        string $method,
        int $code,
        int $page,
        int $itemcount,
        bool $hasnext,
        int $idcount
    ): void {
        mtrace(sprintf(
            '[clean] status API %s HTTP %d | page=%d | items=%d | hasNext=%s | ids=%d' . PHP_EOL,
            $method,
            $code,
            $page,
            $itemcount,
            $hasnext ? 'true' : 'false',
            $idcount
        ));
    }
}
