<?php

namespace JagdishJP\Billdesk\Contracts;

interface Message
{
    /**
     * handle a message.
     *
     * @param array $options
     *
     * @return mixed
     */
    public function handle(array $options);

    /**
     * Format data for checksum.
     *
     * @return string
     */
    public function format();
}
