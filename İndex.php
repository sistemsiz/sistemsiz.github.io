<?php
$public_ip = @file_get_contents('https://api.ipify.org') ?: 'Public IP alınamadı';
$local_ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];

function detectDevice($user_agent) {
    if (stripos($user_agent, 'iPhone') !== false) {
        return 'iPhone';
    } elseif (stripos($user_agent, 'iPad') !== false) {
        return 'iPad';
    } elseif (stripos($user_agent, 'Android') !== false) {
        preg_match('/Android.*; (.+?) Build/', $user_agent, $matches);
        return isset($matches[1]) ? $matches[1] : 'Android Cihaz';
    } elseif (stripos($user_agent, 'Windows') !== false) {
        return 'Windows PC';
    } elseif (stripos($user_agent, 'Mac') !== false) {
        return 'Mac';
    } else {
        return 'Bilinmeyen Cihaz';
    }
}

$device = detectDevice($user_agent);

function detectOSVersion($user_agent) {
    if (stripos($user_agent, 'Android') !== false) {
        preg_match('/Android ([\d.]+)/', $user_agent, $matches);
        return isset($matches[1]) ? 'Android ' . $matches[1] : 'Versiyon Bilgisi Yok';
    } elseif (stripos($user_agent, 'iPhone') !== false || stripos($user_agent, 'iPad') !== false) {
        preg_match('/OS ([\d_]+)/', $user_agent, $matches);
        return isset($matches[1]) ? 'iOS ' . str_replace('_', '.', $matches[1]) : 'Versiyon Bilgisi Yok';
    } elseif (stripos($user_agent, 'Windows NT') !== false) {
        preg_match('/Windows NT ([\d.]+)/', $user_agent, $matches);
        return isset($matches[1]) ? 'Windows ' . $matches[1] : 'Versiyon Bilgisi Yok';
    }
    return 'Versiyon Bilgisi Yok';
}

function detectConnectionType($ip, $user_agent) {
    $json = @file_get_contents("http://ipinfo.io/{$ip}/json");
    if ($json) {
        $data = json_decode($json, true);
        if (isset($data['org']) && strpos(strtolower($data['org']), 'mobile') !== false) {
            return 'Mobil Veri';
        }
    }

    if (stripos($user_agent, 'Mobile') !== false) {
        return 'Mobil Veri';
    }

    return 'Wi-Fi';
}

function detectCity($ip) {
    $json = @file_get_contents("http://ipinfo.io/{$ip}/json");
    if ($json) {
        $data = json_decode($json, true);
        return isset($data['city']) ? $data['city'] : 'Şehir bilgisi alınamadı';
    }
    return 'Şehir bilgisi alınamadı';
}

function detectVPN($ip) {
    $json = @file_get_contents("http://ipinfo.io/{$ip}/json");
    if ($json) {
        $data = json_decode($json, true);
        if (isset($data['org']) && (strpos(strtolower($data['org']), 'vpn') !== false || strpos(strtolower($data['org']), 'proxy') !== false)) {
            return true;
        }
    }
    return false;
}

function detectLeague($ip) {
    $json = @file_get_contents("http://ipinfo.io/{$ip}/json");
    if ($json) {
        $data = json_decode($json, true);
        return isset($data['org']) ? $data['org'] : 'Lig bilgisi alınamadı';
    }
    return 'Lig bilgisi alınamadı';
}

function detectMobileOperator($ip) {
    $json = @file_get_contents("http://ipinfo.io/{$ip}/json");
    if ($json) {
        $data = json_decode($json, true);
        return isset($data['org']) ? $data['org'] : 'Operatör bilgisi alınamadı';
    }
    return 'Operatör bilgisi alınamadı';
}

$is_vpn = detectVPN($public_ip);
$league = detectLeague($public_ip);
$city = detectCity($public_ip);
$connection_type = detectConnectionType($public_ip, $user_agent);
$file = 'ips.txt';

