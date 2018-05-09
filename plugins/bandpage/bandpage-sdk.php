<?php
namespace BandPage;
/**
 * Copyright 2012 BandPage, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */
class BandPageAPIConfiguration {
    const
        SDK_LANGUAGE = 'php',
        SDK_VERSION  = '0.1',

        AUTH_REQUIRED = true,

        URL_BASE = 'https://api-read.bandpage.com',

        URI_TOKEN  = 'token',
        URI_WIDGET = 'widgets'
    ;

    private static $DEFAULT_PARAMS = array(
        'since' => 0,
        'until' => 0,
        'limit' => 10
    );

    private static $SDK_VERSION_SIGNATURE = null;

    public static function getDefaultParams() {
        return self::$DEFAULT_PARAMS;
    }

    public static function getVersionSignature() {
        if (!is_null(static::$SDK_VERSION_SIGNATURE))
            return static::$SDK_VERSION_SIGNATURE;

        return static::$SDK_VERSION_SIGNATURE
            = sprintf("%s/%s", static::SDK_LANGUAGE, static::SDK_VERSION);
    }
}
if (!function_exists('json_decode')) {
    throw new \Exception('BandPageAPI needs the JSON PHP extension.');
}

class BandPageAPI {

    private $credential = array(
        'clientId'        => '',
        'sharedSecret'    => '',
        'accessToken'     => '',
        'tokenType'       => '',
        'tokenExpiration' => ''
    );

    private $defaultParams = array();

    private $transportStrategy;

    private static $registeredTransports = array();

    /**
     * Public API
     */

    // return a new instance of BandPageAPI
    // TODO:: add auto select for strategy if not explicitly passed in
    public static function of(
        array $credential,
        IBandPageAPITransportStrategy $transport_strategy = null
    ) {
        return new static(
            $credential,
            static::getTransportStrategy($transport_strategy)
        );
    }

    public function get($rmid) {
        return json_decode($this->executeRequest(
            $this->getRequestUrl($rmid)
        )->getBody(), true);
    }

    public function getConnection($rmid, $connection, $params = array()) {
        $response = $this->executeRequest(
            $this->getRequestUrl($rmid, $connection),
            $params
        );

        return BandPageAPIConnection::of($rmid, $connection, $this, $response);
    }

    public function getWidget($type, $params) {
        return json_decode($this->executeRequest(
            $this->getWidgetUrl($type),
            $params
        )->getBody(), true);
    }

    public static function registerTransport($class) {
        static::$registeredTransports[] = $class;
    }

    /* URL Builders */

    private function getRequestUrl($rmid, $connection = null) {
        return sprintf(
            "%s/%s",
            BandPageAPIConfiguration::URL_BASE,
            $connection ? "$rmid/$connection" : $rmid
        );
    }

    private function getTokenUrl() {
        return sprintf(
            "%s/%s",
            BandPageAPIConfiguration::URL_BASE,
            BandPageAPIConfiguration::URI_TOKEN
        );
    }
    private function getWidgetUrl($type) {
        return sprintf(
            "%s/%s/%s",
            BandPageAPIConfiguration::URL_BASE,
            BandPageAPIConfiguration::URI_WIDGET,
            $type
        );
    }

    /**
     * Private methods
     */

    private function __construct($credential, $transport_strategy) {

        $this->defaultParams = BandPageAPIConfiguration::getDefaultParams();

        if (!$transport_strategy instanceof IBandPageAPITransportStrategy) {
            throw new BandPageAPIException(
                'BandPageAPI needs an instance of IBandPageAPITransportStrategy.',
                BandPageAPIException::BAD_TRANSPORT
            );
        }

        $this->transportStrategy = $transport_strategy;

        if (!is_array($credential)) {
            throw new BandPageAPIException(
                'BandPageAPI needs authentication configuration.',
                BandPageAPIException::NO_AUTHENTICATION
            );
        }

        $this->credential = array_merge($this->credential, $credential);
    }

    /* Authentication */

    /**
     * Authenticate if there is no token
     * @argument    {[Array]} $auth_config
     *                $auth_config should be either
     *                 an array with keys shared_secret and clientId
     */
    private function auth() {

        $request = BandPageAPIRequest::of(
            $this->getTokenUrl(),
            BandPageAPIRequest::POST,
            $this->getAuthorizionHeaders(),
            $this->getAuthorizationParams()
        );

        $response = $this->transportStrategy->makeRequest($request);

        $body = json_decode($response->getBody());

        $this->credential = array_merge(
            $this->credential,
            $this->getNormalizedCredential($body)
        );
    }

