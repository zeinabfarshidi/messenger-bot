<?php
/*
Plugin Name: Pashmak
Plugin URI: http://welearn.site
Description: our first plugin
Author: welearn team
Version: 1.0
Author URI: http://welearn.site
Text Domain: wl-pashmak
*/

add_action( 'admin_notices', 'test_echo' );
function test_echo() {
    echo 'hello welearn.site';
}