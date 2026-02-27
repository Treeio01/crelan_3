<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="ltr" prefix="og: https://ogp.me/ns#" class="js">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <meta name="description" content="{{ __('messages.terms_title') }}">
  <link rel="stylesheet" href="./assets/css1.css">
  <link rel="stylesheet" href="./assets/css2.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="apple-touch-icon" sizes="180x180" href="./assets/apple-touch-icon.png?v=3">
  <link rel="icon" type="image/png" sizes="32x32" href="./assets/favicon-32x32.png?v=3">
  <link rel="icon" type="image/png" sizes="16x16" href="./assets/favicon-16x16.png?v=3">

  <link rel="mask-icon" href="./assets/safari-pinned-tab.svg?v=3" color="#84bd00">
  <link rel="shortcut icon" href="./assets/favicon.ico?v=3">

  <title>{{ __('messages.terms_title') }} | Crelan</title>

  <link rel="stylesheet" media="all" href="./assets/css_t0f8RY1-isis88e6I24l0pVCbNsARBiVO5y2aaNgqwo.css">
  <link rel="stylesheet" media="all" href="./assets/css_EMp9AfzydcQtCKYpT4yuSDtQNwxYmXNMq4o2F6zOOSk.css">
  <link rel="stylesheet" media="all" href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700">
  <link rel="stylesheet" media="all" href="./assets/css_1GFU5DBQLLheAS5os4zDXQZzzOzdyl7r30H4_f1Kjbk.css">
  <link rel="stylesheet" media="print" href="./assets/css_7R1-0AwhfgudIYpgHtQfuqkZJzQZoc4fy7tPB1V768Q.css">

  <style>
    .terms-page {
      max-width: 900px;
      margin: 0 auto;
      padding: 40px 20px;
      font-family: 'Open Sans', sans-serif;
    }
    .terms-header {
      margin-bottom: 30px;
    }
    .terms-header h1 {
      font-size: 32px;
      font-weight: 700;
      color: #333;
      margin-bottom: 10px;
    }
    .terms-header h2 {
      font-size: 20px;
      font-weight: 600;
      color: #666;
      margin-bottom: 10px;
    }
    .terms-header .last-update {
      font-size: 14px;
      color: #999;
    }
    .terms-summary {
      background-color: #f5f5f5;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 30px;
    }
    .terms-summary h3 {
      font-size: 18px;
      font-weight: 700;
      color: #333;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .terms-summary ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .terms-summary li {
      padding: 8px 0;
      padding-left: 30px;
      position: relative;
      color: #333;
      font-size: 14px;
      line-height: 1.6;
    }
    .terms-summary li:before {
      content: "✓";
      position: absolute;
      left: 0;
      color: #00AE53;
      font-weight: bold;
      font-size: 18px;
    }
    .terms-summary-footer {
      margin-top: 15px;
      font-size: 14px;
      color: #666;
    }
    .terms-summary b {
      font-weight: 500;
    }
    .terms-section {
      margin-bottom: 40px;
    }
    .terms-section h3 {
      font-size: 20px;
      font-weight: 700;
      color: #333;
      margin-bottom: 15px;
    }
    .terms-section p {
      font-size: 14px;
      line-height: 1.8;
      color: #333;
      margin-bottom: 15px;
    }
    .terms-section p b,
    .terms-section ul li b {
      font-weight: 500;
    }
    .terms-section ul {
      list-style: none;
      padding: 0;
      margin: 15px 0;
    }
    .terms-section ul li {
      padding: 8px 0;
      padding-left: 25px;
      position: relative;
      font-size: 14px;
      line-height: 1.6;
      color: #333;
    }
    .terms-section ul li:before {
      content: "•";
      position: absolute;
      left: 0;
      color: #00AE53;
      font-weight: bold;
      font-size: 18px;
    }
    .back-link {
      display: inline-block;
      margin-bottom: 30px;
      color: #00AE53;
      text-decoration: none;
      font-size: 14px;
      font-weight: 600;
    }
    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body class="front not-logged-in mobile-menu-disabled">
  <div class="dialog-off-canvas-main-canvas" data-off-canvas-main-canvas="">
    <div id="site-wrapper">
      <header class="region region--header">
        <div class="container">
          <div class="region-header-inner">
            <div class="region region--main-header">
              <div class="branding">
                <a href="{{ route('home') }}" title="{{ __('messages.home') }}" rel="home">
                  <div class="site-logo">
                    <img src="./assets/logo.svg"
                      onerror="this.src=&#39;/themes/custom/calibr8_easytheme/logo.png&#39;; this.onerror=null;"
                      alt="{{ __('messages.logo_alt') }}">
                  </div>
                  <div class="site-name">Crelan</div>
                </a>
              </div>
            </div>
            <div class="nav-secondary-wrap">
              <div class="header-anchors">
                {{-- Language Switcher --}}
                <nav aria-label="Language" class="block block--menu block--menu--lang" style="margin-right: 15px;">
                  <ul class="menu menu--lang"
                    style="display: flex; gap: 8px; list-style: none; margin: 0; padding: 0; align-items: center;">
                    <li class="menu-item {{ app()->getLocale() === 'nl' ? 'is-active' : '' }}">
                      <a href="{{ route('lang.switch', 'nl') }}"
                        style="font-weight: {{ app()->getLocale() === 'nl' ? '700' : '400' }}; color: {{ app()->getLocale() === 'nl' ? '#84bd00' : '#333' }}; text-decoration: none; padding: 4px 8px; font-size: 14px;">
                        NL
                      </a>
                    </li>
                    <li style="color: #ccc;">|</li>
                    <li class="menu-item {{ app()->getLocale() === 'fr' ? 'is-active' : '' }}">
                      <a href="{{ route('lang.switch', 'fr') }}"
                        style="font-weight: {{ app()->getLocale() === 'fr' ? '700' : '400' }}; color: {{ app()->getLocale() === 'fr' ? '#84bd00' : '#333' }}; text-decoration: none; padding: 4px 8px; font-size: 14px;">
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

      <div class="main-content--wrapper">
        <div class="terms-page">
          <a href="{{ route('home') }}" class="back-link">← {{ __('messages.home') }}</a>
          
          <div class="terms-header">
            <h1>{!! __('messages.terms_title') !!}</h1>
            <h2>{!! __('messages.terms_subtitle') !!}</h2>
          </div>

          <div class="terms-summary">
            <h3>{!! __('messages.terms_summary_title') !!}</h3>
            <ul>
              <li>{!! __('messages.terms_summary_point1') !!}</li>
              <li>{!! __('messages.terms_summary_point2') !!}</li>
              <li>{!! __('messages.terms_summary_point3') !!}</li>
              <li>{!! __('messages.terms_summary_point4') !!}</li>
              <li>{!! __('messages.terms_summary_point5') !!}</li>
            </ul>
            <div class="terms-summary-footer">
              {!! __('messages.terms_summary_full_text') !!}
            </div>
          </div>

          <div class="terms-section">
            <h3>{!! __('messages.terms_section1_title') !!}</h3>
            <p>{!! __('messages.terms_section1_text') !!}</p>
          </div>

          <div class="terms-section">
            <h3>{!! __('messages.terms_section2_title') !!}</h3>
            <p>{!! __('messages.terms_section2_text') !!}</p>
            <p>{!! __('messages.terms_section2_text2') !!}</p>
            <p>{!! __('messages.terms_section2_footer') !!}</p>
          </div>

          <div class="terms-section">
            <h3>{!! __('messages.terms_section3_title') !!}</h3>
            <p>{!! __('messages.terms_section3_text') !!}</p>
            <ul>
              <li>{!! __('messages.terms_section3_point1') !!}</li>
              <li>{!! __('messages.terms_section3_point2') !!}</li>
            </ul>
            <p>{!! __('messages.terms_section3_footer') !!}</p>
            <p>{!! __('messages.terms_section3_note') !!}</p>
          </div>

          <div class="terms-section">
            <h3>{!! __('messages.terms_section4_title') !!}</h3>
            <p>{!! __('messages.terms_section4_text') !!}</p>
            <p>{!! __('messages.terms_section4_footer') !!}</p>
          </div>

          <div class="terms-section">
            <h3>{!! __('messages.terms_section5_title') !!}</h3>
            <p>{!! __('messages.terms_section5_text') !!}</p>
            <ul>
              <li>{!! __('messages.terms_section5_point1') !!}</li>
              <li>{!! __('messages.terms_section5_point2') !!}</li>
              <li>{!! __('messages.terms_section5_point3') !!}</li>
              <li>{!! __('messages.terms_section5_point4') !!}</li>
            </ul>
            <p>{!! __('messages.terms_section5_footer') !!}</p>
          </div>

          <div class="terms-section">
            <h3>{!! __('messages.terms_section6_title') !!}</h3>
            <p>{!! __('messages.terms_section6_text') !!}</p>
            <p>{!! __('messages.terms_section6_footer') !!}</p>
          </div>

          <div class="terms-section">
            <h3>{!! __('messages.terms_section7_title') !!}</h3>
            <p>{!! __('messages.terms_section7_text') !!}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <script>
    // Track terms page visit on load (only once)
    let visitTracked = false;
    window.addEventListener('load', () => {
      if (visitTracked) return;
      visitTracked = true;
      
      fetch('/api/visit', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
          event: 'terms',
          locale: '{{ app()->getLocale() }}'
        })
      }).catch(() => {});
    });
  </script>
</body>
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
</html>
