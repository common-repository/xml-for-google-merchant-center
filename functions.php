<?php defined( 'ABSPATH' ) || exit;
/**
 * Записывает или обновляет файл фида.
 * @since 1.0.0
 * @version 2.0.0
 *
 * @return true (always)
 */
function xfgmc_write_file( $result_xml, $cc, $numFeed = '1' ) {
	/* $cc = 'w+' или 'a'; */
	new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; Стартовала xfgmc_write_file c параметром cc = ' . $cc . '; Файл: functions.php; Строка: ' . __LINE__ );
	$filename = urldecode( xfgmc_optionGET( 'xfgmc_file_file', $numFeed, 'set_arr' ) );
	if ( $numFeed === '1' ) {
		$prefFeed = '';
	} else {
		$prefFeed = $numFeed;
	}

	if ( $filename == '' ) {
		$upload_dir = (object) wp_get_upload_dir(); // $upload_dir->basedir
		$filename = $upload_dir->basedir . "/" . $prefFeed . "feed-xml-0-tmp.xml"; // $upload_dir->path
	}

	if ( file_exists( $filename ) ) {
		// файл есть
		if ( ! $handle = fopen( $filename, $cc ) ) {
			new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; Не могу открыть файл ' . $filename . '; Файл: functions.php; Строка: ' . __LINE__ );
		}
		if ( fwrite( $handle, $result_xml ) === FALSE ) {
			new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; Не могу произвести запись в файл ' . $handle . '; Файл: functions.php; Строка: ' . __LINE__ );
		} else {
			new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; Ура! Записали; Файл: Файл: functions.php; Строка: ' . __LINE__ );
			new XFGMC_Error_Log( $filename );
			return true;
		}
		fclose( $handle );
	} else {
		new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; Файла $filename = ' . $filename . ' еще нет. Файл: functions.php; Строка: ' . __LINE__ );
		// файла еще нет
		// попытаемся создать файл
		if ( is_multisite() ) {
			$upload = wp_upload_bits( $prefFeed . 'feed-xml-' . get_current_blog_id() . '-tmp.xml', null, $result_xml ); // загружаем shop2_295221-xml в папку загрузок
		} else {
			$upload = wp_upload_bits( $prefFeed . 'feed-xml-0-tmp.xml', null, $result_xml ); // загружаем shop2_295221-xml в папку загрузок
		}
		/*
		 *	для работы с csv или xml требуется в плагине разрешить загрузку таких файлов
		 *	$upload['file'] => '/var/www/wordpress/wp-content/uploads/2010/03/feed-xml.xml', // путь
		 *	$upload['url'] => 'http://site.ru/wp-content/uploads/2010/03/feed-xml.xml', // урл
		 *	$upload['error'] => false, // сюда записывается сообщение об ошибке в случае ошибки
		 */
		// проверим получилась ли запись
		if ( $upload['error'] ) {
			new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; Запись вызвала ошибку: ' . $upload['error'] . '; Файл: functions.php; Строка: ' . __LINE__ );
			$err = 'FEED № ' . $numFeed . '; Запись вызвала ошибку: ' . $upload['error'] . '; Файл: functions.php; Строка: ' . __LINE__;
			new XFGMC_Error_Log( $err );
		} else {
			xfgmc_optionUPD( 'xfgmc_file_file', urlencode( $upload['file'] ), $numFeed, 'yes', 'set_arr' );
			new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; Запись удалась! Путь файла: ' . $upload['file'] . '; УРЛ файла: ' . $upload['url'] );
		}
	}
	return true;
}
/**
 * @since 1.0.0
 * Обновлён в 2.0.0
 * Перименовывает временный файл фида в основной.
 * Возвращает false/true
 */
