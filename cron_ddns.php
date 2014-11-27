<?php
$token = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";
$domain = "admin.example.org";

function get_real_ip(){
	$a = file_get_contents("http://internet.yandex.ru/");
	$start = mb_strpos($a, "IPv4:")+6;
	$a = mb_substr($a, $start);
	$end = mb_strpos($a, " ");
	$ip = mb_substr($a, 0, $end);
	if(preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])'.'\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]?|[0-9])$/', $ip) == FALSE){
		die(date("Y-m-d H:i:s")." - Получил не правильный IP адресс -".$ip);
	}
	return trim($ip);
}
function get_yandex_data($token, $domain){
    $res = file_get_contents("https://pddimp.yandex.ru/nsapi/get_domain_records.xml?token=".$token."&domain=".$domain);
    $xml = simplexml_load_string($res);
    return $xml;
}
function edit_a_record($token, $domain, $subdomain, $record_id, $new_ip){
    $answer = file_get_contents("https://pddimp.yandex.ru/nsapi/edit_a_record.xml?token=$token&subdomain=$subdomain&record_id=$record_id&content=$new_ip");
    return $answer;
}

$real_ip = get_real_ip();

$ya_data = get_yandex_data($token, $domain);

for($i=0;$i<count($ya_data->domains->domain->response->record);$i++){
	if(strval($ya_data->domains->domain->response->record[$i]['type'] == "A")){
		$ip = strval($ya_data->domains->domain->response->record[$i]);
		$subdomain = strval($ya_data->domains->domain->response->record[$i]['subdomain']);
		$id = strval($ya_data->domains->domain->response->record[$i]['id']);
		if($ip != $real_ip){
			edit_a_record($token, $domain, $subdomain, $id, $real_ip);
			echo date("Y-m-d H:i:s")." - Изменил запись для домена ".$subdomain.".".$domain." из ".$ip." на ".$real_ip;
		}
	}
}
?>
