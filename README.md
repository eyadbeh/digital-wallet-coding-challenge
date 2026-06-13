# Digital Wallet

A Laravel app that handles receiving and sending money for a digital wallet.

## What it does

**Receiving money:** Banks (PayTech and Acme) send us webhooks whenever a client receives money. We accept the raw payload immediately, log it, and queue it for background processing. Each bank sends data in a different format, so we have a dedicated parser for each one. Duplicate transactions are handled automatically — if the same reference comes in twice, we just skip it.

**Sending money:** When we need to send money, we generate an XML document that the bank understands. The generator handles conditional fields.

## How to run

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

To process queued webhooks, run the queue worker in a separate terminal:

```bash
php artisan queue:work
```

## Testing APIs with Postman

### 1. Receiving Money (Webhooks)

Send a **POST** request with a raw text body:

- **PayTech:** `http://127.0.0.1:8000/api/webhooks/paytech`

    ```text
    20250615156,50#202506159000001#note/debt payment march/internal_reference/A462JE81
    ```

- **Acme:** `http://127.0.0.1:8000/api/webhooks/acme`
    ```text
    156,50//202506159000001//20250615
    ```

### 2. Sending Money (XML Generation)

Send a **POST** request with a raw JSON body to test the XML generator:

- **URL:** `http://127.0.0.1:8000/api/payments/xml`
- **Body:**
    ```json
    {
        "reference": "e0f4763d-28ea-42d4-ac1c-c4013c242105",
        "date": "2025-02-25 06:33:00+03",
        "amount": 177.39,
        "currency": "EGP",
        "sender_account": "EG6980000204608016212908",
        "receiver_bank_code": "xxxxxxxxxx",
        "receiver_account": "EG6980000204608016211111",
        "beneficiary_name": "Eyad Osama",
        "notes": ["Ay 7aga", "Ay 7agaa"],
        "payment_type": 421,
        "charge_details": "RB"
    }
    ```

## Tech

Built with Laravel, MySQL.
