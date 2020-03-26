<?php
ini_set('error_reporting', E_ALL);

/* reserve.php */
// 引用 Chinwei6/LinePay PHP Library

// 建立訂單資訊作為 POST 的參數
// $params = json_encode($params) ;
// 發送 reserve 請求，reserve() 回傳的結果為 Associative Array 格式
$successCallback = "https://linepay.zwa886.com/success.php" ;
$params ='{"amount":1500,"currency":"TWD","orderId":"MKSI_S_20180904_1000001","packages":[{"id":"1","amount":1500,"products":[{"id":"productsId-001","name":"結帳金額","imageUrl":"https://www.hotzsoft.com/userfiles/images/%E9%A6%96%E9%A0%81/logo768.jpg","quantity":1,"price":1500}]}],"redirectUrls":{"confirmUrl":"'.$successCallback.'","cancelUrl":"https://www.amazon.com/"}}';
$requestApi = "/v3/payments/request" ;
$result = go($requestApi,$params);
$infos = json_decode($result,true) ;
/*  
LINE Pay商家支援小組。
該LINE Pay Sandbox ID如下：
ID : test_202003123125@line.pay
PW : test_202003123125  
如果您需要管理您自己伺服器的ACL(Access Control List),請註冊以下的LINE Pay server IP:
211.249.40.1~
211.249.40.30

Channel ID  1653944678
Channel Secret Key  81b9c41c87cf56452bcf107c1ce53998

如果您想檢查交易結果，請登入您的商家後台後使用管理交易頁面下的交易紀錄搜尋交易功能。
*/

?>
<!DOCTYPE html>
<html>
<head>
  <title></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="height: 100%;height: 100%;text-align: center;">
  <div>
    <h1> 前往 LINE PAY <br>付款頁面</h1>
        <button onclick='window.location.href="<?=$infos['info']['paymentUrl']['web']?>"'
                style="margin:1rem;font-size: 2rem"> web 
        </button>
        <button onclick='window.location.href="<?=$infos['info']['paymentUrl']['app']?>"'
                style="margin:1rem;font-size: 2rem"> app 
        </button>
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