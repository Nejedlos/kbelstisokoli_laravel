<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $page = \App\Models\Page::where('slug', 'home')->first();

        if (!$page) {
            return;
        }

        $contentTranslations = $page->getTranslations('content');

        if (!isset($contentTranslations['en'])) {
            return;
        }

        $updated = false;
        foreach ($contentTranslations['en'] as $index => $block) {
            if ($block['type'] === 'hero') {
                $contentTranslations['en'][$index]['data']['show_upcoming_events'] = true;
                $updated = true;
            }
        }

        if ($updated) {
            foreach ($contentTranslations as $lang => $value) {
                $page->setTranslation('content', $lang, $value);
            }
            $page->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $page = \App\Models\Page::where('slug', 'home')->first();

        if (!$page) {
            return;
        }

        $contentTranslations = $page->getTranslations('content');

        if (!isset($contentTranslations['en'])) {
            return;
        }

        $updated = false;
        foreach ($contentTranslations['en'] as $index => $block) {
            if ($block['type'] === 'hero') {
                $contentTranslations['en'][$index]['data']['show_upcoming_events'] = false;
                $updated = true;
            }
        }

        if ($updated) {
            foreach ($contentTranslations as $lang => $value) {
                $page->setTranslation('content', $lang, $value);
            }
            $page->save();
        }
    }
};
