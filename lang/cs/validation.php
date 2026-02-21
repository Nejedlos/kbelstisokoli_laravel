<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validační hlášky
    |--------------------------------------------------------------------------
    |
    | Následující hlášky se používají k informování uživatele o chybách v poli.
    |
    */

    'accepted' => ':attribute musí být potvrzen.',
    'accepted_if' => ':attribute musí být potvrzen, když :other je :value.',
    'active_url' => ':attribute není platnou URL adresou.',
    'after' => ':attribute musí být datum po :date.',
    'after_or_equal' => ':attribute musí být datum po nebo rovno :date.',
    'alpha' => ':attribute může obsahovat pouze písmena.',
    'alpha_dash' => ':attribute může obsahovat pouze písmena, čísla, pomlčky a podtržítka.',
    'alpha_num' => ':attribute může obsahovat pouze písmena a čísla.',
    'array' => ':attribute musí být pole.',
    'before' => ':attribute musí být datum před :date.',
    'before_or_equal' => ':attribute musí být datum před nebo rovno :date.',
    'between' => [
        'numeric' => ':attribute musí být mezi :min a :max.',
        'file' => ':attribute musí být mezi :min a :max kilobajty.',
        'string' => ':attribute musí mít mezi :min a :max znaky.',
        'array' => ':attribute musí mít mezi :min a :max položkami.',
    ],
    'boolean' => ':attribute musí být pravda nebo nepravda.',
    'confirmed' => 'Potvrzení :attribute nesouhlasí.',
    'current_password' => 'Heslo je nesprávné.',
    'date' => ':attribute není platné datum.',
    'date_equals' => ':attribute musí být datum rovno :date.',
    'date_format' => ':attribute neodpovídá formátu :format.',
    'declined' => ':attribute musí být odmítnut.',
    'declined_if' => ':attribute musí být odmítnut, když :other je :value.',
    'different' => ':attribute a :other se musí lišit.',
    'digits' => ':attribute musí mít :digits číslic.',
    'digits_between' => ':attribute musí mít mezi :min a :max číslic.',
    'dimensions' => ':attribute má neplatné rozměry obrázku.',
    'distinct' => ':attribute má duplicitní hodnotu.',
    'email' => ':attribute musí být platná e-mailová adresa.',
    'ends_with' => ':attribute musí končit jednou z následujících hodnot: :values.',
    'enum' => 'Vybraný :attribute je neplatný.',
    'exists' => 'Vybraný :attribute je neplatný.',
    'file' => ':attribute musí být soubor.',
    'filled' => ':attribute musí mít hodnotu.',
    'gt' => [
        'numeric' => ':attribute musí být větší než :value.',
        'file' => ':attribute musí být větší než :value kilobajtů.',
        'string' => ':attribute musí mít více než :value znaků.',
        'array' => ':attribute musí mít více než :value položek.',
    ],
    'gte' => [
        'numeric' => ':attribute musí být větší nebo rovno :value.',
        'file' => ':attribute musí být větší nebo rovno :value kilobajtům.',
        'string' => ':attribute musí mít :value nebo více znaků.',
        'array' => ':attribute musí mít :value nebo více položek.',
    ],
    'image' => ':attribute musí být obrázek.',
    'in' => 'Vybraný :attribute je neplatný.',
    'in_array' => ':attribute neexistuje v :other.',
    'integer' => ':attribute musí být celé číslo.',
    'ip' => ':attribute musí být platná IP adresa.',
    'ipv4' => ':attribute musí být platná IPv4 adresa.',
    'ipv6' => ':attribute musí být platná IPv6 adresa.',
    'json' => ':attribute musí být platný JSON řetězec.',
    'lt' => [
        'numeric' => ':attribute musí být menší než :value.',
        'file' => ':attribute musí být menší než :value kilobajtů.',
        'string' => ':attribute musí mít méně než :value znaků.',
        'array' => ':attribute musí mít méně než :value položek.',
    ],
    'lte' => [
        'numeric' => ':attribute musí být menší nebo rovno :value.',
        'file' => ':attribute musí být menší nebo rovno :value kilobajtům.',
        'string' => ':attribute musí mít maximálně :value znaků.',
        'array' => ':attribute musí mít maximálně :value položek.',
    ],
    'max' => [
        'numeric' => ':attribute nesmí být větší než :max.',
        'file' => ':attribute nesmí být větší než :max kilobajtů.',
        'string' => ':attribute nesmí mít více než :max znaků.',
        'array' => ':attribute nesmí mít více než :max položek.',
    ],
    'mimes' => ':attribute musí být soubor typu: :values.',
    'mimetypes' => ':attribute musí být soubor typu: :values.',
    'min' => [
        'numeric' => ':attribute musí být alespoň :min.',
        'file' => ':attribute musí mít alespoň :min kilobajtů.',
        'string' => ':attribute musí mít alespoň :min znaků.',
        'array' => ':attribute musí mít alespoň :min položek.',
    ],
    'multiple_of' => ':attribute musí být násobkem :value.',
    'not_in' => 'Vybraný :attribute je neplatný.',
    'not_regex' => 'Formát :attribute je neplatný.',
    'numeric' => ':attribute musí být číslo.',
    'password' => [
        'letters' => ':attribute musí obsahovat alespoň jedno písmeno.',
        'mixed' => ':attribute musí obsahovat alespoň jedno velké a jedno malé písmeno.',
        'numbers' => ':attribute musí obsahovat alespoň jedno číslo.',
        'symbols' => ':attribute musí obsahovat alespoň jeden symbol.',
        'uncompromised' => 'Zadané :attribute se objevilo v únicích dat. Zvolte prosím jiné.',
    ],
    'present' => ':attribute musí být přítomen.',
    'prohibited' => ':attribute je zakázán.',
    'prohibited_if' => ':attribute je zakázán, když :other je :value.',
    'prohibited_unless' => ':attribute je zakázán, pokud :other není v :values.',
    'prohibits' => ':attribute zakazuje :other v přítomnosti.',
    'regex' => 'Formát :attribute je neplatný.',
    'required' => ':attribute musíte vyplnit, bez toho to nepůjde.',
    'required_if' => ':attribute musí být vyplněn, když :other je :value.',
    'required_unless' => ':attribute musí být vyplněn, pokud :other není v :values.',
    'required_with' => ':attribute musí být vyplněn, když :values je přítomno.',
    'required_with_all' => ':attribute musí být vyplněn, když :values jsou přítomny.',
    'required_without' => ':attribute musí být vyplněn, když :values není přítomno.',
    'required_without_all' => ':attribute musí být vyplněn, když žádné z :values nejsou přítomny.',
    'same' => ':attribute a :other se musí shodovat.',
    'size' => [
        'numeric' => ':attribute musí být :size.',
        'file' => ':attribute musí mít :size kilobajtů.',
        'string' => ':attribute musí mít :size znaků.',
        'array' => ':attribute musí obsahovat :size položek.',
    ],
    'starts_with' => ':attribute musí začínat jednou z následujících hodnot: :values.',
    'string' => ':attribute musí být řetězec.',
    'timezone' => ':attribute musí být platná časová zóna.',
    'unique' => ':attribute je již obsazen.',
    'uploaded' => 'Nahrávání :attribute selhalo.',
    'url' => 'Formát :attribute je neplatný.',
    'uuid' => ':attribute musí být platné UUID.',

    /*
    |--------------------------------------------------------------------------
    | Vlastní validační hlášky
    |--------------------------------------------------------------------------
    |
    | Zde můžete specifikovat vlastní hlášky pro konkrétní atributy.
    |
    */

    'custom' => [
        'email' => [
            'required' => 'Zapomněli jste vyplnit e-mail, bez něj to nepůjde.',
            'email' => 'Tahle adresa nevypadá jako správný e-mail. Zkuste to prosím opravit.',
        ],
        'password' => [
            'required' => 'Bez hesla se dál nepohneme.',
            'min' => 'Heslo je příliš krátké, mělo by mít alespoň :min znaků.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Vlastní názvy atributů
    |--------------------------------------------------------------------------
    |
    | Následující řádky se používají k nahrazení zástupných symbolů atributů.
    |
    */

    'attributes' => [
        'email' => 'e-mailová adresa',
        'password' => 'heslo',
        'password_confirmation' => 'potvrzení hesla',
    ],

];