    private function getAccessToken() {
        if ($this->isValidToken()) {
            return $this->credential['accessToken'];
        }

        $this->auth();

        return $this->credential['accessToken'];
    }

    private function getAuthorizionHeaders() {
        $authstring = base64_encode(sprintf(
            "%s:%s",
            $this->credential['clientId'],
            $this->credential['sharedSecret']
        ));

        return $this->getHeaders(
            array('Authorization' => "Basic $authstring")
        );
    }

    private function getAuthorizationParams() {
        return array(
            'client_id'   => $this->credential['clientId'],
            'grant_type' =>'client_credentials'
        );
    }

    private function getNormalizedCredential($credential) {
        return array(
            'accessToken'     => $credential->access_token,
            'tokenType'       => $credential->token_type,
            'tokenExpiration' => time() + $credential->expires_in
        );
    }

    private static function getTransportStrategy(
        IBandPageAPITransportStrategy $strategy = null
    ) {
        if ($strategy instanceof IBandPageAPITransportStrategy)
            return $strategy;

        if (count(static::$registeredTransports) < 1)
            throw new BandPageAPIException('No transport strategy available.');

        $class = static::$registeredTransports[0];

        return new $class;
    }


    private function isValidToken() {
        return isset($this->credential['accessToken'])
            && $this->credential['accessToken']
            && $this->credential['tokenExpiration'] < time();
    }

    /* API Access/Querying */

    /**
     * Creates a new BandPageAPIRequest object and sends it
     * to the defined transport strategy.
     *
     * @return BandPageAPIResponse $response
     */
    private function executeRequest($resource, $params=array()) {
        $request = BandPageAPIRequest::of(
            $resource,
            BandPageAPIRequest::GET,
            $this->getRequestHeaders(),
            $this->getRequestParams($params)
        );

        $response = $this->transportStrategy->makeRequest($request);

        if ($response->isError()) {
            throw new BandPageAPIException("There was a problem with the request.");
        }

        return $response;
    }

    private function getDefaultHeaders() {
        return array(
            'RM-SDK-Version' => BandPageAPIConfiguration::getVersionSignature()
        );
    }

    private function getHeaders($headers = array()) {
        return array_merge($this->getDefaultHeaders(), $headers);
    }

    private function getDefaultParams() {
        $today = time();

        $milliSecondsInADay = 86400000;

        return array(
            'since' => $today - (30 * $milliSecondsInADay),
            'until' => $today,
            'limit' => 10
        );
    }

    private function getRequestHeaders() {
        if (!BandPageAPIConfiguration::AUTH_REQUIRED)
            return $this->getHeaders();

        $accessToken = $this->getAccessToken();
        return $this->getHeaders(array("Authorization" => "Bearer $accessToken"));
    }

    private function getRequestParams($params) {
        return array_merge($this->defaultParams, $params);
    }
}
class BandPageAPIException extends \Exception {

    const NO_CLIENT_ID        = 0;
    const BAD_CLIENT_ID       = 1;
    const NO_SHARED_SECRET    = 2;
    const BAD_SHARED_SECRET   = 3;
    const BAD_ACCESS_TOKEN    = 4;
    const NO_RESPONSE_CODE    = 5;
    const NO_RESPONSE_HEADERS = 6;
    const NO_AUTHENTICATION   = 7;
    const BAD_TRANSPORT       = 8;

    public function __toString() {
        return sprintf(
            "%s: [%01d] %s",
            __CLASS__,
            $this->code,
            $this->message
        );
    }
}
class BandPageAPIRequest {

    const GET    = 'GET';
    const POST   = 'POST';
    const DELETE = 'DELETE';

    private $url;
    private $body;
    private $headers;
    private $params = null;
    private $method;

    /* Public API */

    public function getHeaders() {
        return $this->headers;
    }

    public function getMethod() {
        return $this->method;
    }

    public function getParam($key) {
        return isset($this->params[$key])
            ? $this->params[$key]
            : null;
    }

    public function getParams() {
        return $this->params;
    }

    public function getResource() {
        return $this->resource;
    }

    public function getUrl() {
        return $this->url;
    }

    /* Instance Construction */

    private function __construct(
        $url,
        $method,
        $headers,
        $params,
        $body = null
    ) {

        $this->url = $url;

        $this->method = $method;

        $this->headers = $headers;

        $this->params = $params;

        if (isset($body)) {
            $this->body = $body;
        }
    }

