<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use function Laravel\Prompts\text;
use function Laravel\Prompts\select;
use function Laravel\Prompts\password;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

class ProductionDeploySetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:production:setup {connection?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup production deployment (Git, Envoy, Environment)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info('🔧 Production Setup - Kbelští sokoli');

        $connection = $this->argument('connection');

        if (!$connection && !env('PROD_HOST')) {
            $connection = text(
                label: 'SSH příkaz nebo spojení (nepovinné)?',
                placeholder: 'ssh -p 20001 ssh-588875@dw191.webglobe.com',
                hint: 'Můžete vložit celý SSH příkaz, ze kterého se pokusíme vybrat uživatele, hostitele a port.'
            );
        }

        $parsed = $connection ? $this->parseConnectionString($connection) : [];

        while (true) {
            $host = text(
                label: 'IP adresa nebo hostitel produkčního serveru?',
                placeholder: 'dw191.webglobe.com',
                default: $parsed['host'] ?? env('PROD_HOST', ''),
                hint: 'Tyto údaje jsou nezbytné pro připojení k SSH konzoli, přes kterou se spouští všechny příkazy (git, composer, build).',
                required: true
            );

            $port = text(
                label: 'SSH port?',
                placeholder: '22',
                default: $parsed['port'] ?? env('PROD_PORT', '22'),
                hint: 'Výchozí port je 22. U hostingu Webglobe se často používá 20001.',
                required: true
            );

            $user = text(
                label: 'SSH uživatel na serveru?',
                placeholder: 'ssh-588875',
                default: $parsed['user'] ?? env('PROD_USER', ''),
                hint: 'Uživatelské jméno pro SSH přístup (např. ssh-XXXXXX).',
                required: true
            );

            // Uložit základní nastavení spojení ihned pro případ selhání (zapamatovat nastavení)
            $this->updateEnv([
                'PROD_HOST' => $host,
                'PROD_PORT' => $port,
                'PROD_USER' => $user,
            ]);

            info("🔍 Pokouším se o navázání SSH spojení se serverem {$user}@{$host}:{$port}...");

            if ($this->ensureSshConnection($host, $port, $user)) {
                break;
            }

            if (!confirm('Nepodařilo se navázat SSH spojení. Chcete upravit údaje a zkusit to znovu?', true)) {
                return self::FAILURE;
            }

            // Vyčistit parsované údaje pro další pokus, aby se použily ty z .env
            $parsed = [];
        }

        // --- Automatická detekce binárek po úspěšném připojení ---
        $detectedPhp = env('PROD_PHP_BINARY', 'php');
        $detectedNode = env('PROD_NODE_BINARY', 'node20');
        $detectedNpm = env('PROD_NPM_BINARY', 'npm');

        $this->discoverBinaries($host, $port, $user, $detectedPhp, $detectedNode, $detectedNpm);

        $phpBinary = text(
            label: 'PHP binárka na serveru?',
            placeholder: 'php8.4',
            default: $detectedPhp,
            hint: 'Na některých hostinzích (např. Webglobe) je potřeba volat konkrétní verzi, např. php8.4.',
            required: true
        );

        $nodeBinary = text(
            label: 'Node.js binárka na serveru?',
            placeholder: 'node20',
            default: $detectedNode,
            hint: 'Vite 6 vyžaduje Node.js 18+. Na Webglobe zkuste "node20" nebo "node18".',
            required: true
        );

        $npmBinary = text(
            label: 'NPM binárka na serveru?',
            placeholder: 'npm',
            default: $detectedNpm,
            hint: 'Zadejte "npm". Pokud používáte konkrétní verzi Node, systém by měl automaticky vybrat správné NPM.',
            required: true
        );

        // Uložit aktualizované binárky do .env
        $this->updateEnv([
            'PROD_PHP_BINARY' => $phpBinary,
            'PROD_NODE_BINARY' => $nodeBinary,
            'PROD_NPM_BINARY' => $npmBinary,
        ]);

