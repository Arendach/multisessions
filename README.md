# Vodafone multisessions

## Установка

###### Установка пакета
```
$ composer require arendach/multisessions
```
###### Опублікувати файли пакета
```
$ php artisan vendor:publish --tag=multisessions
```

## Принцип роботи

> В основі роботи мультисесій лежить кешування *laravel*. Для кожної сесії є можливість виставити власне сховище(Redis, file, memcached, database)
> 
> Для кожної окремої сесії задається час життя(після останньої дії користувача).