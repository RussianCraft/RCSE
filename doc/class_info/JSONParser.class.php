<?php

JSONParser {
    public __construct(Logger $logger)
    protected compareType(string $type, array $variants) : bool
    protected jsonCheckData(array $json, string $data) : bool
    protected jsonReadAndParseData(string $file_dir, string $file_name): array
    protected jsonParseAndWriteData(string $file_dir, string $file_name, array $json): bool
    protected jsonObtainAndCheckData(string $file_dir, string $file_name, string $entry)
    protected jsonObtainDataSimpliest(string $file_dir, string $file_name)
    protected jsonObtainDataSimple(string $type, string $file_dir, string $file_name, array $types)
    protected jsonUpdateDataSimple(string $type, string $file_dir, string $file_name, array $types, array $contents) : bool
    protected jsonRemoveDataSimple(string $type, string $file_dir, string $file_name) : bool
    protected jsonObtainDataDouble(string $type1, string $type2, string $file_dir, string $file_name, array $types)
    protected jsonObtainDataAllNSmall(string $type, string $file_dir, string $file_name, array $types)
}