        if (!$this->checkServerRequirements($host, $port, $user, $phpBinary, $nodeBinary, $npmBinary)) {
            if (!confirm('Server nesplňuje některé požadavky. Chcete přesto pokračovat?', false)) {
                return self::FAILURE;
            }
        }

        // 1. Funkční adresář (vše kromě public)
        $path = $this->browseServerPath($host, $port, $user, 'Zvolte FUNKČNÍ ADRESÁŘ (kam přijde jádro aplikace)');

        // 2. Veřejný adresář (kam přijde obsah public)
        $publicPath = $this->browseServerPath($host, $port, $user, 'Zvolte VEŘEJNÝ ADRESÁŘ (kam přijdou veřejné soubory, obvykle www, public_html)');

        $token = password(
            label: 'GitHub Personal Access Token (pro Git autentikaci)?',
            placeholder: 'ghp_...',
            hint: 'Token zajistí automatické stažení kódu z GitHubu na server bez nutnosti nastavování SSH klíčů.',
            required: true
        );

        // 3. Konfigurace databáze
        $dbConfig = [];
        info("🗄️  Konfigurace databáze na produkci");
        $dbConfig['db_connection'] = select('Typ databáze?', ['mysql', 'mariadb', 'pgsql', 'sqlite'], 'mysql');
        $dbConfig['db_host'] = text('DB Host', default: '127.0.0.1');
        $dbConfig['db_port'] = text('DB Port', default: '3306');
        $dbConfig['db_database'] = text('Název databáze', required: true);
        $dbConfig['db_username'] = text('DB Uživatel', required: true);
        $dbConfig['db_password'] = password('DB Heslo', required: true);
        $dbConfig['db_prefix'] = text('Prefix tabulek (volitelné)', default: 'new_', hint: 'Např. new_ zajistí, že tabulky budou mít název new_users atd.');

        // Uložit do .env pro příště
        $envData = [
            'PROD_HOST' => $host,
            'PROD_PORT' => $port,
            'PROD_USER' => $user,
            'PROD_PHP_BINARY' => $phpBinary,
            'PROD_NODE_BINARY' => $nodeBinary,
            'PROD_NPM_BINARY' => $npmBinary,
            'PROD_PATH' => $path,
            'PROD_PUBLIC_PATH' => $publicPath,
            'PROD_GIT_TOKEN' => $token,
            'PROD_DB_CONNECTION' => $dbConfig['db_connection'],
            'PROD_DB_HOST' => $dbConfig['db_host'],
            'PROD_DB_PORT' => $dbConfig['db_port'],
            'PROD_DB_DATABASE' => $dbConfig['db_database'],
            'PROD_DB_USERNAME' => $dbConfig['db_username'],
            'PROD_DB_PREFIX' => $dbConfig['db_prefix'] ?? '',
        ];

        $this->updateEnv($envData);

        info('✅ Nastavení bylo uloženo do .env.');

        if (confirm('Chcete nyní spustit úvodní setup (git clone, composer, npm, atd.) na serveru?', true)) {
            $this->runEnvoySetup($host, $port, $user, $phpBinary, $path, $token, $publicPath, $dbConfig, $nodeBinary, $npmBinary);
        }

