<?php
/**
 * API клиент для сайта Professionali.RU
 *
 * @author    Valetin Gernovich <gernovich@ya.ru>
 * @copyright Copyright (c) 2012, Valetin Gernovich. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * Api клиент для professionali.ru
 *
 * @package Pro\Api
 * @author  Valetin Gernovich <gernovich@ya.ru>
 */
class Pro_Api_Client
{
    /**
     * HTTP Метод GET
     *
     * @var string
     */
    const HTTP_GET = 'GET';

    /**
     * HTTP Метод POST
     *
     * @var string
     */
    const HTTP_POST = 'POST';

    /**
     * Хост для апи
     *
     * @var string
     */
    const API_HOST = 'https://api.professionali.ru';

    /**
     * Метод API авторизации
     *
     * @var string
     */
    const POINT_AUTHORIZATION = '/oauth/authorize.html';

    /**
     * Метод API получение токена по коду
     *
     * @var string
     */
    const POINT_GET_TOKEN = '/oauth/getToken.json';

    /**
     * Метод API обновление токена
     *
     * @var string
     */
    const POINT_REFRESH_TOKEN = '/oauth/refreshToken.json';

    /**
     * Метод API завершение сианса
     *
     * @var string
     */
    const POINT_LOGOUT = '/oauth/logout.json';

    /**
     * Метод API получения общей информ о пользователе
     *
     * @var string
     */
    const POINT_GET_CURRENT   = '/users/get.json?ids[]=me&fields=id,name,link,avatar_big';

    /**
     * Имя ключа токена
     *
     * @var string
     */
    const NAME_ACCESS_TOKEN = 'access_token';

    /**
     * Имя ключа времени жизни токена
     *
     * @var string
     */
    const NAME_EXPIRES_IN = 'expires_in';

    /**
     * Имя ключа подписи запроса
     *
     * @var string
     */
    const NAME_SIGNATURE = 'signature';

    /**
     * Вид отображения окна авторизации в виде страници
     *
     * @var string
     */
    const DISPLAY_PAGE  = 'page';

    /**
     * Вид отображения окна авторизации в виде PopUp страници
     *
     * @var string
     */
    const DISPLAY_POPUP = 'popup';

    /**
     * Вид отображения окна авторизации для Touch Screen-ов
     *
     * @var string
     */
    const DISPLAY_TOUCH = 'touch';

    /**
     * Вид отображения окна авторизации для Wap подключения
     *
     * @var string
     */
    const DISPLAY_WAP   = 'wap';

    /**
     * Индификатор приложения
     *
     * @var string
     */
    protected $app_id = null;

    /**
     * Секретный код приложения
     *
     * @var string
     */
    protected $app_secret = null;

    /**
     * Token доступа
     *
     * @var string
     */
    protected $access_token = null;

    /**
     * Время устаревания токена
     *
     * @var integer
     */
    protected $access_token_expires = null;

    /**
     * Конструктор
     *
     * @param string|null $app_id     Индификатор приложения
     * @param string|null $app_secret Секретный код приложения
     */
    public function __construct($app_id, $app_secret, &$access_token = null, &$access_token_expires = null)
    {
        if (!extension_loaded('curl')) {
            throw new Exception('Нет расширения curl');
        }
        $this->app_id     = $app_id;
        $this->app_secret = $app_secret;
        $this->access_token = &$access_token;
        $this->access_token_expires = &$access_token_expires;
    }

    /**
     * Получение ссылки на автаризацию
     *
     * @param string $redirect_uri Адрес редиректа после авторизации
     * @param string $display      Внешний вид диалога
     * @return string
     */
    public function getAuthenticationUrl($redirect_uri, $display = self::DISPLAY_PAGE)
    {
        $parameters = array(
            'response_type' => 'code',
            'client_id'     => $this->app_id,
            'redirect_uri'  => $redirect_uri,
            'display'       => $display,
        );
        return Pro_Api_Client::API_HOST.Pro_Api_Client::POINT_AUTHORIZATION.
            '?'.http_build_query($parameters, null, '&');
    }

