<?php

namespace Flute\Modules\EventWebhook\src\Discord\Webhook;

use Flute\Modules\EventWebhook\src\Discord\Exception\DiscordInvalidResponseException;
use Flute\Modules\EventWebhook\src\Discord\Message\AbstractDiscordMessage;

class DiscordWebhook
{
    /**
     * The url prefix for Discord webhooks. In case you use want to use the identifier.
     */
    public const DISCORD_WEBHOOK_URL_PREFIX = 'https://discordapp.com/api/webhooks/';

    private $webhookUrl;

    public function __construct(string $webhookUrl)
    {
        $this->webhookUrl = $webhookUrl;
    }

    public function send(AbstractDiscordMessage $message, bool $wait = true): ?array
    {
        return $this->runCurl(
            $this->webhookUrl . '?wait=' . ($wait ? 'true' : 'false'),
            'POST',
            $message
        );
    }

    public function get(string $messageId): array
    {
        return $this->runCurl(
            $this->webhookUrl . '/messages/' . $messageId,
            'GET'
        );
    }

    public function update(string $messageId, AbstractDiscordMessage $message): array
    {
        return $this->runCurl(
            $this->webhookUrl . '/messages/' . $messageId,
            'PATCH',
            $message
        );
    }

    public function delete(string $messageId): void
    {
        $this->runCurl(
            $this->webhookUrl . '/messages/' . $messageId,
            'DELETE'
        );
    }

    private function runCurl(string $url, string $method, ?AbstractDiscordMessage $postData = null, array $headers = ["Content-Type: application/json"]): ?array
    {
        $sent = false;

        while (!$sent) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($postData !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData, JSON_THROW_ON_ERROR));
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (429 === $code) {
                $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
                usleep((int) ($response['retry_after'] * 1000));
            } else {
                $sent = true;
                if ($code < 200 || $code >= 400) {
                    dd($response);
                    $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
                    throw new DiscordInvalidResponseException('Discord Webhook returned invalid response: ' . $code . '.', $code, null, $response);
                }
                if (!empty($response)) {
                    return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
                }
            }
        }

        return null;
    }
}
