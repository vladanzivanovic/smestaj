<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 3/20/2017
 * Time: 5:17 PM
 */

namespace SiteBundle\Validators\ValidatorConstants;


final class UserFieldsConstants
{
    const NUMERIC_REGEX = '/^[\p{Latin}[A-ž0-9]+$/m';
    const EMAIL_REGEX = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';

    const ADD_NEW_USER = [
        'firstName' => [
            'required' => true,
            'regexp' => self::NUMERIC_REGEX,
            'name' => 'ime'
        ],
        'lastName' => [
            'required' => true,
            'regexp' => self::NUMERIC_REGEX,
            'name' => 'prezime'
        ],
        'email' => [
            'required' => true,
            'regexp' => self::EMAIL_REGEX,
            'name' => 'email'
        ],
        'password' => [
            'required' => true,
            'equal' => 'rePassword',
            'name' => 'lozinka'
        ],
        'rePassword' => [
            'required' => true,
            'name' => 'ponovljena lozinka'
        ]
    ];
}
