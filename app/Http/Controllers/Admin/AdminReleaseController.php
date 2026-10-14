<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use ZipArchive;

class AdminReleaseController extends Controller
{
    /**
     * Display the releases dashboard.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $release = null;

        if (Storage::exists('releases/nimbus.zip')) {
            $zipPath = 'releases/nimbus.zip';
            $sizeInBytes = Storage::size($zipPath);
            $lastModified = Storage::lastModified($zipPath);
            
            $version = 'Unknown';
            if (Storage::exists('releases/version.txt')) {
                $version = trim(Storage::get('releases/version.txt'));
            }

            $info = [];
            if (Storage::exists('releases/release_info.json')) {
                $info = json_decode(Storage::get('releases/release_info.json'), true) ?? [];
            }

            $realZipPath = Storage::path($zipPath);
            $sha256 = $info['sha256'] ?? (file_exists($realZipPath) ? hash_file('sha256', $realZipPath) : null);
            $changelog = $info['changelog'] ?? ['General updates, security enhancements, and performance optimizations.'];
            $commands = $info['commands'] ?? [
                'composer install --no-dev --optimize-autoloader --no-interaction',
                'php artisan migrate --force',
                'npm run build',
                'php artisan config:clear',
                'php artisan route:clear',
                'php artisan cache:clear',
                'systemctl restart nimbus-php-fpm'
            ];

            $release = [
                'exists' => true,
                'filename' => 'nimbus.zip',
                'version' => $info['version'] ?? $version,
                'sha256' => $sha256,
                'changelog' => $changelog,
                'commands' => $commands,
                'size' => $this->formatBytes($sizeInBytes),
                'uploaded_at' => $info['uploaded_at'] ?? date('Y-m-d H:i:s', $lastModified),
            ];
        }

        return Inertia::render('Admin/Releases', [
            'release' => $release,
            'urls' => [
                'install' => request()->getSchemeAndHttpHost() . '/install.sh',
                'uninstall' => request()->getSchemeAndHttpHost() . '/uninstall.sh',
                'download' => request()->getSchemeAndHttpHost() . '/nimbus.zip',
                'update_check' => request()->getSchemeAndHttpHost() . '/api/v1/nimbus/updates/check',
            ]
        ]);
    }

    /**
     * Upload and extract a new release zip package.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upload(Request $request)
    {
        $request->validate([
            'release_file' => 'required|file|mimes:zip|max:1048576', // Max 1GB
            'custom_changelog' => 'nullable|string',
            'custom_commands' => 'nullable|string',
        ]);

        $file = $request->file('release_file');
        $zip = new ZipArchive();

        if ($zip->open($file->getRealPath()) !== true) {
            return back()->withErrors(['release_file' => 'Unable to open the ZIP archive.']);
        }

        // Validate ZIP structure (requires install.sh and uninstall.sh in root)
        $hasInstall = $zip->locateName('install.sh') !== false;
        $hasUninstall = $zip->locateName('uninstall.sh') !== false;

        if (!$hasInstall || !$hasUninstall) {
            $zip->close();
            return back()->withErrors(['release_file' => 'The release ZIP must contain both install.sh and uninstall.sh at the root level.']);
        }

        // Parse version number from VERSION file if present
        $version = 'Unknown';
        $versionContent = $zip->getFromName('VERSION');
        if ($versionContent !== false) {
            $version = trim($versionContent);
        }

        // Check for update-manifest.json or manifest.json in the zip
        $manifest = [];
        $manifestContent = $zip->getFromName('update-manifest.json') ?: $zip->getFromName('manifest.json');
        if ($manifestContent !== false) {
            $decoded = json_decode($manifestContent, true);
            if (is_array($decoded)) {
                $manifest = $decoded;
                if (!empty($decoded['version'])) {
                    $version = trim($decoded['version']);
                }
            }
        }

        // Determine changelog
        $changelog = [];
        if ($request->filled('custom_changelog')) {
            $changelog = array_values(array_filter(array_map('trim', explode("\n", $request->input('custom_changelog')))));
        } elseif (!empty($manifest['changelog']) && is_array($manifest['changelog'])) {
            $changelog = $manifest['changelog'];
        } else {
            // Check for instructions.txt or changelog.txt in the zip
            $txtContent = $zip->getFromName('changelog.txt') ?: $zip->getFromName('instructions.txt');
            if ($txtContent !== false) {
                $changelog = array_values(array_filter(array_map('trim', explode("\n", $txtContent))));
            }
        }

        if (empty($changelog)) {
            $changelog = ["Release v{$version} - Security patches, core improvements, and bug fixes."];
        }

        // Determine ordered update commands
        $commands = [];
        if ($request->filled('custom_commands')) {
            $commands = array_values(array_filter(array_map('trim', explode("\n", $request->input('custom_commands')))));
        } elseif (!empty($manifest['commands']) && is_array($manifest['commands'])) {
            $commands = $manifest['commands'];
        } else {
            // Check for requirements.txt or instructions.txt in the zip
            $reqContent = $zip->getFromName('requirements.txt') ?: $zip->getFromName('update-commands.txt');
            if ($reqContent !== false) {
                $commands = array_values(array_filter(array_map('trim', explode("\n", $reqContent))));
            }
        }

        if (empty($commands)) {
            $commands = [
                'composer install --no-dev --optimize-autoloader --no-interaction',
                'php artisan migrate --force',
                'npm run build',
                'php artisan config:clear',
                'php artisan route:clear',
                'php artisan cache:clear',
                'systemctl restart nimbus-php-fpm'
            ];
        }

        // Retrieve script contents to store separately
        $installContent = $zip->getFromName('install.sh');
        $uninstallContent = $zip->getFromName('uninstall.sh');

        $zip->close();

        // Calculate SHA-256 hash of the uploaded zip
        $sha256 = hash_file('sha256', $file->getRealPath());

        // Ensure releases directory exists
        Storage::makeDirectory('releases');

        // Store the files
        Storage::putFileAs('releases', $file, 'nimbus.zip');
        Storage::put('releases/version.txt', $version);
        Storage::put('releases/install.sh', $installContent);
        Storage::put('releases/uninstall.sh', $uninstallContent);

        // Store release metadata for OTA checks
        $releaseInfo = [
            'version' => $version,
            'sha256' => $sha256,
            'size' => $this->formatBytes($file->getSize()),
            'size_bytes' => $file->getSize(),
            'uploaded_at' => date('Y-m-d H:i:s'),
            'changelog' => $changelog,
            'commands' => $commands,
        ];
        Storage::put('releases/release_info.json', json_encode($releaseInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return back()->with('success', "Nimbus release package v{$version} uploaded and registered successfully.");
    }

    /**
     * Delete the current release and its extracted scripts.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy()
    {
        Storage::delete([
            'releases/nimbus.zip',
            'releases/version.txt',
            'releases/install.sh',
            'releases/uninstall.sh',
            'releases/release_info.json'
        ]);

        return back()->with('success', 'Release package deleted successfully.');
    }

    /**
     * Helper to format bytes to human-readable format.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
