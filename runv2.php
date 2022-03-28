<?php
/*
$json = str_replace('&q;', '"', file_get_contents('/var/www/rozetka.ua/rozetka.json'));
//file_put_contents('/var/www/rozetka.ua/rozetka_clean.json', print_r(json_decode($json), 1));
$json = json_decode($json);

$vowels = array("\n", "\r", "\t", "\x0B", "&#8203;", "&l;", "/&g;", "/p&g;", "/b&g;", "/ul&g;", "/li&g;", "li&g;", "ul&g;", "b&g;", "p&g;");
foreach ($json as $key => $value) {
	if (@$value->body->content->goods->description) {
	 	$text = str_replace($vowels, ' ', $value->body->content->goods->description);
	 	$text = str_replace("  ", ' ', trim($text));
	 	echo $text;
	} 
}
die();
*/
$links_json = (array)json_decode(file_get_contents('/var/www/rozetka.ua/links.json')); // Загружаем ссылки
foreach ($links_json as $key => $value) {
	$links[$key] = array($value, '1');
}


require_once('/var/www/lib/functions.php');
require_once('/var/www/lib/PHPExcel.php');
require_once('/var/www/lib/PHPExcel/Writer/Excel2007.php');
require_once('/var/www/lib/PHPMailer-master/class.phpmailer.php');
require_once('/var/www/lib/PHPMailer-master/class.smtp.php');
$curr_date = date('d.m.y');

/**
 * Собираем первые странички разделов, проверяем в цикле по несколько раз
 */
echo 'Собираем первые странички разделов'.PHP_EOL;

if (!file_exists('/var/www/rozetka.ua/content_mp/'.date('d.m.Y'))) {
	mkdir('/var/www/rozetka.ua/content_mp/'.date('d.m.Y'));
	chmod('/var/www/rozetka.ua/content_mp/'.date('d.m.Y'), 0777);
}

for ($loop=0; $loop < 15 ; $loop++) { 
	$is_tasks = 0;
	foreach ($links as $url => $value) {
		$urlreal = $url.'1';
		$id = preg_replace('/[^a-zA-Z0-9&]/', '', $urlreal);
		if (file_exists('/var/www/rozetka.ua/content_mp/'.date('d.m.Y').'/'.$id.'.txt')) {
			echo $id.'.txt already scanned'.PHP_EOL;
		} else {
			echo $urlreal.' сканирую начальные странички'.PHP_EOL;
			scanbot($urlreal, $id);
			$is_tasks++;
		}
		if ($is_tasks >= 7) {
			sleep(45);
			$is_tasks = 0;
		}		
	}
}

//sleep(10);
/**
  * Узнаем сколько где страниц пагинации
  */ 
echo 'Узнаем сколько где страниц пагинации'.PHP_EOL;

foreach ($links as $url => $value) {
	$urlreal = $url.'1';
	$id = preg_replace('/[^a-zA-Z0-9&]/', '', $urlreal);
	$filename = '/var/www/rozetka.ua/content_mp/'.date('d.m.Y').'/'.$id.'.txt';
	if (file_exists($filename)) {
		$content = file_get_contents($filename);
		preg_match_all("~novisited paginator-catalog-l-link.*>(.+)<~isU", $content, $matches2);
		//print_r($matches2);
		$temparrpage = array();
		foreach ($matches2[1] as $key => $value) {
			$value = trim($value);
			//echo 'value:'.$value.PHP_EOL;
			if (is_numeric($value)) {
				$temparrpage[] = $value;
			}
		}
		if (@max($temparrpage) > $links[$url][1]) {
			$qOfPaginationPages = @max($temparrpage);
			//echo 'pagination pages: '.$qOfPaginationPages.PHP_EOL;
			$links[$url][1] = $qOfPaginationPages;
		}
	}
}
print_r($links);

//sleep(10);

/**
	* Запускаем сбор контента по страницам пагинации
	*/
echo 'Запускаем сбор контента по страницам пагинации'.PHP_EOL;

