<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'accepted'             => 'Il campo :attribute deve essere accettato.',
    'active_url'           => 'Il campo :attribute non è un URL valido.',
    'after'                => 'Il campo :attribute deve essere una data successiva al :date.',
    'after_or_equal'       => 'Il campo :attribute deve essere una data successiva o uguale al :date.',
    'alpha'                => 'Il campo :attribute può contenere solo lettere.',
    'alpha_dash'           => 'Il campo :attribute può contenere solo lettere, numeri, trattini e underscore.',
    'alpha_num'            => 'Il campo :attribute può contenere solo lettere e numeri.',
    'array'                => 'Il campo :attribute deve essere un array.',
    'before'               => 'Il campo :attribute deve essere una data precedente al :date.',
    'before_or_equal'      => 'Il campo :attribute deve essere una data precedente o uguale al :date.',
    'boolean'              => 'Il campo :attribute deve essere vero o falso.',
    'confirmed'            => 'La conferma di :attribute non coincide.',
    'date'                 => 'Il campo :attribute non è una data valida.',
    'date_equals'          => 'Il campo :attribute deve essere una data uguale al :date.',
    'date_format'          => 'Il campo :attribute non corrisponde al formato :format.',
    'digits'               => 'Il campo :attribute deve contenere :digits cifre.',
    'digits_between'       => 'Il campo :attribute deve contenere tra :min e :max cifre.',
    'dimensions'           => 'Il campo :attribute ha dimensioni non valide.',
    'distinct'             => 'Il campo :attribute contiene valori duplicati.',
    'email'                => 'Il campo :attribute deve essere un indirizzo email valido.',
    'ends_with'            => 'Il campo :attribute deve terminare con uno dei seguenti valori: :values.',
    'exists'               => 'Il valore selezionato per :attribute non è valido.',
    'file'                 => 'Il campo :attribute deve essere un file.',
    'filled'               => 'Il campo :attribute deve essere compilato.',
    'gt' => [
        'numeric' => 'Il campo :attribute deve essere maggiore di :value.',
        'file'    => 'Il campo :attribute deve essere maggiore di :value kilobyte.',
        'string'  => 'Il campo :attribute deve contenere più di :value caratteri.',
        'array'   => 'Il campo :attribute deve contenere più di :value elementi.',
    ],
    'gte' => [
        'numeric' => 'Il campo :attribute deve essere maggiore o uguale a :value.',
        'file'    => 'Il campo :attribute deve essere maggiore o uguale a :value kilobyte.',
        'string'  => 'Il campo :attribute deve contenere almeno :value caratteri.',
        'array'   => 'Il campo :attribute deve contenere almeno :value elementi.',
    ],
    'image'                => 'Il campo :attribute deve essere un\'immagine.',
    'in'                   => 'Il valore selezionato per :attribute non è valido.',
    'integer'              => 'Il campo :attribute deve essere un numero intero.',
    'ip'                   => 'Il campo :attribute deve essere un indirizzo IP valido.',
    'ipv4'                 => 'Il campo :attribute deve essere un indirizzo IPv4 valido.',
    'ipv6'                 => 'Il campo :attribute deve essere un indirizzo IPv6 valido.',
    'json'                 => 'Il campo :attribute deve essere una stringa JSON valida.',
    'lt' => [
        'numeric' => 'Il campo :attribute deve essere minore di :value.',
        'file'    => 'Il campo :attribute deve essere minore di :value kilobyte.',
        'string'  => 'Il campo :attribute deve contenere meno di :value caratteri.',
        'array'   => 'Il campo :attribute deve contenere meno di :value elementi.',
    ],
    'lte' => [
        'numeric' => 'Il campo :attribute deve essere minore o uguale a :value.',
        'file'    => 'Il campo :attribute deve essere minore o uguale a :value kilobyte.',
        'string'  => 'Il campo :attribute deve contenere al massimo :value caratteri.',
        'array'   => 'Il campo :attribute deve contenere al massimo :value elementi.',
    ],
    'max' => [
        'numeric' => 'Il campo :attribute non può essere maggiore di :max.',
        'file'    => 'Il campo :attribute non può superare :max kilobyte.',
        'string'  => 'Il campo :attribute non può superare i :max caratteri.',
        'array'   => 'Il campo :attribute non può superare i :max elementi.',
    ],
    'min' => [
        'numeric' => 'Il campo :attribute deve essere almeno :min.',
        'file'    => 'Il campo :attribute deve essere almeno :min kilobyte.',
        'string'  => 'Il campo :attribute deve contenere almeno :min caratteri.',
        'array'   => 'Il campo :attribute deve contenere almeno :min elementi.',
    ],
    'not_in'               => 'Il valore selezionato per :attribute non è valido.',
    'numeric'              => 'Il campo :attribute deve essere un numero.',
    'present'              => 'Il campo :attribute deve essere presente.',
    'regex'                => 'Il formato del campo :attribute non è valido.',
    'required'             => 'Il campo :attribute è obbligatorio.',
    'required_if'          => 'Il campo :attribute è obbligatorio quando :other è :value.',
    'required_unless'      => 'Il campo :attribute è obbligatorio a meno che :other sia in :values.',
    'required_with'        => 'Il campo :attribute è obbligatorio quando è presente :values.',
    'required_with_all'    => 'Il campo :attribute è obbligatorio quando sono presenti :values.',
    'required_without'     => 'Il campo :attribute è obbligatorio quando non è presente :values.',
    'required_without_all' => 'Il campo :attribute è obbligatorio quando nessuno di :values è presente.',
    'same'                 => 'Il campo :attribute e :other devono coincidere.',
    'size' => [
        'numeric' => 'Il campo :attribute deve essere :size.',
        'file'    => 'Il campo :attribute deve essere di :size kilobyte.',
        'string'  => 'Il campo :attribute deve contenere :size caratteri.',
        'array'   => 'Il campo :attribute deve contenere :size elementi.',
    ],
    'starts_with'          => 'Il campo :attribute deve iniziare con uno dei seguenti valori: :values.',
    'string'               => 'Il campo :attribute deve essere una stringa.',
    'timezone'             => 'Il campo :attribute deve essere una zona valida.',
    'unique'               => 'Il campo :attribute è già stato utilizzato.',
    'uploaded'             => 'Il caricamento del campo :attribute non è riuscito.',
    'url'                  => 'Il campo :attribute non è un URL valido.',
    'uuid'                 => 'Il campo :attribute deve essere un UUID valido.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'email' => 'email',
        'password' => 'password',
        'user_type' => 'tipo utente',

        'private_name' => 'nome',
        'private_surname' => 'cognome',
        'private_cf' => 'codice fiscale',
        'private_address' => 'indirizzo',
        'private_cap' => 'CAP',
        'private_city_id' => 'città',
        'private_phone' => 'telefono',

        'company_society' => 'ragione sociale',
        'company_name' => 'nome referente',
        'company_surname' => 'cognome referente',
        'company_cf' => 'codice fiscale',
        'company_pi' => 'partita IVA',
        'company_sdi' => 'codice univoco SDI',
        'company_address' => 'indirizzo',
        'company_cap' => 'CAP',

        'terms_service' => 'termini di servizio',
    ],

];
