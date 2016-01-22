<?php

namespace Soy\Exception;

use League\CLImate\CLImate;

class SoyException extends \Exception
{
    public function output(CLImate $climate)
    {
        $climate->error(get_class($this) . ': ' . $this->getMessage());
        $climate->dim($this->getTraceAsString())->br();
    }
}
