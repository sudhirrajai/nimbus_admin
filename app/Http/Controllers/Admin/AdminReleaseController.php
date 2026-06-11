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

            $release = [
                'exists' => true,
                'filename' => 'nimbus.zip',
                'version' => $version,
                'size' => $this->formatBytes($sizeInBytes),
                'uploaded_at' => date('Y-m-d H:i:s', $lastModified),
            ];
        }

        return Inertia::render('Admin/Releases', [
            'release' => $release,
            'urls' => [
                'install' => request()->getSchemeAndHttpHost() . '/install.sh',
                'uninstall' => request()->getSchemeAndHttpHost() . '/uninstall.sh',
                'download' => request()->getSchemeAndHttpHost() . '/nimbus.zip',
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

        // Retrieve script contents to store separately
        $installContent = $zip->getFromName('install.sh');
        $uninstallContent = $zip->getFromName('uninstall.sh');

        $zip->close();

        // Ensure releases directory exists
        Storage::makeDirectory('releases');

        // Store the files
        Storage::putFileAs('releases', $file, 'nimbus.zip');
        Storage::put('releases/version.txt', $version);
        Storage::put('releases/install.sh', $installContent);
        Storage::put('releases/uninstall.sh', $uninstallContent);

        return back()->with('success', 'Nimbus release package uploaded and processed successfully.');
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
            'releases/uninstall.sh'
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