    public static function of($url, $method, $headers, $params, $body = null) {
        return new BandPageAPIRequest($url, $method, $headers, $params, $body);
    }
}
class BandPageAPIResponse {

    private $code;
    private $body;
    private $headers;
    private $links;
    private $parsedHeaders;
    private $request;

    /* Instance Construction */

    private function __construct($code, $headers, $body, BandPageAPIRequest $request) {

        $this->code = $code;

        $this->headers = $headers;

        $this->body = $body;

        $this->request = $request;
    }

    public static function of($code, $headers, $body, BandPageAPIRequest $request) {

        // we definitely need an http code and headers
        if (is_null($code) || is_null($headers)) {
            throw new BandPageAPIException(
                'BandPageAPIResponse must have an HTTP code and Headers set',
                BandPageAPIException::NO_RESPONSE_HEADERS
            );
        }

        return new BandPageAPIResponse($code, $headers, $body, $request);
    }

    /* Public API */

    public static function extractValue($regex, $source) {
        $matches = array();
        preg_match($regex, $source, $matches);
        return $matches[1];
    }

    public function getBody() {
        return $this->body;
    }

    private function getHeaders() {
        return $this->headers;
    }

    public function getLinks() {
        if ($this->links != null)
            return $this->links;

        $headers = $this->getHeaders();

        // printf("%s %s Headers: %s\n", __CLASS__, __METHOD__, print_r($headers, true));

        if (!isset($headers['Link']))
            return array();

        $links = array_map(function($link) {
            list ($url, $rel, $type, $title) = explode(';', $link);

            return array(
                'url'   => BandPageAPIResponse::extractValue('/<(.+)>$/', $url),
                'rel'   => BandPageAPIResponse::extractValue('/"(.+)"$/', $rel),
                'type'  => BandPageAPIResponse::extractValue('/"(.+)"$/', $type),
                'title' => BandPageAPIResponse::extractValue('/"(.+)"$/', $title)
            );
        }, $headers['Link']);

        $keys = array_map(function($link) {
            return $link['rel'];
        }, $links);

        return $this->links = array_combine($keys, $links);
    }

    public function getRequest() {
        return $this->request;
    }

    public function isError() {
        return $this->code > 299;
    }
}
class BandPageAPIConnection {
    private $rmid;
    private $connection;
    private $api;
    private $responses = array();
    private $objects = array();
    private $max;
    private $lastPage;
    private $limit;

    /* Public API */

    public function getPage($page_num) {
        if (isset($this->objects[$page_num]))
            return $this->objects[$page_num];

        if (!is_null($this->lastPage) && $page_num > $this->lastPage)
            $this->invalidPage($page_num);

        for ($i = count($this->objects) ; $i <= $page_num ; $i++) {
            $this->fetchPage($page_num);
        }

        return $this->objects[$page_num];
    }

    /* Instance Construction */

    private function __construct(
        $rmid = '',
        $connection = '',
        BandPageAPI $api,
        BandPageAPIResponse $response
    ) {
        $this->rmid = $rmid;
        $this->connection = $connection;
        $this->api = $api;
        $this->applyResponse($response);
    }

    public static function of(
        $rmid = '',
        $connection = '',
        BandPageAPI $api = null,
        BandPageAPIResponse $response = null
    ) {
        if (!$rmid || !is_string($rmid))
            throw new \InvalidArgumentException('Invalid RMID.');

        if (!$connection || !is_string($connection))
            throw new \InvalidArgumentException('Invalid connection type.');

        if (!$api instanceof BandPageAPI)
            throw new \InvalidArgumentException('BandPageAPIConnection requires an instance of BandPageAPI.');

        if (!$response instanceof BandPageAPIResponse)
            throw new \InvalidArgumentException('BandPageAPIConnection requires an instance of BandPageAPIResponse.');

        return new BandPageAPIConnection($rmid, $connection, $api, $response);
    }

    /* Helper Methods */

    private function applyResponse(BandPageAPIResponse $response) {
        $this->limit = $response->getRequest()->getParam('limit');
        $this->responses[] = $response;
        $this->objects[] = json_decode($response->getBody(), true);
    }

    private function fetchPage($page_num) {
        $params = $this->getNextRequestParams($page_num);

        $conn_object = $this->api->getConnection(
            $this->rmid,
            $this->connection,
            $params
        );

        $this->applyResponse($conn_object->getResponse(0));

        $this->validatePage($page_num);
    }

