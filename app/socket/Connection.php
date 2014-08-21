<?php

use \Sidney\Latchet\BaseConnection;

class Connection extends BaseConnection
{

    public function open($connection)
    {
        /* @var $connection \Ratchet\Wamp\WampConnection */

        //in case of a mysql timeout, reconnect
        //to the database
        $app = app();
        $app['db']->reconnect();
    }

    public function close($connection)
    {
        /* @var $connection \Ratchet\Wamp\WampConnection */
    }

    public function error($connection, $exception)
    {
        //close the connection
        $connection->close();
        throw new Exception($exception);
    }

}
