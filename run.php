<?php
require_once('/var/www/lib/functions.php');
require_once('/var/www/lib/PHPExcel.php');
require_once('/var/www/lib/PHPExcel/Writer/Excel2007.php');
require_once('/var/www/lib/PHPMailer-master/class.phpmailer.php');
require_once('/var/www/lib/PHPMailer-master/class.smtp.php');
$curr_date 			= date('d.m.y');
$curr_date_big  = date('d.m.Y');

$links = array(
/**/
'https://rozetka.com.ua/ua/search/?class=0&text=AEG&section_id=80025&view=tile&producer=202&sort=expensive&price=200-5000&page='										=>	array('AEG', 				1),
'https://rozetka.com.ua/ua/search/?class=0&text=Aurora&section_id=2394287&producer=263&sort=expensive&price=200-5000&view=tile&page='								=>	array('Aurora', 		1),
'https://rozetka.com.ua/ua/search/?class=0&text=Aurora&sort=expensive&section_id=80025&producer=263&redirected=1&price=200-5000&view=tile&page='		=>	array('Aurora', 		1),
'https://rozetka.com.ua/ua/search/?text=Bollire&producer=159125&view=tile&sort=expensive&class=0&redirected=1&price=225-10000&page='								=>	array('Bollire', 		1),
'https://rozetka.com.ua/ua/search/?class=0&text=Bomann&section_id=80025&producer=11457&redirected=1&view=tile&page='																=>	array('Bomann', 		1),
'https://rozetka.com.ua/ua/search/?class=0&text=Clatronic&producer=1149&sort=expensive&redirected=1&price=200-10000&view=tile&page='								=>	array('Clatronic', 	1),
'https://rozetka.com.ua/ua/search/?class=0&text=First&section_id=80025&sort=expensive&producer=102311&price=200-5000&view=tile&page='								=>	array('First', 			1),
'https://rozetka.com.ua/ua/search/?class=0&text=Hilton&section_id=80025&view=tile&sort=expensive&producer=1545&price=200-5000&page='								=>	array('Hilton', 		1),
'https://rozetka.com.ua/ua/search/?class=0&text=Hilton&sort=expensive&section_id=2394287&producer=1545&redirected=1&price=200-5000&view=tile&page='	=>	array('Hilton', 		1),
'https://rozetka.com.ua/ua/search/?class=0&text=Julia&section_id=2394287&view=tile&sort=expensive&producer=138014&page='														=>	array('Julia', 			1),
'https://rozetka.com.ua/ua/search/?text=Liberton&view=tile&sort=expensive&class=0&producer=1111&redirected=1&price=200-20000&page='									=>	array('Liberton', 	1),
'https://rozetka.com.ua/ua/search/?class=0&text=PETRA&sort=expensive&section_id=80025&producer=121054&price=200-10000&view=tile&page='							=>	array('PETRA', 			1),
'https://rozetka.com.ua/ua/search/?class=0&text=Polaris&section_id=80025&view=tile&sort=expensive&producer=266&price=200-10000&page='								=>	array('Polaris', 		1),
'https://rozetka.com.ua/ua/search/?class=0&text=Polaris&sort=expensive&section_id=2394287&producer=266&price=200-5000&view=tile&page='							=>	array('Polaris', 		1),
'https://rozetka.com.ua/ua/search/?class=0&text=Princess&section_id=80025&view=tile&sort=expensive&producer=123313&price=509-10000&page='						=>	array('Princess', 	1),
'https://rozetka.com.ua/ua/search/?text=ProfiCare&producer=216120&view=tile&price=355-5000&page='																										=>	array('ProfiCare', 	1),
'https://rozetka.com.ua/ua/search/?text=Profi+Cook&view=tile&producer=116728&sort=expensive&price=200-20000&page='																	=>	array('ProfiCook', 	1),
'https://rozetka.com.ua/ua/search/?text=STEBA&producer=2849&view=tile&sort=expensive&price=200-25000&page='																					=>	array('STEBA', 			1),
'https://rozetka.com.ua/ua/search/?text=Topcom&producer=1411&view=tile&price=200-5000&page='																												=>	array('Topcom', 		1),
'https://rozetka.com.ua/ua/search/?class=0&text=TRISTAR&section_id=80025&view=tile&sort=expensive&producer=1757&price=200-15000&page='							=>	array('TRISTAR', 		1),
'https://rozetka.com.ua/ua/search/?class=0&text=VES&section_id=80025&view=tile&producer=2238&sort=expensive&price=200-10000&page='									=>	array('VES', 				1),
'https://rozetka.com.ua/ua/search/?text=Vitrinor&producer=174441&view=tile&price=200-5000&page='																										=>	array('Vitrinor', 	1),

'https://rozetka.com.ua/ua/search/?section_id=&text=ASTOR&producer=271404&page='																																=>	array('ASTOR', 			1),
'https://rozetka.com.ua/ua/search/?text=HAUSLICH&producer=237864&page='																																					=>	array('HAUSLICH', 	1),
'https://rozetka.com.ua/ua/search/?text=PHILIPPE+RATEK&page='																																										=>	array('PHILIPPE RATEK', 	1),
'https://rozetka.com.ua/ua/search/?text=SINBO&producer=4665&page='																																							=>	array('SINBO', 			1),
'https://rozetka.com.ua/ua/search/?text=VERTEX&producer=1279&page='																																							=>	array('VERTEX', 		1),
	);