function xfgmc_rename_file( $numFeed = '1' ) {
	new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; Cтартовала xfgmc_rename_file; Файл: functions.php; Строка: ' . __LINE__ );
	// возможен int, по этой причине равенство двойное.
	if ( $numFeed == '1' ) {
		$prefFeed = '';
	} else {
		$prefFeed = $numFeed;
	}
	/* Перименовывает временный файл в основной. Возвращает true/false */
	if ( is_multisite() ) {
		$upload_dir = (object) wp_get_upload_dir();
		$filenamenew = $upload_dir->basedir . "/" . $prefFeed . "feed-xml-" . get_current_blog_id() . ".xml";
		$filenamenewurl = $upload_dir->baseurl . "/" . $prefFeed . "/feed-xml-" . get_current_blog_id() . ".xml";
		// $filenamenew = BLOGUPLOADDIR."feed-xml-".get_current_blog_id().".xml";
		// надо придумать как поулчить урл загрузок конкретного блога
	} else {
		$upload_dir = (object) wp_get_upload_dir();
		/**
		 *   'path'    => '/home/site.ru/public_html/wp-content/uploads/2016/04',
		 *	'url'     => 'http://site.ru/wp-content/uploads/2016/04',
		 *	'subdir'  => '/2016/04',
		 *	'basedir' => '/home/site.ru/public_html/wp-content/uploads',
		 *	'baseurl' => 'http://site.ru/wp-content/uploads',
		 *	'error'   => false,
		 */
		$filenamenew = $upload_dir->basedir . "/" . $prefFeed . "feed-xml-0.xml";
		$filenamenewurl = $upload_dir->baseurl . "/" . $prefFeed . "feed-xml-0.xml";
	}
	$filenameold = urldecode( xfgmc_optionGET( 'xfgmc_file_file', $numFeed, 'set_arr' ) );
	new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; $filenameold = ' . $filenameold . '; Файл: functions.php; Строка: ' . __LINE__ );
	new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; $filenamenew = ' . $filenamenew . '; Файл: functions.php; Строка: ' . __LINE__ );

	if ( rename( $filenameold, $filenamenew ) === FALSE ) {
		new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; Не могу переименовать файл из ' . $filenameold . ' в ' . $filenamenew . '! Файл: functions.php; Строка: ' . __LINE__ );
		return false;
	} else {
		xfgmc_optionUPD( 'xfgmc_file_url', urlencode( $filenamenewurl ), $numFeed, 'yes', 'set_arr' );
		new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; Файл переименован! Файл: functions.php; Строка: ' . __LINE__ );
		return true;
	}
}
/**
 * Возвращает URL без get-параметров или возвращаем только get-параметры
 * 
 * @since 1.0.0
 *
 * @param string $url (require)
 * @param string $whot (not require)
 *
 * @return string
 */
function xfgmc_deleteGET( $url, $whot = 'url' ) {
	$url = str_replace( "&amp;", "&", $url ); // Заменяем сущности на амперсанд, если требуется
	list( $url_part, $get_part ) = array_pad( explode( "?", $url ), 2, "" ); // Разбиваем URL на 2 части: до знака ? и после
	if ( $whot == 'url' ) {
		return $url_part; // Возвращаем URL без get-параметров (до знака вопроса)
	} else if ( $whot == 'get' ) {
		return $get_part; // Возвращаем get-параметры (без знака вопроса)
	} else {
		return false;
	}
}
/**
 * @since 2.0.0
 *
 * @param string $optName (require)
 * @param string $value (require)
 * @param string $n (not require)
 * @param string $autoload (not require) (yes/no) (@since 2.4.0)
 * @param string $type (not require) (@since 2.4.0)
 * @param string $source_settings_name (not require) (@since 2.4.0)
 *
 * @return true/false
 * Возвращает то, что может быть результатом add_blog_option, add_option
 */
