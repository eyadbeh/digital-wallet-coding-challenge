<?php

namespace App\Services\Parsers;

class PayTechParser implements BankParserInterface
{
    public function parse(string $payload): array
    {
        $transactions = [];
        $lines = explode("\n", $payload);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $transactions[] = $this->parseLine($line);
        }
        return $transactions;
    }

    private function parseLine(string $line): array
    {
        $parts = explode("#", $line);
        if (count($parts) < 3) {
            return [];
        }
        $dateAndAmount = $parts[0];
        $reference = $parts[1];
        $keyValueString = $parts[2] ?? '';

        $date = substr($dateAndAmount, 0, 8);
        $amountStr = substr($dateAndAmount, 8);

        $amount = (float) str_replace(',', '.', $amountStr);

        $keyValues = $this->parseKeyValues($keyValueString);

        $formattedDate = substr($date, 0, 4)
            . '-'
            . substr($date, 4, 2)
            . '-'
            . substr($date, 6, 2);

        return [
            'reference' => $reference,
            'amount' => $amount,
            'transaction_date' => $formattedDate,
            'note' => $keyValues['note'] ?? null,
            'internal_reference' => $keyValues['internal_reference'] ?? null,
            'raw_line' => $line,
        ];
    }

    private function parseKeyValues(string $keyValueString): array
    {
        $results = [];

        if (empty($keyValueString)) {
            return $results;
        }

        $knownKeys = ['note', 'internal_reference'];

        $positions = [];

        foreach ($knownKeys as $key) {
            $keyWithSlash = $key . '/';

            $pos = strpos($keyValueString, $keyWithSlash);

            if ($pos !== false) {
                $positions[$key] = $pos;
            }
        }

        if (empty($positions)) {
            return $results;
        }

        asort($positions);

        $keys = array_keys($positions);

        for ($i = 0; $i < count($keys); $i++) {
            $key = $keys[$i];

            $valueStart = $positions[$key] + strlen($key) + 1;

            if ($i + 1 < count($keys)) {
                $nextKeyPos = $positions[$keys[$i + 1]];
                $value = substr($keyValueString, $valueStart, $nextKeyPos - $valueStart);
            } else {
                $value = substr($keyValueString, $valueStart);
            }

            $results[$key] = rtrim($value, '/');
        }

        return $results;
    }
}
