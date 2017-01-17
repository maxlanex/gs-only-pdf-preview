<?php

// Expose protected vars/methods.
require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
require_once dirname( dirname( __FILE__ ) ) . '/includes/class-gopp-image-editor-gs.php';
class Test_GOPP_Image_Editor_GS extends GOPP_Image_Editor_GS {

	public static function public_is_win() { return parent::is_win(); }
	public static function public_set_is_win( $is_win ) { self::$is_win = $is_win; }
	public static function public_gs_valid( $file, $no_read_check = false ) { return parent::gs_valid( $file, $no_read_check ); }
	public static function public_gs_cmd( $args ) { return parent::gs_cmd( $args ); }
	public static function public_gs_cmd_path() { return parent::gs_cmd_path(); }
	public static function public_set_gs_cmd_path( $path ) { parent::$gs_cmd_path = $path; }
	public function public_get_gs_args( $filename ) { return parent::get_gs_args( $filename ); }
	public function public_initial_gs_args() { return parent::initial_gs_args(); }
	public static function public_escapeshellarg( $arg ) { return parent::escapeshellarg( $arg ); }

	public function public_set_resolution( $resolution ) { $this->resolution = $resolution; }
	public function public_set_page( $page ) { $this->page = $page; }
	public function public_set_quality( $quality ) { $this->quality = $quality; }
}