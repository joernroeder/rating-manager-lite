<?php
/*
 * This file contains admin UI functions
 */
 
 /*
 * Get icon list
 */
function elm_rml_icon_list() {
	$array = array(
		'star-1' => __('Star 1 ', 'elm'),
		'star-2' => __('Star 2 ', 'elm'),
		'star-3' => __('Star 3 ', 'elm'),
		'heart-1' => __('Heart 1 ', 'elm'),
		'heart-2' => __('Heart 2 ', 'elm')
	);

	return $array;
}

/*
 * Get font family list
 */
function elm_rml_font_family_list() {
	$array = array(
		'Georgia' => 'Georgia',
		'Palatino Linotype' => 'Palatino Linotype',
		'Times New Roman' => 'Times New Roman',
		'Arial' => 'Arial',
		'Arial Black' => 'Arial Black',
		'Comic Sans MS' => 'Comic Sans MS',
		'Impact' => 'Impact',
		'Lucida Sans Unicode' => 'Lucida Sans Unicode',
		'Tahoma' => 'Tahoma',
		'Trebuchet MS' => 'Trebuchet MS',
		'Verdana' => 'Verdana',
		'Courier New' => 'Courier New',
		'Lucida Console' => 'Lucida Console'
	);

	return $array;
}

/*
 * Get font style list
 */
function elm_rml_font_style_list() {
	$array = array(
		'normal' => 'Normal',
		'italic' => 'Italic'
	);

	return $array;
}

/*
 * Get font size list
 */
function elm_rml_font_size_list() {
	
	for( $i = 1; $i <= 35; $i++ ) {
		$px = $i . 'px';
		
		$array[$px] = $px;
	}

	return $array;
}

/*
 * Get image size list
 */
function elm_rml_image_size() {
	for( $i = 10; $i <= 100; $i++ ) {
		$px = $i . 'px';
		
		$array[$px] = $px;
	}

	return $array;
}