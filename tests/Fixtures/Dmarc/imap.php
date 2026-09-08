<?php

namespace App\Services\Dmarc;

// Loaded only inside isolated test processes; never shadows IMAP in other tests.
function imap_open(...$arguments)
{
    return app('test.imap')->open(...$arguments);
}

function imap_last_error()
{
    return app('test.imap')->lastError();
}

function imap_search(...$arguments)
{
    return app('test.imap')->search(...$arguments);
}

function imap_fetch_overview(...$arguments)
{
    return app('test.imap')->overview(...$arguments);
}

function imap_close(...$arguments)
{
    return app('test.imap')->close(...$arguments);
}

function imap_errors()
{
    return app('test.imap')->errors();
}

function imap_alerts()
{
    return app('test.imap')->alerts();
}
