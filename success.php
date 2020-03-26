<?php 
ini_set('error_reporting', E_ALL);

$orderId = $_GET['orderId'] ;
$transactionId = $_GET['transactionId'] ;

?>
<!DOCTYPE html>
<html>
<head>
  <title>完成交易</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="height: 100%;height: 100%;text-align: center;font-size: 1.4rem">
  <div>
    <h1> 買家付款成功!!</h1>
      <form method="post" action="confirm.php">
        訂單編號 : <input type="txst"
                           name="orderId"
                           value="<?=$orderId?>"
                           style="height:1.8rem;font-size:1.4rem" disabled><br><br>
        交易序號 : <input type="text"
                           name="transactionId"
                           value="<?=$transactionId?>"
                           style="height:1.8rem;font-size:1.4rem" readonly><br><br>
        訂單金額 : <input type="text"
                           name="amount"
                           value="1350"
                           style="height:1.8rem;font-size:1.4rem"><br><br>
        <hr>
        <br>
        <input class="btn-line"
               type="submit"
               style="margin:1rem 1rem 0.5rem;font-size: 2rem"
               value="完成交易"
               ><br>(因應分段執行 可省略)
      </form>

  </div>
</body>
</html>
<?php
function go ($uri, $params = '{}' ){
  $apiUrl = "https://sandbox-api-pay.line.me" ; // 測試 LINE提供的測試環境
  $url = $apiUrl.$uri ;
// $apiUrl = "https://api-pay.line.me"; // 正式 LINE的正式環境
  $channelId     = "1653944678"  ; // 通路ID
  $channelSecret = "81b9c41c87cf56452bcf107c1ce53998" ;  // 通路密鑰

  $ch = curl_init() ;
  $uuid = gen_uuid() ;
  $meg = $channelSecret.$uri.$params.$uuid ;
  $signature = signature('post',$meg,$channelSecret) ;
  $header = array(
    'Content-Type: application/json; charset=UTF-8'
    , 'X-LINE-ChannelId: '.$channelId
    , 'X-LINE-Authorization-Nonce: '.$uuid
    , 'X-LINE-Authorization: '.$signature
  );

  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    // 要求结果为字符串且输出到屏幕上
  // curl_setopt($ch, CURLOPT_HEADER, 0);            // 不要http header 加快效率
  // curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
  // curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0');

  curl_setopt($ch, CURLOPT_TIMEOUT, 15);

  // ebay -> 用純get
  // amazon -> 要打開
  // post 提交方式
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $params );

  // https请求 不验证证书和hosts
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

  // withcookie
  // curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookie_jar'); // 讀取
  // curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookie_jar'); // 寫入

  $output = curl_exec($ch);
  curl_close($ch);
  return $output ;
}

function gen_uuid() {
  return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    // 32 bits for "time_low"
    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

    // 16 bits for "time_mid"
    mt_rand( 0, 0xffff ),

    // 16 bits for "time_hi_and_version",
    // four most significant bits holds version number 4
    mt_rand( 0, 0x0fff ) | 0x4000,

    // 16 bits, 8 bits for "clk_seq_hi_res",
    // 8 bits for "clk_seq_low",
    // two most significant bits holds zero and one for variant DCE1.1
    mt_rand( 0, 0x3fff ) | 0x8000,

    // 48 bits for "node"
    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
  );
}

function signature($type,$meg,$secret) {
  if( strtolower($type)=='post') {
    $tmp = hash_hmac('sha256', $meg, $secret, true) ;
    return base64_encode($tmp) ;
  }
}