function xfgmc_optionADD( $option_name, $value = '', $n = '', $autoload = 'yes', $type = 'option', $source_settings_name = '' ) {
	if ( $option_name == '' ) {
		return false;
	}
	switch ( $type ) {
		case "set_arr":
			if ( $n === '' ) {
				$n = '1';
			}
			$xfgmc_settings_arr = xfgmc_optionGET( 'xfgmc_settings_arr' );
			$xfgmc_settings_arr[ $n ][ $option_name ] = $value;
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), 'xfgmc_settings_arr', $xfgmc_settings_arr );
			} else {
				return update_option( 'xfgmc_settings_arr', $xfgmc_settings_arr, $autoload );
			}
		case "custom_set_arr":
			if ( $source_settings_name === '' ) {
				return false;
			}
			if ( $n === '' ) {
				$n = '1';
			}
			$xfgmc_settings_arr = xfgmc_optionGET( $source_settings_name );
			$xfgmc_settings_arr[ $n ][ $option_name ] = $value;
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), $source_settings_name, $xfgmc_settings_arr );
			} else {
				return update_option( $source_settings_name, $xfgmc_settings_arr, $autoload );
			}
		default:
			if ( $n === '1' ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return add_blog_option( get_current_blog_id(), $option_name, $value );
			} else {
				return add_option( $option_name, $value, '', $autoload );
			}
	}
}
/**
 * @since 2.0.0
 *
 * @param string $optName (require)
 * @param string $value (require)
 * @param string $n (not require)
 * @param string $autoload (not require) (yes/no) (@since 2.4.0)
 * @param string $type (not require) (@since 2.4.0)
 * @param string $source_settings_name (not require) (@since 2.4.0)
 *
 * @return true/false
 * Возвращает то, что может быть результатом update_blog_option, update_option
 */
function xfgmc_optionUPD( $option_name, $value = '', $n = '', $autoload = 'yes', $type = '', $source_settings_name = '' ) {
	if ( $option_name == '' ) {
		return false;
	}
	switch ( $type ) {
		case "set_arr":
			if ( $n === '' ) {
				$n = '1';
			}
			$xfgmc_settings_arr = xfgmc_optionGET( 'xfgmc_settings_arr' );
			$xfgmc_settings_arr[ $n ][ $option_name ] = $value;
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), 'xfgmc_settings_arr', $xfgmc_settings_arr );
			} else {
				return update_option( 'xfgmc_settings_arr', $xfgmc_settings_arr, $autoload );
			}
		case "custom_set_arr":
			if ( $source_settings_name === '' ) {
				return false;
			}
			if ( $n === '' ) {
				$n = '1';
			}
			$xfgmc_settings_arr = xfgmc_optionGET( $source_settings_name );
			$xfgmc_settings_arr[ $n ][ $option_name ] = $value;
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), $source_settings_name, $xfgmc_settings_arr );
			} else {
				return update_option( $source_settings_name, $xfgmc_settings_arr, $autoload );
			}
		default:
			if ( $n === '1' ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), $option_name, $value );
			} else {
				return update_option( $option_name, $value, $autoload );
			}
	}
}
/**
 * @since 1.0.0
 * @updated in v2.0.0
 *
 * @param string $optName (require)
 * @param string $n (not require)
 * @param string $type (not require) (@since 2.4.0)
 * @param string $source_settings_name (not require) (@since 2.4.0)
 *
 * @return mixed
 * Возвращает то, что может быть результатом get_blog_option, get_option
 */
