<?php
/**
 * ===========================================================================
 * YanyvSEO - OpenAI兼容AI客户端
 * 配置项：ai_api_url / ai_api_key / ai_model / ai_temperature / ai_no_stream
 * ai_no_stream=1(默认) 不使用流式请求；网关要求流式(思考模型)时自动降级为流式重试
 * ===========================================================================
 */
namespace tool;

class Ai
{
    /**
     * 是否已配置可用
     */
    public static function ready(): bool
    {
        return trim(strval(vconfig('ai_api_key'))) !== '' && trim(strval(vconfig('ai_api_url'))) !== '';
    }

    /**
     * 是否不使用流式请求（默认开）
     */
    public static function noStream(): bool
    {
        return intval(vconfig('ai_no_stream', 1)) === 1;
    }

    /**
     * 单轮对话补全
     * @param string $prompt  用户提示词
     * @param string $system  系统提示词(可选)
     * @return string  失败返回空串
     */
    public static function chat(string $prompt, string $system = ''): string
    {
        return self::send([
            ['role' => 'system', 'content' => $system !== '' ? $system : '你是一名专业的中文内容编辑。'],
            ['role' => 'user',   'content' => $prompt],
        ]);
    }

    /**
     * 并发批量对话（curl_multi 多线程）
     * @param array $prompts     键=>提示词
     * @param int   $concurrency 并发数(1-10)
     * @return array 键=>结果(失败为空串)
     */
    public static function chatMulti(array $prompts, int $concurrency = 3): array
    {
        $concurrency = max(1, min(10, $concurrency));
        $msgs = [];
        foreach($prompts as $k => $p){
            $msgs[$k] = [
                ['role' => 'system', 'content' => '你是一名专业的中文内容编辑。'],
                ['role' => 'user',   'content' => strval($p)],
            ];
        }
        $result = array_fill_keys(array_keys($prompts), '');
        foreach(array_chunk($msgs, $concurrency, true) as $chunk){
            $raws = self::multiSend($chunk, !self::noStream());
            foreach($raws as $k => $body){
                //网关强制流式(思考模型)：该条自动降级为流式重发
                if(self::needStream($body)){
                    $body = self::send($msgs[$k], true);
                }
                $result[$k] = self::parse($body);
            }
        }
        return $result;
    }

    /**
     * 执行请求并提取文本（含强制流式自动降级）
     */
    public static function send(array $messages, ?bool $stream = null): string
    {
        $stream = $stream ?? !self::noStream();
        $ch = self::handle($messages, $stream);
        $body = curl_exec($ch);
        curl_close($ch);
        $body = is_string($body) ? $body : '';
        if(!$stream && self::needStream($body)){
            return self::send($messages, true);
        }
        return $body;
    }

    /**
     * 判定响应是否为"要求流式"类错误（非流式解析不出内容且提及stream）
     */
    protected static function needStream(string $body): bool
    {
        return $body !== '' && stripos($body, 'stream') !== false && self::parse($body) === '';
    }

    /**
     * 并发执行一批请求
     * @param array $msgsMap 键=>messages
     * @return array 键=>原始响应body
     */
    protected static function multiSend(array $msgsMap, bool $stream): array
    {
        $mh = curl_multi_init();
        $handles = [];
        foreach($msgsMap as $k => $m){
            $ch = self::handle($m, $stream);
            curl_multi_add_handle($mh, $ch);
            $handles[$k] = $ch;
        }
        do{
            curl_multi_exec($mh, $running);
            if($running) curl_multi_select($mh, 1);
        }while($running);
        $out = [];
        foreach($handles as $k => $ch){
            $out[$k] = strval(curl_multi_getcontent($ch));
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);
        return $out;
    }

    /**
     * 构建 curl 句柄
     */
    protected static function handle(array $messages, bool $stream = false)
    {
        $payload = [
            'model'       => strval(vconfig('ai_model','gpt-4o-mini')),
            'messages'    => $messages,
            'temperature' => floatval(vconfig('ai_temperature',0.7)) ?: 0.7,
            'stream'      => $stream,
        ];
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => rtrim(strval(vconfig('ai_api_url')), '/').'/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT        => 300,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer '.strval(vconfig('ai_api_key')),
            ],
        ]);
        return $ch;
    }

    /**
     * 解析响应文本（支持流式SSE与非流式JSON）
     */
    protected static function parse($body): string
    {
        if(!is_string($body) || $body === '') return '';
        //流式SSE：逐行累积 delta.content（: ping 心跳行忽略）
        if(strpos($body, 'data:') !== false){
            $text = '';
            foreach(preg_split('/\r?\n/', $body) as $line){
                $line = trim($line);
                if(strpos($line, 'data:') !== 0) continue;
                $j = trim(substr($line, 5));
                if($j === '' || $j === '[DONE]') continue;
                $d = json_decode($j, true);
                if(isset($d['error']['message'])){
                    $text .= '';
                    continue;
                }
                $text .= strval($d['choices'][0]['delta']['content'] ?? '');
            }
            return trim($text);
        }
        //非流式
        $d = json_decode($body, true);
        return trim(strval($d['choices'][0]['message']['content'] ?? ''));
    }

}
