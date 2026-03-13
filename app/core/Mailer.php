<?php
namespace App\Core;

class Mailer
{
    public static function notifyHR(string $subject, string $message): bool
    {
        $cfg = Config::app()['mail'];
        return self::sendTo($cfg['to_hr'], $subject, $message);
    }

    public static function sendTo(string $to, string $subject, string $message): bool
    {
        $cfg = Config::app()['mail'];
        if (!$cfg['enabled']) {
            return false;
        }
        $headers = 'From: ' . $cfg['from'] . "\r\n" . 'Content-Type: text/plain; charset=utf-8';
        return @mail($to, $subject, $message, $headers);
    }
}
