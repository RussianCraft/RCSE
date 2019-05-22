<?php

Logger {
    /*Коды сообщений */
    const EMERGENCY
    const ALERT
    const CRITICAL
    const ERROR
    const WARNING
    const NOTICE
    const INFO
    const DEBUG

    /*Методы */
    public __construct([$level_threshold = self::DEBUG])
    public __destruct()
    private logSetFilePath()
    public logSetLevelThreshold($level_threshold)
    public logGetFilePath()
    private logGetTimestamp([bool $formatted = true])
    public log($level, string $message, string $source)
    private logFormatMessage(string $level, string $message, string $source)
}