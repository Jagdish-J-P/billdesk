<?php

namespace JagdishJP\Billdesk\Constant;

class Response
{
    public const STATUS = [
        '0300' => 'Success',
        '0399' => 'Invalid Authentication At Bank',
        'NA'   => 'Invalid Input in the Request Message',
        '0002' => 'BillDesk is waiting for Response from Bank',
        '0001' => 'Error at BillDesk',
    ];
}
