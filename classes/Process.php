<?php

namespace Shasoft\Console;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process as SymfonyProcess;
use Symfony\Component\Process\Exception\ProcessFailedException;

if (!defined('STDIN')) {
    //define('STDIN', fopen('php://stdin', 'r'));
    define('STDIN', array("pipe", "r"));
}
if (!defined('STDOUT')) {
    //define('STDOUT', fopen('php://output', 'w'));
    define('STDOUT', array("pipe", "w"));
}
if (!defined('STDERR')) {
    //define('STDERR', fopen('php://stderr', 'w'));
    define('STDERR', array("pipe", "w"));
}


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
    public static function exec(
        string $cmd,
        ?string $path = null,
        bool $sync = true,
        bool $outHead = true,
        bool $outBody = true
    ): int {
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
        //
        /*
        $process = SymfonyProcess::fromShellCommandline($cmd, $path);
        try {
            $process->mustRun();

            echo $process->getOutput();
        } catch (ProcessFailedException $exception) {
            echo $exception->getMessage();
        }
        //*/
        //*
        // Выводить содержимое?
        if ($outBody) {
            $hStdout = \STDOUT;
        } else {
            $hStdout = array("pipe", "w");
        }
        //s_dump(\STDIN, \STDOUT, \STDERR);
        //s_dump($cmd);
        // Выполнить команду
        $handle = proc_open($cmd, array(0 => \STDIN, 1 => $hStdout, 2 => \STDERR), $pipes, $cwd);
        if ($handle == false) {
            Console::writeLn("<error>Ошибка выполнения команды</>");
            exit(1);
        }
        // Ждем завершения
        if ($sync) {
            while (true) {
                // Читать статус
                $meta_info = proc_get_status($handle);
                // Процесс запущен?
                if (array_key_exists('running', $meta_info) && !$meta_info['running']) {
                    //s_dump($meta_info);
                    // Закрыть
                    $ret = proc_close($handle);
                    //s_dd($ret, $meta_info);
                    // Ожидание закончено
                    break;
                }
                // Ждать 1 секунду sleep(1);
                // Ждать 64 миллисекунды
                usleep(64);
            }
            if ($outHead) Console::writeLn('--<title>cmd</> ' . $cmd);
            if ($outHead) Console::writeLn('<<' . $cmd_exec);
        } else {
            // Запустили асинхронно
            $meta_info = proc_get_status($handle);
            s_dd($meta_info);
            self::$asyncProc[$meta_info['pid']] = [
                'handle' => $handle,
                'cmd' => $cmd
            ];
        }
        //*/
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
