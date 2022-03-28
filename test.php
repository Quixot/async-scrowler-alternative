<?php 
		$filename = '/var/www/rozetka.ua/content/14.04.2021/httpsrozetkacomua424134p424134.txt';
		if (file_exists($filename)) {
			$content = file_get_contents($filename);
			// Код товара
			preg_match("~class=\"product__code-accent.*>.*>(.+)<~isU", $content, $matches_code);

			$code = str_replace('&nbsp;', '', $matches_code[1]);
			$code = trim($code);
			// Раздел
			preg_match_all("~class=\"breadcrumbs__link.*span>(.+)<~isU", $content, $matches_catalogue);
			//print_r($matches_catalogue);
			$catalogue = trim($matches_catalogue[1][count($matches_catalogue[1])-2]);
			// Бренд
			preg_match_all("~class=\"breadcrumbs__link.*span>(.+)<~isU", $content, $matches_brand);
			$brand = trim(str_replace($catalogue, '', trim(end($matches_brand[1]))));
			
			// Полное наименование товара
			preg_match("~<h1.*>(.+)<~isU", $content, $matches_name);
			$name = trim($matches_name[1]);
			// Цена продажи
			preg_match("~http://schema.org/InStock.*price.*:(.+),~isU", $content, $matches_price);
			//print_r($matches_price);
			$price = preg_replace('~[^\d.]+~', '' , $matches_price[1]);
			// Продавец этого товара
			preg_match("~class=\"product-seller__logo.*alt=\"(.+)\"~isU", $content, $matches_seller);
			$seller = trim($matches_seller[1]);
			// Статус позиции (в наличии, не в наличии и т.д.)
			//preg_match("~name=\"detail_status\">(.+)<~isU", $content, $matches_status);
			preg_match("~sell_status:\"(.+)\"~isU", $content, $matches_status);
			$status = trim($matches_status[1]);

			preg_match("~product__rating-reviews\" href=\"(.+)\"~isU", $content, $matches_url);
			$url = trim(str_replace('comments/', '', $matches_url[1]));

			preg_match("~article&q;:&q;(.+)&q;~isU", $content, $matches_model);
			$model = trim($matches_model[1]);

			preg_match_all("~characteristics-full__label\">(.+)<.*class=\"characteristics-full__sub-list\">(.+)</ul~isU", $content, $matches_filters, PREG_SET_ORDER);
			//print_r($matches_filters);

/**/
			echo $code.PHP_EOL;
			echo $catalogue.PHP_EOL;
			echo $brand.PHP_EOL;
			echo $name.PHP_EOL;
			echo $price.PHP_EOL;
			echo $seller.PHP_EOL;
			echo $status.PHP_EOL;
			echo $url.PHP_EOL;
			echo $model.PHP_EOL;



			foreach ($matches_filters as $key => $value) {
				echo PHP_EOL.strip_tags($value[1]).":   ";
				preg_match_all("~<li.*>.*>.*>(.+)</li>~isU", $value[2], $ff, PREG_SET_ORDER);
				foreach ($ff as $k => $cur_f) {
					echo strip_tags(str_replace("\n", ", ", $cur_f[1])).", ";
				}
			}
			

			$itemBase[$url] = array(
				$code, $catalogue, $brand, $name, $price, $seller, $status, $url
				);
		}


