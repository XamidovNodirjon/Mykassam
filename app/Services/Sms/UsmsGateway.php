<?php

namespace App\Services\Sms;

use App\Enums\SmsPurpose;
use App\Exceptions\SmsGatewayException;
use App\Services\Contracts\SmsGatewayInterface;
use Closure;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * usms.uz API v1 client (https://usms.uz/docs).
 */
class UsmsGateway implements SmsGatewayInterface
{
    public function __construct(
        #[Config('sms.usms.base_url')] private string $baseUrl,
        #[Config('sms.usms.login')] private string $login,
        #[Config('sms.usms.secret')] private string $secret,
        #[Config('sms.usms.from')] private string $from,
        /** @var array<string, int> Template id per SmsPurpose value. */
        #[Config('sms.usms.templates')] private array $templates,
    ) {}

    public function send(string $phone, SmsPurpose $purpose, array $params, string $idempotencyKey): ?string
    {
        $templateId = (int) ($this->templates[$purpose->value] ?? 0);

        if ($templateId === 0) {
            throw new SmsGatewayException("\"{$purpose->label()}\" uchun USMS shabloni sozlanmagan.");
        }

        $response = $this->call(fn (PendingRequest $request): Response => $request->post('send', [
            'phone' => $phone,
            'template_id' => $templateId,
            'params' => $params,
            'from' => $this->from,
            'idem_key' => $idempotencyKey,
        ]));

        $id = $response->json('id');

        return $id === null ? null : (string) $id;
    }

    public function balance(): ?int
    {
        return (int) $this->call(fn (PendingRequest $request): Response => $request->get('balance'))->json('balance');
    }

    /**
     * @param  Closure(PendingRequest): Response  $send
     *
     * @throws SmsGatewayException
     */
    private function call(Closure $send): Response
    {
        $request = Http::baseUrl($this->baseUrl)
            ->withToken("{$this->login}:{$this->secret}")
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(15);

        try {
            $response = $send($request);
        } catch (ConnectionException $exception) {
            throw new SmsGatewayException("USMS serveriga ulanib bo'lmadi.", previous: $exception);
        }

        if ($response->failed()) {
            $error = $response->json('error');

            throw new SmsGatewayException(sprintf(
                'USMS xatosi (HTTP %d): %s',
                $response->status(),
                is_string($error) ? $error : Str::limit($response->body(), 200),
            ));
        }

        return $response;
    }
}
