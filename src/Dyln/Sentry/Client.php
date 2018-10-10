<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 30/09/18
 * Time: 20:35
 */

namespace Dyln\Sentry;


class Client extends \Raven_Client
{
    public function __construct($options_or_dsn = null, array $options = [])
    {
        parent::__construct($options_or_dsn, $options);
        $this->reprSerializer = new ReprSerializer($this->mb_detect_order, $this->message_limit);

    }
}