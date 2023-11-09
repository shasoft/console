<?php

namespace Shasoft\Console;

use Symfony\Component\Process\PhpExecutableFinder;

class Process
{
    /**
     * Получить путь до запускаемого php
     */
    public static function phpPath(): string
    {
        $phpFinder = new PhpExecutableFinder();
        return $phpFinder->find();
    }
    /**
     * Запустить выполнение команды
     * @param string $cmd - команда для выполнения
     * @param string $path - директория для выполнения команды
     */
    protected static $asyncProc = [];
    public static function exec(string $cmd, ?string $path = null, bool $synh = true, bool $outHead = true, bool $outBody = true): int
    {
        $ret = 0;
        $cmd_exec = str_repeat('=', 90) . ' <fg=red>cmd_exec</> ';
        $cwd      = null;
        if ($outHead) Console::writeLn('>>' . $cmd_exec);
        if ($outHead) Console::writeLn('>><title>cmd</> ' . $cmd);
        if (!is_null($path)) {
            if ($outHead) Console::writeLn('path: ' . $path);
            $cwd = realpath($path);
            if ($outHead) Console::writeLn('cwd: <file>' . $cwd . '</>');
        }
        // Выводить содержимое?
        if ($outBody) {
            $hStdout = \STDOUT;
        } else {
            $hStdout = array("pipe", "w");
        }
        // Выполнить команду
        $handle = proc_open($cmd, array(0 => \STDIN, 1 => $hStdout, 2 => \STDERR), $pipes, $cwd);
        if ($handle == false) {
            Console::writeLn("<error>Ошибка выполнения команды</>");
            exit(1);
        }
        // Ждем завершения
        if ($synh) {
            while (true) {
                // Читать статус
                $meta_info = proc_get_status($handle);
                // Процесс запущен?
                if (!$meta_info['running']) {
                    // Закрыть
                    $ret = proc_close($handle);
                    // Ожидание закончено
                    break;
                }
                // Ждать 1 секунду
                sleep(1);
            }
            if ($outHead) Console::writeLn('--<title>cmd</> ' . $cmd);
            if ($outHead) Console::writeLn('<<' . $cmd_exec);
        } else {
            // Запустили асинхронно
            $meta_info = proc_get_status($handle);
            self::$asyncProc[$meta_info['pid']] = [
                'handle' => $handle,
                'cmd' => $cmd
            ];
        }
        if ($outHead) Console::writeLn('ret: ' . var_export($ret, true));
        return $ret;
    }
    // Ждать завершения асинхронных процессов
    public static function waitAsync(): void
    {
        while (true) {
            foreach (self::$asyncProc as $pid => $item) {
                // Читать статус
                $meta_info = proc_get_status($item['handle']);
                // Если процесс остановлен
                if (!$meta_info['running']) {
                    // Закрыть
                    $rc = proc_close($item['handle']);
                    // Удалить процесс из списка
                    unset(self::$asyncProc[$pid]);
                    //
                    Console::writeLn('<<<title>cmd</> ' . $item['cmd']);
                    // Один процесс завершился, дальше можно СПИСОК процессов не проверять
                    // потому что их может УЖЕ и не быть
                    break;
                }
            }
            // Есть процессы в списке?
            if (empty(self::$asyncProc)) {
                // Все задачи завершены
                break;
            }
            // Ждать 1 секунду
            sleep(1);
        }
    }
}
