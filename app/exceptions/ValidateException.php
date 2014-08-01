<?php

/**
 * Exception for when a validation fails.
 *
 * @author Björn Tantau <bjoern.tantau@limora.com>
 */
class ValidateException extends Exception
{

    /**
     * Errors object.
     *
     * @var Laravel\Messages
     */
    protected $errors;

    /**
     * Create a new validate exception instance.
     *
     * @param  Laravel\Validator|Laravel\Messages  $container
     * @return void
     */
    public function __construct($container)
    {
        $this->errors = ($container instanceof Validator) ? $container->errors : $container;

        parent::__construct(null);
    }

    /**
     * Gets the errors object.
     *
     * @return Laravel\Messages
     */
    public function get()
    {
        return $this->errors;
    }

}