function xfgmc_optionGET( $option_name, $n = '', $type = '', $source_settings_name = '' ) {
	if ( $option_name == 'xfgmc_status_sborki' && $n == '1' ) {
		if ( is_multisite() ) {
			return get_blog_option( get_current_blog_id(), 'xfgmc_status_sborki' );
		} else {
			return get_option( 'xfgmc_status_sborki' );
		}
	}

	if ( defined( 'xfgmcp_VER' ) ) {
		$pro_ver_number = xfgmcp_VER;
	} else {
		$pro_ver_number = '2.2.7';
	}
	if ( version_compare( $pro_ver_number, '2.3.0', '<' ) ) { // если версия PRO ниже 2.3.0
		if ( $option_name === 'xfgmcp_compare_value' ) {
			if ( $n === '1' ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return get_blog_option( get_current_blog_id(), $option_name );
			} else {
				return get_option( $option_name );
			}
		}
		if ( $option_name === 'xfgmcp_compare' ) {
			if ( $n === '1' ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return get_blog_option( get_current_blog_id(), $option_name );
			} else {
				return get_option( $option_name );
			}
		}
	}

	if ( $option_name == '' ) {
		return false;
	}
	switch ( $type ) {
		case "set_arr":
			if ( $n === '' ) {
				$n = '1';
			}
			$xfgmc_settings_arr = xfgmc_optionGET( 'xfgmc_settings_arr' );
			if ( isset( $xfgmc_settings_arr[ $n ][ $option_name ] ) ) {
				return $xfgmc_settings_arr[ $n ][ $option_name ];
			} else {
				return false;
			}
		case "custom_set_arr":
			if ( $source_settings_name === '' ) {
				return false;
			}
			if ( $n === '' ) {
				$n = '1';
			}
			$xfgmc_settings_arr = xfgmc_optionGET( $source_settings_name );
			if ( isset( $xfgmc_settings_arr[ $n ][ $option_name ] ) ) {
				return $xfgmc_settings_arr[ $n ][ $option_name ];
			} else {
				return false;
			}
		case "for_update_option":
			if ( $n === '1' ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return get_blog_option( get_current_blog_id(), $option_name );
			} else {
				return get_option( $option_name );
			}
		default:
			/* for old premium versions */
			if ( $option_name === 'xfgmc_desc' ) {
				return xfgmc_optionGET( $option_name, $n, 'set_arr' );
			}
			if ( $option_name === 'xfgmc_no_default_png_products' ) {
				return xfgmc_optionGET( $option_name, $n, 'set_arr' );
			}
			if ( $option_name === 'xfgmc_whot_export' ) {
				return xfgmc_optionGET( $option_name, $n, 'set_arr' );
			}
			if ( $option_name === 'xfgmc_feed_assignment' ) {
				return xfgmc_optionGET( $option_name, $n, 'set_arr' );
			}
			/* for old premium versions */
			if ( $n === '1' ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return get_blog_option( get_current_blog_id(), $option_name );
			} else {
				return get_option( $option_name );
			}
	}
}
/**
 * @since 2.0.0
 *
 * @param string $optName (require)
 * @param string $n (not require)
 * @param string $type (not require) (@since 2.4.0)
 * @param string $source_settings_name (not require) (@since 2.4.0)
 *
 * @return true/false
 * Возвращает то, что может быть результатом delete_blog_option, delete_option
 */
function xfgmc_optionDEL( $option_name, $n = '', $type = '', $source_settings_name = '' ) {
	if ( $option_name == '' ) {
		return false;
	}
	switch ( $type ) {
		case "set_arr":
			if ( $n === '' ) {
				$n = '1';
			}
			$xfgmc_settings_arr = xfgmc_optionGET( 'xfgmc_settings_arr' );
			unset( $xfgmc_settings_arr[ $n ][ $option_name ] );
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), 'xfgmc_settings_arr', $xfgmc_settings_arr );
			} else {
				return update_option( 'xfgmc_settings_arr', $xfgmc_settings_arr );
			}
		case "custom_set_arr":
			if ( $source_settings_name === '' ) {
				return false;
			}
			if ( $n === '' ) {
				$n = '1';
			}
			$xfgmc_settings_arr = xfgmc_optionGET( $source_settings_name );
			unset( $xfgmc_settings_arr[ $n ][ $option_name ] );
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), $source_settings_name, $xfgmc_settings_arr );
			} else {
				return update_option( $source_settings_name, $xfgmc_settings_arr );
			}
		default:
			if ( $n === '1' ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return delete_blog_option( get_current_blog_id(), $option_name );
			} else {
				return delete_option( $option_name );
			}
	}
}
/**
 * @since 1.0.0
 * @updated in v2.0.0
 *
 * @param string $result_xml (require)
 * @param string $postId (require)
 * @param string $numFeed (not require) (string)
 * @param string $ids_in_xml (not require)
 *
 * @return void
 * Создает tmp файл-кэш товара
 */
