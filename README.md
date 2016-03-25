# Cheevauva\Container

Простой и в тоже время мощный контейнер внедрения зависимостей.

## Терминология

Карта зависимостей - массив содержащий отношения между классами, переданый в конструктор контейнера.
Компонент - класс описаные в карте зависимостей;

## Идеология

1. Внедрение зависимостей только через публичные свойства;
2. Запрет на использование конструкторов в компонентах (в связи с тем что на момент вызова конструктора не передаются зависимосте в объект);
3. Наследование зависимостей от классов и трейтов

## Возможности

1. Паттерн "Посредник" (Mediator)
2. Паттерн "Локатор Сервисов" (Service Locator)

## Запуск тестов

php composer.phar update
php vendor/bin/phpunit 
