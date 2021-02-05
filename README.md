# Vodafone multisessions

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
\Arendach\MultiSessions\MultiSessionsServiceProvider::class
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