$is_tasks = 0;
for ($loop=0; $loop < 15; $loop++) {
	echo 'Попытка '.$loop.PHP_EOL.PHP_EOL;
	foreach ($links as $url => $value) {
		if ($value[1] > 1) {
			for ($i=2; $i <= $value[1]; $i++) {
				$urlreal = $url.$i;
				$id = preg_replace('/[^a-zA-Z0-9&]/', '', $urlreal);
				if (file_exists('/var/www/rozetka.ua/content_mp/'.date('d.m.Y').'/'.$id.'.txt')) {
					//echo $urlreal.' already scanned'.PHP_EOL;
				} else {
					echo $urlreal.' Сканирую страницы пагинации'.PHP_EOL;
					scanbot($urlreal, $id);
					$is_tasks++;
				}
				if ($is_tasks >= 7) {
					sleep(45);
					$is_tasks = 0;
				}
			}
		}
	}
	if ($is_tasks) {
		$is_tasks = 0;
		sleep(45);
	}
}
file_put_contents('/var/www/rozetka.ua/links.txt', print_r($links, 1));

//print_r($links);
//sleep(10);

/**
 * Собираем ссылки на товар (эта операция не подразумевает сканирования)
 */
echo 'Собираем ссылки на товар'.PHP_EOL;

$items = array();
foreach ($links as $url => $value) {
	if ($value[1] > 0) {
		for ($i=1; $i <= $value[1]; $i++) {
			$urlreal = $url.$i;
			$id = preg_replace('/[^a-zA-Z0-9&]/', '', $urlreal);
			$filename = '/var/www/rozetka.ua/content_mp/'.date('d.m.Y').'/'.$id.'.txt';
			if (file_exists($filename)) {
				$content = file_get_contents($filename);
				preg_match_all("~class=\"over-wraper\"(.+)class=\"g-rating\"~isU", $content, $matches, PREG_SET_ORDER);
				if ($matches) {
					foreach ($matches as $key) {
						preg_match("~class=\"g-i-tile-i-title.*href=\"(.+)\"~isU", $key[1], $matches_item);
						//print_r($matches_item);
						//if (stripos($key[1], 'Купить') !== false) { // Проверяем наличие
							$items[] = $matches_item[1];
						//}
					}
				}
			}
		}
	}
}
//print_r($items);

//sleep(10);

/**
 * Собираем странички с ценами
 */
echo 'Собираю странички с ценами'.PHP_EOL;

$items = array_unique($items);
$items_urls = array();

$page_in_process = 0;
for ($loop=0; $loop < 17; $loop++) {
	foreach ($items as $url) {
		$url = $url.'';
		if (stripos($url, '/') !== false) {
			$id = preg_replace('/[^a-zA-Z0-9&]/', '', $url);

			if (file_exists('/var/www/rozetka.ua/content_mp/'.date('d.m.Y').'/'.$id.'.txt')) {
				//echo $url.' already scanned'.PHP_EOL;
			} else {
				echo $url.'characteristics/ сканирую'.PHP_EOL;
				scanbot($url.'characteristics/', $id);
				$page_in_process++;
				if ($page_in_process > 11) {
					$page_in_process = 0;
					echo '************************************************'.PHP_EOL;
					sleep(60);
				}
			}
			$items_urls[$id] = $url; // Заносим в массив id и адрес странички, чтобы её было легче найти при обработке
		}
	}
}
if ($page_in_process) {
	sleep(30);
}


/**
 * Обрабатываем странички с ценами
 */
