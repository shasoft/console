<?php

namespace Shasoft\Console;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class Console
{
    // Запуск в режиме консоли?
    public static function is(): bool
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }
    protected static bool $enable = true;
    public static function enableOutput(bool $enable): bool
    {
        $ret          = self::$enable;
        self::$enable = $enable;
        return $ret;
    }
    // Вывести на консоль
    protected static $output = false;
    public static function write(string $text): void
    {
        if (self::$enable) {
            if (self::is()) {
                if (self::$output === false) {
                    self::$output = new \Symfony\Component\Console\Output\ConsoleOutput();
                    // Установить стиль вывода файла
                    $outputStyle = new OutputFormatterStyle('black', 'cyan');
                    self::$output->getFormatter()->setStyle('file', $outputStyle);
                    // Установить стиль вывода класса
                    $outputStyle = new OutputFormatterStyle('black', 'yellow');
                    self::$output->getFormatter()->setStyle('class', $outputStyle);
                    // Установить стиль вывода заголовка
                    $outputStyle = new OutputFormatterStyle('magenta', null, ['bold']);
                    self::$output->getFormatter()->setStyle('title', $outputStyle);
                    // Установить стиль вывода описания
                    $outputStyle = new OutputFormatterStyle('blue', null, ['bold']);
                    self::$output->getFormatter()->setStyle('desc', $outputStyle);
                    // Установить стиль вывода текста
                    $outputStyle = new OutputFormatterStyle('white', null, ['bold']);
                    self::$output->getFormatter()->setStyle('text', $outputStyle);
                    // Ok
                    $outputStyle = new OutputFormatterStyle('green', null, ['bold']);
                    self::$output->getFormatter()->setStyle('success', $outputStyle);
                    // Предупреждение
                    $outputStyle = new OutputFormatterStyle('yellow', null, ['bold']);
                    self::$output->getFormatter()->setStyle('warning', $outputStyle);
                }
                self::$output->write($text);
            } else {
                echo '<div style="border:1px red solid">' . htmlentities($text) . "</div>";
            }
        }
    }
    public static function writeLn(string $text)
    {
        self::write($text . "\n");
    }
    /**
     * Отформатировать переменные
     */
    public static function format(...$args): string
    {
        $ret = '';
        // https://stillat.com/blog/2016/12/03/custom-command-styles-with-laravel-artisan
        foreach ($args as $arg) {
            $text = json_encode($arg, JSON_UNESCAPED_SLASHES);
            switch (gettype($arg)) {
                case 'boolean':
                case 'NULL': {
                        if ($arg === true) {
                            $color = 'green';
                        } else {
                            $color = 'red';
                        }
                    }
                    break;
                case 'integer':
                case 'double': {
                        $color = 'blue';
                    }
                    break;
                case 'string': {
                        $color = 'magenta';
                        $clr   = "white";
                        $text  = str_replace("\\\\", "\\", $text);
                        $text  = str_replace("\n", "<fg=" . $clr . ">\\n</>", $text);
                        $text  = str_replace("\r", "<fg=" . $clr . ">\\r</>", $text);
                        $text  = str_replace("\t", "<fg=" . $clr . ">\\t</>", $text);
                    }
                    break;
                default: {
                        $color = 'default';
                    }
                    break;
            }
        }
        //dump($value);
        //var_export($value, true)
        //dd($text);
        return '<fg=' . $color . '>' . $text . '</>';
    }
    // Копировать строку в буфер обмена
    public static function copyToClipboard(string $text): void
    {
        // Имя ОС
        $osName = php_uname();
        // Может это windows?
        if (preg_match('/windows/i', $osName)) {
            // Windows
            exec('echo ' . $text . '| clip');
        } else if (preg_match('/darwin/i', $osName)) {
            // Mac
            exec('echo ' . $text . ' | pbcopy');
        } else {
            // Linux
            exec('echo ' . $text . ' | xclip -sel clip');
        }
    }
}
