<?php

namespace app\components;

use Symfony\Component\Mailer\Transport\TransportInterface;
use yii\symfonymailer\Mailer as BaseMailer;

/**
 * Mailer that keeps the reason a send failed.
 *
 * yii\symfonymailer\Mailer swallows the transport exception, logs it and returns
 * false, so a controller can only tell the user "sending failed" while the real
 * reason (a rejected recipient, a bad login, "566 SMTP limit exceeded") sits in
 * the log. The transport is wrapped so that reason can be shown to the person
 * who pressed Send.
 */
class Mailer extends BaseMailer
{
    /**
     * @var string|null why the last send() failed, or null if it succeeded
     */
    public $lastError;

    /**
     * {@inheritdoc}
     */
    public function getTransport(): TransportInterface
    {
        $transport = parent::getTransport();

        if (!$transport instanceof FailureRecordingTransport) {
            $transport = new FailureRecordingTransport($transport);
            // Also clears the cached Symfony mailer, so the wrapper is used
            $this->setTransport($transport);
        }

        return $transport;
    }

    /**
     * {@inheritdoc}
     */
    protected function sendMessage($message): bool
    {
        $this->lastError = null;

        $transport = $this->getTransport();
        if ($transport instanceof FailureRecordingTransport) {
            $transport->lastError = null;
        }

        $isSuccessful = parent::sendMessage($message);

        if (!$isSuccessful && $transport instanceof FailureRecordingTransport) {
            $this->lastError = $transport->lastError;
        }

        return $isSuccessful;
    }
}
