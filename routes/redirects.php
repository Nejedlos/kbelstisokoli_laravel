<?php

use Illuminate\Support\Facades\Route;

// Hlavní stránky
Route::permanentRedirect('/aktualne.php', '/novinky');
Route::permanentRedirect('/novinky.php', '/novinky');
Route::permanentRedirect('/rozpis_zapasu.php', '/zapasy');
Route::permanentRedirect('/soupiska.php', '/tymy/soupisky');
Route::permanentRedirect('/fotoalbum.php', '/galerie');
Route::permanentRedirect('/kontakty.php', '/kontakt');
Route::permanentRedirect('/kontakt.php', '/kontakt');
Route::permanentRedirect('/historie.php', '/historie');
Route::permanentRedirect('/dokumenty.php', '/dokumenty');
Route::permanentRedirect('/clanky.php', '/novinky');
Route::permanentRedirect('/tabulka.php', '/zapasy');
Route::permanentRedirect('/aktualni_tabulka.php', '/zapasy');
Route::permanentRedirect('/statistika_hracu.php', '/zapasy');

// Docházka a interní sekce
Route::permanentRedirect('/dochazka', '/clenska-sekce/dochazka');
Route::permanentRedirect('/dochajda.php', '/clenska-sekce/dochazka');

// Složky
Route::permanentRedirect('/fotoalbum', '/galerie');
Route::permanentRedirect('/dokumenty', '/dokumenty');

// Staré CMS Made Simple index s parametry (pokud Laravel zachytí)
Route::get('/index.php', function (\Illuminate\Http\Request $request) {
    if ($request->has('page')) {
        $page = $request->query('page');

        // Specifické mapování pro známé stránky
        $mapping = [
            'aktualne' => '/novinky',
            'rozpis-zapasu' => '/zapasy',
            'soupiska' => '/tymy/soupisky',
            'kontakt' => '/kontakt',
            'historie' => '/historie',
            'dokumenty' => '/dokumenty',
        ];

        if (isset($mapping[$page])) {
            return redirect($mapping[$page], 301);
        }

        // Zkusíme najít stránku se stejným slugem v novém systému
        return redirect('/' . $page, 301);
    }

    if ($request->has('rozpis_zapasu') || $request->query('zobrazit') === 'zapasy') {
        return redirect('/zapasy', 301);
    }

    if ($request->has('soupiska')) {
        return redirect('/tymy/soupisky', 301);
    }

    return redirect('/', 301);
});
