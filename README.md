# Laravel Мультисесії

## Установка

###### Установка пакета
```
composer require arendach/multisessions
```
###### Опублікувати файли пакета
```
php artisan vendor:publish --tag=multisessions
```

###### Middleware для ініціалізації сесії
```php
// app/Http/Kernel.php -> middlewareGroups['web']
...
\Arendach\MultiSessions\Middleware\MultiSessionsStart::class,
...
```

###### Middleware для перезагрузки сесій якщо змінена IP адреса користувача
> Використовується в звязці з пакетами `arendach/vodafone-msisdn` та/або `arendach/vodafone-name`
> 
> Для роботи необхідно передати в заголовках `X-USER-IP-ADDRESS` і налаштувати cors, доодати заголовок в масив `exposed_headers`
```php
// app/Http/Kernel.php -> middlewareGroups['web']
...
\Arendach\MultiSessions\Middleware\RebootPersonificationSession::class,
...
```

###### Service Provider
```php
// Додати в app.providers
...
\Arendach\MultiSessions\Providers\MultiSessionsServiceProvider::class
...
```

## Принцип роботи

> В основі роботи мультисесій лежить кешування *laravel*. Для кожної сесії є можливість виставити власне сховище(Redis, file, memcached, database)
> 
> Для кожної окремої сесії задається час життя(після останньої дії користувача). Браузеру відправляються куки з унікальним ідентифікатором сесії, а в кеш записуються дані для цього ідентифікатора які живуть одинаковий час.
> 
> Після кожного запиту на сервер час життя для кожної сесії оновлюється, від даного моменту + час життя сесії.
> 
> Переданий ідентифікатор в куках шифрується стандартними методами шифрування cookies Laravel.


## Конфігурація

###### Конфігурація для кожної сесії находиться в файлі `config/multisessions.php`

```php
return [
    'personification' => [
        'driver'   => 'database',
        'lifetime' => '20',//  minutes
    ],
];
```

> `personification` - ідентифікатор(назва сесії)
> 
> `driver` - сховище для кешу
> 
> `lifetime` - час життя сесії в хвилинах

## Як використовувати

> При підключенні сервіс провайдера реєструється singleton для кожної сесії.
> 
> Для того щоб отримати екземпляр сесії необхідно визвати статичний метод `instance`

```php
$key = 'personification'; // клюю масива з файла конфігурації

$sesion = \Arendach\MultiSessions\Session::instance($key);
```

> В класі Session доступні настуні публічні методи

```php
// set(string $key, mixed $value): self
// метод записує в сховище дані по ключу
$session->set('slug-key', 'hello world'); 

// has(string $key): bool
// метод перевіряє наявність даних по ключу в сесії, вертає true навіть якщо значення null
$session->has('slug-key'); // true

// get(string $key): mixed
// метод повертає дані з сесії по ключу або null якщо немає нічого
$session->get('slug-key'); // hello world
```