$itemBase = array();
foreach ($items_urls as $id => $currurl) {
	if ($id && $currurl) {
		$filename = '/var/www/rozetka.ua/content_mp/'.date('d.m.Y').'/'.$id.'.txt';
		if (file_exists($filename)) {
			$content = file_get_contents($filename);
			// Код товара
			preg_match("~name=\"detail-code-i\">(.+)<~isU", $content, $matches_code);
			$code = trim($matches_code[1]);
			// Раздел
			preg_match_all("~class=\"breadcrumbs-title.*itemprop=\"name\">(.+)<~isU", $content, $matches_catalogue);
			$catalogue = trim($matches_catalogue[1][count($matches_catalogue[1])-2]);
			// Бренд
			preg_match_all("~class=\"breadcrumbs-title.*itemprop=\"name\">(.+)<~isU", $content, $matches_brand);
			$brand = trim(str_replace($catalogue, '', trim(end($matches_brand[1]))));
			
			// Полное наименование товара
			preg_match("~<h2.*itemprop=\"name\">(.+)<~isU", $content, $matches_name);
			$name = trim($matches_name[1]);
			// Цена продажи
			preg_match("~item_price:\"(.+)\"~isU", $content, $matches_price);
			$price = preg_replace('~[\D]+~', '' , $matches_price[1]);
			// Продавец этого товара
			preg_match("~merchant-logo responsive-img ng-star-inserted.*alt=\"(.+)\"~isU", $content, $matches_seller);
			$seller = trim($matches_seller[1]);
			// Статус позиции (в наличии, не в наличии и т.д.)
			//preg_match("~name=\"detail_status\">(.+)<~isU", $content, $matches_status);
			preg_match("~sell_status: '(.+)'~isU", $content, $matches_status);
			$status = trim($matches_status[1]);

			// JSON блок с описанием
			preg_match("~id=\"serverApp-state\".*>(.+)</script>~isU", $content, $matches_json);
			if ($matches_json[1]) {
				$json = str_replace('&q;', '"', $matches_json[1]);
				//file_put_contents('/var/www/rozetka.ua/rozetka_clean.json', print_r(json_decode($json), 1));
				$json = json_decode($json);

				$vowels = array("\n", "\r", "\t", "\x0B", "&#8203;", "&l;", "/&g;", "/p&g;", "/b&g;", "/ul&g;", "/li&g;", "li&g;", "ul&g;", "b&g;", "p&g;");
				foreach ($json as $key => $value) {
					if (@$value->body->content->goods->description) {
					 	$text = str_replace($vowels, ' ', $value->body->content->goods->description);
					 	$text = str_replace("  ", ' ', trim($text));
					 	//echo $text;
					} 
				}
			}
		

			$url = $currurl;
/**/
			echo $code.PHP_EOL;
			echo $brand_type.PHP_EOL;
			echo $name.PHP_EOL;
			echo $price.PHP_EOL;
			echo $seller.PHP_EOL;
			echo $status.PHP_EOL;
			echo $url.PHP_EOL;
			echo $text.PHP_EOL;

			$itemBase[$url] = array(
				$code, $catalogue, $brand, $name, $price, $seller, $status, $url
				);
		}
	}
}


/**
 * Создаём файлик Excel и пишем туда массив
 */
$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()->setCreator("PricingLogix");
$objPHPExcel->getProperties()->setTitle('Rozetka');
$objPHPExcel->getProperties()->setSubject('Rozetka'.date('H:i:s'));
$objPHPExcel->getProperties()->setDescription("generated by PricingLogix");

$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setTitle('Rozetka.ua_'.$curr_date);
$objPHPExcel->getActiveSheet()->SetCellValue('A' . 1, 'Код');
$objPHPExcel->getActiveSheet()->SetCellValue('B' . 1, 'Тип');
$objPHPExcel->getActiveSheet()->SetCellValue('C' . 1, 'Бренд');
$objPHPExcel->getActiveSheet()->SetCellValue('D' . 1, 'Имя');
$objPHPExcel->getActiveSheet()->SetCellValue('E' . 1, 'Цена');
$objPHPExcel->getActiveSheet()->SetCellValue('F' . 1, 'Продавец товара');
$objPHPExcel->getActiveSheet()->SetCellValue('G' . 1, 'Статус товара');
$objPHPExcel->getActiveSheet()->SetCellValue('H' . 1, 'url');

$i = 2;
foreach ($itemBase as $key => $value) {
	//echo $value[2].PHP_EOL;
	$objPHPExcel->getActiveSheet()->SetCellValue('A' . $i, $value[0]);
	$objPHPExcel->getActiveSheet()->SetCellValue('B' . $i, $value[1]);
	$objPHPExcel->getActiveSheet()->SetCellValue('C' . $i, $value[2]);
	$objPHPExcel->getActiveSheet()->SetCellValue('D' . $i, $value[3]);
	$objPHPExcel->getActiveSheet()->SetCellValue('E' . $i, $value[4]);
	$objPHPExcel->getActiveSheet()->SetCellValue('F' . $i, $value[5]);
	$objPHPExcel->getActiveSheet()->SetCellValue('G' . $i, $value[6]);
	$objPHPExcel->getActiveSheet()->SetCellValue('H' . $i, $value[7]);
	$i++;
}	
$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$objWriter->save('/var/www/rozetka.ua/reports/'.$curr_date.'_rozetka.ua.xlsx');


