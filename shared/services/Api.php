<?php
namespace SiteLint\Auth;

use Exception;
use stdClass;
use SiteLint\Shared\Http\ResponseStatus;

/**
 * Class to communicate with SiteLint API.
 *
 * PHP version >=5.3
 *
 * @package    SiteLint
 * @author     <support@sitelint.com>
 * @copyright  since 2022 SiteLint.com
 * @version    Git: $Id$
 */

const MAX_REFRESH_ACCESS_TOKEN_RETRIES = 1;

class Api
{
    private $config;
    private $appConfig;
    private $apiBaseUrl;

    const OPTION_NAME = 'sitelint';

    /** URL paths for all used resources endpoints methods */
    const URL_CHECK_EMAIL = 'user/check-email',
        URL_LOGIN = 'user/login',
        URL_CREATE = 'user/signup',
        URL_WORKSPACES = 'workspaces/user',
        URL_TOKENS = 'api-token/workspaces',
        URL_SITES = 'sites',
        URL_REFRESH_ACCESS_TOKEN = 'auth/refreshAccessToken',
        URL_AUDITS = 'audits';


    public function __construct()
    {
        $this->config = file_get_contents(__DIR__ . '/../../public/config.json');
        $this->appConfig = json_decode($this->config, true);
        $this->apiBaseUrl = $this->appConfig['apiUrl'];
    }

    /**
     * Allows to create user.
     *
     * @param array $data
     * @return array
     */
    public function create($data)
    {
        return $this->post(self::URL_CREATE, $data);
    }

    /**
     * Allows to log in account and obtain user key.
     *
     * @param array $data
     * @return array
     */
    public function login($data)
    {
        return $this->post(self::URL_LOGIN, $data);
    }

    /**
     * Allows to log in account and obtain user key.
     *
     * @param array $data
     * @return array
     */
    public function checkEmail($email)
    {
        $queryParams = [
            'email' => $email
        ];

        return $this->get(self::URL_CHECK_EMAIL, $queryParams);
    }

    /**
     * Allows to log in account and obtain user key.
     *
     * @param array $data
     * @return array
     */
    public function workspaces($email)
    {
        $queryParams = [
            'email' => $email
        ];

        return $this->get(self::URL_WORKSPACES,  $queryParams);
    }

    /**
     * Allows to log in account and obtain user key.
     *
     * @param array $data
     * @return array
     */
    public function tokens($workspace)
    {
        $queryParams = [
            "skip" => 0,
            "limit" => 0
        ];

        return $this->get(self::URL_TOKENS . "/$workspace", $queryParams);
    }

    /**
     * Allows to fetch sites by workspace Id.
     *
     * @param array $workspace
     * @return array
     */
    public function sites($workspace)
    {
        $queryParams = [
            "workspaceId" => $workspace,
            "skip" => 0,
            "limit" => 0,
        ];

        return $this->get(self::URL_SITES, $queryParams);
    }

    /**
     * Allows to log in account and obtain user key.
     *
     * @param array $data
     * @return array
     */
    public function audits($apiToken)
    {
      if (empty($apiToken)) {
        return [];
      }

      $queryParams = [
          "auditTypes" => "accessibility,logs,performance,privacy,quality,security,seo",
          "statuses" => "error,passed",
          "impactsType" => "critical,high,low,info",
          "standardVersions" => "1.0,2.0,2.1,2.2",
          "standardLevels" => "A,AA,AAA,best_practices",
          "errors" => "true",
          "needsReview" => "true",
          "recommendations" => "true"
      ];

      return $this->get(self::URL_AUDITS . "/$apiToken/last", $queryParams);
    }

    /**
     * Helper function to execute POST request.
     *
     * @param string $path request path
     * @param array $data optional POST data array
     * @return array|string array data or json encoded string of result
     * @throws Exception
     */
    private function post($path, $data)
    {
        $option = get_option(self::OPTION_NAME);
        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];

        if (isset($option['accessToken'])) {
            $headers['Authorization'] = 'Bearer ' . $option['accessToken'];
        }

        $httpParams = [];

        $httpParams['httpversion'] = '1.1';
        $httpParams['headers'] = $headers;
        $httpParams['body'] = json_encode($data);
        $httpParams['sslverify'] = false;

        $response = wp_remote_post($this->apiBaseUrl . $path, $httpParams);

        if (is_wp_error($response)) {
            print_r($response);

            return NULL;
        }

        return $response;
    }

    /**
     * Helper function to execute POST request.
     *
     * @param string $path request path
     * @param array $data optional POST data array
     * @return array|string array data or json encoded string of result
     * @throws Exception
     */
    private function get($path, $query, $retryCount = 0)
    {
        $option = get_option(self::OPTION_NAME);
        $headers = ['Accept' => 'application/json'];

        if (isset($option['accessToken'])) {
            $headers['Authorization'] = 'Bearer ' . $option['accessToken'];
        }

        $httpParams = [];
        $httpParams['httpversion'] = '1.1';
        $httpParams['headers'] = $headers;
        $httpParams['sslverify'] = false;

        $response = wp_remote_get($this->apiBaseUrl . $path . '?' . http_build_query($query), $httpParams);

        if (is_wp_error($response)) {
            return NULL;
        }

        $body = wp_remote_retrieve_body($response);

        if (empty($body)) {
          $body = new stdClass();
        } else {
          $body = json_decode($body, true); // Corrected variable usage
        }

        if ($body === null && json_last_error() !== JSON_ERROR_NONE) {
          $body = new stdClass();
        }

        if (isset($body->status) && $body->status == ResponseStatus::FORBIDDEN) {
            $retryCount++;

            if ($retryCount > MAX_REFRESH_ACCESS_TOKEN_RETRIES) {
                return NULL;
            }

            $this->refreshAccessToken();
            return $this->get($path, $query, $retryCount);
        }

        return $response;
    }

    private function refreshAccessToken()
    {
        $option = get_option(self::OPTION_NAME);

        if (isset($option['accessToken'])) {
            $headers['x-refresh-token'] = $option['refreshToken'];
        }

        $httpParams = [];

        $httpParams['httpversion'] = '1.1';
        $httpParams['headers'] = $headers;
        $httpParams['sslverify'] = false;
        $response = wp_remote_get($this->apiBaseUrl . self::URL_REFRESH_ACCESS_TOKEN, $httpParams);

        if (is_wp_error($response) || isset($body['error'])) {
            $this->deactivate();

            $page = esc_url_raw($_SERVER['PHP_SELF']);
            $sec = '1';

            header("Refresh: $sec; url=$page");
        }

        if (isset($response['headers']['x-access-token'])) {
            $this->updateOptions([
                'accessToken' => $response['headers']['x-access-token']
            ]);
        }
    }

    private function deactivate()
    {
        $this->updateOptions([
            'active' => false,
            'accessToken' => null,
            'refreshToken' => null,
            'apiToken' => null,
            'apiTokens' => null,
            'workspace' => null,
            'workspaces' => null,
            'email' => null,
            'audits' => null,
        ]);
    }

    private function updateOptions(array $options)
    {
        $current = $this->getOptions();
        foreach ($options as $key => $option) {
            $current[$key] = $option;
        }
        update_option(self::OPTION_NAME, $current);
    }

    /**
     * @return array
     */
    private function getOptions()
    {
        return get_option(self::OPTION_NAME);
    }
}
