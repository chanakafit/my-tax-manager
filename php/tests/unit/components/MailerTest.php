<?php

namespace tests\unit\components;

use app\components\Mailer;
use Codeception\Test\Unit;

/**
 * Test the mailer keeps the reason a send failed
 */
class MailerTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    private function mailer($dsn)
    {
        return new Mailer([
            'viewPath' => '@app/mail',
            'transport' => ['dsn' => $dsn],
        ]);
    }

    private function message(Mailer $mailer)
    {
        return $mailer->compose()
            ->setFrom('billing@example.com')
            ->setTo('customer@example.com')
            ->setSubject('Invoice')
            ->setTextBody('Invoice attached');
    }

    /**
     * A refused transport reports false and keeps the reason
     */
    public function testFailedSendKeepsTheReason()
    {
        $mailer = $this->mailer('smtp://127.0.0.1:1');

        verify($mailer->send($this->message($mailer)))->false();
        verify($mailer->lastError)->notEmpty();
    }

    /**
     * A successful send leaves no error behind
     */
    public function testSuccessfulSendHasNoError()
    {
        $mailer = $this->mailer('null://null');

        verify($mailer->send($this->message($mailer)))->true();
        verify($mailer->lastError)->null();
    }

    /**
     * The reason is reset between sends
     */
    public function testErrorIsResetOnNextSend()
    {
        $mailer = $this->mailer('smtp://127.0.0.1:1');
        $mailer->send($this->message($mailer));
        verify($mailer->lastError)->notEmpty();

        $working = $this->mailer('null://null');
        $working->lastError = 'stale';
        $working->send($this->message($working));
        verify($working->lastError)->null();
    }
}
