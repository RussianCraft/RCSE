<?php

Database {
    public __construct(Logger $logger, Configurator $configurator)
    private databaseInit()
    public databaseGetData(string $table, string $type[, string $marker = ""]) : array
    public databaseSendData(string $table, string $type, array $contents[, string $marker = ""]) : bool
    public databaseCheckData(string $table, string $type[, string $marker = ""]) : bool
    public  databaseDeleteData(string $table, string $marker) : bool
    private databasePrepareAndExecute(string $query, array $params) : PDOStatement
}