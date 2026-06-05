<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateLicenseKeys extends Command
{
    protected $signature = 'license:generate-keys {--force : Overwrite existing keys}';
    protected $description = 'Generate RSA keypair for license signing';

    public function handle()
    {
        $dir = storage_path('app/license_keys');
        $privatePath = $dir . '/private.pem';
        $publicPath = $dir . '/public.pem';

        if (file_exists($privatePath) && !$this->option('force')) {
            $this->error('Keys already exist. Use --force to overwrite.');
            return 1;
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $config = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        // Try to find openssl.cnf for Windows compatibility
        $opensslCnf = null;
        $phpDir = dirname(PHP_BINARY);
        $candidates = [
            $phpDir . '/extras/ssl/openssl.cnf',
            '/etc/ssl/openssl.cnf',
            '/etc/pki/tls/openssl.cnf',
            '/usr/local/ssl/openssl.cnf',
        ];
        foreach ($candidates as $c) {
            if (file_exists($c)) {
                $config['config'] = $c;
                break;
            }
        }

        $key = openssl_pkey_new($config);
        if (!$key) {
            $this->error('Failed to generate RSA key. OpenSSL error: ' . openssl_error_string());
            return 1;
        }

        openssl_pkey_export($key, $privKey, null, $config);
        $details = openssl_pkey_get_details($key);
        $pubKey = $details['key'];

        file_put_contents($privatePath, $privKey);
        chmod($privatePath, 0600);

        file_put_contents($publicPath, $pubKey);

        $this->info('✅ RSA keypair generated successfully!');
        $this->newLine();
        $this->info('Private key: ' . $privatePath);
        $this->info('Public key:  ' . $publicPath);
        $this->newLine();
        $this->warn('⚠️  Copy the public key below and hardcode it in the Nimbus LicenseService:');
        $this->newLine();
        $this->line($pubKey);

        return 0;
    }
}