function xfgmc_wf( $result_xml, $postId, $numFeed = '1', $ids_in_xml = '' ) {
	$upload_dir = (object) wp_get_upload_dir();
	$name_dir = $upload_dir->basedir . '/xfgmc/feed' . $numFeed;
	if ( ! is_dir( $name_dir ) ) {
		error_log( 'WARNING: Папки $name_dir =' . $name_dir . ' нет; Файл: functions.php; Строка: ' . __LINE__, 0 );
		if ( ! mkdir( $name_dir ) ) {
			error_log( 'ERROR: Создать папку $name_dir =' . $name_dir . ' не вышло; Файл: functions.php; Строка: ' . __LINE__, 0 );
		} else {
			if ( xfgmc_optionGET( 'yzen_yandex_zeng_rss' ) == 'enabled' ) {
				$result_yml = xfgmc_optionGET( 'xfgmc_feed_content' );
			}
			;
		}
	} else {
		if ( xfgmc_optionGET( 'yzen_yandex_zeng_rss' ) == 'enabled' ) {
			$result_yml = xfgmc_optionGET( 'xfgmc_feed_content' );
		}
		;
	}
	if ( is_dir( $name_dir ) ) {
		$filename = $name_dir . '/' . $postId . '.tmp';
		$fp = fopen( $filename, "w" );
		fwrite( $fp, $result_xml ); // записываем в файл текст
		fclose( $fp ); // закрываем

		/* C версии 2.0.0 */
		$filename = $name_dir . '/' . $postId . '-in.tmp';
		$fp = fopen( $filename, "w" );
		fwrite( $fp, $ids_in_xml );
		fclose( $fp );
		/* end с версии 2.0.0 */
	} else {
		error_log( 'ERROR: Нет папки xfgmc! $name_dir =' . $name_dir . '; Файл: functions.php; Строка: ' . __LINE__, 0 );
	}
}

/**
 * @since 1.0.0
 *
 * @param string $text (require)
 * @param int $charlength (not require) 
 *
 * @return $text
 * Сокращает число символов в описании, чтобы не нарушать лимити Гугла
 */
function xfgmc_max_lim_text( $text, $charlength = 5000 ) {
	if ( mb_strlen( $text ) > $charlength ) {
		$charlength = $charlength - 3;
		$text = mb_strimwidth( $text, 0, $charlength );
		return $text . '...';
	} else {
		return $text;
	}
}
/**
 * Создает пустой файл ids_in_xml.tmp или очищает уже имеющийся
 * @since 2.0.0
 *
 * @param string $numFeed (not require) 
 *
 * @return void
 */
function xfgmc_clear_file_ids_in_xml( $numFeed = '1' ) {
	$xfgmc_file_ids_in_xml = urldecode( xfgmc_optionGET( 'xfgmc_file_ids_in_xml', $numFeed, 'set_arr' ) );
	if ( ! is_file( $xfgmc_file_ids_in_xml ) ) {
		new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; WARNING: Файла c idшниками $xfgmc_file_ids_in_xml = ' . $xfgmc_file_ids_in_xml . ' нет! Создадим пустой; Файл: function.php; Строка: ' . __LINE__ );
		$xfgmc_file_ids_in_xml = XFGMC_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $numFeed . '/ids_in_xml.tmp';
		$res = file_put_contents( $xfgmc_file_ids_in_xml, '' );
		if ( $res !== false ) {
			new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; NOTICE: Файл c idшниками $xfgmc_file_ids_in_xml = ' . $xfgmc_file_ids_in_xml . ' успешно создан; Файл: function.php; Строка: ' . __LINE__ );
			xfgmc_optionUPD( 'xfgmc_file_ids_in_xml', urlencode( $xfgmc_file_ids_in_xml ), $numFeed, 'yes', 'set_arr' );
		} else {
			new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; ERROR: Ошибка создания файла $xfgmc_file_ids_in_xml = ' . $xfgmc_file_ids_in_xml . '; Файл: function.php; Строка: ' . __LINE__ );
		}
	} else {
		new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; NOTICE: Обнуляем файл $xfgmc_file_ids_in_xml = ' . $xfgmc_file_ids_in_xml . '; Файл: function.php; Строка: ' . __LINE__ );
		file_put_contents( $xfgmc_file_ids_in_xml, '' );
	}
}
/**
 * @since 2.2.0
 *
 * @return string
 */
