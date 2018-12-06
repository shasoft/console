# console
Вывод в консоль цветного текста

Данный пакет предназначен для вывод в консоль цветного текста
![Пример вывод](images/show_colors.png)

Также поддерживаются отступы (даже для многострочных текстов)


## Установка
``composer require shasoft/console``

## Использование

```php
use Shasoft\Console\Console;
// Вывод цветного текста в строке
Console::color('red')->bgcolor('green')->write('Красный текст на зеленом фоне')->enter();
// Вывод цветного текста в строке
Console::color('green')->bgcolor('red')->writeln('Зеленый текст на красном фоне');
// Вывод цветного текста в строке
Console::color('red')->bgcolor('white')->write('Красный текст на белом фоне фоне')->reset()->writeln('Вывод текста цветом по умолчанию');
```

## Синтаксический сахар

```php
use Shasoft\Console\Console;
// Вывод цветного текста в строке
Console::red()->green()->writeln('Красный текст на зеленом фоне');
```

## Подробнее

Более детально смотрите на [сайте](http://shasoft.com/article/klass_php_dlya_vyvoda_v_konsol_tsvetnogo_teksta)