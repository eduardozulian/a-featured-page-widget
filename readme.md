# A Featured Page Widget #
*A WordPress widget to feature a page and display its excerpt and post thumbnail.*  


**Official WordPress plugin directory**: http://wordpress.org/plugins/a-featured-page-widget/  
**Contributors:** eduardozulian  
**Tags:** widget, sidebar, page widget, featured page, pages  
**Requires at least:** 3.0  
**Tested up to:** 3.6  
**Stable tag:** 1.1  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html

## Description ##

This plugin creates a widget that features a specific page, showing its excerpt. You can also choose a post thumbnail among the registered ones in your theme.

### Languages ###

* Portuguese (Brazil)

## Installation ##

1. Upload `a-featured-page-widget` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Widgets' menu and drag it to your sidebar

## Frequently Asked Questions ##

### Can I change the default text "Continue reading"? ###

Yes. In your `functions.php` file, you cand use the `afpw_link_text` filter:
```
<?php
function mytheme_change_afpw_link_text() {
    return 'Learn more';
}

add_filter( 'afpw_link_text', 'mytheme_change_afpw_link_text' );
?>
```

### Why am I unable to define a manual excerpt for my pages? ###

First, check if the option for excerpts is not showing under "Screen Options". If that's the case, probably your theme doesn't support excerpts in pages. You need to use [`add_post_type_support()`](http://codex.wordpress.org/Function_Reference/add_post_type_support) inside your `functions.php` file:
```
<?php
function mytheme_add_page_excerpt() {
    add_post_type_support( 'page', 'excerpt' );
}

add_action( 'init', 'mytheme_add_page_excerpt' );
?>
```

## Changelog ##

### 1.1 ###
* Added a filter for the "Continue reading" text.
* Now we have a FAQ.

### 1.0 ###
* First version.
