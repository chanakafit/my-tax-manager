<?php

namespace app\components;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;

/**
 * Wraps a mail transport and remembers why the last send failed.
 *
 * The exception is re-thrown, so the mailer still logs it and reports the
 * failure the usual way - this only keeps the reason reachable afterwards.
 */
class FailureRecordingTransport implements TransportInterface
{
    /**
     * @var string|null message from the last failed send
     */
    public $lastError;

    /**
     * @var TransportInterface
     */
    private $transport;

    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * {@inheritdoc}
     */
    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        try {
            return $this->transport->send($message, $envelope);
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            throw $e;
        }
    }

    /**
     * The wrapped transport
     * @return TransportInterface
     */
    public function getInnerTransport(): TransportInterface
    {
        return $this->transport;
    }

    public function __toString(): string
    {
        return (string)$this->transport;
    }
}