    /**
     * Получение токена доступа
     *
     * @param string $code         Код авторизации
     * @param string $redirect_uri Адрес редиректа после авторизации
     *
     * @return array
     */
    public function getAccessTokenFromCode($code, $redirect_uri)
    {
        $result = $this->executeRequest(
            Pro_Api_Client::API_HOST.Pro_Api_Client::POINT_GET_TOKEN,
            array(
                'code'          => $code,
                'redirect_uri'  => $redirect_uri,
                'client_id'     => $this->app_id,
                'client_secret' => $this->app_secret,
            ),
            self::HTTP_POST
        )->getJsonDecode();

        if (isset($result[self::NAME_ACCESS_TOKEN])) {
            $this->setAccessToken($result[self::NAME_ACCESS_TOKEN]);
            $this->access_token_expires = time()+$result[self::NAME_EXPIRES_IN];
        }
        return $result;
    }

    /**
     * Проверить устарел ли токен доступа
     *
     * @return boolean
     */
    public function isExpiresAccessToken()
    {
        return $this->access_token_expires - time() < 0;
    }

    /**
     * Получение текущего токена доступа
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * Время устаревания токена
     *
     * @return string
     */
    public function getExpires()
    {
        return $this->access_token_expires;
    }

    /**
     * Установить токен доступа
     *
     * @param string $token Токен доступа
     */
    public function setAccessToken($token)
    {
        $this->access_token = $token;
    }

    /**
     * Выполнить запрос
     *
     * @param string  $ressource_url Адрес API метода
     * @param array   $parameters    Параметры запроса
     * @param string  $method        HTTP метод запроса
     * @param boolean $subscribe     Подписать запорс
     *
     * @return Pro_Api_Dialogue
     */
    public function fetch($resource_url, array $parameters = array(), $method = self::HTTP_GET, $subscribe = false)
    {
        // добавление токена в параметры запроса
        if ($this->access_token) {
            if ($method == self::HTTP_GET) {
                $parameters[self::NAME_ACCESS_TOKEN] = $this->access_token;
            } elseif (strpos($resource_url, self::NAME_ACCESS_TOKEN) === false){
                $resource_url .= (strpos($resource_url, '?')!==false ? '&' : '?').
                    self::NAME_ACCESS_TOKEN.'='.$this->access_token;
            }
        }
        // Проверяем чтоб есть ключ не устарел, если устарел обновляем его
        if($this->getAccessToken() && $this->isExpiresAccessToken()) {
            $this->refreshAccessToken();
        }
        // подписываем запрос при необходимости
        if ($subscribe) {
            $parameters = array_merge(
                $parameters,
                array(self::NAME_SIGNATURE => $this->getSignature($resource_url, $parameters))
            );
        }
        return $this->executeRequest($resource_url, $parameters, $method);
    }

    /**
     * Выполнить запрос
     *
     * @param string $url        Адрес API метода
     * @param mixed  $parameters Параметры запроса
     * @param string $method     HTTP метод запроса
     *
     * @return Pro_Api_Dialogue
     */
    private function executeRequest($url, array $parameters = array(), $method = self::HTTP_GET)
    {
        $curl_options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_FOLLOWLOCATION => true,
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_HEADER         => true,
        );

        switch($method) {
            case self::HTTP_POST:
                $curl_options[CURLOPT_POST] = true;
                $curl_options[CURLOPT_POSTFIELDS] = http_build_query($parameters);
                $post = $parameters;
                break;
            case self::HTTP_GET:
                $url .= (strpos($url, '?')!==false ? '&' : '?') . http_build_query($parameters);
                $post = array();
                break;
        }
        $curl_options[CURLOPT_URL] = $url;

