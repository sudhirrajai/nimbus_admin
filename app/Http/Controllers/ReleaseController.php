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

        $content = Storage::get('releases/install.sh');
        
        // Convert to ZIP install mode and inject the VMCore domain
        $content = str_replace('INSTALL_MODE="git"', 'INSTALL_MODE="zip"', $content);
        $content = str_replace('{{VMCORE_URL}}', url('/'), $content);

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
        
        // Inject the VMCore domain for fallback reinstall URLs
        $content = str_replace('{{VMCORE_URL}}', url('/'), $content);

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
}
