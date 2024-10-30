<?php
/*
Plugin Name: Corona Virus Live Ticker
Description: Corona Virus Live Ticker
Author: Plamen Marinov
Version: 1.1
Author URI: https://www.webwapstudio.com/corona-virus-live-ticker.html
*/

if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Corona_Virus_Info_Live_Ticker' ) ):
/**
* Main The Corona Virus Live Ticker class
*/
class Corona_Virus_Info_Live_Ticker {
   public function __construct() {}
   public $position='';
   public function set_default_options() {
      $defaults = array(
  		'on_home'                 => '1',
  		'on_home_pos'             => 'top',
		'on_front'                => '1',
		'on_front_pos'            => 'top',
		'on_page'                 => '1',
		'on_page_pos'             => 'top',
		'on_post'                 => '1',
		'on_post_pos'             => 'top',
		'on_category'             => '1',
		'on_category_pos'         => 'top',
		'on_archive'              => '1',
        'on_archive_pos'          => 'top',
		'on_search'               => '1',
		'on_search_pos'           => 'top',
		'on_global'               => '1',
		'color'                   => '#FF0000',
		'global_link'             => 'https://www.arcgis.com/apps/opsdashboard/index.html#/bda7594740fd40299423467b48e9ecf6',
		'order_by'                => 'Country',
		'asc_desc'                => 'asc',
		'content_selector'        => '#site-content',
		'select_selector'         => '',
		'desktop_speed'           => '15',
		'mobile_speed'            => '15',
		'on_mobile'               => '1',
		'on_label'                => '1',
		'label_text'              => 'COVID19 Live Data',
		'label_color'             => '#FFFFFF',
		'on_new_confirmed'        => '1',
		'on_new_deaths'           => '1',
		'on_new_recovered'        => '1',
		'all'                     => '1',
		'use_links'               => '1',
		'open_links'               => '1'

     );
     $theme=wp_get_theme();
     $rows = @file(plugin_dir_path(__FILE__) . 'themes.db');
     foreach ($rows as $row) {
         $row=preg_replace("/\n|\r/","",$row);
         $d=preg_split("/\|/",$row);
         if ($theme['Name']==$d[0]) {
            $defaults['content_selector']=$d[2];
            break;
         }
    }
    $request = wp_remote_get('https://covid19.webwapstudio.com/countries/');
    $json = wp_remote_retrieve_body( $request );
    $rows=json_decode($json,true);
    foreach ($rows as $d) {
         $defaults['c_'.$d['CountryCode']]='1';
         $defaults['n_'.$d['CountryCode']]=$d['Country'];
         $defaults['l_'.$d['CountryCode']]=$d['URL'];
    }
    return $defaults;
  }
  
  public function set_default_data() {
     $json='';
     $request = wp_remote_get('https://covid19.webwapstudio.com/summary/');
     if ( is_wp_error( $request ) ) {
        $json='';
     } else {
        $json = wp_remote_retrieve_body( $request );
     }
     $defaults = array(
  		'json' => $json,
  		'last_updated' => '0'
    );
    return $defaults;
  }
   public function load_plugin() {
      if (get_option('ptmbg_covid_info_options')==false) {
         add_option('ptmbg_covid_info_options', $this->set_default_options());
      }
      if (get_option('ptmbg_covid_info_data')==false) {
         add_option('ptmbg_covid_info_data', $this->set_default_data());
      }
      if (is_admin()){
         add_action('admin_menu', array( $this,'menu_options'));
         add_action( 'admin_bar_menu', array( $this,'link_to_settings'),1000 );
         add_action('admin_notices', array($this,'my_admin_notice'));
         add_action('admin_init', array($this,'admin_init'));
      } else {
         add_action('wp_footer', array($this,'display_ticker_to_page'));
         add_shortcode( 'corona-virus-live-ticker', array($this,'ptm_covid_ticker_shortcode'));
      }
   }
   
   public function ptm_covid_ticker_shortcode() {
   
   }
   
   public function admin_init() {
      register_setting('ptmbg_covid_info_options','ptmbg_covid_info_options',array( $this,'options_validate'));
   }
   public function options_validate($input) {
      return $input;
   }
   public function menu_options() {
      add_options_page(__('Corona Virus Live Ticker','corona_virus_info'), __('Corona Virus Live Ticker','corona_virus_info'), 'manage_options', 'ptmbg_corona_virus_info', array( $this,'admin_options_page'));
  }

  public function link_to_settings( $wp_admin_bar ) {
	$args = array(
		'id'    => 'ptmbg_corona_virus_info',
		'title' => '<div class="wp-menu-image dashicons-before dashicons-info-outline" style="display:inline-block !important"></div>&nbsp;<span class="ab-label">' .__('Corona Virus Live Ticker','corona_virus_info') . '</span>',
        'href'  => admin_url( 'admin.php?page=ptmbg_corona_virus_info' ),
        'meta'  => array( 'class' => 'menupop' )
	);
	$wp_admin_bar->add_node( $args );
   }
   
   public function my_admin_notice() {
      global $pagenow;
      if ( isset($_GET['page']) ) {
         if (sanitize_text_field($_GET['page']) == 'ptmbg_corona_virus_info') {
            return;
         }
      }
   ?>
     <div class="notice notice-info is-dismissible"><div style="padding:20px;font-size:1.2em">
     <strong><?php echo __( 'Thank you for installing "Corona Virus Live Ticker" plugin!', 'corona_virus_info' );?></strong>
     <br><?php echo __( "Let's get started:", 'corona_virus_info' );?>
     <a href="/wp-admin/admin.php?page=ptmbg_corona_virus_info"><?php echo __( "Settings", 'corona_virus_info' );?></a>
     </div></div>
   <?php
   }
   
