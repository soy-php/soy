<?php

namespace Soy\Exception;

class UnknownComponentException extends SoyException
{
    /**
     * @var string
     */
    protected $component;

    /**
     * @param string $message
     * @param string $component
     */
    public function __construct($message, $component = null)
    {
        parent::__construct($message);
        $this->component = $component;
    }

    /**
     * @return string
     */
    public function getComponent()
    {
        return $this->component;
    }
}
