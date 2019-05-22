<?php

File {
    public __construct([string $file_dir = NULL, [string $file_name = NULL]])
    public __deconstruct()
    public fileOpen(string $mode, [string $file_dir = NULL, [string $file_name = NULL])
    private fileCreateDir()
    private fileSetPermissions()
    private fileClose()
    public fileRead(string $file_dir, string $file_name) : string
    public fileWrite(string $file_dir, string $file_name, string $contents)
    public fileWriteLine(string $contents)
}