[![Build Status](https://travis-ci.org/gitlost/ghostscript-only-pdf-preview.png?branch=master)](https://travis-ci.org/gitlost/ghostscript-only-pdf-preview)
[![codecov.io](http://codecov.io/github/gitlost/ghostscript-only-pdf-preview/coverage.svg?branch=master)](http://codecov.io/github/gitlost/ghostscript-only-pdf-preview?branch=master)
# GhostScript Only PDF Preview #
**Contributors:** [gitlost](https://profiles.wordpress.org/gitlost)  
**Tags:** GhostScript, PDF, PDF Preview, GhostScript Only  
**Requires at least:** 4.7.0  
**Tested up to:** 4.7.1  
**Stable tag:** 1.0.0  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Uses GhostScript directly to generate PDF previews.

## Description ##

The plugin pre-empts the standard WordPress 4.7 PDF preview production process (which uses the PHP extension `Imagick`) to call GhostScript directly to produce the preview.

This means that only GhostScript is required on the server. Neither the PHP module `Imagick` nor the server package `ImageMagick` is needed or used (though it's fine if they're installed anyway, and if they are they'll be used by WP (unless you override it) to produce the intermediate sizes of the preview).

### Background ###

The plugin was prompted by the demonstration `WP_Image_Editor_Imagick_External` class uploaded to the WP Trac ticket [#39262 Fall back to ImageMagick command line when the pecl imagic is not available on the server](https://core.trac.wordpress.org/ticket/39262) by [Hristo Pandjarov](https://profiles.wordpress.org/hristo-sg), and by the wish to solve the WP Trac ticket [#39216 PDFs with non-opaque alpha channels can result in previews with black backgrounds.](https://core.trac.wordpress.org/ticket/39216), which particularly affects PDFs with CMYK color spaces (common in the print world).

The plugin by-passes (as far as PDF previews are concerned) #39216, and also by-passes the related issue [#39331 unsharpMaskImage in Imagick's thumbnail_image is not compatible with CMYK jpegs.](https://core.trac.wordpress.org/ticket/39331), as the preview jpegs produced directly by GhostScript use sRGB color spaces.

### Limitations ###

The plugin requires the [PHP function `exec`](http://php.net/manual/en/function.exec.php) to be enabled on your system. So if the [PHP ini setting `disable_functions`](http://php.net/manual/en/ini.core.php#ini.disable-functions) includes `exec`, the plugin won't work. Neither will it work if the (somewhat outdated) [`suhosin` security extension](https://suhosin.org/stories/index.html) is installed and `exec` is [blacklisted](https://suhosin.org/stories/configuration.html#suhosin-executor-func-blacklist).

Also, the plugin is incompatible with the [PHP ini setting `safe_mode`](http://php.net/manual/en/ini.sect.safe-mode.php#ini.safe-mode), an old (and misnamed) setting that was deprecated in PHP 5.3.0 and removed in PHP 5.4.0.

### Security ###

The plugin uses the PHP function `exec` to call GhostScript as a shell command. This has security implications as uncareful use with user supplied data (eg the upload file name or the file itself) could introduce an attack vector.

I believe these concerns are addressed here through screening of the file and its name and escaping of arguments. This belief is backed by a bounty of fifteen hundred thousand intergalactic credits to anyone who spots a security issue. Please disclose responsibly.

### Performance ###

Unsurprisingly it's faster. Crude benchmarking (see the [script `perf_vs_imagick.php`](https://github.com/gitlost/ghostscript-only-pdf-preview/blob/master/perf/perf_vs_imagick.php)) suggest it's around 40% faster. However the production of the preview is only a part of the overhead of uploading a PDF (and doesn't include producing the intermediate thumbnail sizes for instance) so any speed-up will probably not be that noticeable.

On jpeg thumbnail size it appears to be comparable, maybe a bit larger on average. To mitigate this the default jpeg quality for the PDF preview has been lowered to 70 (from 82), which results in some extra "ringing" (speckles around letters) but the previews tested remain very readable. Note that this only affects the "full" PDF thumbnail - the intermediate-sized thumbnails as produced by `Imagick` or `GD` and any other non-PDF images remain at the standard jpeg quality of 82. Use the [WP filter `wp_editor_set_quality`](https://developer.wordpress.org/reference/hooks/wp_editor_set_quality/) to override this, for instance to restore the quality to 82 you could add to your theme's "functions.php":

	function mytheme_wp_editor_set_quality( $quality, $mime_type ) {
		if ( 'application/pdf' === $mime_type ) {
			$quality = 82;
		}
		return $quality;
	}
	add_filter( 'wp_editor_set_quality', 'mytheme_wp_editor_set_quality', 10, 2 );

### Tool ###

A basic administration tool to regenerate (or generate, if they previously didn't have a preview) the previews of all PDFs uploaded to the system is included. Note that if you have a lot of PDFs you may experience the White Screen Of Death (WSOD) if the tool exceeds the [maximum execution time](http://php.net/manual/en/info.configuration.php#ini.max-execution-time) allowed. Note also that as the filenames of the previews don't (normally) change, you will probably have to refresh your browser to see the updated thumbnails.

As workarounds for the possible WSOD issue above, and as facilities in themselves, a "Regenerate PDF Previews" bulk action is added to the list mode of the Media Library, and a "Regenerate Preview" row action is added to each PDF entry in the list. So previews can be regenerated in batches or individually instead.

### And ###

A google-cheating schoolboy French translation is supplied.

The plugin runs on WP 4.7.0, and requires GhostScript to be installed on the server. The plugin should run on PHP 5.2.17 to 7.1, and on both Unix and Windows systems.

The project is on [github](https://github.com/gitlost/ghostscript-only-pdf-preview).

## Installation ##

Install the plugin in the standard way via the 'Plugins' menu in WordPress and then activate.

To install GhostScript, see [How to install Ghostscript](https://ghostscript.com/doc/current/Install.htm) on the official GhostScript site. For Ubuntu users, there's a package:

	sudo apt-get install ghostscript

For Windows, there's an installer available at the [GhostScript download page](https://ghostscript.com/download/gsdnld.html).

## Frequently Asked Questions ##

### What filters are available? ###

Three plugin-specific filters are available:

* `gopp_editor_set_resolution` sets the resolution of the PDF preview.
* `gopp_editor_set_page` sets the page to render for the PDF preview.
* `gopp_image_gs_cmd_path` short-circuits the determination of the path of the GhostScript executable on your system.

The `gopp_editor_set_resolution` filter is an analogue of the standard [`wp_editor_set_quality`](https://developer.wordpress.org/reference/hooks/wp_editor_set_quality/) filter, and allows one to override the default resolution of 128 DPI used for the PDF preview. For instance, in your theme's "functions.php":

	function mytheme_gopp_editor_set_resolution( $resolution, $filename ) {
		return 100;
	}
	add_filter( 'gopp_editor_set_resolution', 'mytheme_gopp_editor_set_resolution', 10, 2 );

Similarly the `gopp_editor_set_page` filter allows one to override the default of rendering the first page:

	function mytheme_gopp_editor_set_page( $page, $filename ) {
		return 2; // Render the second page instead.
	}
	add_filter( 'gopp_editor_set_page', 'mytheme_gopp_editor_set_page', 10, 2 );

The `gopp_image_gs_cmd_path` filter is necessary if your GhostScript installation is in a non-standard location and the plugin fails to determine where it is (if this happens you'll get a **Warning: no GhostScript!** notice on activation):

	function mytheme_gopp_image_gs_cmd_path( $gs_cmd_path, $is_win ) {
		return $is_win ? 'D:\\My GhostScript Location\\bin\\gswin32c.exe' : '/my ghostscript location/gs';
	}
	add_filter( 'gopp_image_gs_cmd_path', 'mytheme_gopp_image_gs_cmd_path', 10, 2 );

The filter can also be used just for performance reasons, especially on Windows systems to save searching the registry and directories.

Note that the value of `gs_cmd_path` is cached as a transient by the plugin for performance reasons, with a lifetime of one day. You can clear it by de-activating and re-activating the plugin, or by manually calling the `clear` method of the GhostScript Image Editor:

	function mytheme_gopp_init() {
		if ( class_exists( 'GOPP_Image_Editor_GS' ) ) {
			GOPP_Image_Editor_GS::clear();
		}
	}
	add_filter( 'init', 'mytheme_gopp_init' );

## Screenshots ##

### 1. Before: upload of various PDFs with alpha channels and/or CMYK color spaces resulting in broken previews. ###
![Before: upload of various PDFs with alpha channels and/or CMYK color spaces resulting in broken previews.](https://github.com/gitlost/ghostscript-only-pdf-preview/raw/master/assets/screenshot-1.png)

### 2. After: upload of the same PDFs resulting in a result. ###
![After: upload of the same PDFs resulting in a result.](https://github.com/gitlost/ghostscript-only-pdf-preview/raw/master/assets/screenshot-2.png)

### 3. Regenerate PDF Previews administration tool front page. ###
![Regenerate PDF Previews administration tool front page.](https://github.com/gitlost/ghostscript-only-pdf-preview/raw/master/assets/screenshot-3.png)

### 4. Regenerate PDF Previews administration tool after processing. ###
![Regenerate PDF Previews administration tool after processing.](https://github.com/gitlost/ghostscript-only-pdf-preview/raw/master/assets/screenshot-4.png)

### 5. Regenerate PDF Previews bulk action in list mode of Media Library. ###
![Regenerate PDF Previews bulk action in list mode of Media Library.](https://github.com/gitlost/ghostscript-only-pdf-preview/raw/master/assets/screenshot-5.png)

### 6. Regenerate Preview row action in list mode of Media Library. ###
![Regenerate Preview row action in list mode of Media Library.](https://github.com/gitlost/ghostscript-only-pdf-preview/raw/master/assets/screenshot-6.png)


## Changelog ##

### 1.0.0 (13 Jan 2017) ###
* Initial release.

### 0.9.0 (8 Jan 2017) ###
* Initial github version.

## Upgrade Notice ##

### 1.0.0 ###
Improved PDF preview experience.
