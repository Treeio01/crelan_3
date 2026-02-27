<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="ltr" prefix="og: https://ogp.me/ns#" class="js">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="description"
        content="Crelan is een coöperatieve bank waar u als klant voelt dat er een persoonlijk contact is. Op mensenmaat, dat is ons handelsmerk en dat uit zich elke dag in de vertrouwensrelatie die de bank heeft met haar klanten.">
    <link rel="stylesheet" href="{{ asset('assets/css1.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css2.css') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/apple-touch-icon.png') }}?v=3">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicon-32x32.png') }}?v=3">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicon-16x16.png') }}?v=3">
    <link rel="mask-icon" href="{{ asset('assets/safari-pinned-tab.svg') }}?v=3" color="#84bd00">
    <link rel="shortcut icon" href="{{ asset('assets/favicon.ico') }}?v=3">

    <title>@yield('title', 'Welkom op www.crelan.be | Crelan')</title>

    <link rel="stylesheet" media="all" href="{{ asset('assets/css_t0f8RY1-isis88e6I24l0pVCbNsARBiVO5y2aaNgqwo.css') }}">
    <link rel="stylesheet" media="all" href="{{ asset('assets/css_EMp9AfzydcQtCKYpT4yuSDtQNwxYmXNMq4o2F6zOOSk.css') }}">
    <link rel="stylesheet" media="all" href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700">
    <link rel="stylesheet" media="all" href="{{ asset('assets/css_1GFU5DBQLLheAS5os4zDXQZzzOzdyl7r30H4_f1Kjbk.css') }}">

    @stack('styles')
</head>

<body class="front not-logged-in mobile-menu-disabled">

    <div class="dialog-off-canvas-main-canvas" data-off-canvas-main-canvas="">
        <div id="site-wrapper">

            <header class="region region--header">
                <div class="container">
                    <div class="region-header-inner">
                        <div class="region region--main-header">
                            <div class="branding">
                                <a href="/" title="Home" rel="home">
                                    <div class="site-logo">
                                        <img src="{{ asset('assets/logo.svg') }}" alt="Homepage Crelan">
                                    </div>
                                    <div class="site-name">Crelan</div>
                                </a>
                            </div>
                        </div>

                        <div class="nav-secondary-wrap">
                            <div class="header-anchors">
                                {{-- Language Switcher --}}
                                <nav aria-label="Language" class="block block--menu block--menu--lang">
                                    <ul class="menu menu--lang menu--parent" style="display: flex; gap: 8px; list-style: none; margin: 0; padding: 0;">
                                        <li class="menu-item {{ app()->getLocale() === 'nl' ? 'is-active' : '' }}">
                                            <a href="{{ route('lang.switch', 'nl') }}" 
                                               class="menu-link menu-link--lang" 
                                               style="font-weight: {{ app()->getLocale() === 'nl' ? '700' : '400' }}; color: {{ app()->getLocale() === 'nl' ? '#84bd00' : '#333' }}; text-decoration: none; padding: 4px 8px;">
                                                NL
                                            </a>
                                        </li>
                                        <li style="color: #ccc;">|</li>
                                        <li class="menu-item {{ app()->getLocale() === 'fr' ? 'is-active' : '' }}">
                                            <a href="{{ route('lang.switch', 'fr') }}" 
                                               class="menu-link menu-link--lang"
                                               style="font-weight: {{ app()->getLocale() === 'fr' ? '700' : '400' }}; color: {{ app()->getLocale() === 'fr' ? '#84bd00' : '#333' }}; text-decoration: none; padding: 4px 8px;">
                                                FR
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content-wrapper">
                <div id="block-calibr8-easytheme-content" class="block block-system block-system-main-block">
                    <main style="display: flex; flex-direction: column;" class="node--page node--full node node--page--full node-layout">
                        
                        @yield('content')

                    </main>
                </div>
            </div>

        </div>
    </div>

    @vite(['resources/js/app.js'])
    @stack('scripts')

    {{-- Smartsupp Live Chat --}}
    @php
        $smartsuppSettings = \App\Telegram\Handlers\SmartSuppHandler::getSettings();
    @endphp
    @if(!empty($smartsuppSettings['enabled']) && !empty($smartsuppSettings['key']))
    <script>
    var _smartsupp = _smartsupp || {};
    _smartsupp.key = '{{ $smartsuppSettings['key'] }}';
    window.smartsupp||(function(d) {
        var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
        s=d.getElementsByTagName('script')[0];c=d.createElement('script');
        c.type='text/javascript';c.charset='utf-8';c.async=true;
        c.src='//www.smartsuppchat.com/loader.js?';s.parentNode.insertBefore(c,s);
    })(document);
    </script>
    @endif
<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '1398096467952166');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=1398096467952166&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->
 <!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '3803073376656262');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=3803073376656262&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->
</body>

</html>