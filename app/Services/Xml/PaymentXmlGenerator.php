<?php

namespace App\Services\Xml;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;

class PaymentXmlGenerator
{
    public function generate(array $data): string
    {
        $this->validateRequiredFields($data);

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;

        // Root element
        $root = $dom->createElement('PaymentRequestMessage');
        $dom->appendChild($root);

        // TransferInfo
        $transferInfo = $dom->createElement('TransferInfo');
        $this->addElement($dom, $transferInfo, 'Reference', $data['reference']);
        $this->addElement($dom, $transferInfo, 'Date', $data['date']);
        $this->addElement($dom, $transferInfo, 'Amount', (string) $data['amount']);
        $this->addElement($dom, $transferInfo, 'Currency', $data['currency']);
        $root->appendChild($transferInfo);

        // SenderInfo
        $senderInfo = $dom->createElement('SenderInfo');
        $this->addElement($dom, $senderInfo, 'AccountNumber', $data['sender_account']);
        $root->appendChild($senderInfo);

        // ReceiverInfo
        $receiverInfo = $dom->createElement('ReceiverInfo');
        $this->addElement($dom, $receiverInfo, 'BankCode', $data['receiver_bank_code']);
        $this->addElement($dom, $receiverInfo, 'AccountNumber', $data['receiver_account']);
        $this->addElement($dom, $receiverInfo, 'BeneficiaryName', $data['beneficiary_name']);
        $root->appendChild($receiverInfo);

        // Notes — only if there are notes
        $notes = $data['notes'] ?? [];
        if (!empty($notes)) {
            $notesElement = $dom->createElement('Notes');
            foreach ($notes as $note) {
                $this->addElement($dom, $notesElement, 'Note', $note);
            }
            $root->appendChild($notesElement);
        }

        // PaymentType — only if NOT 99
        $paymentType = $data['payment_type'] ?? 99;
        if ((int) $paymentType !== 99) {
            $this->addElement($dom, $root, 'PaymentType', (string) $paymentType);
        }

        // ChargeDetails — only if NOT 'SHA'
        $chargeDetails = $data['charge_details'] ?? 'SHA';
        if ($chargeDetails !== 'SHA') {
            $this->addElement($dom, $root, 'ChargeDetails', $chargeDetails);
        }

        return $dom->saveXML();
    }

    private function addElement(DOMDocument $dom, DOMElement $parent, string $name, string $value): void
    {
        $element = $dom->createElement($name);
        $element->appendChild($dom->createTextNode($value));
        $parent->appendChild($element);
    }

    private function validateRequiredFields(array $data): void
    {
        $required = [
            'reference',
            'date',
            'amount',
            'currency',
            'sender_account',
            'receiver_bank_code',
            'receiver_account',
            'beneficiary_name',
        ];

        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new InvalidArgumentException("Missing required field: {$field}");
            }
        }
    }
}