        $ch = curl_init();
        curl_setopt_array($ch, $curl_options);
        $dialogue = new Pro_Api_Dialogue(curl_exec($ch), $ch, $url, $post);
        curl_close($ch);

        $json_decode = $dialogue->getJsonDecode();

        if ($dialogue->getHttpCode() != 200) {
            $code = $dialogue->getHttpCode();
            $desc = 'Неизвестная ошибка';
            if (isset($json_decode['error'], $json_decode['description'])) {
                $code = $json_decode['error'];
                $desc = $json_decode['description'];
                // токен устарел
                if ($code == 'invalid_token') {
                    $this->refreshAccessToken();
                    switch($method) {
                        case self::HTTP_POST: {
                            $url = preg_replace('/('.self::NAME_ACCESS_TOKEN.'=)[a-z\d]{32}/', '$1'.$token, $url);
                            break;
                        }
                        case self::HTTP_GET: {
                            $parameters[self::NAME_ACCESS_TOKEN] = $token;
                            break;
                        }
                    }
                    return $this->executeRequest($url, $parameters, $method);
                }
                // токен не найден
                if ($code == 'undefined_token') {
                    $this->access_token = null;
                    $this->access_token_expires = null;
                }
            } elseif (isset($json_decode['code'], $json_decode['error'])) {
                $code = $json_decode['code'];
                $desc = $json_decode['error'];
            }
            throw new Pro_Api_Exception($code, $desc, $dialogue);
        }

        return $dialogue;
    }

    /**
     * Выход
     */
    public function logout()
    {
        $this->fetch(
            self::API_HOST.self::POINT_LOGOUT,
            array(self::NAME_ACCESS_TOKEN => $this->access_token),
            self::HTTP_GET
        );
        $this->access_token = null;
        $this->access_token_expires = null;
    }

    /**
     * Обновление токена доступа
     *
     * @return array
     */
    public function refreshAccessToken()
    {
        $result = $this->executeRequest(
                self::API_HOST.self::POINT_REFRESH_TOKEN,
                array(self::NAME_ACCESS_TOKEN => $this->access_token),
                self::HTTP_GET
        )->getJsonDecode();
        if (isset($result[self::NAME_ACCESS_TOKEN])) {
            $this->setAccessToken($result[self::NAME_ACCESS_TOKEN]);
            $this->access_token_expires = strtotime($result[self::NAME_EXPIRES_IN]);
        }
        return $result;
    }

    /**
     * Получение токена доступа
     *
     * @return array
     */
    public function getCurrentUser()
    {
        $result = $this->fetch(
            Pro_Api_Client::API_HOST.Pro_Api_Client::POINT_GET_CURRENT,
            array(self::NAME_ACCESS_TOKEN => $this->access_token),
            self::HTTP_GET
        )->getJsonDecode();
        return $result[0];
    }

    /**
     * Строит сигнатуру для ссылки с POST параметрами
     *
     * @param string $url  Ссылка
     * @param array  $post POST параметры
     *
     * @return string
     */
    private function getSignature($url, array $post = array())
    {
        $and = (strpos($url, '?') === false) ? '?' : '&';
        $parsed = parse_url($url.$and.http_build_query($post));

        // параметры запроса
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $parsed['query']);
        } else {
            $parsed['query'] = array();
        }

        $url_hash = '';
        if (!empty($parsed['query'])) {
            unset($parsed['query'][self::NAME_ACCESS_TOKEN], $parsed['query'][self::NAME_SIGNATURE]);
            ksort($parsed['query']);
            $url_hash .= implode('', array_keys($parsed['query']));
            $url_hash .= implode('', array_values($parsed['query']));
        }
        unset($parsed['query']);
        ksort($parsed);
        $url_hash .= implode('', array_values($parsed));

        // хэш url с секретным кодом приложения
        return md5(md5($url_hash).$this->app_secret);
    }
}