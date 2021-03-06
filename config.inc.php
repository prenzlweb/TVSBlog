<?php

	/*
	 * Addon tvsblog
	 * @author wandel[at]thavis[dot]com Michael Wandel
	 * @author <a href="http://www.thavis.com">www.thavis.com</a>
	 * @package redaxo4
	 * @version $Id: config.inc.php,v 1.0 2010/07/16 12:00:00 kills Exp $
	 */

	$mypage = 'tvsblog'; // only for this file

	$REX['ADDON']['page'][$mypage] = $mypage;
	$REX['ADDON']['rxid'][$mypage] = '765';
	$REX['ADDON']['name'][$mypage] = 'TvsBlog';
	$REX['ADDON']['perm'][$mypage] = 'tvsblog[]';
	$REX['ADDON']['version'][$mypage] = "1.3.1";
	$REX['ADDON']['author'][$mypage] = "Michael Wandel | THAVIS GmbH & Co. KG";

	$REX['PERM'][] = 'tvsblog[]';

	$Basedir = dirname(__FILE__);

	if($REX['GG'])
		rex_register_extension('OUTPUT_FILTER', 'tvsblog_add_head');

	// Rexseo-Integration
	if (OOAddon :: isAvailable('rexseo') || OOAddon :: isAvailable('rexseo42')) {	
		require_once dirname(__FILE__) ."/classes/urlRewrite.inc.php";
		rex_register_extension('REXSEO_PATHLIST_CREATED', 'tvsblog_extended_urls');
		// URLs in Sitemap eintragen
		rex_register_extension('REXSEO_SITEMAP_ARRAY_CREATED', 'tvsblog_extended_sitemap');
	}

    if ($REX['REDAXO'] != 1) {
    
		function tvsblog_add_head($params) {

			global $REX;
			$table_pre = "rex_".$REX['ADDON']['rxid']['tvsblog'];
			$art_table = $table_pre . "_articles";
			$tvsblog_post_id = rex_get('post_id', 'int', -1);
			$output = $params['subject'];
			$headinsert = "";
			
			if ($tvsblog_post_id <> -1) {
				$sql = new rex_sql();
				$sql->setQuery("SELECT * FROM " . $art_table . " WHERE id = " . $tvsblog_post_id);

				$fb_image = "";
				if ($sql->getRows() > 0 ) {
					$fb_image = $sql->getValue('fb_image');
					if ($fb_image == "") {
						$fb_image = explode(",",$sql->getValue('filelist'));
						$fb_image = $fb_image[0];
					}
					$fb_title = utf8_decode($sql->getValue('title'));
					$fb_title = str_replace(chr(34),"",$fb_title);
					$fb_description = utf8_decode(strip_tags($sql->getValue('description')));
					$fb_description = str_replace(chr(34),"",$fb_description);
					if (OOAddon :: isAvailable('rexseo') || OOAddon :: isAvailable('rexseo42'))
						$fb_url = getTVSBlogURL($tvsblog_post_id, $REX['CUR_CLANG']);
					else
						$fb_url = "http://" . $_SERVER['SERVER_NAME'] . rex_getUrl('','') . "?post_id=" . $tvsblog_post_id;
				}
				if ($fb_image != "") {
					$headinsert .= "	<link rel=\"image_src\" href=\"http://" . $_SERVER['SERVER_NAME'] . "/files/" . $fb_image . "\" />\n";
					$headinsert .= "	<meta property=\"og:image\" content=\"http://" . $_SERVER['SERVER_NAME'] . "/files/" . $fb_image . "\" />\n";
				}
				$headinsert .= "	<meta property=\"og:title\" content=\"" . $fb_title . "\" />\n";
				$headinsert .= "	<meta property=\"og:description\" content=\"" . $fb_description . "\" />\n";
				$headinsert .= "	<meta property=\"og:type\" content=\"blog\" />\n";
				$headinsert .= "	<meta property=\"og:url\" content=\"" . $fb_url . "\" />\n";
				$output = str_replace("<html ", "<html prefix=\"og: http://ogp.me/ns#\" ", $output);
			}
			$headinsert .= "	<link href=\"files/addons/tvsblog/wmuSlider/wmuSlider.css\" type=\"text/css\" rel=\"stylesheet\" media=\"screen\" />\n";
			$headinsert .= "	<script src=\"files/addons/tvsblog/wmuSlider/jquery.wmuSlider.min.js\" type=\"text/javascript\"></script>\n";
			//$headinsert .= "	<script src=\"files/addons/tvsblog/wmuSlider/modernizr.custom.min.js\"></script>\n";
			$output = str_replace("</head>", $headinsert . "</head>", $output);
			return $output;
		}
	}
?>