/**
 * Собираем первые странички разделов, проверяем в цикле по несколько раз
 */
echo 'Собираем первые странички разделов'.PHP_EOL;

if (!file_exists('/var/www/rozetka.ua/content/'.$curr_date_big)) {
	mkdir('/var/www/rozetka.ua/content/'.$curr_date_big);
	chmod('/var/www/rozetka.ua/content/'.$curr_date_big, 0777);
}

for ($loop=0; $loop < 7 ; $loop++) { 
	$is_tasks = 0;
	foreach ($links as $url => $value) {
		$urlreal = $url.'1';
		$id = preg_replace('/[^a-zA-Z0-9&]/', '', $urlreal);
		if (file_exists('/var/www/rozetka.ua/content/'.$curr_date_big.'/'.$id.'.txt')) {
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
print_r($links);
//sleep(10);
/**
  * Узнаем сколько где страниц пагинации
  */ 
echo 'Узнаем сколько где страниц пагинации'.PHP_EOL;

foreach ($links as $url => $value) {
	$urlreal = $url.'1';
	$id = preg_replace('/[^a-zA-Z0-9&]/', '', $urlreal);
	$filename = '/var/www/rozetka.ua/content/'.$curr_date_big.'/'.$id.'.txt';
	echo $filename.PHP_EOL;
	//echo $id.PHP_EOL;

	if (file_exists($filename)) {
		$content = file_get_contents($filename);
		preg_match_all("~pagination__link.*>(.+)<~isU", $content, $matches2);
		//print_r($matches2);
		$temparrpage = array();
		foreach ($matches2[1] as $key => $value) {
			$value = trim(strip_tags($value));
			//echo 'value:'.$value.PHP_EOL;
			if (is_numeric($value)) {
				$temparrpage[] = $value;
			}
		}
		if (@max($temparrpage) > $links[$url][1]) {
			$qOfPaginationPages = @max($temparrpage);
			echo 'pagination pages: '.$qOfPaginationPages.PHP_EOL;
			$links[$url][1] = $qOfPaginationPages;
		}
	}
}

//print_r($links);
//die();


//sleep(10);

/**
	* Запускаем сбор контента по страницам пагинации
	*/
echo 'Запускаем сбор контента по страницам пагинации'.PHP_EOL;

$is_tasks = 0;
for ($loop=0; $loop < 5; $loop++) {
	echo 'Попытка '.$loop.PHP_EOL.PHP_EOL;
	foreach ($links as $url => $value) {
		echo $url.PHP_EOL;
		print_r($value).PHP_EOL;
		if ($value[1] > 1) {
			for ($i=2; $i <= $value[1]; $i++) {
				$urlreal = $url.$i;
				$id = preg_replace('/[^a-zA-Z0-9&]/', '', $urlreal);
				if (file_exists('/var/www/rozetka.ua/content/'.$curr_date_big.'/'.$id.'.txt')) {
					echo $urlreal.' already scanned'.PHP_EOL;
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
			$filename = '/var/www/rozetka.ua/content/'.$curr_date_big.'/'.$id.'.txt';
			if (file_exists($filename)) {
				$content = file_get_contents($filename);
				preg_match_all("~catalog-grid__cell(.+)</li>~isU", $content, $matches, PREG_SET_ORDER);
				//if (!$matches) {
					//mail('alexandr.volkoff@gmail.com', 'Parser Rozetka', "file $filename don't have links");
					//die('file: '.$filename);
				//}
				if ($matches) {
					foreach ($matches as $key) {
						preg_match("~goods-tile__heading.*href=\"(.+)\"~isU", $key[1], $matches_item);
						if (@!$matches_item[1]) {
							preg_match("~goods-tile__colors-link.*href=\"(.+)\"~isU", $key[1], $matches_item);
						}
						//print_r($matches_item);
						//if (stripos($key[1], 'Купить') !== false) { // Проверяем наличие
						//if (!$matches_item[1]) {
							//echo $key[1];die();
						//}
							$items[] = $matches_item[1];
						//}
					}
				}
			}
		}
	}
}
//file_put_contents('/var/www/rozetka.ua/alllinks.txt', print_r($items, 1));
//die();

//mail('alexandr.volkoff@gmail.com', 'Parser Rozetka', 'links has been saved');
//print_r($items);
//echo count($items);
//die();

//sleep(10);

/**
 * Собираем странички с ценами
 */
echo 'Собираю странички с ценами'.PHP_EOL;
$items = array_unique($items);
file_put_contents('/var/www/rozetka.ua/alllinks.txt', print_r($items, 1));


//echo count($items);die();
$items_urls = array();

$page_in_process = 0;

for ($loop=0; $loop < 7; $loop++) {
	foreach ($items as $url) {
		
		if (stripos($url, '/') !== false) {
			$id = preg_replace('/[^a-zA-Z0-9&]/', '', $url);

			if (file_exists('/var/www/rozetka.ua/content/'.$curr_date_big.'/'.$id.'.txt') && filesize('/var/www/rozetka.ua/content/'.$curr_date_big.'/'.$id.'.txt') > 0) {
				echo $url.' already scanned'.PHP_EOL;
			} else {
				echo $url.' сканирую'.PHP_EOL;
				scanbot2($url, $id);
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

//die();

/**
 * Обрабатываем странички с ценами
 */
$itemBase = array();
foreach ($items_urls as $id => $currurl) {
	if ($id && $currurl) {
		$filename = '/var/www/rozetka.ua/content/'.$curr_date_big.'/'.$id.'.txt';
		if (file_exists($filename)) {
			$content = file_get_contents($filename);
			// Код товара
			preg_match("~class=\"product__code-accent.*>.*>(.+)<~isU", $content, $matches_code);
			if (!$matches_code) {
				preg_match("~goods_id: '(.+)'~isU", $content, $matches_code);
			}

			$code = str_replace('&nbsp;', '', $matches_code[1]);
			$code = trim($code);

			// Раздел
			preg_match_all("~class=\"breadcrumbs__link.*span>(.+)<~isU", $content, $matches_catalogue);
			echo '**';
			//print_r($matches_catalogue);
			if (!$matches_catalogue[1]) {
				preg_match_all("~breadcrumbs-link.*span.*>(.+)<~isU", $content, $matches_catalogue);
				echo '***';
				//print_r($matches_catalogue);
			}
			//print_r($matches_catalogue);
			//
			
			$catalogue = trim($matches_catalogue[1][count($matches_catalogue[1])-2]);
			///if (!$catalogue) {
				//print_r($matches_catalogue);
				//file_put_contents('/var/www/rozetka.ua/content/content.txt', $content);
				//die();
			//}

			// Бренд
			preg_match_all("~class=\"breadcrumbs__link.*span>(.+)<~isU", $content, $matches_brand);
			if (!$matches_brand) {
				preg_match_all("~class=\"breadcrumbs-link.*span>(.+)<~isU", $content, $matches_brand);
			}
			$brand = trim(str_replace($catalogue, '', trim(end($matches_brand[1]))));
			
			// Полное наименование товара
			preg_match("~<h1.*>(.+)<~isU", $content, $matches_name);
			$name = trim($matches_name[1]);

			// Цена продажи
			preg_match("~price_uah: '(.+)'~isU", $content, $matches_price);
			if (!$matches_price) {
				preg_match("~http://schema.org/InStock.*price.*:(.+),~isU", $content, $matches_price);
			}
			//print_r($matches_price);
			$price = preg_replace('~[^\d.]+~', '' , $matches_price[1]);

			// Продавец этого товара
			preg_match("~class=\"product-seller__logo.*alt=\"(.+)\"~isU", $content, $matches_seller);
			if (!$matches_seller) {
				preg_match("~safe-merchant-label-title\">(.+)<~isU", $content, $matches_seller);
			}
			//if (!$matches_seller) {
			//	preg_match("~class=\"product-seller__title.*>.*>(.+)<~isU", $content, $matches_seller);
			//}
			if (!$matches_seller) {
				preg_match("~class=\"product-seller__title.*<a.*>(.+)</a~isU", $content, $matches_seller);
			}						
			//print_r($matches_seller);
			$seller = trim(strip_tags($matches_seller[1]));

			// Статус позиции (в наличии, не в наличии и т.д.)
			//preg_match("~name=\"detail_status\">(.+)<~isU", $content, $matches_status);
			preg_match("~sell_status:\"(.+)\"~isU", $content, $matches_status);
			if (!$matches_status) {
				preg_match("~sell_status&q;:&q;(.+)&q;~isU", $content, $matches_status);
			}
			$status = trim($matches_status[1]);

			$url = $currurl;
/*
			echo $code.PHP_EOL;
			echo $catalogue.PHP_EOL;
			echo $brand.PHP_EOL;
			echo $name.PHP_EOL;
			echo $price.PHP_EOL;
			echo $seller.PHP_EOL;
			echo $status.PHP_EOL;
			echo $url.PHP_EOL;
			die();
*/
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
$objPHPExcel->getProperties()->setSubject('Rozetka Price Monitoring');
$objPHPExcel->getProperties()->setDescription("generated by PricingLogix");

$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setTitle('Rozetka_'.date('d.m.y'));
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
	if ($value[0]) {
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

}	
$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$objWriter->save('/var/www/rozetka.ua/reports/'.date('d.m.y').'_rozetka.ua.xlsx');


if (isset($argv[1])) {
	$recipients = explode("\n", file_get_contents('/var/www/rozetka.ua/mail/'.$argv[1].'.txt'));
	array_walk($recipients, 'trim_value');
	echo 'sending email...';

	//$recipients = array('alexandr.volkoff@gmail.com', 'rsp.bt.tech@gmail.com');
	$file_to_attach = '/var/www/rozetka.ua/reports/'.date('d.m.y').'_rozetka.ua.xlsx';

	$email = new PHPMailer();
	$email->IsSMTP();
	$email->Host = "mx1.mirohost.net";
	$email->Port = 25;
	$email->SMTPAuth = true;
	$email->Username = "argos@pricinglogix.com";
	$email->Password = "0Rps8883iMPu";//*rybuyC?D7hf
	$email->isHTML(true);
	$email->From      = 'argos@pricinglogix.com';
	$email->FromName  = 'PricingLogix';
	$email->Subject   = 'Мониторинг Rozetka.ua для МаркетПлейс';
	$email->AddEmbeddedImage('/var/www/lib/logo.png', 'pricinglogix-logo', 'logo.png');
	$email->Body = 'Во влоежении файл мониторинга по Rozetka.ua для МаркетПлейс:<br/>'.human_filesize(filesize($file_to_attach)).'<br/>';
	$email->Body .= '<img alt="PHPMailer" src="cid:pricinglogix-logo"><br><br><a href="http://pricinglogix.com/">http://pricinglogix.com</a>';
	foreach($recipients as $mailaddr) {
	  $email->AddAddress($mailaddr);
	}
	
	$email->AddAttachment( $file_to_attach , date('d.m.y').'_rozetka.ua.xlsx' );
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
	global $curr_date_big;

	$useragent = check_useragent();
	$proxy = check_proxy();
	$url = escapeshellarg($url);
	$id = escapeshellarg($id);
	
	if ($useragent && $proxy && stripos($url, 'http') !== false) {
		$cmd = 'timeout -k 40s 41s /usr/local/bin/casperjs /var/www/rozetka.ua/run.js '.$url.' '.$useragent.' '.$proxy[1].' '.$proxy[2].' '.$proxy[3].' '.$proxy[4].' '.$id.' '.$curr_date_big.' '.' --ignore-ssl-errors=true --ssl-protocol=any';
		//zecho "$cmd > /dev/null 2>/dev/null &".PHP_EOL;
		//echo $id.PHP_EOL;
		//echo $cmd.PHP_EOL;
		//die();
		exec("$cmd > /dev/null 2>/dev/null &");
		//exec($cmd, $out, $err);
	}
}

function scanbot2($url, $id) {
/**
 * Основная функция, сканирует страницы
 */
	global $links;
	global $bad_urls;
	global $curr_date_big;

	$useragent = check_useragent();
	$proxy = check_proxy();
	$url = escapeshellarg($url);
	$id = escapeshellarg($id);
	
	if ($useragent && $proxy && stripos($url, 'http') !== false) {
		$cmd = 'timeout -k 40s 41s /usr/local/bin/casperjs /var/www/rozetka.ua/run_item.js '.$url.' '.$useragent.' '.$proxy[1].' '.$proxy[2].' '.$proxy[3].' '.$proxy[4].' '.$id.' '.$curr_date_big.' '.' --ignore-ssl-errors=true --ssl-protocol=any';
		//zecho "$cmd > /dev/null 2>/dev/null &".PHP_EOL;
		echo $id.PHP_EOL;
		echo $cmd.PHP_EOL;
		//die();
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
	//$proxy_array = glob('/var/www/lib/proxies/*{14,15,16}.proxy', GLOB_BRACE);
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

function human_filesize($bytes, $decimals = 2) {
  $sz = 'BKMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}
