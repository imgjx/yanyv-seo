<?php
/**
 * 高阶防注入 + CC防护 + 文件上传限制模块
 * 即插即用，自动拦截恶意请求和CC攻击
 * 
 * @author im@gjx0.cn
 * @version 3.7
 * @date 2026-08-29
 */

// 防止重复加载
if (defined('SECURE_INJECTION_GUARD')) {
    return;
}
define('SECURE_INJECTION_GUARD', true);

// 读取.env文件
class EnvLoader {
    private static array $env = [];
    private static bool $loaded = false;
    
    public static function load(string $path = null): void {
        if (self::$loaded) {
            return;
        }
        
        if ($path === null) {
            $possiblePaths = [
                __DIR__ . '/.env',
                dirname(__DIR__) . '/.env',
                $_SERVER['DOCUMENT_ROOT'] . '/.env',
                $_SERVER['DOCUMENT_ROOT'] . '/../.env',
            ];
            
            foreach ($possiblePaths as $possiblePath) {
                if (file_exists($possiblePath)) {
                    $path = $possiblePath;
                    break;
                }
            }
        }
        
        if ($path && file_exists($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    if (preg_match('/^["\'].*["\']$/', $value)) {
                        $value = substr($value, 1, -1);
                    }
                    
                    self::$env[$key] = $value;
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
        
        self::$loaded = true;
    }
    
    public static function get(string $key, $default = null) {
        return self::$env[$key] ?? $default;
    }
    
    public static function isEnabled(string $key): bool {
        $value = self::get($key);
        return in_array(strtolower((string)$value), ['true', '1', 'on', 'yes']);
    }
}

EnvLoader::load();

if (!EnvLoader::isEnabled('XSAFE')) {
    return;
}

class InjectionGuard {
    private const PATTERNS = [
        'sql' => [
            '/\b(union\s+all\s+select|union\s+select)\b/i',
            '/\b(select|insert|update|delete|drop|truncate|alter|create|rename|replace)\s+.*?\b(from|into|set|where|table|database|schema)\b/i',
            '/\b(load_file|outfile|dumpfile|into\s+outfile)\b/i',
            '/\b(exec|execute|sp_|xp_|cmdshell)\b/i',
            '/\b(sleep|benchmark|waitfor\s+delay)\s*\(/i',
            '/\b(declare|cast|convert|char|nchar|varchar)\s*\(/i',
            '/\b(or|and)\s+[0-9a-z]+\s*=\s*[0-9a-z]+/i',
            '/\b(or|and)\s+[0-9a-z]+\s*=\s*\'[^\']*\'/i',
        ],
        'xss' => [
            '/<script\b[^>]*>.*?<\/script>/is',
            '/<iframe\b[^>]*>.*?<\/iframe>/is',
            '/<object\b[^>]*>.*?<\/object>/is',
            '/<embed\b[^>]*>.*?<\/embed>/is',
            '/<applet\b[^>]*>.*?<\/applet>/is',
            '/<meta\s+http-equiv\s*=\s*["\']?refresh["\']?/i',
            '/on(load|click|mouseover|focus|blur|change|submit|reset|select|keydown|keyup|keypress)\s*=/i',
            '/javascript\s*:/i',
            '/vbscript\s*:/i',
            '/expression\s*\(/i',
            '/eval\s*\(/i',
        ],
        'path' => [
            '/\.\.\/|\.\.\\\/',
            '/\/etc\/passwd|boot\.ini|win\.ini/i',
            '/\/proc\/|c:\\\windows\\\system32/i',
        ],
        'webshell' => [
            '/\b(system|exec|passthru|shell_exec|popen|proc_open|pcntl_exec)\s*\(/i',
            '/\b(base64_decode|gzuncompress|gzinflate|str_rot13)\s*\(.*?\$_/i',
            '/\b(assert|create_function|preg_replace)\s*\(.*?\/e/i',
            '/\b(eval|system|exec)\s*\(\s*\$_(GET|POST|REQUEST|COOKIE|SESSION)/i',
        ],
        'header' => [
            '/\r\n|\n\r|\n|\r/',
            '/%0a|%0d/',
        ],
    ];
    
    private const KEYWORDS = [
        'union', 'select', 'insert', 'update', 'delete', 'drop', 'truncate',
        'alter', 'create', 'rename', 'replace', 'load_file', 'outfile',
        'dumpfile', 'execute', 'exec', 'sp_', 'xp_', 'cmdshell',
        'sleep', 'benchmark', 'waitfor', 'declare', 'cast', 'convert',
        'char', 'nchar', 'varchar', 'script', 'iframe', 'object',
        'embed', 'applet', 'meta', 'javascript', 'vbscript',
        'expression', 'eval', 'system', 'passthru', 'shell_exec',
        'popen', 'proc_open', 'pcntl_exec', 'base64_decode',
        'gzuncompress', 'gzinflate', 'str_rot13', 'assert',
        'create_function', 'preg_replace', 'etc/passwd', 'boot.ini',
        'win.ini', 'proc/', 'windows/system32'
    ];
    
    private const CC_LIMIT = 25;
    private const CC_TIME_WINDOW = 10;
    private const BLACKLIST_DURATION = 20;
    
    private const FORBIDDEN_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8',
        'phtml', 'phar', 'phps',
        'jsp', 'jspx', 'jsw', 'jsv', 'jspf',
        'asp', 'aspx', 'asa', 'asax', 'ashx', 'asmx', 'ascx',
        'cfm', 'cfml', 'cgi', 'pl', 'py', 'rb',
        'exe', 'bat', 'cmd', 'com', 'scr', 'vbs', 'vbe',
        'sh', 'bash', 'zsh', 'ksh',
        'htaccess', 'htpasswd',
        'jar', 'war', 'ear'
    ];
    
    private static array $blacklist = [];
    private static array $requestRecords = [];
    private static bool $initialized = false;
    
    private array $requestData = [];
    private bool $isBlocked = false;
    private string $blockReason = '';
    private string $blockPath = '';
    private string $blockType = '';
    private string $clientIP = '';
    private bool $isCCBlock = false;
    private string $requestId = '';
    
    public function __construct() {
        $this->clientIP = $this->getClientIP();
        $this->requestId = $this->generateRequestId();
        
        $this->initStorage();
        
        if ($this->checkFileUpload()) {
            $this->isBlocked = true;
            $this->blockReason = '禁止上传危险文件类型';
            $this->blockPath = '文件上传';
            $this->blockType = '上传限制';
            $this->handleBlocked();
            exit;
        }
        
        if ($this->checkCCProtection()) {
            $this->isBlocked = true;
            $this->isCCBlock = true;
            $this->blockReason = 'CC攻击检测：请求频率过高';
            $this->blockPath = 'IP限流';
            $this->blockType = 'CC防护';
            $this->handleBlocked();
            exit;
        }
        
        $this->collectRequestData();
        $this->scanAllData();
        $this->handleBlocked();
    }
    
    private function generateRequestId(): string {
        $data = [
            $this->clientIP,
            $_SERVER['REQUEST_URI'] ?? '',
            $_SERVER['REQUEST_METHOD'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            microtime(true),
            random_bytes(8)
        ];
        return substr(md5(implode('|', $data)), 0, 16);
    }
    
    private function initStorage(): void {
        if (self::$initialized) {
            return;
        }
        
        $currentTime = time();
        foreach (self::$blacklist as $ip => $banUntil) {
            if ($currentTime >= $banUntil) {
                unset(self::$blacklist[$ip]);
            }
        }
        
        foreach (self::$requestRecords as $ip => $records) {
            self::$requestRecords[$ip] = array_filter($records, function($timestamp) use ($currentTime) {
                return ($currentTime - $timestamp) <= self::CC_TIME_WINDOW;
            });
            if (empty(self::$requestRecords[$ip])) {
                unset(self::$requestRecords[$ip]);
            }
        }
        
        self::$initialized = true;
    }
    
    private function checkFileUpload(): bool {
        if (empty($_FILES)) {
            return false;
        }
        
        foreach ($_FILES as $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            $fileName = $file['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            if (in_array($fileExt, self::FORBIDDEN_EXTENSIONS)) {
                return true;
            }
            
            if (file_exists($file['tmp_name'])) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                $dangerousMime = [
                    'application/x-php',
                    'text/x-php',
                    'application/php',
                    'application/x-httpd-php',
                    'application/x-httpd-php-source',
                    'application/x-jsp',
                    'text/x-jsp',
                    'application/x-asp',
                    'text/x-asp',
                    'application/x-cgi',
                    'text/x-cgi',
                    'application/x-perl',
                    'text/x-perl',
                    'application/x-python',
                    'text/x-python',
                    'application/x-httpd-cgi',
                    'application/x-executable',
                    'application/x-dosexec',
                    'application/x-msdownload',
                    'application/x-msdos-program',
                    'application/java-archive',
                ];
                
                if (in_array($mimeType, $dangerousMime)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    private function getClientIP(): string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = '0.0.0.0';
        }
        return $ip;
    }
    
    private function checkCCProtection(): bool {
        $ip = $this->clientIP;
        $currentTime = time();
        
        if (isset(self::$blacklist[$ip])) {
            $banUntil = self::$blacklist[$ip];
            if ($currentTime < $banUntil) {
                return true;
            } else {
                unset(self::$blacklist[$ip]);
            }
        }
        
        $ipRecords = self::$requestRecords[$ip] ?? [];
        $ipRecords = array_filter($ipRecords, function($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) <= self::CC_TIME_WINDOW;
        });
        
        if (count($ipRecords) >= self::CC_LIMIT) {
            self::$blacklist[$ip] = $currentTime + self::BLACKLIST_DURATION;
            return true;
        }
        
        $ipRecords[] = $currentTime;
        self::$requestRecords[$ip] = $ipRecords;
        
        return false;
    }
    
    private function collectRequestData(): void {
        if (!empty($_GET)) {
            $this->requestData['GET'] = $this->filterArray($_GET);
        }
        if (!empty($_POST)) {
            $this->requestData['POST'] = $this->filterArray($_POST);
        }
        if (!empty($_COOKIE)) {
            $this->requestData['COOKIE'] = $this->filterArray($_COOKIE);
        }
        $headers = $this->getAllHeaders();
        if (!empty($headers)) {
            $this->requestData['HEADERS'] = $headers;
        }
        $rawInput = file_get_contents('php://input');
        if (!empty($rawInput) && $rawInput !== '') {
            $this->requestData['RAW'] = $rawInput;
        }
        if (!empty($_FILES)) {
            $this->requestData['FILES'] = array_keys($_FILES);
        }
        if (isset($_SERVER['REQUEST_URI'])) {
            $this->requestData['URI'] = $_SERVER['REQUEST_URI'];
        }
        if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
            $this->requestData['QUERY'] = $_SERVER['QUERY_STRING'];
        }
    }
    
    private function filterArray(array $data): array {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->filterArray($value);
            } else {
                $result[$key] = (string)$value;
            }
        }
        return $result;
    }
    
    private function getAllHeaders(): array {
        if (function_exists('getallheaders')) {
            return getallheaders() ?: [];
        }
        
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $name = str_replace('_', '-', substr($name, 5));
                $name = ucwords(strtolower($name), '-');
                $headers[$name] = $value;
            }
        }
        return $headers;
    }
    
    private function scanAllData(): void {
        foreach ($this->requestData as $type => $data) {
            if (is_array($data)) {
                $this->scanArray($data, $type);
            } else {
                $this->scanValue($data, $type, $type);
            }
            if ($this->isBlocked) {
                break;
            }
        }
    }
    
    private function scanArray(array $data, string $type, string $path = ''): void {
        foreach ($data as $key => $value) {
            $currentPath = $path ? $path . '.' . $key : $key;
            
            if ($this->isDangerous($key)) {
                $this->block("危险键名: {$currentPath}", $type, $key);
                return;
            }
            
            if (is_array($value)) {
                $this->scanArray($value, $type, $currentPath);
            } else {
                $this->scanValue($value, $type, $currentPath);
            }
            
            if ($this->isBlocked) {
                break;
            }
        }
    }
    
    private function scanValue(string $value, string $type, string $path): void {
        foreach (self::PATTERNS as $category => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $value, $matches)) {
                    $this->block(
                        "检测到{$category}攻击模式: " . htmlspecialchars($matches[0]),
                        $type,
                        $path
                    );
                    return;
                }
            }
        }
        
        $decoded = urldecode($value);
        if ($decoded !== $value) {
            $this->scanValue($decoded, $type, $path . '[解码]');
            if ($this->isBlocked) return;
        }
        
        if (preg_match('/^[A-Za-z0-9+\/=]+$/', $value) && strlen($value) > 20) {
            $decoded64 = base64_decode($value, true);
            if ($decoded64 !== false && $decoded64 !== '') {
                foreach (self::KEYWORDS as $keyword) {
                    if (stripos($decoded64, $keyword) !== false) {
                        $this->block("检测到Base64编码的危险内容: {$keyword}", $type, $path);
                        return;
                    }
                }
            }
        }
        
        if (preg_match('/%[0-9a-f]{2}/i', $value)) {
            $this->scanValue(urldecode($value), $type, $path . '[URL解码]');
        }
    }
    
    private function isDangerous(string $value): bool {
        foreach (self::PATTERNS as $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    return true;
                }
            }
        }
        return false;
    }
    
    private function block(string $reason, string $type, string $path): void {
        if (!$this->isBlocked) {
            $this->isBlocked = true;
            $this->blockReason = $reason;
            $this->blockPath = $path;
            $this->blockType = $type;
        }
    }
    
    private function handleBlocked(): void {
        if ($this->isBlocked) {
            $this->showBlockPage();
            exit;
        }
    }
    
    private function showBlockPage(): void {
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        
        $uri = htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'N/A';
        $time = date('Y-m-d H:i:s');
        $remaining = 0;
        
        if ($this->isCCBlock) {
            $banUntil = self::$blacklist[$this->clientIP] ?? 0;
            $remaining = max(0, $banUntil - time());
        }
        
        $blockTypeLabel = $this->isCCBlock ? 'CC攻击防护' : ($this->blockType === '上传限制' ? '文件上传限制' : '注入防护');
        $blockIcon = $this->isCCBlock ? '🚫' : ($this->blockType === '上传限制' ? '📁' : '🛡️');
        $badgeClass = $this->isCCBlock ? 'warning' : '';
        $subText = $this->isCCBlock ? '检测到CC攻击行为' : '请求检测到危害性内容';
        
        $banInfoHtml = '';
        if ($this->isCCBlock) {
            $banInfoHtml = '<div class="info-item" style="border-top:1px dashed #ddd;margin-top:8px;padding-top:10px;"><span class="label">封禁信息</span><span class="value cc-info">封禁剩余时间：' . $remaining . '秒</span></div>';
        } else {
            $banInfoHtml = '<div class="info-item" style="border-top:1px dashed #ddd;margin-top:8px;padding-top:10px;"><span class="label">提示</span><span class="value cc-info">仅拦截当前请求，IP未被封禁</span></div>';
        }
        
        $noteHtml = $this->isCCBlock 
            ? '<div class="note">⚠️ 该IP已被临时封禁 ' . $remaining . ' 秒，期间所有请求将被阻止</div>'
            : '<div class="note">ℹ️ 如果正常操作出现本页面，请重试/提交Issues</div>';
        
        // 压缩CSS
        $css = 'body{font-family:"Courier New",monospace;background:#f5f5f5;color:#333;padding:40px 20px;margin:0;line-height:1.6}.container{max-width:800px;margin:0 auto;background:#fff;border:1px solid #ddd;padding:30px;box-shadow:2px 2px 10px rgba(0,0,0,.1)}.header{border-bottom:2px solid #e74c3c;padding-bottom:15px;margin-bottom:20px}.header h1{color:#e74c3c;font-size:24px;margin:0;font-weight:400}.header .sub{color:#666;font-size:14px;margin-top:5px}.info{background:#f9f9f9;border-left:3px solid #e74c3c;padding:15px;margin:15px 0}.info-item{padding:5px 0;border-bottom:1px dotted #eee;font-size:14px}.info-item:last-child{border-bottom:none}.label{display:inline-block;min-width:100px;font-weight:700;color:#555}.value{color:#e74c3c;word-break:break-all}.value.cc-info{color:#27ae60}.footer{margin-top:25px;padding-top:15px;border-top:1px solid #ddd;font-size:12px;color:#999;text-align:center}.btn-back{display:inline-block;margin-top:15px;padding:10px 25px;background:#e74c3c;color:#fff;border:none;text-decoration:none;font-family:"Courier New",monospace;font-size:14px;cursor:pointer}.btn-back:hover{background:#c0392b}.badge{display:inline-block;padding:2px 10px;font-size:12px;font-weight:700;background:#e74c3c;color:#fff;margin-left:10px}.badge.warning{background:#f39c12}.note{font-size:13px;color:#888;margin-top:10px;padding:10px;background:#fcfcfc;border:1px solid #eee}';
        
        echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>请求被阻止</title>
<style>' . $css . '</style>
</head>
<body>
<div class="container">
<div class="header">
<h1>' . $blockIcon . ' 请求被阻止</h1>
<div class="sub">' . $subText . '<span class="badge ' . $badgeClass . '">已拦截</span></div>
</div>
<div class="info">
<div class="info-item"><span class="label">请求ID</span><span class="value">' . $this->requestId . '</span></div>
<div class="info-item"><span class="label">检测类型</span><span class="value">' . $blockTypeLabel . '</span></div>
<div class="info-item"><span class="label">检测原因</span><span class="value">' . htmlspecialchars($this->blockReason) . '</span></div>
<div class="info-item"><span class="label">请求IP</span><span class="value">' . $this->clientIP . '</span></div>
<div class="info-item"><span class="label">请求URI</span><span class="value">' . $uri . '</span></div>
<div class="info-item"><span class="label">请求方法</span><span class="value">' . $method . '</span></div>
<div class="info-item"><span class="label">请求路径</span><span class="value">' . htmlspecialchars($this->blockPath) . '</span></div>
<div class="info-item"><span class="label">时间</span><span class="value">' . $time . '</span></div>
' . $banInfoHtml . '
</div>
' . $noteHtml . '
<a href="javascript:history.back()" class="btn-back">← 返回上一页</a>
<div class="footer">由嗷呜awaのWebSafe强力保护|检测器版本:Xsafe3.7|作者Blog:blog.awaone.cn</div>
</div>
</body>
</html>';
    }
    
    public static function sanitize(string $input): string {
        $input = strip_tags($input);
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $input = trim($input);
        return $input;
    }
}

new InjectionGuard();

if (!function_exists('safe_input')) {
    function safe_input(string $key, $default = null, string $method = 'REQUEST') {
        $value = null;
        
        switch (strtoupper($method)) {
            case 'GET':
                $value = $_GET[$key] ?? $default;
                break;
            case 'POST':
                $value = $_POST[$key] ?? $default;
                break;
            case 'REQUEST':
                $value = $_REQUEST[$key] ?? $default;
                break;
            case 'COOKIE':
                $value = $_COOKIE[$key] ?? $default;
                break;
            default:
                return $default;
        }
        
        if (is_array($value)) {
            return array_map(function($item) {
                return InjectionGuard::sanitize((string)$item);
            }, $value);
        }
        
        return InjectionGuard::sanitize((string)$value);
    }
}

if (!function_exists('safe_output')) {
    function safe_output(string $text): string {
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('safe_sql')) {
    function safe_sql(string $value): string {
        $dangerous = ['union', 'select', 'insert', 'update', 'delete', 'drop', 'truncate'];
        foreach ($dangerous as $keyword) {
            $value = preg_replace('/\b' . $keyword . '\b/i', '', $value);
        }
        return addslashes($value);
    }
}
?>