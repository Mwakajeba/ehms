<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class SmsHelper
{
    /**
     * Send SMS via Kilakona API
     */
    public static function send($phone, $message): array
    {
        $phone = preg_replace('/[^0-9+]/', '', (string) $phone);

        if (empty($phone)) {
            return [
                'success' => false,
                'error' => 'Invalid phone number provided.',
                'http_code' => 0,
                'response' => null,
            ];
        }

        if (empty(trim((string) $message))) {
            return [
                'success' => false,
                'error' => 'Message cannot be empty.',
                'http_code' => 0,
                'response' => null,
            ];
        }

        $senderId = trim((string) config('services.sms.senderid'));
        $apiKey = trim((string) config('services.sms.api_key'));
        $apiSecret = trim((string) config('services.sms.api_secret'));
        $url = trim((string) config('services.sms.url'));
        $callbackUrl = config('services.sms.callback_url');

        if (empty($senderId) || empty($apiKey) || empty($apiSecret) || empty($url)) {
            $error = 'Kilakona SMS is not properly configured. Set SMS_SENDERID, SMS_API_KEY, SMS_API_SECRET, and SMS_URL in .env.';
            Log::error('SMS sending failed (Kilakona) - Missing config', [
                'senderid' => $senderId ?: 'missing',
                'api_key' => $apiKey ? 'set' : 'missing',
                'api_secret' => $apiSecret ? 'set' : 'missing',
                'url' => $url ?: 'missing',
            ]);

            return [
                'success' => false,
                'error' => $error,
                'http_code' => 0,
                'response' => null,
            ];
        }

        $data = [
            'senderId' => $senderId,
            'messageType' => 'text',
            'message' => $message,
            'contacts' => $phone,
        ];

        if ($callbackUrl) {
            $data['deliveryReportUrl'] = $callbackUrl;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'api_key: ' . $apiKey,
            'api_secret: ' . $apiSecret,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);

            Log::error('SMS sending failed (Kilakona) - cURL Error', [
                'error' => $error,
                'phone' => $phone,
            ]);

            return [
                'success' => false,
                'error' => $error,
                'http_code' => 0,
                'response' => null,
            ];
        }

        curl_close($ch);

        $responseData = is_array(json_decode($response, true)) ? json_decode($response, true) : [];
        $apiSuccess = ($responseData['success'] ?? null) !== false
            && $httpCode >= 200
            && $httpCode < 300;
        $apiMessage = $responseData['message'] ?? null;

        if ($apiSuccess) {
            Log::info('SMS sent successfully (Kilakona)', [
                'phone' => $phone,
                'sender_id' => $senderId,
                'http_code' => $httpCode,
                'response' => $responseData,
            ]);
        } else {
            Log::error('SMS sending failed (Kilakona) - API Error', [
                'phone' => $phone,
                'sender_id' => $senderId,
                'http_code' => $httpCode,
                'raw_response' => $response,
                'response' => $responseData,
            ]);
        }

        $error = null;
        if (!$apiSuccess) {
            $error = $apiMessage ?? 'API request failed';
            if (is_string($error) && stripos($error, 'sender id') !== false) {
                $error .= " (configured sender: {$senderId}). Use a Sender ID approved in your Kilakona dashboard for API key \"{$apiKey}\".";
            }
        }

        return [
            'success' => $apiSuccess,
            'http_code' => $httpCode,
            'response' => $responseData,
            'raw_response' => $response,
            'error' => $error,
        ];
    }

    public static function isConfigured(): bool
    {
        return !empty(trim((string) config('services.sms.senderid')))
            && !empty(trim((string) config('services.sms.api_key')))
            && !empty(trim((string) config('services.sms.api_secret')))
            && !empty(trim((string) config('services.sms.url')));
    }

    public static function test($testPhone): array
    {
        if (!self::isConfigured()) {
            return [
                'success' => false,
                'message' => 'SMS is not properly configured.',
                'error' => 'SMS configuration missing',
            ];
        }

        $testMessage = 'Test SMS from EHMS. If you receive this, your Kilakona SMS configuration is working correctly.';

        return self::send($testPhone, $testMessage);
    }
}
