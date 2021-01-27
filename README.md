[![Build Status](https://travis-ci.com/cheevauva/PDIC.svg?branch=pdic)](https://travis-ci.com/cheevauva/PDIC)

# PDIC

Меленький и в тоже время мощный контейнер внедрения зависимостей через публичные свойства, с использованием карты зависимостей


## Установка

Перед тем как использовать PDIC в вашем проекте, добавьте его в файл composer.json:

```bash
./composer.phar require cheevauva/pdic "1.1.*"
```


## Использование

### Объявление сервисов
Сервис - это объект, который что-то делает как часть более крупной системы. Примеры сервисов: подключение к базе данных, шаблонизатор или почтовая программа

```php
$map = [
    '?session_storage' => SessionStorage::class,
    '?session' => Session::class,
    SessionStorage::class => [
        '^1' => '@sessionId',
    ],
    Session::class => [
        '^1' => '?session_storage'
    ],
];
$container = new \PDIC\Container($map, [
    'sessionId' => 'SESSION_ID',
]);
```

Поскольку объекты создаются только тогда, когда вы их получаете, порядок определений не имеет значения. 

Использовать определенные сервисы очень просто: 

```php
$container->get('session');
```
Вышеупомянутый вызов примерно эквивалентен следующему коду: 

```php
$storage = new SessionStorage('SESSION_ID');
$session = new Session($storage);
```

### Объявление фабричных сервисов

По умолчанию каждый раз, когда вы получаете сервис, PDIC возвращает один и тот же ее экземпляр. Если вы хотите, чтобы для всех вызовов возвращался другой экземпляр, объявите карту зависимостей следующим образом

```php
$map = [
    '?session_storage' => SessionStorage::class,
    '?session' => '*' . Session::class,
    SessionStorage::class => [
        '^1' => '@sessionId',
    ],
    Session::class => [
        '^1' => '?session_storage'
    ],
];
```
Теперь каждый вызов:

```php
$container->get('session');
```
возвращает новый экземпляр Session

### Объявление параметров

Определение параметра позволяет упростить настройку сущностей в вашем контейнере. Параметры передаются при создании экземпляра контейнера:

```php
$container = new \PDIC\Container([
    '?filesystem' => Filesystem::class,
    Filesystem::class => [
        '^1' => '@basePath',
    ]
], [
    'basePath' => __DIR__ . '/',
]);
```

