<?php

namespace App\Support;

use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\PhpExecutableFinder;

class BinaryHelper
{
    protected static array $cache = [];

    /**
     * Vrátí nejlepší cestu k PHP binárce.
     */
    public static function getPhpBinary(): string
    {
        if (isset(self::$cache['php'])) {
            return self::$cache['php'];
        }

        $finder = new PhpExecutableFinder;
        $defaultPhp = $finder->find(false) ?: PHP_BINARY;

        if (config('app.env') === 'production') {
            $configured = config('app.prod_php_binary');
            $php = $configured ?: $defaultPhp;
        } else {
            $configured = config('app.local_php_binary');
            $php = $configured ?: $defaultPhp;
        }

        return self::$cache['php'] = $php;
    }

    /**
     * Vrátí nejlepší cestu k Node.js binárce (v18+).
     */
    public static function getNodeBinary(): string
    {
        if (isset(self::$cache['node'])) {
            return self::$cache['node'];
        }

        if (config('app.env') !== 'production') {
            return self::$cache['node'] = 'node';
        }

        $configured = config('app.prod_node_binary');

        // Pokud je nastaveno něco jiného než jen "node", věříme konfiguraci
        if ($configured && $configured !== 'node') {
            return self::$cache['node'] = $configured;
        }

        // Autodetekce na hostingu (Webglobe)
        $potential = ['node22', 'node20', 'node18', 'node'];
        foreach ($potential as $bin) {
            try {
                $process = Process::run("which $bin");
                if ($process->successful() && ! empty(trim($process->output()))) {
                    $path = trim($process->output());

                    // Ověření verze (potřebujeme v18+)
                    $versionProcess = Process::run("$path -v");
                    if ($versionProcess->successful()) {
                        $ver = $versionProcess->output();
                        if (preg_match('/v(18|2[0-9])/', $ver)) {
                            return self::$cache['node'] = $path;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Ignorujeme chyby při detekci
            }
        }

        return self::$cache['node'] = $configured ?: 'node';
    }

    /**
     * Vrátí nejlepší cestu k NPM binárce.
     */
    public static function getNpmBinary(?string $nodeBinary = null): string
    {
        if (isset(self::$cache['npm'])) {
            return self::$cache['npm'];
        }

        if (config('app.env') !== 'production') {
            return self::$cache['npm'] = 'npm';
        }

        $configured = config('app.prod_npm_binary');

        // Pokud je nastaveno něco jiného než jen "npm", věříme konfiguraci
        if ($configured && $configured !== 'npm') {
            return self::$cache['npm'] = $configured;
        }

        // Pokud nemáme nodeBinary, zkusíme ho zjistit
        if (! $nodeBinary) {
            $nodeBinary = self::getNodeBinary();
        }

        // Pokud máme specifický node (např. node20), zkusíme odpovídající npm (npm20)
        if (preg_match('/node(\d+)/', $nodeBinary, $m)) {
            $npmBin = 'npm'.$m[1];
            try {
                $process = Process::run("which $npmBin");
                if ($process->successful() && ! empty(trim($process->output()))) {
                    return self::$cache['npm'] = trim($process->output());
                }
            } catch (\Throwable $e) {
                // Ignorujeme
            }
        }

        return self::$cache['npm'] = $configured ?: 'npm';
    }

    /**
     * Vrátí pole environment proměnných potřebných pro běh binárek (zejména NPM).
     */
    public static function getEnvironmentVariables(): array
    {
        $vars = [];

        if ($token = config('app.fontawesome_token')) {
            $vars['FONTAWESOME_TOKEN'] = $token;
        }

        return $vars;
    }

    /**
     * Vrátí řetězec pro export proměnných v shellu (např. export KEY=VAL; export ...).
     */
    public static function getShellExportString(): string
    {
        $vars = self::getEnvironmentVariables();
        $exports = [];

        foreach ($vars as $key => $val) {
            $exports[] = "export {$key}='{$val}'";
        }

        return implode('; ', $exports);
    }
}
