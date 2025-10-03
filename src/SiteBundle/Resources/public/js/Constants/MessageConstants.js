const MESSAGE = {
    ERROR: {
        // USER_EXIST: 'Izgleda da ste već registrovani.',
        INACTIVE_USER: 'Vaš nalog nije aktivan. <a href="{0}" class="btn btn-default">Aktiviraj sada.</a>',
        REQUIRED_FIELD: 'Obavezno polje',
        EMAIL_NOT_VALID: 'Email nije validan',
        EMAIL_EXISTS: 'Email adresa je već registrovana.',
        ADS_EXISTS: 'Ime oglasa je zauzeto.',
        MIN_LENGTH: 'Morate uneti minimum {0} karaktera',
        PASSWORD_EQUALS: 'Lozinke se ne poklapaju',
        LOGIN_ERROR: 'Došlo je do greške prilikom prijavljivanja.',
        IS_LOWER_THEN: 'Polje "{0}" mora biti veće od "{1}" polja.',
        IS_HIGHER_THEN: 'Polje "{0}" mora biti manje od "{1}" polja.',
        DATE_NOT_VALID: 'Datum nije validan.',
        ONLY_NUMBER_ALLOWED: "Dozvoljeni su samo brojevi",
        ONLY_ALPHANUMERIC_ALLOWED: "Dozvoljeni su brojevi, slova i navodnici.",
        APPLICATION_ERROR: "Došlo je do greške prilikom slanja zahteva.",
        ID_REQUIRED: 'Molimo vas unesite Id',
        YOUTUBE_EXIST: 'Video je već unešen.',
        YOUTUBE_URL_NOT_VALID: 'Youtube url nije validan',
        BAD_CREDENTIALS: 'Uneti podaci nisu tačni.',
        WEB_NOT_ALLOWED: 'Web sajt adresa nije dozvoljena',
        EMAIL_NOT_ALLOWED: 'Email adresa nije dozvoljena',
        PHONE_NOT_ALLOWED: 'Telefon nije dozvoljen',
        FACEBOOK_EMAIL_MISSING: 'Email adresa nije pronađena u Vašem facebook nalogu. Molimo vas popunite obavezna polja u formi.'
    },
    WARNING:{
        USER_ACTIVATED: ' Vaš nalog je već aktivan.',
        ACCEPT_POLICY: 'Niste prihvatili uslove sajta.'
    },
    SUCCESS:{
        SIGNIN: 'Dobrodošli {0}',
        SIGNUP: 'Uspešno ste se registrovali. Na vašu email adresu je poslat aktivacioni email.',
        USER_ACCOUNT_ACTIVATED: 'Vaš nalog je uspešno aktiviran. Možete se prijaviti.',
        USER_RESET_PASSWORD: "Na vašu email adresu je poslat link za resetovanje lozinke",
        RESERVATION_SEND: "Vaša rezervacija je uspešno poslata.",
        USER_UPDATE: "Uspešno ste promenili podatke",
    },
    GENERIC:{
        LOADER: 'Molimo vas sačekajte'
    },
    INFO: {
        FILES_UPLOADING: 'Vaše slike se učitavaju. Molimo vas sačekajte',
    },
};

export {MESSAGE};