    private function getCursorForOffset($offset) {
        $page = $offset / $this->limit;
        $cursor = $offset % $this->limit;

        return array($page, $cursor);
    }

    private function getNextRequestParams($page_num) {
        $old_page_num = $page_num - 1;

        $response = $this->getResponse($old_page_num);

        $links = $response->getLinks();

        return $this->getParamsFromUrl($links['next']['url']);
    }

    private function getParamsFromUrl($url) {
        $url_parts = parse_url($url);

        $params = array();

        parse_str($url_parts['query'], $params);

        return $params;
    }

    public function getResponse($page_num) {
        return $this->responses[$page_num];
    }

    private function invalidPage($page_num) {
        throw new BandPageAPIException("Invalid page number: $page_num");
    }

    private function validatePage($page_num) {
        $count = count($this->objects[$page_num]);

        if ($count == $this->limit)
            return;

        if ($count == 0) {
            $this->lastPage = $page_num - 1;
            $this->max = $this->lastPage * $this->limit;
            $this->invalidPage($page_num);
        }

        if ($count < $this->limit) {
            $this->lastPage = $page_num;
            $this->max = ($page_num * $this->lastPage) - ($this->limit - $count);
        }
    }
}
/*
 * Interface for concrete HTTP transport strategy classes.
 *
 * @returns {BandPageAPIResponse}
 */
interface IBandPageAPITransportStrategy {
    public function makeRequest(BandPageAPIRequest $request);
}
if(function_exists("curl_init")){
BandPageAPI::registerTransport('\\BandPage\\BandPageAPITransportCurl');

class      BandPageAPITransportCurl
implements IBandPageAPITransportStrategy {
    function makeRequest(BandPageAPIRequest $request) {
        $curl = curl_init($request->getUrl());

        $headers = $request->getHeaders();

        curl_setopt_array($curl, array(
            CURLOPT_HEADER => true,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array_map(function($key) use ($headers) {
                return sprintf("%s: %s", $key, $headers[$key]);
            }, array_keys($headers))
        ));

        $paramString = http_build_query($request->getParams());

        if ($request->getMethod() == "POST") {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $paramString);
        } else {
            $url = sprintf("%s?%s", $request->getUrl(), $paramString);
            curl_setopt($curl, CURLOPT_URL, $url);
        }

        $response = curl_exec($curl);
        $response_info = curl_getinfo($curl);

        $ygdrasil = "\r\n\r\n";
        list ($responseHeaders, $responseBody) = explode($ygdrasil, $response);

        $header_map = array();

        foreach (array_slice(explode("\r\n", $responseHeaders), 1) as $header) {

            if (false === strpos($header, ':')) {
                continue;
            }

            list ($key, $value) = preg_split("/:\s+/", $header);

            if (!isset($header_map[$key])) {
                $header_map[$key] = $value;
                continue;
            }

            if (is_array($header_map[$key])) {
                $header_map[$key][] = $value;
                continue;
            }

            $header_map[$key]
                = array($header_map[$key], $value);
        }

        return BandPageAPIResponse::of(
            $response_info['http_code'],
            $header_map,
            $responseBody,
            $request
        );
    }
}
}
if(class_exists("\\HttpRequest")){
BandPageAPI::registerTransport('\\BandPage\\BandPageAPITransportHttpRequest');

class      BandPageAPITransportHttpRequest
implements IBandPageAPITransportStrategy {
    function makeRequest(BandPageAPIRequest $request) {

        $url     = $request->getUrl();
        $method  = $request->getMethod();
        $headers = $request->getHeaders();
        $params  = $request->getParams();

        $req = new \HttpRequest($url, $this->getHttpMethod($method), array(
            'headers' => $headers
        ));

        if ($method === "POST") {
            $req->setPostFields($params);
        } else {
            $req->addQueryData($params);
        }

        $response = $req->send();

        return BandPageAPIResponse::of(
            $response->getResponseCode(),
            $response->getHeaders(),
            $response->getBody(),
            $request
        );
    }

    function getHttpMethod($method = "GET") {
        $methods = array(
            'GET'    => \HTTP_METH_GET,
            'POST'   => \HTTP_METH_POST,
            'PUT'    => \HTTP_METH_PUT,
            'DELETE' => \HTTP_METH_DELETE
        );

        return $methods[$method];
    }
}
}