if (isset($argv[1])) {
	$recipients = explode("\n", file_get_contents('/var/www/rozetka.ua/mail/'.$argv[1].'.txt'));
	array_walk($recipients, 'trim_value');
	echo 'sending email...';

	//$recipients = array('alexandr.volkoff@gmail.com', 'rsp.bt.tech@gmail.com');

	$email = new PHPMailer();
	$email->IsSMTP();
	$email->Host = "mx1.mirohost.net";
	$email->Port = 25;
	$email->SMTPAuth = true;
	$email->Username = "argos@pricinglogix.com";
	$email->Password = "0Rps8883iMPu";
	$email->isHTML(true);
	$email->From      = 'argos@pricinglogix.com';
	$email->FromName  = 'PricingLogix';
	$email->Subject   = 'Мониторинг Rozetka.ua для МаркетПлейс';
	$email->AddEmbeddedImage('/var/www/lib/logo.png', 'pricinglogix-logo', 'logo.png');
	$email->Body = 'Во влоежении файл мониторинга по Rozetka.ua для МаркетПлейс:<br/><br/>';
	$email->Body .= '<img alt="PHPMailer" src="cid:pricinglogix-logo"><br><br><a href="http://pricinglogix.com/">http://pricinglogix.com</a>';
	foreach($recipients as $mailaddr) {
	  $email->AddAddress($mailaddr);
	}
	$file_to_attach = '/var/www/rozetka.ua/reports/'.$curr_date.'_rozetka.ua.xlsx';
	$email->AddAttachment( $file_to_attach , $curr_date.'_rozetka.ua.xlsx' );
	$email->CharSet = "UTF-8";
	$email->Encoding = 'base64';
	$email->Send();
}







function scanbot($url, $id) {
/**
 * Основная функция, сканирует страницы
 */
	global $links;
	global $bad_urls;

	$useragent = check_useragent();
	$proxy = check_proxy();
	$url = escapeshellarg($url);
	$id = escapeshellarg($id);
	
	if ($useragent && $proxy && stripos($url, 'http') !== false) {
		$cmd = 'timeout -k 41s 42s /usr/local/bin/casperjs /var/www/rozetka.ua/runv2.js '.$url.' '.$useragent.' '.$proxy[1].' '.$proxy[2].' '.$proxy[3].' '.$proxy[4].' '.$id;
		//zecho "$cmd > /dev/null 2>/dev/null &".PHP_EOL;
		//echo $id.PHP_EOL;
		exec("$cmd > /dev/null 2>/dev/null &");
		//exec($cmd, $out, $err);
	}
}

function check_useragent() {
/**
 * Выбор агента
 */
	$useragent_list = explode("\n", file_get_contents( '/var/www/lib/useragents_short.txt' ));
	array_walk($useragent_list, 'trim_value');

	$useragent_index = mt_rand(0, count($useragent_list)-1);
	$useragent = $useragent_list[$useragent_index];

	return escapeshellarg($useragent);
}

function check_proxy() {
/**
 * Выбор прокси
 */	
	$proxy_array = glob('/var/www/lib/proxies/*.proxy');
	$alive_proxy_list = '';
	foreach ($proxy_array as $key) {
		$alive_proxy_list .= file_get_contents($key);
		$alive_proxy_list .= "\n";
	}
	$alive_proxy_list = trim($alive_proxy_list);

	$proxy_list 		= explode("\n", $alive_proxy_list);
	shuffle($proxy_list);

	array_walk($proxy_list, 'trim_value');

	$proxy_auth = $proxy_list[ mt_rand(0, count($proxy_list)-1) ];
	preg_match("~(.+):(.+):(.+):(.+)$~isU", trim($proxy_auth), $matches_proxy);
	if (!$matches_proxy) {
	 	preg_match("~(.+):(.+)$~isU", trim($proxy_auth), $matches_proxy);
	}

	return $matches_proxy;
}
