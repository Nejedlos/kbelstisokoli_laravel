<?php

namespace App\Traits;

trait HasMatchResult
{
    /**
     * Zjistí, zda zápas skončil vítězstvím našeho týmu.
     */
    public function getIsWinAttribute(): bool
    {
        if (!$this->has_score) {
            return false;
        }

        if ($this->score_home === $this->score_away) {
            return false;
        }

        return $this->is_home
            ? $this->score_home > $this->score_away
            : $this->score_away > $this->score_home;
    }

    /**
     * Zjistí, zda zápas skončil prohrou našeho týmu.
     */
    public function getIsLossAttribute(): bool
    {
        if (!$this->has_score) {
            return false;
        }

        if ($this->score_home === $this->score_away) {
            return false;
        }

        return $this->is_home
            ? $this->score_home < $this->score_away
            : $this->score_away < $this->score_home;
    }

    /**
     * Zjistí, zda zápas skončil remízou.
     */
    public function getIsDrawAttribute(): bool
    {
        if (!$this->has_score) {
            return false;
        }

        return $this->score_home === $this->score_away;
    }

    /**
     * Zjistí, zda má zápas vyplněné skóre.
     */
    public function getHasScoreAttribute(): bool
    {
        return !is_null($this->score_home) && !is_null($this->score_away);
    }

    /**
     * Vrátí skóre našeho týmu.
     */
    public function getOurScoreAttribute(): ?int
    {
        if (!$this->has_score) {
            return null;
        }

        return $this->is_home ? $this->score_home : $this->score_away;
    }

    /**
     * Vrátí skóre soupeře.
     */
    public function getOpponentScoreAttribute(): ?int
    {
        if (!$this->has_score) {
            return null;
        }

        return $this->is_home ? $this->score_away : $this->score_home;
    }

    /**
     * Vrátí písmeno výsledku (V/P/R).
     */
    public function getResultLetterAttribute(): string
    {
        if (!$this->has_score) {
            return '';
        }

        if ($this->is_win) return 'V';
        if ($this->is_loss) return 'P';
        return 'R';
    }

    /**
     * Vrátí CSS třídu pro text výsledku (zelená/červená/šedá).
     */
    public function getResultTextColorAttribute(): string
    {
        if (!$this->has_score) return 'text-slate-400';
        if ($this->is_win) return 'text-emerald-600';
        if ($this->is_loss) return 'text-rose-600';
        return 'text-slate-600';
    }

    /**
     * Vrátí CSS třídu pro pozadí výsledku (zelená/červená/šedá).
     */
    public function getResultBgColorAttribute(): string
    {
        if (!$this->has_score) return 'bg-slate-100';
        if ($this->is_win) return 'bg-emerald-500';
        if ($this->is_loss) return 'bg-rose-500';
        return 'bg-slate-500';
    }
}