$log = date("Y-m-d H:i:s") . PHP_EOL;
$log .= "Public IP: " . $public_ip . PHP_EOL;
$log .= "Tarayıcı: " . $user_agent . PHP_EOL;
$log .= "Local IP: " . $local_ip . PHP_EOL;
$log .= "Cihaz: " . $device . PHP_EOL;
$log .= "Cihaz OS: " . detectOSVersion($user_agent) . PHP_EOL;
$log .= "Bağlantı Türü: " . $connection_type . PHP_EOL;
$log .= "VPN: " . ($is_vpn ? 'Evet' : 'Hayır') . PHP_EOL;
$log .= "Lig: " . $league . PHP_EOL;
$log .= "Şehir: " . $city . PHP_EOL;
$log .= detectMobileOperator($public_ip) . PHP_EOL;
$log .= str_repeat("-", 25) . PHP_EOL;

if (!file_put_contents($file, $log, FILE_APPEND | LOCK_EX)) {
    echo "";
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistemsiz</title>
    <meta name="description" content="Sistemsiz'e giriş yapın. Hızlı ve kolay erişim için Giriş'e basın.">
    <meta name="keywords" content="sistemsiz, giriş, erişim, sistem, site">
    <link rel="icon" href="logo.png" type="image/png">
    <style>
        body {
            background-color: black;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            color: white;
            text-align: center; 
        }

        .logo {
            position: absolute;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            width: 200px; 
            z-index: 1;
        }

        .text {
            font-size: 3em;
            opacity: 0;
            animation: fadeIn 50s forwards;
            cursor: pointer;
            z-index: 0;
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        .note {
            font-size: 1.2em;
            color: #aaa;
            margin-top: 10px;
            opacity: 0;
            animation: fadeIn 0s 0s forwards; 
        }

        @media (max-width: 600px) {
            .text {
                font-size: 2.5em; 
            }
            .note {
                font-size: 1em; 
            }
            .logo {
                width: 150px;
            }
        }
    </style>
</head>
<body>
    <img src="logo.png" alt="Sistemsiz" class="logo"> 
    <div class="text" id="linkText"></div>
    <div class="note" id="noteText">Sisteme giriş yapmak için, yukarıdan ↑ Giriş'e basın ve ardından Visit Site butonuna basın, ve içeridesiniz..</div>
   <a style="display:block;font-size:16px;font-weight:500;text-align:center;border-radius:8px;padding:5px;background:transparent;text-decoration:none;color:#fff;" href="https://t.me/systemsiz" target="_blank">
    <svg style="width:30px;height:20px;vertical-align:middle;margin:0px 5px;" viewBox="0 0 21 18">
        <g fill="none">
            <path fill="#389ce9" d="M0.554,7.092 L19.117,0.078 C19.737,-0.156 20.429,0.156 20.663,0.776 C20.745,0.994 20.763,1.23 20.713,1.457 L17.513,16.059 C17.351,16.799 16.62,17.268 15.88,17.105 C15.696,17.065 15.523,16.987 15.37,16.877 L8.997,12.271 C8.614,11.994 8.527,11.458 8.805,11.074 C8.835,11.033 8.869,10.994 8.905,10.958 L15.458,4.661 C15.594,4.53 15.598,4.313 15.467,4.176 C15.354,4.059 15.174,4.037 15.036,4.125 L6.104,9.795 C5.575,10.131 4.922,10.207 4.329,10.002 L0.577,8.704 C0.13,8.55 -0.107,8.061 0.047,7.614 C0.131,7.374 0.316,7.182 0.554,7.092 Z"></path>
        </g>
    </svg>Telegram
</a>
    <script>
        const text = "Giriş";
        const link = "https://46bc-2a09-bac1-72c0-18-00-150-8f.ngrok-free.app/"; 
        const fallbackLink = 'http://192.168.1.12';
        const textContainer = document.getElementById('linkText');

        text.split('').forEach((letter, index) => {
            const span = document.createElement('span');
            span.className = 'letter';
            span.style.animationDelay = `${index * 0.2}s`;
            span.textContent = letter;
            textContainer.appendChild(span);
        });

        textContainer.onclick = async () => { 
            try {
                const response = await fetch(link);
                if (!response.ok) {
                    throw new Error('Response not OK');
                }
                window.location.href = link;
            } catch (error) {
                const errorMessage = error.message;
                if (errorMessage.includes('ERR_NGROK_3200') || errorMessage.includes('ERR_NGROK_3004')) {
                    window.location.href = fallbackLink;
                } else {
                    window.location.href = fallbackLink;
                }
            }
        };
    </script>
</body>
</html>
