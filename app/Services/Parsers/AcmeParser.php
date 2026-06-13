<?php

namespace App\Services\Parsers;

class AcmeParser implements BankParserInterface
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

            $parts = explode("//", $line);
            if (count($parts) < 3) {
                continue;
            }

            $amountStr = $parts[0];
            $amount = (float) str_replace(',', '.', $amountStr);

            $reference = $parts[1];

            $dateStr = $parts[2];
            $date = substr($dateStr, 0, 4)
                . '-'
                . substr($dateStr, 4, 2)
                . '-'
                . substr($dateStr, 6, 2);


            $transactions[] = [
                'reference' => $reference,
                'amount' => $amount,
                'transaction_date' => $date,
                'note' => null,
                'internal_reference' => null,
                'raw_line' => $line,
            ];
        }
        return $transactions;
    }
}