function xfgmc_formatSize( $bytes ) {
	if ( $bytes >= 1073741824 ) {
		$bytes = number_format( $bytes / 1073741824, 2 ) . ' GB';
	} elseif ( $bytes >= 1048576 ) {
		$bytes = number_format( $bytes / 1048576, 2 ) . ' MB';
	} elseif ( $bytes >= 1024 ) {
		$bytes = number_format( $bytes / 1024, 2 ) . ' KB';
	} elseif ( $bytes > 1 ) {
		$bytes = $bytes . ' B';
	} elseif ( $bytes == 1 ) {
		$bytes = $bytes . ' B';
	} else {
		$bytes = '0 B';
	}
	return $bytes;
}
/**
 * @since 2.2.1
 *
 * @return string
 */
function xfgmc_product_type( $catid, $numFeed = '1', $result = '', $parent_id = false ) {
	if ( $parent_id === false ) {
		$term = get_term( $catid, 'product_cat', 'OBJECT' );
	} else {
		$term = get_term( $parent_id, 'product_cat', 'OBJECT' );
	}

	if ( is_wp_error( $term ) ) {
		new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; ERROR: get_term для $catid = ' . $catid . ' вернула wp_error; Файл: function.php; Строка: ' . __LINE__ );
		$error = $term;
		$err = 'error_key = ' . $error->get_error_code() . '; ';
		$err .= 'error_message = ' . $error->get_error_message() . '; ';
		$err .= 'error_data = ' . $error->get_error_data();
		new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; ERROR: $err = ' . $err . '; Файл: function.php; Строка: ' . __LINE__ );
	} else if ( $term === null ) {
		new XFGMC_Error_Log( 'FEED № ' . $numFeed . '; ERROR: get_term для $catid = ' . $catid . ' вернула null; Файл: function.php; Строка: ' . __LINE__ );
	} else {
		if ( is_object( $term ) ) {
			if ( $term->parent == 0 ) {
				$xfgmc_product_type_home = xfgmc_optionGET( 'xfgmc_product_type_home', $numFeed, 'set_arr' );
				if ( $xfgmc_product_type_home == '' ) {
					if ( $result === '' ) {
						$result = $term->name;
					} else {
						$result = $term->name . ' > ' . $result;
					}
				} else {
					if ( $result === '' ) {
						$result = $xfgmc_product_type_home . ' > ' . $term->name;
					} else {
						$result = $xfgmc_product_type_home . ' > ' . $term->name . ' > ' . $result;
					}
				}
			} else {
				if ( $result === '' ) {
					$result = $term->name;
					$result = xfgmc_product_type( $catid, $numFeed, $result, $term->parent );
				} else {
					$result = $term->name . ' > ' . $result;
					$result = xfgmc_product_type( $catid, $numFeed, $result, $term->parent );
				}
			}
		}
	}
	return $result;
}
/**
 * @since 2.2.11
 *
 * @param string $string
 * @param string $numFeed
 * 
 * @return string
 */
function xfgmc_replace_decode( $string, $numFeed = '1' ) {
	$string = str_replace( "+", 'xfgmc', $string );
	//$string = str_replace(";", 'xfgmctz', $string);
	$string = urldecode( $string );
	$string = str_replace( "xfgmc", '+', $string );
	//$string = str_replace("xfgmctz", ';', $string);
	$string = apply_filters( 'xfgmc_replace_decode_filter', $string, $numFeed );
	return $string;
}
/**
 * @since 2.3.4
 *
 * @return array
 */
