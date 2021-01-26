<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Api V1 Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for display API errors
    |
    */

    'expired' => [
        'title' => 'Resource expired',
        'detail' => 'This resource is no longer available.'
    ],
    'not_found'  => [
        'title' => 'Resource not found',
        'detail' => 'This resource is not found in database.'
    ],
    'bad_request'  => [
        'title' => 'Bad request',
        'detail' => 'Server cannot or will not process the request due to something that is perceived to be a client error.'
    ],

    'unprocessable_entity'  => [
        'title' => 'Unprocessable entity',
        'detail' => 'Server understands the content type of the request entity, and the syntax of the request entity is correct, but it was unable to process the contained instructions.'
    ],
    'forbidden' => [
        'title' => 'Forbidden',
        'detail' => 'Access denied for the resource.'
    ]

];
