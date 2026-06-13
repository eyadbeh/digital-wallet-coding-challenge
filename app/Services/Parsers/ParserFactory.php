<?php
namespace App\Services\Parsers;
use InvalidArgumentException;
class ParserFactory
{
    public static function create(string $bank): BankParserInterface
    {
        return match ($bank) {
            'paytech' => new PayTechParser(),
            'acme' => new AcmeParser(),
            default => throw new InvalidArgumentException("Unsupported bank: {$bank}"),
        };
    }
}