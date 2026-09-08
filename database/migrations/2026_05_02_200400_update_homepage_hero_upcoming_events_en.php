<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $page = Page::where('slug', 'home')->first();

        if (! $page) {
            return;
        }

        $contentTranslations = $page->getTranslations('content');

        if (! isset($contentTranslations['en'])) {
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
        $page = Page::where('slug', 'home')->first();

        if (! $page) {
            return;
        }

        $contentTranslations = $page->getTranslations('content');

        if (! isset($contentTranslations['en'])) {
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
