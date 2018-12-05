<?php

namespace Bergado\Queue;

class Queue
{
    private $spool;
    private $smtp;

    public function __construct(\Swift_SpoolTransport $spool, \Swift_SmtpTransport $smtp)
    {
        $this->spool = $spool;
        $this->smtp = $smtp;
    }

    public function consume()
    {
        $spool = $this->spool->getSpool;
        $spool->setTimeLimit(3);
        $spool->flushQueue($this->smtp);
    }
}
