<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// if uninstall not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option('ptmbg_covid_info_options');
delete_site_option('ptmbg_covid_info_options');
delete_option('ptmbg_covid_info_data');
delete_site_option('ptmbg_covid_info_data');
