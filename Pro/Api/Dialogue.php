<?php
/**
 * API клиент для сайта Professionali.RU
 *
 * @author    Valetin Gernovich <gernovich@ya.ru>
 * @copyright Copyright (c) 2012, Valetin Gernovich. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * Диалог между клиентом и сервером API
 *
 * @package Pro\Api
 * @author  Valetin Gernovich <gernovich@ya.ru>
 */
class Pro_Api_Dialogue
{
    /**
     * URL запроса
     *
     * @var string
     */
    private $url = '';

    /**
     * Параметры запорса
     *
     * @var array
     */
    private $parameters = array();

    /**
     * HTTP код ответа
     *
     * @var integer
     */
    private $http_code = 0;

    /**
     * Content-Type ответа
     *
     * @var string
     */
    private $content_type = '';

    /**
     * HTTP заголовки запроса
     *
     * @var array
     */
    private $request = array();

    /**
     * HTTP заголовки ответа
     *
     * @var array
     */
    private $response = array();

    /**
     * Тело ответа
     *
     * @var array
     */
    private $body = '';

    /**
     * Декодированный JSON тела ответа
     *
     * @var mixed
     */
    private $json_decode = array();

    /**
     * Конструктор
     *
     * @param string   $response Результат запорса
     * @param resource $ch         CURL хендлер
     * @param string   $url        URL запроса
     * @param array    $parameters Параметры запорса
     * @param boolean  $debug      Режим отладки
     *
     * @param array $post
     */
    public function __construct($response, $ch, $url, array $parameters = array(), $debug)
    {
        // разбор параметров запроса
        if ($debug) {
            $this->content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

            // разбор запроса к серверу
            $this->request = str_replace("\r\n", "\n", curl_getinfo($ch, CURLINFO_HEADER_OUT));
            list($this->request, ) = explode("\n\n", $this->request);
            $this->request = explode("\n", $this->request);

            // разбор ответа от сервера
            $response = explode("\n\n", str_replace("\r\n", "\n", $response));
            while ($block = array_shift($response)) {
                // это заголовок
                if (substr($block, 0, 4) == 'HTTP') {
                    $this->response = array_merge($this->response, explode("\n", $block));
                } else {
                    array_unshift($response, $block);
                    break;
                }
            }
            $this->body = implode("\n", $response);
        } else {
            $this->body = $response;
        }

        $this->url = $url;
        $this->parameters = $parameters;
        $this->http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->json_decode = json_decode($this->body, true);
    }

    /**
     * Возвращает URL запроса
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Возвращает параметры запорса
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Возвращает HTTP код ответа
     *
     * @return integer
     */
    public function getHttpCode()
    {
        return $this->http_code;
    }

    /**
     * Возвращает Content-Type ответа
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->content_type;
    }

    /**
     * Возвращает HTTP заголовки запроса
     *
     * @return array
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Возвращает HTTP заголовки ответа
     *
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Возвращает тело ответа
     *
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Возвращает декодированный JSON тела ответа
     *
     * @return array
     */
    public function getJsonDecode()
    {
        return $this->json_decode;
    }

    /**
     * Возвращает параметры запроса в виде массива
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'url'          => $this->url,
            'parameters'   => $this->parameters,
            'request'      => $this->request,
            'http_code'    => $this->http_code,
            'content_type' => $this->content_type,
            'response'     => $this->response,
            'body'         => $this->body,
            'json_decode'  => $this->json_decode,
        );
    }
}