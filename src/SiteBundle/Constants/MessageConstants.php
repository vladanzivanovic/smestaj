<?php
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 3/24/2017
 * Time: 6:44 PM
 */

namespace SiteBundle\Constants;


class MessageConstants
{
    const EMAIL_NOT_VALID = 'Email nije validan';
    const EMAIL_NOT_SENT = 'Email nije poslat';

    const EMPTY_REQUEST = 'Vaš zahtev ne može biti obrađen.';
    const NOT_FOUND = 'Podaci nisu pronađeni.'; // set dynamic
    const RELATIONS_ERROR = 'Postoji veza sa oglasom'; //set dynamic

    const APPLICATION_ERROR = 'Došlo je greške prilikom slanja zahteva.';
    const EXIST = 'Oglas postoji u bazi.'; // set dynamic

    const SUCCESS_SEND_DATA = 'Podaci su uspešno poslati.';
    const SUCCESS_DELETE_DATA = 'Podaci su uspešno obrisani.';

    const DATA_ID_NOT_EXIST = 'Oglas ne postoji u bazi.'; // set dynamic
    const BADGE_ID_NOT_EXIST = 'Bedž ne postoji u bazi.';

    const UPLOAD_FAILED = 'Slanje slika je otkazano.';
    const MAIN_IMAGE_REQUIRED = 'Postavite glavnu sliku.';

    const PASSWORD_NOT_EQUAL = 'Lozinke se ne poklapaju';

    const RESERVATION_CONFIRMED = 'Rezervacija je potvrđena. Hvala Vam na korišćenju usluga sajta mojeveselje.com';
    const RESERVATION_REJECTED = 'Rezervacija je odbijena.';
    const RESERVATION_ALREADY_REJECTED = 'Rezervacija je već odbijena';
    const RESERVATION_ALREADY_CONFIRMED = 'Rezervacija je već potvrđena';

    const FACEBOOK_LOGIN_NOT_FOUND = 'Niste registrovani sa facebook nalogom. Molimo vas prvo se registrujte.';

    const USER_EXISTS = 'Izgleda da ste već registrovani.';
}