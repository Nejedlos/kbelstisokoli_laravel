<?php

namespace App\Support\Media;

use Livewire\Wireable;

/**
 * Třída pro reprezentaci "virtuálního" MediaAsset modelu načteného přímo z disku.
 * Používá se jako fallback v AvatarModal, pokud chybí DB záznamy.
 */
class VirtualAvatarAsset implements Wireable
{
    public $id;
    public $mainUrl;
    public $thumbUrl;

    public function __construct($id, $mainUrl, $thumbUrl)
    {
        $this->id = (string) $id;
        $this->mainUrl = $mainUrl;
        $this->thumbUrl = $thumbUrl;
    }

    /**
     * Simuluje metodu getUrl() ze Spatie Media Library / MediaAsset modelu.
     */
    public function getUrl(string $conversion = ''): string
    {
        return $conversion === 'thumb' ? $this->thumbUrl : $this->mainUrl;
    }

    /**
     * Simuluje dynamické vlastnosti modelu.
     */
    public function __get(string $name)
    {
        if ($name === 'title') {
            return 'Default Avatar ' . $this->id;
        }
        return null;
    }

    /**
     * Umožňuje serializaci pro Livewire (pokud by bylo potřeba,
     * ale snažíme se tomu vyhnout přes Computed Property).
     */
    public function toLivewire(): array
    {
        return [
            'id' => $this->id,
            'mainUrl' => $this->mainUrl,
            'thumbUrl' => $this->thumbUrl,
        ];
    }

    /**
     * Umožňuje hydrataci z Livewire stavu.
     */
    public static function fromLivewire($value): static
    {
        return new static($value['id'], $value['mainUrl'], $value['thumbUrl']);
    }
}
