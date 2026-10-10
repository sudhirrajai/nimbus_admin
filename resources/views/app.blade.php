<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Roook') }}</title>
        <!-- Favicon Suite -->
        <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico?v=4" />
        <link rel="icon" type="image/x-icon" href="/favicon.ico?v=4" />
        <link rel="icon" type="image/svg+xml" href="/favicon.svg?v=4" />
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=4" />
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=4" />
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=4" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=IBM+Plex+Mono:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

        @php
            $seoSettings = \App\Http\Controllers\Admin\AdminSettingsController::getSettings();
            $siteName = $seoSettings['site_name'] ?? config('app.name', 'Roook Hosting');
            $metaTitle = $seoSettings['meta_title'] ?? ($siteName . ' — Managed Cloud Servers & Nimbus Panel');
            $metaDescription = $seoSettings['meta_description'] ?? 'High-performance managed cloud hosting with NVMe SSD infrastructure, isolated Docker architecture, automated daily backups, and a 2-hour provisioning SLA.';
            $metaKeywords = $seoSettings['meta_keywords'] ?? 'managed cloud hosting, nimbus control panel, nvme cloud servers, fast hosting, roook hosting, dedicated servers';
            $metaAuthor = $seoSettings['meta_author'] ?? $siteName;
            $ogImage = url($seoSettings['og_image'] ?? '/og-image.png');
            $twitterHandle = $seoSettings['twitter_handle'] ?? '@roookhost';
            $robots = ($seoSettings['robots_index'] ?? true) ? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' : 'noindex, nofollow';
            $canonicalUrl = url()->current();
        @endphp

        <!-- SEO Primary Meta Tags -->
        <meta name="description" content="{{ $metaDescription }}">
        <meta name="keywords" content="{{ $metaKeywords }}">
        <meta name="author" content="{{ $metaAuthor }}">
        <meta name="robots" content="{{ $robots }}">
        <link rel="canonical" href="{{ $canonicalUrl }}">
        <meta name="theme-color" content="#10b981">
        <meta name="application-name" content="{{ $siteName }}">
        <meta name="apple-mobile-web-app-title" content="{{ $siteName }}">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="format-detection" content="telephone=no">

        @if(!empty($seoSettings['google_site_verification']))
        <meta name="google-site-verification" content="{{ $seoSettings['google_site_verification'] }}">
        @endif
        @if(!empty($seoSettings['bing_site_verification']))
        <meta name="msvalidate.01" content="{{ $seoSettings['bing_site_verification'] }}">
        @endif

        <!-- Open Graph / Facebook / LinkedIn / WhatsApp -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ $canonicalUrl }}">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:title" content="{{ $metaTitle }}">
        <meta property="og:description" content="{{ $metaDescription }}">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:locale" content="en_US">

        <!-- Twitter / X -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ $canonicalUrl }}">
        <meta name="twitter:site" content="{{ $twitterHandle }}">
        <meta name="twitter:creator" content="{{ $twitterHandle }}">
        <meta name="twitter:title" content="{{ $metaTitle }}">
        <meta name="twitter:description" content="{{ $metaDescription }}">
        <meta name="twitter:image" content="{{ $ogImage }}">

        <!-- Schema.org JSON-LD Structured Data -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@graph": [
                {
                    "@type": "Organization",
                    "@id": "{{ url('/') }}#organization",
                    "name": "{{ $seoSettings['company_name'] ?? $siteName }}",
                    "url": "{{ url('/') }}",
                    "logo": {
                        "@type": "ImageObject",
                        "url": "{{ url('/apple-touch-icon.png') }}",
                        "width": 180,
                        "height": 180
                    },
                    "email": "{{ $seoSettings['company_email'] ?? 'billing@vmcore.in' }}",
                    "telephone": "{{ $seoSettings['company_phone'] ?? '+91 80 4567 8900' }}",
                    "address": {
                        "@type": "PostalAddress",
                        "streetAddress": "{{ $seoSettings['company_address_line1'] ?? '' }}",
                        "addressLocality": "{{ $seoSettings['company_address_line2'] ?? '' }}",
                        "addressCountry": "IN"
                    }
                },
                {
                    "@type": "WebSite",
                    "@id": "{{ url('/') }}#website",
                    "url": "{{ url('/') }}",
                    "name": "{{ $siteName }}",
                    "description": "{{ $metaDescription }}",
                    "publisher": {
                        "@id": "{{ url('/') }}#organization"
                    },
                    "inLanguage": "en-US"
                }
            ]
        }
        </script>

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