        return self::SUCCESS;
    }

    protected function parseConnectionString(string $connection): array
    {
        $user = null;
        $host = null;
        $port = null;

        // Odstranění "ssh " na začátku, pokud existuje
        $connection = preg_replace('/^ssh\s+/', '', trim($connection));

        // Extrakce portu pokud je přítomen (-p 20001)
        if (preg_match('/-p\s+(\d+)/', $connection, $matches)) {
            $port = $matches[1];
            // Odstranění portu z řetězce pro další parsování
            $connection = preg_replace('/-p\s+(\d+)/', '', $connection);
        }

        // Extrakce user a host (user@host)
        if (preg_match('/([^@\s]+)@([^@\s]+)/', trim($connection), $matches)) {
            $user = $matches[1];
            $host = $matches[2];
        } else {
            // Možná je to jen host
            $host = trim($connection);
        }

        return array_filter(compact('user', 'host', 'port'));
    }

    protected function ensureSshConnection(string $host, string $port, string $user): bool
    {
        while (true) {
            // 1. Zkusíme se připojit bez hesla (BatchMode)
            $process = Process::run("ssh -p {$port} -o BatchMode=yes -o StrictHostKeyChecking=no -o ConnectTimeout=5 {$user}@{$host} 'exit'");

            if ($process->successful()) {
                return true;
            }

            warning("⚠️ Nepodařilo se připojit k serveru bez hesla (pravděpodobně chybí SSH klíče nebo je přístup zamítnut).");

            if (!confirm("Chcete nyní (znovu) nastavit SSH klíče pro bezheslový přístup?", true)) {
                error("Bez SSH klíčů nebude automatický deploy fungovat spolehlivě.");
                return false;
            }

            // 2. Kontrola existence lokálního klíče
            $home = getenv('HOME');
            $pubKeyPath = "{$home}/.ssh/id_rsa.pub";

            if (!file_exists($pubKeyPath)) {
                info("Klíč ~/.ssh/id_rsa.pub nenalezen. Generuji nový...");
                $genProcess = Process::run("ssh-keygen -t rsa -b 4096 -f {$home}/.ssh/id_rsa -N ''");
                if (!$genProcess->successful()) {
                    error("Nepodařilo se vygenerovat SSH klíč.");
                    return false;
                }
            }

            // 3. Nahrání klíče na server (interaktivně - uživatel bude muset zadat heslo k serveru)
            info("Nyní budete požádáni o HESLO k serveru pro nahrání veřejného klíče.");
            info("Pokud nahrání selže (např. špatné heslo), budete moci pokus opakovat.");

            // Zkusíme ssh-copy-id (běžné na Macu/Linuxu)
            $copyProcess = Process::forever()->tty()->run("ssh-copy-id -p {$port} -o StrictHostKeyChecking=no {$user}@{$host}");

            if (!$copyProcess->successful()) {
                warning("ssh-copy-id selhalo. Zkouším alternativní metodu (opět budete požádáni o heslo)...");
                $pubKey = file_get_contents($pubKeyPath);
                $remoteCmd = "mkdir -p ~/.ssh && chmod 700 ~/.ssh && echo '{$pubKey}' >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys";
                $altCopyProcess = Process::forever()->tty()->run("ssh -p {$port} -o StrictHostKeyChecking=no {$user}@{$host} \"{$remoteCmd}\"");

                if (!$altCopyProcess->successful()) {
                    error("Nepodařilo se nahrát SSH klíč na server. Zkontrolujte prosím přístupové údaje a heslo.");
                    if (!confirm("Chcete zkusit nahrát klíč (zadat heslo) znovu?", true)) {
                        return false;
                    }
                    continue; // Zkusit znovu celou smyčku
                }
            }

            info("✅ SSH klíč byl úspěšně nahrán. Provádím finální test spojení...");

            // Finální test
            $finalCheck = Process::run("ssh -p {$port} -o BatchMode=yes -o StrictHostKeyChecking=no -o ConnectTimeout=5 {$user}@{$host} 'exit'");

            if ($finalCheck->successful()) {
                info("✅ Připojení k serveru je nyní plně funkční.");
                return true;
            }

            error("Ani po nahrání klíče se nepodařilo navázat bezheslové spojení.");
            if (!confirm("Chcete zkusit nahrát klíč znovu (možná jiný problém se spojením)?", true)) {
                return false;
            }
        }
    }

    protected function browseServerPath(string $host, string $port, string $user, string $label, string $currentPath = '.'): string
    {
        while (true) {
            $process = Process::run("ssh -p {$port} -o StrictHostKeyChecking=no -o ConnectTimeout=5 {$user}@{$host} 'ls -F \"{$currentPath}\" | grep / | head -n 20'");

            $dirs = [];
            if ($process->successful()) {
                $output = trim($process->output());
                if (!empty($output)) {
                    $dirs = array_filter(explode("\n", $output));
                    $dirs = array_map(fn($d) => trim($d, '/'), $dirs);
                }
            }

            $options = [];
            if ($currentPath !== '.') {
                $options['..'] = '⬅️ Zpět (..)';
            }

            $options['SELECT'] = "✅ VYBRAT TENTO ADRESÁŘ: " . ($currentPath === '.' ? '(domovský)' : $currentPath);

            foreach ($dirs as $dir) {
                $options[$dir] = "📁 " . $dir;
            }

            $options['MANUAL'] = '✍️ Zadat cestu ručně...';

            $choice = select(
                label: "{$label} (Aktuálně: " . ($currentPath === '.' ? '/' : $currentPath) . ")",
                options: $options,
                default: 'SELECT'
            );

            if ($choice === 'SELECT') {
                // Získáme absolutní cestu přes realpath na serveru
                $realpathCmd = "ssh -p {$port} -o StrictHostKeyChecking=no {$user}@{$host} 'cd \"{$currentPath}\" && pwd'";
                $realpathProcess = Process::run($realpathCmd);
                return trim($realpathProcess->output());
            }

            if ($choice === 'MANUAL') {
                return text(
                    label: "Zadejte absolutní cestu k adresáři:",
                    placeholder: "/var/www/vhosts/example.com/httpdocs",
                    required: true
                );
            }

            if ($choice === '..') {
                $currentPath = dirname($currentPath);
            } else {
                $currentPath = ($currentPath === '.' ? '' : $currentPath . '/') . $choice;
            }
        }
    }

    protected function discoverBinaries(string $host, string $port, string $user, string &$php, string &$node, string &$npm): void
    {
        spin(function () use ($host, $port, $user, &$php, &$node, &$npm) {
            // PHP discovery
            $phpCandidates = array_unique([$php, 'php8.4', 'php8.3', 'php8.2', 'php']);
            foreach ($phpCandidates as $candidate) {
                $process = Process::run("ssh -p {$port} -o StrictHostKeyChecking=no -o ConnectTimeout=5 {$user}@{$host} '{$candidate} -v 2>/dev/null'");
                if ($process->successful() && !empty($process->output())) {
                    preg_match('/PHP ([\d\.]+)/', $process->output(), $matches);
                    if (version_compare($matches[1] ?? '0', '8.4', '>=')) {
                        $php = $candidate;
                        break;
                    }
                }
            }

            // Node discovery
            $nodeCandidates = array_unique([$node, 'node22', 'node20', 'node18', 'node']);
            foreach ($nodeCandidates as $candidate) {
                $process = Process::run("ssh -p {$port} -o StrictHostKeyChecking=no -o ConnectTimeout=5 {$user}@{$host} '{$candidate} -v 2>/dev/null'");
                if ($process->successful() && !empty($process->output())) {
                    preg_match('/v([\d\.]+)/', $process->output(), $matches);
                    if (version_compare($matches[1] ?? '0', '18.0', '>=')) {
                        $node = $candidate;

                        // Try to find matching npm (e.g., node20 -> npm20)
                        $npmCandidates = ['npm'];
                        if (preg_match('/node(\d+)/', $node, $m)) {
                            array_unshift($npmCandidates, 'npm' . $m[1]);
                        }

                        foreach ($npmCandidates as $npmCandidate) {
                            $npmProc = Process::run("ssh -p {$port} -o StrictHostKeyChecking=no -o ConnectTimeout=5 {$user}@{$host} '{$npmCandidate} -v 2>/dev/null'");
                            if ($npmProc->successful()) {
                                $npm = $npmCandidate;
                                break;
                            }
                        }
                        break;
                    }
                }
            }
        }, 'Hledám optimální verze PHP a Node.js na serveru...');
    }

    protected function detectPaths(string $host, string $port, string $user): array
    {
        return spin(function () use ($host, $port, $user) {
            // Zkusíme najít adresáře v domovské složce, které vypadají jako webové kořeny
            $process = Process::run("ssh -p {$port} -o StrictHostKeyChecking=no -o ConnectTimeout=5 {$user}@{$host} 'ls -F | grep / | head -n 10'");

            if (!$process->successful()) {
                return [];
            }

            $output = $process->output();
            $lines = array_filter(explode("\n", $output));

            // Vyčistit lomítka na konci
            $paths = array_map(fn($p) => trim($p, '/'), $lines);

            // Seřadit tak, aby běžné názvy byly nahoře
            usort($paths, function($a, $b) {
                $common = ['www', 'public_html', 'web', 'domains'];
                $aScore = in_array(strtolower($a), $common) ? 1 : 0;
                $bScore = in_array(strtolower($b), $common) ? 1 : 0;
                return $bScore <=> $aScore;
            });

            return $paths;
        }, 'Skenuji server...');
    }

    protected function checkServerRequirements(string $host, string $port, string $user, string &$phpBinary, string &$nodeBinary, string &$npmBinary): bool
    {
        return spin(function () use ($host, $port, $user, &$phpBinary, &$nodeBinary, &$npmBinary) {
            $results = [];
            $allOk = true;

            // 1. PHP Discovery & Check
            $phpCandidates = array_unique([$phpBinary, 'php8.4', 'php8.3', 'php8.2', 'php']);
            $bestPhp = null;
            $bestPhpVer = null;

            foreach ($phpCandidates as $candidate) {
                $process = Process::run("ssh -p {$port} -o StrictHostKeyChecking=no -o ConnectTimeout=5 {$user}@{$host} '{$candidate} -v 2>/dev/null'");
                if ($process->successful() && !empty($process->output())) {
                    preg_match('/PHP ([\d\.]+)/', $process->output(), $matches);
                    $version = $matches[1] ?? '0';
                    if (version_compare($version, '8.4', '>=')) {
                        $bestPhp = $candidate;
                        $bestPhpVer = $version;
                        break;
                    }
                }
            }

            if ($bestPhp) {
                if ($bestPhp !== $phpBinary) {
                    $results[] = "<fg=yellow>ℹ</> PHP: Původní ({$phpBinary}) nevyhovuje, automaticky nalezeno <fg=green>{$bestPhp}</> (v{$bestPhpVer})";
                    $phpBinary = $bestPhp;
                } else {
                    $results[] = "<fg=green>✓</> PHP ({$phpBinary}): Verze {$bestPhpVer}";
                }
            } else {
                $results[] = "<fg=red>✗</> PHP: Žádná z verzí (8.4+) nebyla nalezena.";
                $allOk = false;
            }

            // 2. Git Check
            $process = Process::run("ssh -p {$port} -o StrictHostKeyChecking=no -o ConnectTimeout=5 {$user}@{$host} 'git --version 2>/dev/null'");
            if ($process->successful() && !empty($process->output())) {
                preg_match('/git version ([\d\.]+)/', $process->output(), $matches);
                $results[] = "<fg=green>✓</> Git: Verze " . ($matches[1] ?? 'neznámá');
            } else {
                $results[] = "<fg=red>✗</> Git: Nenalezeno";
                $allOk = false;
            }

            // 3. Composer Check
            $process = Process::run("ssh -p {$port} -o StrictHostKeyChecking=no -o ConnectTimeout=5 {$user}@{$host} 'composer --version 2>/dev/null'");
            if ($process->successful() && !empty($process->output())) {
                preg_match('/Composer version ([\d\.]+)/', $process->output(), $matches);
                $results[] = "<fg=green>✓</> Composer: Verze " . ($matches[1] ?? 'neznámá');
            } else {
                $results[] = "<fg=red>✗</> Composer: Nenalezeno";
                $allOk = false;
            }

            // 4. Node Discovery & Check
            $nodeCandidates = array_unique([$nodeBinary, 'node22', 'node20', 'node18', 'node']);
            $bestNode = null;
            $bestNodeVer = null;

            foreach ($nodeCandidates as $candidate) {
                $process = Process::run("ssh -p {$port} -o StrictHostKeyChecking=no -o ConnectTimeout=5 {$user}@{$host} '{$candidate} -v 2>/dev/null'");
                if ($process->successful() && !empty($process->output())) {
                    preg_match('/v([\d\.]+)/', $process->output(), $matches);
                    $version = $matches[1] ?? '0';
                    if (version_compare($version, '18.0', '>=')) {
                        $bestNode = $candidate;
                        $bestNodeVer = $version;
                        break;
                    }
                }
            }

            if ($bestNode) {
                if ($bestNode !== $nodeBinary) {
                    $results[] = "<fg=yellow>ℹ</> Node.js: Původní ({$nodeBinary}) nevyhovuje, automaticky nalezeno <fg=green>{$bestNode}</> (v{$bestNodeVer})";
                    $nodeBinary = $bestNode;
                } else {
                    $results[] = "<fg=green>✓</> Node.js ({$nodeBinary}): Verze {$bestNodeVer}";
                }
            } else {
                $results[] = "<fg=red>✗</> Node.js: Žádná z verzí (18.0+) nebyla nalezena.";
                $allOk = false;
            }

            // 5. NPM Check
            $npmCandidates = [$npmBinary, 'npm'];
            if (preg_match('/node(\d+)/', $nodeBinary, $m)) {
                array_unshift($npmCandidates, 'npm' . $m[1]);
            }
            $npmCandidates = array_unique($npmCandidates);

            $bestNpm = null;
            foreach ($npmCandidates as $candidate) {
                $process = Process::run("ssh -p {$port} -o StrictHostKeyChecking=no -o ConnectTimeout=5 {$user}@{$host} '{$candidate} -v 2>/dev/null'");
                if ($process->successful() && !empty($process->output())) {
                    $bestNpm = $candidate;
                    $npmBinary = $candidate;
                    break;
                }
            }

            if ($bestNpm) {
                $results[] = "<fg=green>✓</> NPM ({$npmBinary}): Verze " . trim($process->output());
            } else {
                $results[] = "<fg=red>✗</> NPM ({$npmBinary}): Nenalezeno";
                $allOk = false;
            }

            foreach ($results as $res) {
                $this->line($res);
            }

            return $allOk;
        }, 'Prověřuji požadavky serveru...');
    }

    protected function updateEnv(array $data): void
    {
        $path = base_path('.env');

        if (!file_exists($path)) {
            return;
        }

        $content = file_get_contents($path);

        foreach ($data as $key => $value) {
            if (str_contains($content, "{$key}=")) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}=\"{$value}\"", $content);
            } else {
                $content .= "\n{$key}=\"{$value}\"";
            }
        }

        file_put_contents($path, $content);
    }

    protected function runEnvoySetup(string $host, string $port, string $user, string $phpBinary, string $path, string $token, ?string $publicPath, array $dbConfig, string $nodeBinary = 'node', string $npmBinary = 'npm'): void
    {
        while (true) {
            info("🚀 Spouštím Envoy setup na {$user}@{$host}:{$port}...");

            $params = [
                "--host={$host}",
                "--port={$port}",
                "--user={$user}",
                "--php={$phpBinary}",
                "--node={$nodeBinary}",
                "--npm={$npmBinary}",
                "--path={$path}",
                "--token={$token}",
            ];

            if ($publicPath) {
                $params[] = "--public_path={$publicPath}";
            }

            foreach ($dbConfig as $key => $value) {
                $params[] = "--{$key}={$value}";
            }

            $command = base_path('vendor/bin/envoy') . " run setup " . implode(' ', $params);

            $process = Process::forever()->run($command, function (string $type, string $output) {
                echo $output;
            });

            if ($process->successful()) {
                info('🎉 Setup byl úspěšně dokončen!');
                break;
            } else {
                error('❌ Setup selhal. Zkontrolujte prosím SSH přístup a chybové hlášky výše.');

                if (!confirm('Chcete zkusit setup spustit znovu se stejným nastavením?', true)) {
                    break;
                }
            }
        }
    }
}
