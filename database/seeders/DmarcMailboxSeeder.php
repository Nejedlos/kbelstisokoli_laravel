<?php

namespace Database\Seeders;

use App\Models\Dmarc\DmarcMailbox;
use Illuminate\Database\Seeder;

class DmarcMailboxSeeder extends Seeder
{
    public function run(): void
    {
        DmarcMailbox::updateOrCreate(
            ['email' => 'dmarc@kbelstisokoli.cz'],
            [
                'host' => 'mail.webglobe.cz',
                'port' => 993,
                'encryption' => 'ssl',
                'username' => 'dmarc@kbelstisokoli.cz',
                'password' => 'm0xbDdRDm0xbDdRD',
                'status' => 'active',
            ]
        );
    }
}
