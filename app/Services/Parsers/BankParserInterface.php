<?php
namespace App\Services\Parsers;

interface BankParserInterface
{
    public function parse(string $payload): array;
}