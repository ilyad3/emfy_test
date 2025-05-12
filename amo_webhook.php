<?php
$data = $_REQUEST;
$file = fopen("12.txt","w");
fwrite($file,http_build_query($data));
fclose($file);
$code = $data['code'];
$client_id = $data['client_id'];
$referer = $data['referer'];
$link = 'https://'.$referer.'/oauth2/access_token'; //Формируем URL для запроса
$subdomain_array = explode(".",$referer);
$subdomain = $subdomain_array[0];

$secret = "5egbibK9z9gnNAvFYQD3LuRABUPJRj87iOIvpZteuqGxDg6tMhkJPtvPLsJ75eTb";
/** Соберем данные для запроса */
$data = [
	'client_id' => $client_id,
	'client_secret' => $secret,
	'grant_type' => 'authorization_code',
	'code' => $code,
	'redirect_uri' => 'http://52ea85a7d358.vps.myjino.ru/amo_webhook.php',
];
//print_r(json_encode($data));
//echo $link;
/**
 * Нам необходимо инициировать запрос к серверу.
 * Воспользуемся библиотекой cURL (поставляется в составе PHP).
 * Вы также можете использовать и кроссплатформенную программу cURL, если вы не программируете на PHP.
 */
$curl = curl_init(); //Сохраняем дескриптор сеанса cURL
/** Устанавливаем необходимые опции для сеанса cURL  */
curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
curl_setopt($curl,CURLOPT_URL, $link);
curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
curl_setopt($curl,CURLOPT_HEADER, false);
curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
$out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo "<br>";print_r($out);echo"<br>";

/** Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
$code = (int)$code;
$errors = [
	400 => 'Bad request',
	401 => 'Unauthorized',
	403 => 'Forbidden',
	404 => 'Not found',
	500 => 'Internal server error',
	502 => 'Bad gateway',
	503 => 'Service unavailable',
];

try
{
	/** Если код ответа не успешный - возвращаем сообщение об ошибке  */
	if ($code < 200 || $code > 204) {
		throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
	}
}
catch(\Exception $e)
{
	die('Ошибка АВТОРИЗАЦИИ AMOCRM: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
}

/**
 * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
 * нам придётся перевести ответ в формат, понятный PHP
 */
$response = json_decode($out, true);
$expires_in = time()+$response['expires_in'];
$access_token =  $response['access_token'];
$refresh_token =  $response['refresh_token'];
$redirect_uri = 'http://52ea85a7d358.vps.myjino.ru/amo_webhook.php';
$client_secret = $secret;

$insert_values = array(
	"subdomain" => $subdomain,
	"access_token" => $access_token,
	"refresh_token" => $refresh_token,
	"expires_in" => $expires_in,
	"client_secret" => $client_secret,
	"client_id" => $client_id,
	"redirect_uri" => $redirect_uri
);
$file = fopen(__DIR__."/amo/amo.txt","w");
fwrite($file,json_encode($insert_values));
?>