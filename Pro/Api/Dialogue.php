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
    private $url;

    /**
     * POST параметры запорса
     *
     * @var array
     */
    private $post;

    /**
     * HTTP код ответа
     *
     * @var integer
     */
    private $http_code;

    /**
     * Content-Type ответа
     *
     * @var string
     */
    private $content_type;

    /**
     * HTTP заголовки запроса
     *
     * @var array
     */
    private $request;

    /**
     * HTTP заголовки ответа
     *
     * @var array
     */
    private $response;

    /**
     * Тело ответа
     *
     * @var array
     */
    private $body;

    /**
     * Декодированный JSON тела ответа
     *
     * @var mixed
     */
    private $json_decode;

    /**
     * Конструктор
     *
     * @param string   $response Результат запорса
     * @param resource $ch       CURL хендлер
     * @param string   $url      URL запроса
     * @param array    $post     POST параметры запорса
     *
     * @param array $post
     */
    public function __construct($response, $ch, $url, array $post = array())
    {
        $this->url = $url;
        $this->post = $post;
        $this->http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        // разбор запроса к серверу
        $this->request = str_replace("\r\n", "\n", curl_getinfo($ch, CURLINFO_HEADER_OUT));
        list($this->request, ) = explode("\n\n", $this->request);
        $this->request = explode("\n", $this->request);

        // разбор ответа от сервера
        $response = explode("\n\n", str_replace("\r\n", "\n", $response));
        $this->body = array_pop($response);
        $this->response = explode("\n", implode("\n", $response));
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
     * Возвращает POST параметры запорса
     *
     * @return array
     */
    public function getPost()
    {
        return $this->post;
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
            'post'         => $this->post,
            'request'      => $this->request,
            'http_code'    => $this->http_code,
            'content_type' => $this->content_type,
            'response'     => $this->response,
            'body'         => $this->body,
            'json_decode'  => $this->json_decode,
        );
    }
}