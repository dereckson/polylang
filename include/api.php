<?php

/*
 * template tag: displays the language switcher
 *
 * list of parameters accepted in $args:
 *
 * dropdown               => displays a dropdown if set to 1, defaults to 0
 * echo                   => echoes the the switcher if set to 1 (default)
 * hide_if_empty          => hides languages with no posts (or pages) if set to 1 (default)
 * show_flags             => shows flags if set to 1, defaults to 0
 * show_names             => shows languages names if set to 1 (default)
 * display_names_as       => wether to display the language name or code. valid options are 'slug' and 'name'
 * force_home             => forces linking to the home page is set to 1, defaults to 0
 * hide_if_no_translation => hides the link if there is no translation if set to 1, defaults to 0
 * hide_current           => hides the current language if set to 1, defaults to 0
 * post_id                => if not null, link to translations of post defined by post_id, defaults to null
 * raw                    => set this to true to build your own custom language switcher, defaults to 0
 *
 * @since 0.5
 *
 * @param array $args optional
 * @return null|string|array null if displaying, array if raw is requested, string otherwise
 */
function pll_the_languages($args = '') {
	global $polylang;
	if ($polylang instanceof PLL_Frontend && !empty($polylang->links)) {
		$switcher = new PLL_Switcher;
		return $switcher->the_languages($polylang->links, $args);
	}
	return '';
}

/**
 * Gets an array with the cached infomrmation about installed languages.
 *
 * @return array the installed languages, each item a PLL_Language object
 */
function pll_get_languages_list () {
	global $polylang;

	return $polylang->model->get_languages_list();
}

/*
 * returns the current language
 *
 * @since 0.8.1
 *
 * @param string $field optional the language field to return 'name', 'locale', defaults to 'slug'
 * @return string the requested field for the current language
 */
function pll_current_language($field = 'slug') {
	global $polylang;
	return isset($polylang->curlang->$field) ? $polylang->curlang->$field : false;
}

/*
 * returns the default language
 *
 * @since 1.0
 *
 * @param string $field optional the language field to return 'name', 'locale', defaults to 'slug'
 * @return string the requested field for the default language
 */
function pll_default_language($field = 'slug') {
	global $polylang;
	return isset($polylang->options['default_lang']) && ($lang = $polylang->model->get_language($polylang->options['default_lang'])) && isset($lang->$field) ? $lang->$field : false;
}

/*
 * among the post and its translations, returns the id of the post which is in the language represented by $slug
 *
 * @since 0.5
 *
 * @param int $post_id post id
 * @param string $slug optional language code, defaults to current language
 * @return int post id of the translation if exists
 */
function pll_get_post($post_id, $slug = '') {
	global $polylang;
	return isset($polylang) && ($slug = $slug ? $slug : pll_current_language()) ? $polylang->model->get_post($post_id, $slug) : null;
}

/*
 * among the term and its translations, returns the id of the term which is in the language represented by $slug
 *
 * @since 0.5
 *
 * @param int $term_id term id
 * @param string $slug optional language code, defaults to current language
 * @return int term id of the translation if exists
 */
function pll_get_term($term_id, $slug = '') {
	global $polylang;
	return isset($polylang) && ($slug = $slug ? $slug : pll_current_language()) ? $polylang->model->get_term($term_id, $slug) : null;
}

/*
 * returns the home url in the current language
 *
 * @since 0.8
 *
 * @param string $lang language code (optional on frontend)
 * @return string
 */
function pll_home_url($lang = '') {
	global $polylang;

	if (empty($lang))
		$lang = pll_current_language();

	return isset($polylang) && !empty($polylang->links) && !empty($lang) ? $polylang->links->get_home_url($lang) : home_url('/');
}

/*
 * registers a string for translation in the "strings translation" panel
 *
 * @since 0.6
 *
 * @param string $name a unique name for the string
 * @param string $string the string to register
 * @param string $context optional the group in which the string is registered, defaults to 'polylang'
 * @param bool $multiline optional wether the string table should display a multiline textarea or a single line input, defaults to single line
 */
function pll_register_string($name, $string, $context = 'polylang', $multiline = false) {
	global $polylang;
	if ($polylang instanceof PLL_Admin && !empty($polylang->settings_page))
		$polylang->settings_page->register_string($name, $string, $context, $multiline);
}

/*
 * translates a string (previously registered with pll_register_string)
 *
 * @since 0.6
 *
 * @param string $string the string to translate
 * @return string the string translation in the current language
 */
function pll__($string) {
	return __($string, 'pll_string');
}

/*
 * echoes a translated string (previously registered with pll_register_string)
 *
 * @since 0.6
 *
 * @param string $string the string to translate
 */
function pll_e($string) {
	_e($string, 'pll_string');
}

/*
 * returns true if Polylang manages languages and translations for this post type
 *
 * @since 1.0.1
 *
 * @param string post type name
 * @return bool
 */
function pll_is_translated_post_type($post_type) {
	global $polylang;
	return isset($polylang) && $polylang->model->is_translated_post_type($post_type);
}

/*
 * returns true if Polylang manages languages and translations for this taxonomy
 *
 * @since 1.0.1
 *
 * @param string taxonomy name
 * @return bool
 */
function pll_is_translated_taxonomy($tax) {
	global $polylang;
	return isset($polylang) && $polylang->model->is_translated_taxonomy($tax);
}

/**
 * Gets default language information
 *
 * @param string $language_code ISO 639 or locale code
 * @return array|null the default information for the the specified language, or null if it doesn't exist
 */
function pll_get_default_language_information($language_code) {
	global $polylang;
	require(PLL_ADMIN_INC.'/languages.php');
	foreach ($languages as $language) {
		if ($language[0] == $language_code || $language[1] == $language_code) {
			$rtl = (count($language) > 3) && ($language[3] == 'rtl');
			return array(
				'code' => $language[0],
				'locale' => $language[1],
				'name' => $language[2],
				'rtl' => $rtl
			);
		}
	}
	return null;
}

/**
 * Determines if the specified language code is a valid one.
 *
 * @param string $language_code ISO 639 or locale code
 * @return bool true if the language code is valid; otherwise, false.
 */
function pll_is_valid_language_code($language_code) {
	return pll_get_default_language_information($language_code) !== null;
}

/**
 * Adds a language with the default locale, name and direction.
 *
 * @param string $language_code ISO 639 or locale code
 * @param int $language_order language order [optional]
 * @param int &$error_code the error code, or 0 if the operation is succesful
 * @return bool true if the language has been added; false if an error has occured
 */
function pll_add_language($language_code, $language_order = 0, &$error_code = 0) {
	global $polylang;

	$adminModel = new PLL_Admin_Model($polylang->options);

	$info = pll_get_default_language_information($language_code);

	$args = array(
		name => $info['name'],
		slug => $info['code'],
		locale => $info['locale'],
		rtl => $info['rtl'] ? 1 : 0,
		term_group => $language_order
	);
	$error_code = $adminModel->add_language($args);
	return $error_code !== 0;
}

/**
 * Determines whether a language is currently installed.
 *
 * @param string $language_code The language slug
 * @return bool true if the language is installed; otherwise, false.
 */
function pll_is_language_installed($language_code) {
	global $polylang;

	$languages = $polylang->model->get_languages_list();
	foreach ($languages as $language) {
		if ($language->slug == $language_code) {
			return true;
		}
	}

	return false;
}