   public function admin_options_page() {
     if ( !current_user_can( 'manage_options' ) )  {
         wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
     }
     $options=get_option('ptmbg_covid_info_options');
?>
<style>
.nav-tab {cursor:pointer}
.nav-tab-active {cursor:default}
.settings-tab {padding:10px}
.switch {
  position: relative;
  display: inline-block;
  width: 28px;
  height: 16px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 12px;
  width: 12px;
  left: 2px;
  bottom: 2px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #4CAF50;
}

input:focus + .slider {
  box-shadow: 0 0 1px #4CAF50;
}

input:checked + .slider:before {
  -webkit-transform: translateX(12px);
  -ms-transform: translateX(12px);
  transform: translateX(12px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 24px;
}

.slider.round:before {
  border-radius: 50%;
}

.checkbox{margin:5px 0 2px 0; text-align:left;margin-left:2px}
.checkbox .label {position:relative;left:5px;top:0px;cursor:pointer;color:#333}

.settings-countries table {width:100%}
.settings-countries table td {width:33%}
.settings-countries table td input {width:100% !important}
.country_label {padding:3px 5px;background:#e5e5e5;font-weight:bold}
.char-nav {margin-right:20px;cursor:pointer}
.char-nav:hover {text-decoration:underline}
#top-btn {position:fixed;right:5px;bottom:5px;border:1px solid #900;border-radius:4px;cursor:pointer;width:32px;text-align:center;z-index:1000;background:#f00;padding:4px 0;color:#fff;display:none}

.radio-group {
  border: solid 2px #4CAF50;
  margin: 0 !important;
  border-radius: 5px;
  padding:0 !important;
  display: inline-block;
}

.radio-group input[type=radio] {
  display: none;
}

.radio-group label {
  display: inline-block;
  cursor: pointer;
  padding: 0px 10px;
  margin:0 !important;
  vertical-align: top;
}

input[type=radio]:checked + label {
  color: #fff;
  background: #4CAF50;
}

#settings-display .checkbox {margin:0;position:relative;top:-1px}
#settings-display .checkbox .label {position:relative;top:-2px}
</style>
<?php
    echo '<div style="position:relative">';
    echo '<h2>' . __('Corona Virus Live Ticker', 'corona_virus_info') . '</h2>';
    echo '<form action="options.php" method="post">';
    settings_fields('ptmbg_covid_info_options');
    echo '<div id="settings-ticker" class="settings-tab">';
    echo '<h2 class="nav-tab-wrapper nav-ticker" style="margin-bottom:20px;margin-top:0px">';
    echo '<span id="subnav-display" class="nav-tab nav-tab-active" onClick="showTickerTab(' . "'display'" . ')">' .  __('Select Pages', 'corona_virus_info') . '</span>';
    echo '<span id="subnav-settings" class="nav-tab" onClick="showTickerTab(' . "'settings'".')">' . __('Display Options', 'corona_virus_info') . '</span>';
    echo '<span id="subnav-countries" class="nav-tab" onClick="showTickerTab(' . "'countries'".')">' . __('Countries', 'corona_virus_info') . '</span>';
    echo '</h2>';
    echo '<span style="position:absolute;top:12px;right:15px">' . submit_button() . '</span>';
    $this->ticker_display($options);
    $this->ticker_settings($options);
    $this->ticker_countries($options);
    echo '</div>';
    submit_button();
    echo '</form>';
    echo '</div>';
?>
<script>
window.onload=function() {
   if (document.querySelector('.top-btn')) {
   } else {
   document.querySelector('body').insertAdjacentHTML('beforeend','<div id="top-btn" onclick="window.scrollTo(0,0)"><span class="dashicons dashicons-arrow-up-alt2"></span></div>');
   }
   var cbs=document.querySelectorAll('#settings-countries .checkbox input');
   var c=1;
   for (var i=0;i<cbs.length;i++) {
       if (cbs[i].id == 'all') {
          continue;
       }
       if (cbs[i].id == 'on_global') {
          continue;
       }
       if (cbs[i].id == 'use_links') {
          continue;
       }
       if (cbs[i].id == 'open_links') {
          continue;
       }
       if (cbs[i].checked == false) {
          c=0;
       }
   }
   if (c==1) {
      document.getElementById('all').checked = true;
      document.getElementById('all-label').innerHTML = '<?php echo __('Uncheck All', 'corona_virus_info');?>';
   } else {
      document.getElementById('all').checked = false;
      document.getElementById('all-label').innerHTML = '<?php echo __('Check All', 'corona_virus_info');?>';
   }
   showPosition();
   checkCountry();
   showLabel();
   showMobile();
   showLinks();
}


function showTickerTab(id) {
   document.querySelector('.nav-ticker .nav-tab-active').classList.remove('nav-tab-active');
   document.getElementById('subnav-' + id).classList.add('nav-tab-active');
   var els=document.querySelectorAll('.ticker-tab');
   for (var i=0;i<els.length;i++) {
       els[i].style.display='none';
   }
   document.getElementById('ticker-' + id).style.display='block';
   if (id=='countries') {
      document.getElementById('top-btn').style.display='block';
   } else {
      document.getElementById('top-btn').style.display='none';
   }
}

function goTo(id) {
   var rect = document.getElementById("char-"+id).getBoundingClientRect();
   window.scrollTo(0,rect.top-28);
}

function checkAll() {
      var c=1;
      if (document.getElementById('all').checked == true) {
         document.getElementById('all-label').innerHTML = '<?php echo __('Uncheck All', 'corona_virus_info');?>';
      } else {
         c=0;
         document.getElementById('all-label').innerHTML = '<?php echo __('Check All', 'corona_virus_info');?>';
      }
      var cbs=document.querySelectorAll('#ticker-countries .checkbox input');
      for (var i=0;i<cbs.length;i++) {
         if (cbs[i].id == 'all') {
            continue;
         }
         if (cbs[i].id == 'on_global') {
            continue;
         }
         if (cbs[i].id == 'use_links') {
            continue;
         }
         if (cbs[i].id == 'open_links') {
            continue;
         }
         if (c==1) {
            cbs[i].checked = true;
         } else {
            cbs[i].checked = false;
         }
      }
      checkCountry();
}

function checkCountry() {
   var els=document.querySelectorAll('#ticker-countries .checkbox input[type="checkbox"]');
   for (var i=0;i<els.length;i++) {
       if (els[i].id == 'all') {
          continue;
       }
       if (els[i].id == 'on_global') {
          continue;
       }
       if (els[i].id == 'use_links') {
          continue;
       }
       if (els[i].id == 'open_links') {
          continue;
       }
       if (document.getElementById('use_links').checked == false) {
          document.getElementById('l'+els[i].id).style.visibility='hidden';
       } else {
         if (els[i].checked == true) {
            document.getElementById('l'+els[i].id).style.visibility='visible';
          } else {
             document.getElementById('l'+els[i].id).style.visibility='hidden';
          }
       }
   }
}

function showPosition() {
    var els=document.querySelectorAll('#settings-display .checkbox input');
    for (var i=0;i<els.length;i++) {
        id=els[i].id;
        if (els[i].checked) {
           document.getElementById(id + '_pos').style.visibility='visible';
        } else {
           document.getElementById(id + '_pos').style.visibility='hidden';
        }
    }
}

function showMobile() {
   var el=document.getElementById('on_mobile');
   if (el.checked== true ) {
      document.getElementById('mobile-speed').style.visibility='visible';
   } else {
      document.getElementById('mobile-speed').style.visibility='hidden';
   }
}

function showLabel() {
   var el=document.getElementById('on_label');
   if (el.checked== true ) {
      document.getElementById('label-text').style.visibility='visible';
      document.getElementById('label-color').style.visibility='visible';
   } else {
      document.getElementById('label-text').style.visibility='hidden';
      document.getElementById('label-color').style.visibility='hidden';
   }
}

function showGlobalLink() {
   var el=document.getElementById('on_global');
   if (el.checked == true ) {
      document.getElementById('global-link').style.visibility='visible';
   } else {
      document.getElementById('global-link').style.visibility='hidden';
   }
   var el=document.getElementById('use_links');
   if (el.checked == false ) {
      document.getElementById('global-link').style.visibility='hidden';
   }
}

function showLinks() {
   var el=document.getElementById('use_links');
   if (el.checked == true ) {
      document.getElementById('show-links1').style.visibility='visible';
   } else {
      document.getElementById('show-links1').style.visibility='hidden';
   }
   checkCountry();
   showGlobalLink();
}

function selectSelector() {
   document.getElementById('content-selector').value=document.getElementById('select-selector').value;
}

</script>
<?php
  }
  // Ticker Display
  public function ticker_display($options) {
     $fields=array('on_home','on_front','on_page','on_post','on_category','on_archive','on_search');
     foreach ($fields as $field) {
        if (!isset($options[$field])) {
           $options[$field]=0;
        }
     }
  ?>
<div id="ticker-display" class="ticker-tab">
       <table>
          <tr><td width="280"></td><td></td></tr>
          <tr><td valign="middle">
             <div class="checkbox">
                <label for="on_home"><div class="switch">
                   <input type="checkbox" id="on_home" name="ptmbg_covid_info_options[on_home]" <?php checked( true, $options['on_home'] ); ?> value="1" onClick="showPosition()"/>
                   <span class="slider round"></span></div><span class="label"><?php echo __('Home Page', 'corona_virus_info');?></span>
                </label>
             </div>
          </td>
          <td>
             <div class="radio-group" id="on_home_pos">
                <input type="radio" id="on_home_pos1" name="ptmbg_covid_info_options[on_home_pos]" value="top"<?php if ($options['on_home_pos'] == 'top') {echo ' checked="checked"';} ?>>
                <label for="on_home_pos1"><?php echo __('Top', 'corona_virus_info');?></label>
                <input type="radio" id="on_home_pos2" name="ptmbg_covid_info_options[on_home_pos]" value="bottom"<?php if ($options['on_home_pos'] == 'bottom') {echo ' checked="checked"';} ?>>
                <label for="on_home_pos2"><?php echo __('Bottom', 'corona_virus_info');?></label>
             </div>
          </td></tr>

          <tr><td>
             <div class="checkbox">
                <label for="on_front"><div class="switch">
                   <input type="checkbox" id="on_front" name="ptmbg_covid_info_options[on_front]" <?php checked( true, $options['on_front'] ); ?> value="1" onClick="showPosition()"/>
                   <span class="slider round"></span></div><span class="label"><?php echo __('Front page', 'corona_virus_info');?></span>
                </label>
             </div>
          </td>
          <td>
             <div class="radio-group" id="on_front_pos">
                <input type="radio" id="on_front_pos1" name="ptmbg_covid_info_options[on_front_pos]" value="top"<?php if ($options['on_front_pos'] == 'top') {echo ' checked="checked"';} ?>>
                <label for="on_front_pos1"><?php echo __('Top', 'corona_virus_info');?></label>
                <input type="radio" id="on_front_pos2" name="ptmbg_covid_info_options[on_front_pos]" value="bottom"<?php if ($options['on_front_pos'] == 'bottom') {echo ' checked="checked"';} ?>>
                <label for="on_front_pos2"><?php echo __('Bottom', 'corona_virus_info');?></label>
             </div>
          </td></tr>
          <tr><td>
          <div class="checkbox">
          <label for="on_page"><div class="switch">
          <input type="checkbox" id="on_page" name="ptmbg_covid_info_options[on_page]" <?php checked( true, $options['on_page'] ); ?> value="1" onClick="showPosition()"/>
          <span class="slider round"></span></div><span class="label"><?php echo __('All Individual Pages', 'corona_virus_info');?></span>
          </label>
          </div>
          </td>
          <td>
             <div class="radio-group" id="on_page_pos">
                <input type="radio" id="on_page_pos1" name="ptmbg_covid_info_options[on_page_pos]" value="top"<?php if ($options['on_page_pos'] == 'top') {echo ' checked="checked"';} ?>>
                <label for="on_page_pos1"><?php echo __('Top', 'corona_virus_info');?></label>
                <input type="radio" id="on_page_pos2" name="ptmbg_covid_info_options[on_page_pos]" value="bottom"<?php if ($options['on_page_pos'] == 'bottom') {echo ' checked="checked"';} ?>>
                <label for="on_page_pos2"><?php echo __('Bottom', 'corona_virus_info');?></label>
             </div>
          </td></tr>
          <tr><td>
             <div class="checkbox">
                <label for="on_post"><div class="switch">
                   <input type="checkbox" id="on_post" name="ptmbg_covid_info_options[on_post]" <?php checked( true, $options['on_post'] ); ?> value="1" onClick="showPosition()"/>
                   <span class="slider round"></span></div><span class="label"><?php echo __('All Individual posts', 'corona_virus_info');?></span>
                </label>
             </div>
          </td>
          <td>
             <div class="radio-group" id="on_post_pos">
                <input type="radio" id="on_post_pos1" name="ptmbg_covid_info_options[on_post_pos]" value="top"<?php if ($options['on_post_pos'] == 'top') {echo ' checked="checked"';} ?>>
                <label for="on_post_pos1"><?php echo __('Top', 'corona_virus_info');?></label>
                <input type="radio" id="on_post_pos2" name="ptmbg_covid_info_options[on_post_pos]" value="bottom"<?php if ($options['on_post_pos'] == 'bottom') {echo ' checked="checked"';} ?>>
                <label for="on_post_pos2"><?php echo __('Bottom', 'corona_virus_info');?></label>
             </div>
          </td></tr>
          <tr><td>
             <div class="checkbox">
                <label for="on_category"><div class="switch">
                   <input type="checkbox" id="on_category" name="ptmbg_covid_info_options[on_category]" <?php checked( true, $options['on_category'] ); ?> value="1" onClick="showPosition()"/>
                   <span class="slider round"></span></div><span class="label"><?php echo __('All Category pages', 'corona_virus_info');?></span>
                </label>
             </div>
          </td>
          <td>
             <div class="radio-group" id="on_category_pos">
                <input type="radio" id="on_category_pos1" name="ptmbg_covid_info_options[on_category_pos]" value="top"<?php if ($options['on_category_pos'] == 'top') {echo ' checked="checked"';} ?>>
                <label for="on_category_pos1"><?php echo __('Top', 'corona_virus_info');?></label>
                <input type="radio" id="on_category_pos2" name="ptmbg_covid_info_options[on_category_pos]" value="bottom"<?php if ($options['on_category_pos'] == 'bottom') {echo ' checked="checked"';} ?>>
                <label for="on_category_pos2"><?php echo __('Bottom', 'corona_virus_info');?></label>
             </div>
          </td></tr>
          <tr><td>
             <div class="checkbox">
                <label for="on_archive"><div class="switch">
                   <input type="checkbox" id="on_archive" name="ptmbg_covid_info_options[on_archive]" <?php checked( true, $options['on_archive'] ); ?> value="1" onClick="showPosition()"/>
                   <span class="slider round"></span></div><span class="label"><?php echo __('All Archive pages', 'corona_virus_info');?></span>
                </label>
             </div>
          </td>
          <td>
             <div class="radio-group" id="on_archive_pos">
                <input type="radio" id="on_archive_pos1" name="ptmbg_covid_info_options[on_archive_pos]" value="top"<?php if ($options['on_archive_pos'] == 'top') {echo ' checked="checked"';} ?>>
                <label for="on_archive_pos1"><?php echo __('Top', 'corona_virus_info');?></label>
                <input type="radio" id="on_archive_pos2" name="ptmbg_covid_info_options[on_archive_pos]" value="bottom"<?php if ($options['on_archive_pos'] == 'bottom') {echo ' checked="checked"';} ?>>
                <label for="on_archive_pos2"><?php echo __('Bottom', 'corona_virus_info');?></label>
             </div>
          </td></tr>
          <tr><td>
             <div class="checkbox">
                <label for="on_search"><div class="switch">
                   <input type="checkbox" id="on_search" name="ptmbg_covid_info_options[on_search]" <?php checked( true, $options['on_search'] ); ?> value="1" onClick="showPosition()"/>
                   <span class="slider round"></span></div><span class="label"><?php echo __('All Search pages', 'corona_virus_info');?></span>
                </label>
             </div>
          </td><td>
             <div class="radio-group" id="on_search_pos">
                <input type="radio" id="on_search_pos1" name="ptmbg_covid_info_options[on_search_pos]" value="top"<?php if ($options['on_search_pos'] == 'top') {echo ' checked="checked"';} ?>>
                <label for="on_search_pos1"><?php echo __('Top', 'corona_virus_info');?></label>
                <input type="radio" id="on_search_pos2" name="ptmbg_covid_info_options[on_search_pos]" value="bottom"<?php if ($options['on_search_pos'] == 'bottom') {echo ' checked="checked"';} ?>>
                <label for="on_search_pos2"><?php echo __('Bottom', 'corona_virus_info');?></label>
             </div>
          </td></tr>
          <tr><th align="left" colspan="2"><?php echo __('Or add this shord codes to any post/page:', 'corona_virus_info');?></th></tr>
          <tr><th align="left">[corona-virus-live-ticker pos=top]</th><td><?php echo __('Top on page content', 'corona_virus_info');?></td></tr>
          <tr><th align="left">[corona-virus-live-ticker pos=bottom]</th><td><?php echo __('Bottom on page content', 'corona_virus_info');?></td></tr>
          <tr><td colspan="2"><hr></td></tr>
          <tr><th align="left"><?php echo __('Page Content Selector', 'corona_virus_info');?></th>
             <td><input id="content-selector" type="text" name="ptmbg_covid_info_options[content_selector]" value="<?php echo $options['content_selector']; ?>"></td>
          </tr>
          <tr><td></td><td class="description" style="color:#f00"><?php echo __('The id or tag of element between page header and page footer', 'corona_virus_info');?></td></tr>
          <tr><th align="left">Select theme</th><td><select id="select-selector" name="ptmbg_covid_info_options[select_selector]" onChange="selectSelector()"><?php
          $rows = @file(plugin_dir_path(__FILE__) . 'themes.db');
          foreach ($rows as $row) {
              $row=preg_replace("/\n|\r/","",$row);
              $d=preg_split("/\|/",$row);
              echo '<option value="'.$d[2].'">'.$d[0].'</option>';
          }
          ?></select></td></tr>
          <tr><td colspan="2">If You not have founded your theme in the list and cannot detect selector for your theme, <a href="http://webwapstudio.com/contact.php" target="_blank">please contact us</a> and send your site URL.<br></td></tr>
          
       </table>
	</div>
  <?php }
  // Ticker Settings
  public function ticker_settings($options) {
     $fields=array('on_new_confirmed','on_new_deaths','on_new_recovered','on_mobile','on_label');
     foreach ($fields as $field) {
        if (!isset($options[$field])) {
           $options[$field]=0;
        }
     }
  ?>
	<div id="ticker-settings" class="ticker-tab"" style="display:none">
	  <table>
       <tr><td>
       <div class="checkbox" style="display:inline-block;margin-right:20px">
          <label for="on_label"><div class="switch">
             <input type="checkbox" id="on_label" name="ptmbg_covid_info_options[on_label]" <?php checked( true, $options['on_label'] ); ?> value="1" onClick="showLabel()"/>
             <span class="slider round"></span></div><span class="label"><?php echo __('Display Label', 'corona_virus_info');?></span>
          </label>
       </div>
       </td><td id="label-text">
       <input type="text" id="label_text" placeholder="<?php echo __('Label Title', 'corona_virus_info');?>" name="ptmbg_covid_info_options[label_text]" value="<?php echo $options['label_text']; ?>">
       <td>
       </td></tr>
       <tr id="label-color"><th align="left"><?php echo __('Label Color', 'corona_virus_info');?></th>
       <td colspan="2"><input id="label_color" type="color" name="ptmbg_covid_info_options[label_color]" value="<?php echo $options['label_color']; ?>"></td></tr>
      <tr><td colspan="3"><hr></td></tr>
      <tr><td>
      <div class="checkbox" style="display:inline-block;margin-right:20px">
          <label for="on_new_confirmed"><div class="switch">
             <input type="checkbox" id="on_new_confirmed" name="ptmbg_covid_info_options[on_new_confirmed]" <?php checked( true, $options['on_new_confirmed'] ); ?> value="1"/>
             <span class="slider round"></span></div><span class="label"><?php echo __('Display New Confirmed', 'corona_virus_info');?></span>
          </label>
       </div>
       </td><td>
       <div class="checkbox" style="display:inline-block;margin-right:20px">
          <label for="on_new_recovered"><div class="switch">
             <input type="checkbox" id="on_new_recovered" name="ptmbg_covid_info_options[on_new_recovered]" <?php checked( true, $options['on_new_recovered'] ); ?> value="1"/>
             <span class="slider round"></span></div><span class="label"><?php echo __('Display New Recovered', 'corona_virus_info');?></span>
          </label>
       </div>
       </td><td>
       <div class="checkbox" style="display:inline-block;margin-right:20px">
          <label for="on_new_deaths"><div class="switch">
             <input type="checkbox" id="on_new_deaths" name="ptmbg_covid_info_options[on_new_deaths]" <?php checked( true, $options['on_new_deaths'] ); ?> value="1"/>
             <span class="slider round"></span></div><span class="label"><?php echo __('Display New Deaths', 'corona_virus_info');?></span>
          </label>
       </div>
       </td></tr>
       <tr><td colspan="3"><hr></td></tr>
       <tr><th align="left"><?php echo __('Border Color', 'corona_virus_info');?></th>
       <td colspan="2"><input id="color" type="color" name="ptmbg_covid_info_options[color]" value="<?php echo $options['color']; ?>"></td></tr>
       <tr><td colspan="3"><hr></td></tr>
       <tr><th align="left"><?php echo __('Decktop Speed', 'corona_virus_info');?></th>
       <td><input id="speed" type="text" style="width:60px" name="ptmbg_covid_info_options[desktop_speed]" value="<?php echo $options['desktop_speed']; ?>"></td></tr>

       <tr><td>
       <div class="checkbox">
          <label for="on_mobile"><div class="switch">
             <input type="checkbox" id="on_mobile" name="ptmbg_covid_info_options[on_mobile]" <?php checked( true, $options['on_mobile'] ); ?> value="1" onClick="showMobile()"/>
             <span class="slider round"></span></div><span class="label"><?php echo __('Display on Mobile Devices', 'corona_virus_info');?></span>
          </label>
       </div>
       </td><td></td></tr>
       <tr id="mobile-speed"><th align="left"><?php echo __('Mobile Speed', 'corona_virus_info');?></th>
       <td><input id="speed" type="text" style="width:60px" name="ptmbg_covid_info_options[mobile_speed]" value="<?php echo $options['mobile_speed']; ?>"></td></tr>
       <tr><td colspan="3"><hr></td></tr>
       <tr><th align="left">Order By</th>
       <td>
       <select name="ptmbg_covid_info_options[order_by]">
           <option value="Country"<?php if ($options['order_by'] == 'Country') {echo ' selected="selected"';} ?>><?php echo __('Country name', 'corona_virus_info');?></option>
           <option value="NewConfirmed"<?php if ($options['order_by'] == 'NewConfirmed') {echo ' selected="selected"';} ?>><?php echo __('New Confirmed', 'corona_virus_info');?></option>
           <option value="NewRecovered"<?php if ($options['order_by'] == 'NewRecovered') {echo ' selected="selected"';} ?>><?php echo __('New Recovered', 'corona_virus_info');?></option>
           <option value="NewDeaths"<?php if ($options['order_by'] == 'NewDeaths') {echo ' selected="selected"';} ?>><?php echo __('New Deaths', 'corona_virus_info');?></option>
       </select></td>
       <td>
             <div class="radio-group" id="">
                <input type="radio" id="asc_desc1" name="ptmbg_covid_info_options[asc_desc]" value="asc"<?php if ($options['asc_desc'] == 'asc') {echo ' checked="checked"';} ?>>
                <label for="asc_desc1"><?php echo __('Ascend', 'corona_virus_info');?></label>
                <input type="radio" id="asc_desc2" name="ptmbg_covid_info_options[asc_desc]" value="desc"<?php if ($options['asc_desc'] == 'desc') {echo ' checked="checked"';} ?>>
                <label for="asc_desc2"><?php echo __('Descend', 'corona_virus_info');?></label>
             </div>
       </td></tr>
     </table>
  </div>
  <?php }
  // Ticker Countries
  public function ticker_countries($options) {
     $fields=array('on_global','all','use_links','open_links');
     foreach ($options as $key=>$val) {
        if (preg_match("/^l\_/",$key)) {
           $fields[] = preg_replace("/^l/","c",$key);
        }
     }
     foreach ($fields as $field) {
        if (!isset($options[$field])) {
           $options[$field]=0;
        }
     }

  ?>
	<div id="ticker-countries" class="ticker-tab" style="display:none">
<?php
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVYZ';
    $chars=str_split($characters);

    $data=array();
    foreach($options as $key=>$val) {
        if(preg_match("/^n_/",$key)) {
          $data[$val]=$key;
        }
    }
    ksort($data);
    echo '<table width="100%"><tr><td width="33%"></td><td width="33%"></td><td width="33%"></td></tr>';
?>

     <tr><td>
     <div class="checkbox">
       <label for="on_global"><div class="switch">
          <input type="checkbox" id="on_global" onClick="showGlobalLink()" name="ptmbg_covid_info_options[on_global]" <?php checked( true, $options['on_global'] ); ?> value="1" />
          <span class="slider round"></span></div><span class="label"><?php echo __('Display Global Info', 'corona_virus_info');?></span>
       </label>
    </div>
    </td><td colspan="2"><table id="global-link" width="100%"><th align="left" width="120"><?php echo __('Global Info URL', 'corona_virus_info');?>:</th><td colspan="2"><input style="width:100%" id="global_link" type="text" name="ptmbg_covid_info_options[global_link]" value="<?php echo $options['global_link']; ?>"></td></tr></table></td></tr>

<?php
    echo '<tr><td colspan="3"><div class="country_label">';
    foreach ($chars as $ch) {
       echo '<span class="char-nav" onClick="goTo(' . "'$ch'" . ')">'.$ch.'</span>';
    }
    echo '</div></td></tr>';
?>
     <tr><td>
     <div class="checkbox">
       <label for="all"><div class="switch">
          <input type="checkbox" id="all" name="ptmbg_covid_info_options[all]" onClick="checkAll()" <?php checked( true, $options['all'] ); ?> value="1" />
          <span class="slider round"></span></div><span class="label" id="all-label"><?php echo __('Uncheck All', 'corona_virus_info');?></span>
       </label>
    </div>
    </td>
    <td>
       <div class="checkbox">
          <label for="use_links"><div class="switch">
             <input type="checkbox" id="use_links" onClick="showLinks()" name="ptmbg_covid_info_options[use_links]" <?php checked( true, $options['use_links'] ); ?> value="1"/>
             <span class="slider round"></span></div><span class="label"><?php echo __('Use Links', 'corona_virus_info');?></span>
          </label>
       </div>
       </td>
       <td id="show-links1">
       <div class="checkbox">
          <label for="open_links"><div class="switch">
             <input type="checkbox" id="open_links" name="ptmbg_covid_info_options[open_links]" <?php checked( true, $options['open_links'] ); ?> value="1"/>
             <span class="slider round"></span></div><span class="label"><?php echo __('Open Links in new tab', 'corona_virus_info');?></span>
          </label>
       </div>
       </td>
    </tr>
<?php
    echo '<tr><td>';
    $char=array_shift($chars);
    $r=1;
    foreach($data as $key=>$val) {
       $id=preg_replace("/.*\_/","",$val);
       $first=substr($key,0,1);
       if ($first==$char) {
          echo '</td></tr><tr id="char-'.$char.'"><td colspan="3"><div class="country_label">' . $char , '</div></td></tr><tr><td>';
          $char=array_shift($chars);
          $r=1;
       }
       echo '<input type="hidden" name="ptmbg_covid_info_options[n_'.$id.']" value="'.$options['n_'.$id].'">';
       echo '<input type="hidden" name="ptmbg_covid_info_options[l_'.$id.']" value="'.$options['l_'.$id].'">';
       echo '<div class="checkbox">';
       echo '<label for="c_'.$id.'"><div class="switch">';
       echo '<input type="checkbox" id="c_'.$id.'" name="ptmbg_covid_info_options[c_'.$id.']" ';
       checked( true, $options['c_'.$id.''] );
       echo ' value="1" onClick="checkCountry()"/>';
       echo '<span class="slider round"></span></div><span class="label">' . __($options['n_'.$id]) . '</span>';
       echo '</label>';
       echo '</div>';
       echo '<div><input type="text" id="lc_'.$id.'" name="ptmbg_covid_info_options[l_'.$id.']" value="' . $options['l_'.$id] . '" placeholder="Link to Coutry site" style="width:100% !important"></div>';
       $r++;
       if ($r==4) {
          $r=1;
          echo '</td></tr><tr><td>';
       } else {
          echo '</td><td>';
       }
    }
    echo '</td></tr></table>';
    echo '</div>';
  }

  public function display_ticker_to_page() {
     if ($this->check_page()) {
        $this->show_ticker();
     } else {
        global $post;
        if (preg_match("/\[corona\-virus\-live\-ticker pos=top\]|\[corona\-virus\-live\-ticker pos=bottom\]/s",$post->post_content,$matches)) {
           $position=preg_replace("/.*pos\=/","",$matches[0]);
           $this->position=preg_replace("/\].*/","",$position);
           $this->show_ticker();
        }
     }
  }
  
  public function show_ticker() {
     $data=get_option('ptmbg_covid_info_data');
     if ($data['last_updated'] < date("Ymd")) {
        $request = wp_remote_get('https://covid19.webwapstudio.com/summary/');
        if ( is_wp_error( $request ) ) {
           return;
        }
        $json = wp_remote_retrieve_body( $request );
        $data['json']=$json;
        $data['last_updated']=date("Ymd");
        update_option('ptmbg_covid_info_data',$data);
    }
    $options=get_option('ptmbg_covid_info_options');
?>
<style>
.covid-info {
   width:100%;
   border:1px solid <?php echo $options['color'];?>;
   padding-top:2px;
   position:relative
}
#temp_covid-info {display:none}
.marquee {
    width: 100%;
    overflow: hidden;
    background: #fff;
    font-size: 14px;
    font-weight: normal;
    padding: 1px;
}

.u-r, ur {
  width: 0;
  height: 0;
  border-left: 8px solid transparent;
  border-right: 8px solid transparent;
  border-bottom: 10px solid #d00;
  display:inline-block;
  margin-right:5px;
}

.u-g, ug {
  width: 0;
  height: 0;
  border-left: 8px solid transparent;
  border-right: 8px solid transparent;
  border-bottom: 10px solid #090;
  display:inline-block;
  margin-right:5px;
}

.d-r, dr {
  width: 0;
  height: 0;
  border-left: 8px solid transparent;
  border-right: 8px solid transparent;
  border-top: 10px solid #d00;
  display:inline-block;
  margin-right:5px;
}

.d-g, dg {
  width: 0;
  height: 0;
  border-left: 8px solid transparent;
  border-right: 8px solid transparent;
  border-top: 10px solid #090;
  display:inline-block;
  margin-right:5px;
}

.marquee div {cursor:pointer;display:inline-block}
.c-r, cr {color:#d00;display:inline;margin-right:5px}
.c-g, cg {color:#090;display:inline;margin-right:5px}
.c-b, cb {color:#009;display:inline}
#pop-up {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,.8);
    z-index: 999999999;
    display:none;
}
#covid-info, #data-info {
   position: fixed;
   left: calc(50% - 250px);
   top: calc(50% - 250px);
   border-radius: 10px;
   max-width:500px;
   width:100%;
   border:4px solid <?php echo $options['color'];?>;
   background:#fff;
   z-index: 9999999999;
   display:none;
}
#covid-info-title {
  background-color:<?php echo $options['color'];?>;
  color:#fff;
  text-align:center;
  font-weight:bold;
  padding:2px 0;
}
.covid-info-head {position:relative}
.covid-close {
	position: absolute;
	right:0px;
	top:0px;
	width: 22px;
	height: 22px;
}
.covid-close div {
    position:relative;
   	width: 21px;
	height: 21px;
	border: 2px solid #fff;
	background-color: <?php echo $options['color'];?>;
	border-radius: 50%;
	z-index: 99999999999;
	cursor:pointer;
	opacity:0.8;
}

.covid-close div::before, .covid-close div::after {
	position: absolute;
	top: 7px;
	left: 2px;
	width: 13px;
	height: 3px;
	content: "";
	background-color: #fff;
}
.covid-close div::before {
	-ms-transform: rotate(-45deg);
	-webkit-transform: rotate(-45deg);
	transform: rotate(-45deg);
}
.covid-close div::after {
	-ms-transform: rotate(45deg);
	-webkit-transform: rotate(45deg);
	transform: rotate(45deg);
}

.covid-close div:hover {opacity:1.0}
#covid-info-content {color:#000;padding:20px}
#covid-info-content table {margin:0 !important}
#covid-info-content td {text-align:right;color:#333;position:relative}
#covid-info-content td.status {border-right-width:0 !important}
#covid-info-content a {display:inline-block;margin-top:20px;border:2px solid <?php echo $options['color'];?>;border-radius:6px;padding:10px;text-decoration:none;color:<?php echo $options['color'];?>}
z b {margin:0 5px}
.js-marquee-wrapper {width:150000px !important}
@media only screen and (max-width: 600px) {
   #covid-info, #data-info {
      width: calc(100% - 40px);
      left: 20px;
      top: 50px;
   }
}

</style>
<script>
var summary={};
<?php
if (isset($options['use_links'])) {
if ($options['use_links'] == 1) {
$links=array();
foreach ($options as $key=>$val) {
    if ((preg_match("/l\_/",$key)) && (trim($val) != '')) {
       $links[preg_replace("/l\_/","",$key)]=$val;
    }
}
echo "var covidLinksJson='" . json_encode($links) ."';\n";
echo "var covidLinks=JSON.parse(covidLinksJson);\n";
}
}
?>
function formatNumber(num) {
 num=num.toString();
  return (num.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,'))
}
var myTicker;
var isMobile=false;
try{ document.createEvent("TouchEvent"); isMobile = true; } catch(e){ isMobile = false; }
var onMobile='<?php echo $options['on_mobile'];?>';
var marqueeSpeeed='<?php echo $options['desktop_speed'];?>';
function initTicker() {
if ((!isMobile) || ((isMobile) && (onMobile==1))) {
if (isMobile) {
   marqueeSpeeed='<?php echo $options['mobile_speed'];?>';
   document.getElementById("covid-info").style.position="absolute";
   document.getElementById("data-info").style.position="absolute";
}

summary=JSON.parse(json);
document.querySelector("<?php echo $options['content_selector'];?>").insertAdjacentHTML("<?php
   if ($this->position=='top') {
      echo 'beforebegin';
   } else {
      echo 'afterend';
   }
?>",document.getElementById("temp_covid-info").innerHTML);
document.getElementById("temp_covid-info").innerHTML="";
myTicker=jQuery(".marquee").marquee({
speed: marqueeSpeeed,
gap: 50,
delayBeforeStart: 0,
direction: "left",
duplicated: false,
pauseOnHover: true
});

jQuery( ".marquee z" ).bind( "click", function() {
  var html=jQuery(this).find('b').html();
  openCovidInfo(html)
});
}
}

function openDataInfo() {
   document.getElementById('pop-up').style.display="block";
   document.getElementById('data-info').style.display="block";
}

function closeDataInfo() {
   document.getElementById('data-info').style.display="none";
   document.getElementById('pop-up').style.display="none";
}

function openCovidInfo(id) {
   setTimeout(function(){document.querySelector(".js-marquee-wrapper").style.animationPlayState = "paused";}, 50);
   document.getElementById('pop-up').style.display="block";
   document.getElementById('covid-info').style.display="block";
   var html='<table>';
   html+='<tr><th style="text-align:center !important" colspan="3">'+ summary.LastUpdate + '</td></tr>';
   var record={};
   if (id=='Global') {
      document.getElementById('covid-info-title').innerHTML='Global';
      record=summary.Global;
   } else {
      var all=summary.Countries;
      for (i=0;i<all.length;i++) {
          if (all[i]['Country'] == id ) {
             record=all[i];
             document.getElementById('covid-info-title').innerHTML=record.Country;
             break;
          }
      }
   }
   // Confirmed
   html+='<tr><th><?php echo __('Total Confirmed', 'corona_virus_info');?></th><td colspan="2">'+formatNumber(record.TotalConfirmed)+'</td></tr>';
   html+='<tr><th><?php echo __('New Confirmed', 'corona_virus_info');?></th><td class="status">';
   var spanclass='';
   if (record.ChangeConfirmedPercent!='0') {
      if (record.ChangeConfirmedPercent.search(/\-/) != -1) {
         html+='<div class="d-g"></div>';
         spanclass="c-g";
      } else {
         html+='<div class="u-r"></div>';
         spanclass="c-r";
      }
      html+='<span class="'+spanclass+'">('+record.ChangeConfirmedPercent+'%)'+'</span>';
   }
   html+='</td><td><span class="' + spanclass+'">';
   html+=formatNumber(record.NewConfirmed);
   html+='</span></td></tr>';
   // Recovered
   html+='<tr><th><?php echo __('Total Recovered', 'corona_virus_info');?></th><td colspan="2">'+formatNumber(record.TotalRecovered)+'</td></tr>';
   html+='<tr><th><?php echo __('New Recovered', 'corona_virus_info');?></th><td class="status">';
   var spanclass='';
   if (record.ChangeRecoveredPercent!='0') {
      if (record.ChangeRecoveredPercent.search(/\-/) != -1) {
         html+='<div class="d-r"></div>';
         spanclass="c-r";
      } else {
         html+='<div class="u-g"></div>';
         spanclass="c-g";
      }
      html+='<span class="'+spanclass+'">('+record.ChangeRecoveredPercent+'%)'+'</span>';
   }
   html+='</td><td><span class="' + spanclass+'">';
   html+=formatNumber(record.NewRecovered);
   html+='</span></td></tr>';
   // Deaths
   html+='<tr><th><?php echo __('Total Deaths', 'corona_virus_info');?></th><td colspan="2">'+formatNumber(record.TotalDeaths)+'</td></tr>';
   html+='<tr><th><?php echo __('New Deaths', 'corona_virus_info');?></th><td class="status">';
   spanclass='';
   if (record.ChangeDeathsPercent!='0') {
      if (record.ChangeDeathsPercent.search(/\-/) != -1) {
         html+='<div class="d-g"></div>';
         spanclass="c-g";
      } else {
         html+='<div class="u-r"></div>';
         spanclass="c-r";
      }
      html+='<span class="'+spanclass+'">('+record.ChangeDeathsPercent+'%)'+'</span>';
   }
   html+='</td><td><span class="' + spanclass+'">';
   html+=formatNumber(record.NewDeaths);
   html+='</span></td></tr>';
   html+='</table>';
   <?php
   if (isset($options['use_links'])) {
   if ($options['use_links']=='1') {
   ?>
   if (id=='Global') {
       <?php if (trim($options['global_link'])!='') { ?>
       html+='<div style="text-align:center"><a href="<?php echo $options['global_link'];?>"<?php
       if ($options['open_links']=='1') {echo ' target="_blank"';}?>>More Info</a></div>';
       <?php } ?>
   } else {
       if (covidLinks[record.CountryCode]) {
          html+='<div style="text-align:center"><a href="'+covidLinks[record.CountryCode]+'"<?php
       if ($options['open_links']=='1') {echo ' target="_blank"';}?>>More Info</a></div>';
       }
   }
   <?php } }?>
   document.getElementById('covid-info-content').innerHTML=html;
}

</script>
<?php
        $json=json_decode($data['json'],true);
        echo "\n<script>";
        echo "var json='";
        echo preg_replace("/\'/","||",$data['json']);
        echo  "';";
        echo 'json=json.replace(/\|\|/g,' . '"' . "'" . '"' .');';
        echo '</script>';
        echo '<div id="temp_covid-info">';
        echo '<div class="covid-info">';
        echo '<div class="marquee">';
        if (isset($options['on_global'])) {
           if ($options['on_global'] == 1) {
              $global=$json['Global'];
              echo '<z><b>Global</b>';
              if (isset($options['on_new_confirmed'])) {
                 if ($options['on_new_confirmed'] == 1) {
                    echo __('New Confirmed', 'corona_virus_info') , ' ';
                    if ($global['ChangeConfirmedPercent']==0) {
                       echo '<cb>' . $global['NewConfirmed'] . '</cb> ';
                    } else {
                       if (preg_match("/\-/",$global['ChangeConfirmedPercent'])) {
                          echo '<dg></dg>';
                          echo '<cg>';
                          echo '('.$global['ChangeConfirmedPercent'].'%) ';
                          echo number_format($global['NewConfirmed']) . '</cg>';
                       } else {
                          echo '<ur></ur>';
                          echo '<cr>';
                          echo '('.$global['ChangeConfirmedPercent'].'%) ';
                          echo number_format($global['NewConfirmed']) . '</cr>';
                       }
                   }
                 }
              }
              if (isset($options['on_new_recovered'])) {
                 if ($options['on_new_recovered'] == 1) {
                    echo __('New Recovered', 'corona_virus_info') , ' ';
                    if ($global['ChangeRecoveredPercent']==0) {
                       echo '<cb>' . $global['NewRecovered'] . '</cb> ';
                    } else {
                       if (preg_match("/\-/",$global['ChangeRecoveredPercent'])) {
                           echo '<dr></dr>';
                           echo '<cr>';
                           echo '('.$global['ChangeRecoveredPercent'].'%) ';
                           echo number_format($global['NewRecovered']) . '</cr>';
                       } else {
                          echo '<ug></ug>';
                          echo '<cg>';
                          echo '('.$global['ChangeRecoveredPercent'].'%)  ';
                          echo number_format($global['NewRecovered']) . '</cg>';
                       }
                   }
                 }
              }
              
              if (isset($options['on_new_deaths'])) {
                 if ($options['on_new_deaths'] == 1) {
                    echo __('New Deaths', 'corona_virus_info') , ' ';
                    if ($global['ChangeDeathsPercent']==0) {
                       echo '<cb>' . $global['NewDeaths'] . '</cb> ';
                    } else {
                       if (preg_match("/\-/",$global['ChangeDeathsPercent'])) {
                          echo '<dg></dg>';
                          echo '<cg>';
                          echo '('.$global['ChangeDeathsPercent'].'%) ';
                          echo number_format($global['NewDeaths']) . '</cg>';
                       } else {
                          echo '<ur></ur>';
                          echo '<cr>';
                          echo '('.$global['ChangeDeathsPercent'].'%) ';
                          echo number_format($global['NewDeaths']) . '</cr>';
                       }
                   }
                 }
              }
              echo '</z>';
          }
        }
        $countries=$this->sort_data($json['Countries'],$options['order_by'],$options['asc_desc']);
        foreach ($countries as $country) {
           $code=$country['CountryCode'];
           if (isset($options['c_'.$code])) {
              if ($options['c_'.$code] == 1) {
                 echo '<z>';
                 echo '<b>' . $country['Country'] . '</b>';
                 if (isset($options['on_new_confirmed'])) {
                    if ($options['on_new_confirmed'] == 1) {
                       echo __('New Confirmed', 'corona_virus_info') , ' ';
                       if ($country['ChangeConfirmedPercent']==0) {
                          echo '<cb>' . $country['NewConfirmed'] . '</cb> ';
                       } else {
                          if (preg_match("/\-/",$country['ChangeConfirmedPercent'])) {
                             echo '<dg"></dg>';
                             echo '<cg>';
                             echo '('.$country['ChangeConfirmedPercent'].'%) ';
                             echo number_format($country['NewConfirmed']) . '</cg>';
                          } else {
                             echo '<ur></ur>';
                             echo '<cr>';
                             echo '('.$country['ChangeConfirmedPercent'].'%) ';
                             echo number_format($country['NewConfirmed']) . '</cr>';
                          }
                       }
                    }
                 }

                 if (isset($options['on_new_recovered'])) {
                    if ($options['on_new_recovered'] == 1) {
                       echo __('New Recovered', 'corona_virus_info') , ' ';
                       if ($country['ChangeRecoveredPercent']==0) {
                          echo '<cb>' . $country['NewRecovered'] . '</cb> ';
                       } else {
                          if (preg_match("/\-/",$country['ChangeRecoveredPercent'])) {
                             echo '<dr></dr>';
                             echo '<cr>';
                             echo '('.$country['ChangeRecoveredPercent'].'%) ';
                             echo number_format($country['NewRecovered']) . '</cr>';
                           } else {
                              echo '<ug></ug>';
                              echo '<cg>';
                              echo '('.$country['ChangeRecoveredPercent'].'%) ';
                              echo number_format($country['NewRecovered']) . '</cg>';
                           }
                       }
                    }
                 }
                 
                 if (isset($options['on_new_deaths'])) {
                    if ($options['on_new_deaths'] == 1) {
                       echo __('New Deaths', 'corona_virus_info') , ' ';
                       if ($country['ChangeDeathsPercent']==0) {
                          echo '<cb>' . $country['NewDeaths'] . '</cb> ';
                       } else {
                          if (preg_match("/\-/",$country['ChangeDeathsPercent'])) {
                             echo '<dg></dg>';
                             echo '<cg>';
                             echo '('.$country['ChangeDeathsPercent'].'%) ';
                             echo number_format($country['NewDeaths']) . '</cg>';
                          } else {
                             echo '<ur></ur>';
                             echo '<cr>';
                             echo '('.$country['ChangeDeathsPercent'].'%) ';
                             echo number_format($country['NewDeaths']) . '</cr>';
                          }
                       }
                    }
                 }
                 echo '</z>';
              }
           }
        }
       if (isset($options['on_label'])) {
           if ($options['on_label'] == 1) {
              echo '<div onClick="openDataInfo()" style="position:absolute;top:0;left:0;padding:4px;display:inline-block;color:'.$options['label_color'].';background-color:'.$options['color'].'">';
              echo $options['label_text'];
              echo '</div>';
           }
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '<div id="pop-up"></div><div id="covid-info"><div class="covid-info-head"><div id="covid-info-title"></div><div class="covid-close" onClick="document.getElementById(' . "'pop-up'" . ').style.display=' . "'none'" . ';document.getElementById(' . "'covid-info'" . ').style.display=' . "'none'" . ';document.querySelector('."'.js-marquee-wrapper'".').style.animationPlayState = '."'running'".'"><div></div></div></div><div id="covid-info-content"></div></div></div>';
        ?>
        <div id="data-info">
        <div class="covid-info-head">
        <div id="covid-info-title">Data details</div>
        <div style="text-align:center;margin-top:15px">
        <div><strong>Data page </strong><a href="http://covid19.webwapstudio.com/" target="blank">COVID19 DATA</a>
        <div style="margin:5px 0">Data is sourced from <i>Johns Hopkins CSSE</i></div>
        <div>Dong E, Du H, Gardner L.<br>An interactive web-based dashboard to track COVID-19<br>in real time.<br>Lancet Inf Dis. 20(5):533-534. doi: 10.1016/S1473-3099(20)30120-1</div>
        <div style="margin-top:15px"><strong>Why do I see different data from different sources?</strong></div>
        <div there="" are="" various="" sources="" that="" tracking="" and="" aggregating="" coronavirus="" data.<br="">They update at different times<br>and may have different ways of gathering data.</div>
        </div>
        <div class="covid-close" onClick="closeDataInfo()"><div></div></div></div>
        <div id="covid-info-content">
        </div></div></div>
        <?php
        wp_enqueue_script('jquery');
        wp_register_script('marquee',
           plugin_dir_url( __FILE__ ) .'jquery.marquee.min.js',
           array ('jquery'),
           false, false
        );
        wp_enqueue_script('marquee');
        $script='jQuery( document ).ready(function() {';
        $script.='initTicker();';
        $script.='})';
        wp_add_inline_script('marquee', $script, 'after');
  }
  public function sort_data($data,$sort,$order) {
     $field = array_column($data, $sort);
     if ($order=='asc') {
        array_multisort($field, SORT_ASC, $data);
     } else {
        array_multisort($field, SORT_DESC, $data);
     }
     return $data;
  }
  
  public function check_page() {
     $options=get_option('ptmbg_covid_info_options');
     if (isset( $options['on_home'] ) && $options['on_home'] == "1" && is_home()) {
        $this->position=$options['on_home_pos'];
        return true;
     } else if (isset( $options['on_front'] ) && $options['on_front'] == "1" && is_front_page()) {
       $this->position=$options['on_front_pos'];
       return true;
     } else if (isset( $options['on_page'] ) && $options['on_page'] == "1" && is_page()) {
       $this->position=$options['on_page_pos'];
       return true;
     } else if (isset( $options['on_post'] ) && $options['on_post'] == "1" && is_single()) {
       $this->position=$options['on_post_pos'];
       return true;
     } else if (isset( $options['on_category'] ) && $options['on_category'] == "1" && is_category()) {
     $this->position=$options['on_category_pos'];
       return true;
     } else if (isset( $options['on_archive'] ) && $options['on_archive'] == "1" && is_archive()) {
       $this->position=$options['on_archive_pos'];
       return true;
     } else if (isset( $options['on_search'] ) && $options['on_search'] == "1" && is_search()) {
       $this->position=$options['on_search_pos'];
       return true;
     }
     return false;
  }
}
endif; // End If class exists check.

$corona_virus_live_ticker=new Corona_Virus_Info_Live_Ticker;
add_action( 'plugins_loaded', array( $corona_virus_live_ticker, 'load_plugin' ) );

