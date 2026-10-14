<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class ReleaseController extends Controller
{
    /**
     * Serve the dynamically compiled install.sh script.
     *
     * @return \Illuminate\Http\Response
     */
    public function install()
    {
        if (!Storage::exists('releases/install.sh')) {
            abort(404, 'Release installer script not found.');
        }

        $vmcoreUrl = request()->getSchemeAndHttpHost();
        
        // Convert to ZIP install mode and inject the VMCore domain
        $content = str_replace('INSTALL_MODE="git"', 'INSTALL_MODE="zip"', $content);
        $content = preg_replace('/VMCORE_URL="[^"]*"/', 'VMCORE_URL="' . $vmcoreUrl . '"', $content);
        $content = str_replace('{{VMCORE_URL}}', $vmcoreUrl, $content);

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }

    /**
     * Serve the dynamically compiled uninstall.sh script.
     *
     * @return \Illuminate\Http\Response
     */
    public function uninstall()
    {
        if (!Storage::exists('releases/uninstall.sh')) {
            abort(404, 'Release uninstaller script not found.');
        }

        $content = Storage::get('releases/uninstall.sh');
        $vmcoreUrl = request()->getSchemeAndHttpHost();
        
        // Inject the VMCore domain for fallback reinstall URLs
        $content = preg_replace('/VMCORE_URL="[^"]*"/', 'VMCORE_URL="' . $vmcoreUrl . '"', $content);
        $content = str_replace('{{VMCORE_URL}}', $vmcoreUrl, $content);

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }

    /**
     * Stream the Nimbus release ZIP package.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download()
    {
        if (!Storage::exists('releases/nimbus.zip')) {
            abort(404, 'Nimbus ZIP package not found.');
        }

        return response()->download(Storage::path('releases/nimbus.zip'), 'nimbus.zip');
    }

    /**
     * Return OTA update metadata for Nimbus client instances.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUpdates()
    {
        if (!Storage::exists('releases/nimbus.zip')) {
            return response()->json([
                'success' => false,
                'message' => 'No Nimbus releases are currently available.'
            ], 404);
        }

        $version = '1.0.0';
        if (Storage::exists('releases/version.txt')) {
            $version = trim(Storage::get('releases/version.txt'));
        }

        $info = [];
        if (Storage::exists('releases/release_info.json')) {
            $info = json_decode(Storage::get('releases/release_info.json'), true) ?? [];
        }

        $zipPath = Storage::path('releases/nimbus.zip');
        $sha256 = $info['sha256'] ?? (file_exists($zipPath) ? hash_file('sha256', $zipPath) : null);
        $changelog = $info['changelog'] ?? ['General updates, security enhancements, and fixes.'];
        $commands = $info['commands'] ?? [
            'composer install --no-dev --optimize-autoloader --no-interaction',
            'php artisan migrate --force',
            'npm run build',
            'php artisan config:clear',
            'php artisan route:clear',
            'php artisan cache:clear',
            'systemctl restart nimbus-php-fpm'
        ];

        return response()->json([
            'success' => true,
            'version' => $info['version'] ?? $version,
            'download_url' => request()->getSchemeAndHttpHost() . '/nimbus.zip',
            'sha256' => $sha256,
            'changelog' => $changelog,
            'commands' => $commands,
            'release_date' => $info['uploaded_at'] ?? (file_exists($zipPath) ? date('c', filemtime($zipPath)) : date('c')),
        ]);
    }
}
