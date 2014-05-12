# Api Client

Библиотека предоставляющая удобный интерфейс для доступа к API интерфейсу социальной сети [Professionali.ru](http://professionali.ru/).
Подробней об использовании API и доступных методах читайте в [документации](http://dev.professionali.ru/docs/auth/).

## Установка

Библиотека ставится через composer:

```
composer.phar require professionali/api-client:1.*
composer.phar update
```

## Использование

Пример авторизации приложения по средствам библиотеки:
```php
session_start();

// Создаем API клиента
$client = new Pro_Api_Client(
    APP_CODE, // код приложения
    APP_SECRET, // секретный ключ приложения
    $_SESSION['token'],
    $_SESSION['expires']
);

// Редирект с авторизации приложения с токеном
if (!empty($_GET['code'])) {
    $client->getAccessTokenFromCode($_GET['code']);
    // Редиректим на себя же, чтоб убрать код из GET параметра
    header('Location: http://'.$_SERVER['HTTP_HOST'], true, 301);
    exit;
}

// Авторизация приложения
if (!$client->getAccessToken()) {
    header('Location: '.$client->getAuthenticationUrl('http://'.$_SERVER['HTTP_HOST']), true, 301);
    exit;
}

// Здесь приложение уже авторизовано и можно им пользоваться
echo '<pre>';
echo 'AccessToken: '.$client->getAccessToken()."\n";
echo 'CurrentUser: '.print_r($client->getCurrentUser(), true);
echo '</pre>';
```

Пример выполнения запорсов к API:
```php
$dialogue = $client->fetch(
    Pro_Api_Client::API_HOST.'/v6/users/get.json',
    array('ids' => array('me'), 'fields' => 'id,name,link,avatar_big'),
    Pro_Api_Client::HTTP_GET
);
echo '<pre>';
echo 'CurrentUser: '.print_r($dialogue->getJsonDecode(), true);
echo '</pre>';
```