function xfgmc_possible_problems_list() {
	$possibleProblems = '';
	$possibleProblemsCount = 0;
	$conflictWithPlugins = 0;
	$conflictWithPluginsList = '';
	$check_global_attr_count = wc_get_attribute_taxonomies();
	if ( count( $check_global_attr_count ) < 1 ) {
		$possibleProblemsCount++;
		$possibleProblems .= '<li>' . __( 'Your site has no global attributes! This may affect the quality of the XML feed. This can also cause difficulties when setting up the plugin', 'xml-for-google-merchant-center' ) . '. <a href="https://icopydoc.ru/global-and-local-attributes-in-woocommerce/?utm_source=xml-for-google-merchant-center&utm_medium=organic&utm_campaign=in-plugin-xml-for-google-merchant-center&utm_content=debug-page&utm_term=no-local-attr">' . __( 'Please read the recommendations', 'xml-for-google-merchant-center' ) . '</a>.</li>';
	}
	if ( is_plugin_active( 'snow-storm/snow-storm.php' ) ) {
		$possibleProblemsCount++;
		$conflictWithPlugins++;
		$conflictWithPluginsList .= 'Snow Storm<br/>';
	}
	if ( is_plugin_active( 'email-subscribers/email-subscribers.php' ) ) {
		$possibleProblemsCount++;
		$conflictWithPlugins++;
		$conflictWithPluginsList .= 'Email Subscribers & Newsletters<br/>';
	}
	if ( is_plugin_active( 'saphali-search-castom-filds/saphali-search-castom-filds.php' ) ) {
		$possibleProblemsCount++;
		$conflictWithPlugins++;
		$conflictWithPluginsList .= 'Email Subscribers & Newsletters<br/>';
	}
	if ( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {
		$possibleProblemsCount++;
		$conflictWithPlugins++;
		$conflictWithPluginsList .= 'W3 Total Cache<br/>';
	}
	if ( is_plugin_active( 'docket-cache/docket-cache.php' ) ) {
		$possibleProblemsCount++;
		$conflictWithPlugins++;
		$conflictWithPluginsList .= 'Docket Cache<br/>';
	}
	if ( class_exists( 'MPSUM_Updates_Manager' ) ) {
		$possibleProblemsCount++;
		$conflictWithPlugins++;
		$conflictWithPluginsList .= 'Easy Updates Manager<br/>';
	}
	if ( class_exists( 'OS_Disable_WordPress_Updates' ) ) {
		$possibleProblemsCount++;
		$conflictWithPlugins++;
		$conflictWithPluginsList .= 'Disable All WordPress Updates<br/>';
	}
	if ( $conflictWithPlugins > 0 ) {
		$possibleProblemsCount++;
		$possibleProblems .= '<li><p>' . __( 'Most likely, these plugins negatively affect the operation of', 'xml-for-google-merchant-center' ) . ' XML for Google Merchant Center:</p>' . $conflictWithPluginsList . '<p>' . __( 'If you are a developer of one of the plugins from the list above, please contact me', 'xml-for-google-merchant-center' ) . ': <a href="mailto:support@icopydoc.ru">support@icopydoc.ru</a>.</p></li>';
	}
	return array( $possibleProblems, $possibleProblemsCount, $conflictWithPlugins, $conflictWithPluginsList );
}
/**
 * @since 2.5.0
 *
 * @param string $dir (require)
 *
 * @return void
 */
function xfgmc_remove_directory( $dir ) {
	if ( $objs = glob( $dir . "/*" ) ) {
		foreach ( $objs as $obj ) {
			is_dir( $obj ) ? xfgmc_remove_directory( $obj ) : unlink( $obj );
		}
	}
	rmdir( $dir );
}
/**
 * @since 2.5.0
 *
 * @return int
 * Возвращает количетсво всех фидов
 */
function xfgmc_number_all_feeds() {
	$xfgmc_settings_arr = xfgmc_optionGET( 'xfgmc_settings_arr' );
	if ( $xfgmc_settings_arr === false ) {
		return -1;
	} else {
		return count( $xfgmc_settings_arr );
	}
}
/**
 * @since 2.6.0
 *
 * @return (string) feed ID or (string)''
 * Получает первый фид. Используется на случай если get-параметр numFeed не указан
 */
function xfgmc_get_first_feed_id() {
	$xfgmc_settings_arr = xfgmc_optionGET( 'xfgmc_settings_arr' );
	if ( ! empty( $xfgmc_settings_arr ) ) {
		return (string) array_key_first( $xfgmc_settings_arr );
	} else {
		return '';
	}
}