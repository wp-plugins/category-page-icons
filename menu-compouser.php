<?php
/*
Plugin Name: Category &amp; Page &nbsp; I c o n s
Plugin URI: http://plugins.wpdevelop.com
Description: Easy add icons to sidebar of categories and pages. All features are flexible and ajax based. (Wordpress customisation and plugins development &nbsp;&nbsp;&rArr;&nbsp;&nbsp; <a href="http://www.wpdevelop.com">www.wpdevelop.com</a>)
Version: 0.1
Author: Dima Sereda
Author URI: http://www.wpdevelop.com
*/
/*
Plugin Name: Menu compouser
Plugin URI: http://plugins.wpdevelop.com
Description: Flexible compose menu from pages and categories  for WordPress themes .
Version: 0.1
Author: Dima Sereda
Author URI: http://www.wpdevelop.com
*/

/*  Copyright 2009,  Dima Sereda  (email: info@wpdevelop.com),

    www.wpdevelop.com - custom wp-plugins development & WordPress solutions.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
 Tested WordPress Versions: 2.8.3 - 2.8.4

Change log:

= 0.1 =
 * Auto inserting icons into sidebar
 * Icons assigning to Pages
 * Icons assigning to Categories
 * Settings page for configuration icons width,  height, crop option, icons folder and more...
 * Firefox support images showing at selectbar
 * PHP4 support
 * Ajax multiple adding images
 * Ajax deleting images
 
TODO: Make next default settingsvsaving:
 *
 * Footer copyright at admin and client side option
        + Upload direcory entering (alredy option is exist)
        + Saving or deleting data during deactivation
 * Make more correct view and magement at the page of menu compouse
 * Saving data which have to show at footer, { may be saving other data which can show at other places - like at header }
 * Saving CSS style and editing it from the file in settings page

        + Add good icons to the admin pages
        + Deleting images option at admin page
        + Settings page for saving width height crop and icons dir
        + Auto inserting icons to the standard sidebar for pages and categories ( and may be save some CSS data according this)
        + Make public version of category and page-icons (may be post)
 *
 */


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Getting Ajax requests
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$action = $_POST['ajax_action'];
if ( isset($action) ) {

    if (!function_exists ('adebug')) { function adebug() { $var = func_get_args(); echo "<div style='text-align:left;background:#ffffff;border: 1px dashed #ff9933;font-size:11px;line-height:15px;font-family:'Lucida Grande',Verdana,Arial,'Bitstream Vera Sans',sans-serif;'><pre>"; print_r ( $var ); echo "</pre></div>"; } }

    switch ( $action ) :

        case 'delete-image-from-htmltable' :  // adebug($_POST); die();


            $path_icon = $_POST['file_name_dir'] . '/' . $_POST['file_name'];   // get file path icon

            $name_parts = pathinfo($path_icon);                                 // generate ralimg path
            $ext = $name_parts['extension'];
            $file_name_only = trim( substr( $name_parts['basename'], 0, -(1 + strlen($ext)) ) );
            $file_name_only = trim( substr( $file_name_only , 0, -( strlen($_POST['fileicon_size'])) ) );
            $path = $_POST['file_name_dir'] . '/' . $file_name_only . '.' . $ext  ;


            if (file_exists($path))  unlink( $path );
            if (file_exists($path_icon))  unlink( $path_icon );
            ?>
<script type="text/javascript">
    jQuery('#<?php echo $_POST['row_id']; ?>')
    .animate({backgroundColor:'#ffc0c0'}, 50)
    .animate({backgroundColor:'#fff'}, 400)
    .fadeOut(100);
</script>
            <?php
            die();
            break;

    endswitch;

}

if (!defined('WPDEV_CP_VERSION'))    define('WPDEV_CP_VERSION',  '0.1' );                                // 0.1
if (!defined('WPDEV_CP_PUBLIC_VERSION'))    define('WPDEV_CP_PUBLIC_VERSION', 1 );                       // 0

if (!defined('WP_CONTENT_DIR'))      define('WP_CONTENT_DIR', ABSPATH . 'wp-content');                   // Z:\home\test.wpdevelop.com\www/wp-content
if (!defined('WP_CONTENT_URL'))      define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');    // http://test.wpdevelop.com/wp-content

if (!defined('WP_PLUGIN_DIR'))       define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');               // Z:\home\test.wpdevelop.com\www/wp-content/plugins
if (!defined('WP_PLUGIN_URL'))       define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');               // http://test.wpdevelop.com/wp-content/plugins

if (!defined('WPDEV_CP_PLUGIN_FILENAME'))  define('WPDEV_CP_PLUGIN_FILENAME',  basename( __FILE__ ) );              // menu-compouser.php
if (!defined('WPDEV_CP_PLUGIN_DIRNAME'))   define('WPDEV_CP_PLUGIN_DIRNAME',  plugin_basename(dirname(__FILE__)) ); // menu-compouser

if (!defined('WPDEV_CP_PLUGIN_DIR')) define('WPDEV_CP_PLUGIN_DIR', WP_PLUGIN_DIR.'/'.WPDEV_CP_PLUGIN_DIRNAME ); // Z:\home\test.wpdevelop.com\www/wp-content/plugins/menu-compouser
if (!defined('WPDEV_CP_PLUGIN_URL')) define('WPDEV_CP_PLUGIN_URL', WP_PLUGIN_URL.'/'.WPDEV_CP_PLUGIN_DIRNAME ); // http://test.wpdevelop.com/wp-content/plugins/menu-compouser



if (!class_exists('wpdev_compose')) {
    class wpdev_compose {

        var $gui_compose;  // GUI class
        var $gui_settings;
        var $gui_icons;

        var $flash_uploader;
        var $icons_url;
        var $icons_dir;
        var $is_dir_exist;
        var $menu;

        function wpdev_compose() {

            $this->menu  = false;
            $this->icons_dir = ABSPATH . get_option('wpdev_mc_icon_dir');
            $this->icons_url = get_option('siteurl') . '/' . get_option('wpdev_mc_icon_dir');
            $this->is_dir_exist = $this->wpdev_mk_dir($this->icons_dir);

            if ( ! $this->loadLocale() )  $this->loadLocale('en_EN');

            require_once(WPDEV_CP_PLUGIN_DIR. '/include/wpdev-gui.php' ); // Connect my GUI class

            if (! WPDEV_CP_PUBLIC_VERSION) {
                $this->gui_compose = &new wpdev_gui('wpdev-cp',__FILE__,__('Menu compouser'), __('Site Menu'),'object');
                $this->gui_compose->set_icon(array(WPDEV_CP_PLUGIN_URL . '/img/Sidebar-Photos-16x16.png', WPDEV_CP_PLUGIN_URL . '/img/shared-pictures-128x128.png'));
                $this->gui_compose->add_content(array(&$this, 'content_of_compose_menu_page'));

                $reference_for_submenu = $this->gui_compose->gui_html_id_prefix;
            }

            if (WPDEV_CP_PUBLIC_VERSION)
                $this->gui_icons = &new wpdev_gui('wpdev-cp-icons',__FILE__  ,__('Uploading icons for pages and categories'), __('Icons'),'object' );
            else
                $this->gui_icons = &new wpdev_gui('wpdev-cp-icons',__FILE__  ,__('Uploading icons for pages and categories'), __('Icons'),'submenu','', __FILE__ . $reference_for_submenu );
            $this->gui_icons->set_icon(array(WPDEV_CP_PLUGIN_URL . '/img/Sidebar-Photos-16x16.png',  WPDEV_CP_PLUGIN_URL . '/img/Sidebar-Photos-256x256_1.png'));
            $this->gui_icons->add_content(array($this, 'content_of_icons_page'));/**/

            if (WPDEV_CP_PUBLIC_VERSION) $reference_for_submenu = $this->gui_icons->gui_html_id_prefix;

            $this->gui_settings = &new wpdev_gui('wpdev-cp-option',__FILE__  ,__('Menu compouser settings'), __('Settings'),'submenu','', __FILE__ . $reference_for_submenu);
            $this->gui_settings->set_icon(array('', WPDEV_CP_PLUGIN_URL . '/img/Developer-128x128.png'));
            $this->gui_settings->add_content(array($this, 'content_of_settings_page'));/**/

            add_action('admin_menu', array(&$this,'on_add_admin_plugin_page'));
            add_action('wp_head',array(&$this, 'client_side_print_compose_head'));
            add_action( 'wp_footer', array(&$this,'wp_footer') );


            add_filter('plugin_action_links', array(&$this, 'plugin_links'), 10, 2 );

            // Reassign icons for client side
            add_filter('wp_list_pages', array(&$this, 'wp_list_pages_icons'), 10, 1 );
            add_filter('wp_list_categories', array(&$this, 'wp_list_categories_icons'), 10, 1 );


            //User action for showing menu and submenu
            add_action('wpdev_menu',array(&$this, 'wpdev_show_menu'));
            add_action('wpdev_submenu',array(&$this, 'wpdev_show_submenu'));
            add_action('wpdev_memo',array(&$this, 'wpdev_show_memo'),10,1);

            register_activation_hook( __FILE__, array(&$this,'wpdev_compose_activate' ));
            register_deactivation_hook( __FILE__, array(&$this,'wpdev_compose_deactivate' ));

        }

        //   F U N C T I O N S       /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Adds Settings link to plugins settings
        function plugin_links($links, $file) {

            $this_plugin = plugin_basename(__FILE__);

            if ($file == $this_plugin) {
                $settings_link = '<a href="admin.php?page=' . WPDEV_CP_PLUGIN_DIRNAME . '/'. WPDEV_CP_PLUGIN_FILENAME . 'wpdev-cp-option">'.__("Settings").'</a>';
                array_unshift($links, $settings_link);
            }
            return $links;
        }

        // Load locale
        function loadLocale($locale = '') { // Load locale, if not so the  load en_EN default locale from folder "languages" files like "this_file_file_name-ru_RU.po" and "this_file_file_name-ru_RU.mo"
            if ( empty( $locale ) ) $locale = get_locale();
            if ( !empty( $locale ) ) {
            //Filenames like this  "microstock-photo-ru_RU.po",   "microstock-photo-de_DE.po" at folder "languages"
                $mofile = WPDEV_CP_PLUGIN_DIR  .'languages/'.str_replace('.php','',WPDEV_CP_PLUGIN_FILENAME).'-'.$locale.'.mo';
                return load_textdomain(str_replace('.php','',WPDEV_CP_PLUGIN_FILENAME), $mofile);
            } return false;
        }

        //Get array of menu and selected item inside of one array
        function get_menu_array() {

            if ($this->menu !== false) return $this->menu; // caching result

            $top_menu =       explode('|', get_option('wpdev_mc_menu_content'));
            $top_menu_label = explode('|', get_option('wpdev_mc_menu_hints'));
            $top_menu_links = explode('|', get_option('wpdev_mc_menu_links'));
            $top_submenu_id = explode('|', get_option('wpdev_mc_submenu_id'));

            $menu_array = array();

            $slct = false;

            for ($i = 0 ; $i < count($top_menu) ; $i++) {

            // C H I L D S ///////////////////////////////////////////////////////////////////////////////////////////////////////////

                $my_childs = array();
                $top_subm_id = explode ('=', $top_submenu_id[$i] );

                if ($top_subm_id[0] == 'cat') {  // C A T E G O R Y   S U B M E N U  /////////////////////////////////////////////////////

                    $categorys = get_categories('hide_empty=0&child_of='.$top_subm_id[1]);
                    $ii=0; $ic=count($categorys); $my_class ='';

                    if ($ic > 0) {

                        for ($ii = 0 ; $ii < $ic ; $ii++) { $category = $categorys[$ii]; $my_class = '';

                            $perma_load = get_category_link($category->term_id);
                            $perma_real = 'http://' .  $_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'] ;

                            if ($ii == $ic-1) $my_class = ' class="last_submenu" ';
                            if (  str_replace('/','',$perma_load) == str_replace('/','',$perma_real)  ) { $my_class = ' class="selected" '; $slct = $i; }

                            $my_childs[] =array(
                                'class' => $my_class,
                                'link'  => get_category_link($category->term_id),
                                'icon'  => $category->term_icon, // 'cat-'.$category->slug.'.jpg',    // Need to think and set goog name
                                'title' => $category->name
                            );
                        }
                    }

                } elseif($top_subm_id[0] == 'page') { // P A G E S  S U B M E N U  ///////////////////////////////////////////////////////

                    $pages = get_pages( array('child_of' => $top_subm_id[1], 'sort_column' => 'menu_order') );

                    $ii=0; $ic=count($pages); $my_class ='';

                    for ($ii = 0 ; $ii < $ic ; $ii++) { $page = $pages[$ii]; $my_class = '';

                        $perma_load = get_page_link($page->ID);
                        $perma_real = 'http://' .  $_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'] ;

                        if ($ii == $ic-1) $my_class = ' class="last_submenu" ';
                        if (  str_replace('/','',$perma_load) == str_replace('/','',$perma_real)  ) { $my_class = ' class="selected" '; $slct = $i; }

                        $my_childs[] =array(
                            'class' => $my_class,
                            'link'  => get_page_link($page->ID),
                            'icon'  => $page->post_icon, // 'page-'.$page->post_name.'.jpg',    // Need to think and set goog name
                            'title' => $page->post_title
                        );
                    }
                }

                // L I N K S /////////////////////////////////////////////////////////////////////////////////////////////////////////////

                if( strpos( $top_menu_links[$i] , ':') === false)
                    $link_url =   get_option('home') . '/' . $top_menu_links[$i] ;
                else
                    $link_url =  $top_menu_links[$i] ;

                // check if right now atadress bar this link
                $perma_real = 'http://' .  $_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'] ;
                if (  str_replace('/','',$link_url) == str_replace('/','',$perma_real)  ) { $slct = $i; }

                //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

                $menu_array[]=array(
                    'title' => $top_menu[$i],
                    'hint' => $top_menu_label[$i],
                    'link' => "href='" . $link_url . "'",
                    $top_subm_id[0] => $top_subm_id[1],
                    'childs' => $my_childs
                );
            }

            if ($slct !== false) { $menu_array_selected = $menu_array[$slct]; $menu_array_selected['selected_num'] = $slct; }

            $result = array('menu' => $menu_array, 'selected' =>$menu_array_selected);

            $this->menu = $result;

            //debuge(  $result );
            return $result;

        }

        // Make    Dir    in    cickle
        function wpdev_mk_dir($path, $mode = 0777) {

            if (DIRECTORY_SEPARATOR == '/')
                $path=str_replace('\\','/',$path);
            else
                $path=str_replace('/','\\',$path);

            if ( is_dir($path) || empty($path) ) return true;   // Check if directory already exists
            if ( is_file($path) ) return false;                 // Ensure a file does not already exist with the same name

            $dirs = explode(DIRECTORY_SEPARATOR , $path);
            $count = count($dirs);
            $path = $dirs[0];
            for ($i = 1; $i < $count; ++$i) {
                if ($dirs[$i] !="") {
                    $path .= DIRECTORY_SEPARATOR . $dirs[$i];
                    if (!is_dir($path) && !mkdir($path, $mode)) return false;
                }
            }
            return true;
        }

        //   A D M I N     S I D E   /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // H E A D

        // Print scripts only at plugin page
        function on_add_admin_plugin_page() {
            add_action("admin_print_scripts-" . $this->gui_compose->pagehook , array( &$this, 'on_add_admin_js_files'));
            // Change Title of the Main menu inside of submenu
            global $submenu, $menu;
            $submenu[plugin_basename( __FILE__ ) . $this->gui_compose->gui_html_id_prefix][0][0] = __('Menu compouser');

            add_action("admin_print_scripts-" . $this->gui_icons->pagehook ,    array( &$this, 'on_add_admin_js_files'));
            add_action("admin_print_scripts-" . $this->gui_settings->pagehook , array( &$this, 'on_add_admin_js_files'));

        }

        // add hook for printing scripts only at this plugin page
        function on_add_admin_js_files() {
            wp_print_scripts( array( 'sack' ));
            add_action('admin_head', array(&$this, 'head_print_js_css' ), 1); // Write inline scripts and CSS at HEAD
        }

        // Print NOW   admin     J a v a S cr i p t   &    C S S
        function head_print_js_css() {
            if (! is_admin()) wp_print_scripts('jquery');

            //     S T Y L E S       //////////////////////////////////////////////////////////////////////////////////////// ?>
<link rel="stylesheet" href="<?php echo WPDEV_CP_PLUGIN_URL; ?>/css/menu.css" type="text/css" media="all" />      <?php

            if ( is_admin() ) {
                echo   '<link rel="stylesheet" href="'. WPDEV_CP_PLUGIN_URL. '/css/admin.css" type="text/css" media="all" />';
                ?>
<script type="text/javascript">
    //<![CDATA[
    function ajaxDeleteIcon(icon_name, tr_id) {

        var wpdev_flash_uploader_path = '<?php echo WPDEV_CP_PLUGIN_URL . '/' . WPDEV_CP_PLUGIN_FILENAME ; ?>';
        var v_file_name_dir = '<?php echo str_replace('\\','/',$this->icons_dir) ; ?>';
        var v_fileicon_size = '-<?php echo get_option( 'wpdev_mc_icon_size_w' ); ?>x<?php echo get_option( 'wpdev_mc_icon_size_h' ); ?>';

        jQuery.ajax({
            url: wpdev_flash_uploader_path,
            type:'POST',
            success: function (data, textStatus){ if( textStatus == 'success')   jQuery('#ajax_respond').html( data )  },
            error:function (XMLHttpRequest, textStatus, errorThrown){ alert('Ajax sending Error status:' + textStatus)},
            // beforeSend: someFunction,
            data:{
                ajax_action : 'delete-image-from-htmltable',
                file_name_dir : v_file_name_dir,
                fileicon_size : v_fileicon_size,
                file_name : icon_name,
                row_id : tr_id
            }
        });
        return false;
    }
    //]]>

    function changeIcon(icon_name, el_id) {
        var html = '<img src="'+ icon_name + '" >';
        jQuery('#curent'+el_id).html( html );/**/
        jQuery('#current_icon'+el_id).val( icon_name );/**/
    }

</script>
            <?php

            } else {
                echo   '<link rel="stylesheet" href="'. WPDEV_CP_PLUGIN_URL. '/css/client.css" type="text/css" media="all" />';
            }
        }


        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // M E N U   C O M P O U S E R

        function content_of_compose_menu_page() {
            ?>
<div class="clear"><br></div>
<a href="admin.php?page=<?php echo WPDEV_CP_PLUGIN_DIRNAME . '/'. WPDEV_CP_PLUGIN_FILENAME ; ?>wpdev-cp&tab=compouse"><?php _e('Menu compousing'); ?></a> |
<a href="admin.php?page=<?php echo WPDEV_CP_PLUGIN_DIRNAME . '/'. WPDEV_CP_PLUGIN_FILENAME ; ?>wpdev-cp&tab=submenu"><?php _e('Assigning submenu to menu'); ?></a>
<div class="clear"><br></div>

            <?php

            $this->on_save_changes();

            switch ($_GET['tab']) {

                case 'compouse':
                    $this->write_content_of_compose_menu_page();
                    break;

                case 'submenu':
                    $this->write_content_of_compose_sub_menu_page();
                    break;

                default:
                    $this->write_content_of_compose_menu_page();
                    break;
            }
        }

        // CONTENT OF THE ADMIN PAGE
        function write_content_of_compose_menu_page () {
            ?>
<div class="clear" style="height:10px;"></div>
<div id="ajax_working"></div>
<div id="poststuff" class="metabox-holder">
    <div class='meta-box'>
        <div  class="postbox" > <h3 class='hndle'><span><?php _e('Menu compousing'); ?></span></h3>
            <div class="inside">
                <form action=""  method="post" id="menu_compouse_form" name="menu_compouse_form">
                                <?php
                                $this->wpdev_show_menu();
                                $menu_content = get_option( 'wpdev_mc_menu_content' );
                                $menu_hints =   get_option( 'wpdev_mc_menu_hints' );
                                $menu_links =   get_option( 'wpdev_mc_menu_links' );
                                ?>

                    <p><label><?php _e('Please enter menu titles divided by "|" separator'); ?>:</label>
                        <textarea class="text_area" rows="2" name="menu_content" id="menu_content"><?php echo $menu_content ?></textarea></p>

                    <p><label for="menu_hints"><?php _e('Please enter menu hints divided by "|" separator'); ?>:</label>
                        <textarea class="text_area" rows="2" name="menu_hints" id="menu_hints"><?php echo $menu_hints ?></textarea></p>

                    <p><label><?php _e('Please enter menu links divided by "|" separator'); ?>:</label>
                        <textarea class="text_area" rows="2" name="menu_links" id="menu_links"><?php echo $menu_links ?></textarea></p>


                    <p><label><?php _e('Insert this code inside of theme for showing MAIN Menu:'); ?></label>
                        <code> &lt;?php do_action('wpdev_menu'); ?&gt; </code> </p>

                    <p><label><?php _e('Insert this code inside of theme for showing SUB  Menu:'); ?></label>
                        <code> &lt;?php do_action('wpdev_submenu'); ?&gt; </code> </p>

                    <p><label><?php _e('Insert this code inside of theme for showing memo:'); ?></label>
                        <code> &lt;?php do_action('wpdev_memo','My Title', array( 'My text 1', 'My text 2' ) ?&gt; </code> </p><br>



                    <input class="button-primary" style="float:right;" type="submit" value="<?php _e('Save Changes'); ?>" name="Submit"/>
                    <div class="clear" style="height:10px;"></div>
                </form>
            </div>
        </div>
    </div>
</div>
        <?php   // $this->write_content_of_compose_sub_menu_page();
        }

        // CONTENT OF THE ADMIN PAGE
        function write_content_of_compose_sub_menu_page () {
            ?>
<script type="text/javascript">
    var val1 = '<img src="<?php echo WPDEV_CP_PLUGIN_URL; ?>/img/subscriptions-128x128.png"><br />';
    jQuery('div.wrap div.icon32').html(val1);
    jQuery('div.wrap h2').html('<?php _e('Assigning submenu to menu'); ?>');
</script>

<div class="clear" style="height:10px;"></div>

<div id="poststuff" class="metabox-holder">
    <div class='meta-box'>
        <div  class="postbox" >  <div class="handlediv" title=" Click to toggle "><br /></div>
            <h3 class='hndle'><span><?php _e('Assigning submenu to menu '); ?></span></h3>
            <div class="inside">
                <form action=""  method="post" id="menu_compouse_form_sub" name="menu_compouse_form_sub">
                    <div style="float:left;">
                        <div style="margin:0px;margin-top:32px;font-size:12px;font-weight:bold;">
                            <a href="categories.php"><?php _e('Categories'); ?></a>  -></div>
                        <div style="margin:0px;margin-top:12px;font-size:12px;font-weight:bold;">
                            <a href="edit-pages.php"><?php _e('Pages'); ?></a>  -></div>
                    </div>
                                <?php
                                $top_menu =       explode('|', get_option('wpdev_mc_menu_content'));
                                $i=0;$ic=count($top_menu);

                                $submenu = get_option( 'wpdev_mc_submenu_id');
                                $submenu = explode('|',$submenu);

                                foreach ($top_menu as $tm) {$i++;$sb = array(0,0);  $sb = explode('=',$submenu[$i-1]); ?>
                    <div style="float:left;" class="assigning_menu">
                        <h2>
                                            <?php echo $tm; ?>
                        </h2>
                        <br>
                                        <?php
                                        if ($sb[0] == 'cat') $slctd = $sb[1];
                                        else $slctd = 0;
                                        wp_dropdown_categories( array( 'selected' => $slctd, 'hide_empty' => 0, 'name' => 'cat_' . $i, 'orderby' => 'name', 'hierarchical' => 1, 'show_option_none' => __(' '), 'tab_index' => 3 ) ); ?>
                        <br>
                                        <?php
                                        if ($sb[0] == 'page') $slctd = $sb[1];
                                        else $slctd = 0;
                                        wp_dropdown_pages( array( 'selected' => $slctd,  'name' => 'page_' . $i, 'orderby' => 'name', 'hierarchical' => 1, 'show_option_none' => __(' '), 'tab_index' => 4 ) ); ?>
                        <!--br>
                        <input type="text" value="<?php echo $i; ?>" name="menulink_<?php echo $i; ?>" id="menulink_<?php echo $i; ?>" -->
                    </div>
                                <?php
                                }
                                ?>

                    <div class="clear" style="height:10px;"></div>
                    <input type="hidden" value="<?php echo $ic; ?>" name="submenu_count"/>
                    <input class="button-primary" style="float:right;" type="submit" value="<?php _e('Save Changes'); ?>" name="Submit"/>
                    <div class="clear" style="height:10px;"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    //<![CDATA[
    jQuery(document).ready( function($) {
        // close postboxes that should be closed
        $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
        // postboxes setup
        postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
    });
    //]]>
</script>
        <?php
        }

        // SAVING ON SUBMIT BUTTON CLICK
        function on_save_changes() {

            if ( isset($_POST['menu_content']) ) {
            //debuge($_POST);
                $menu_content = $_POST['menu_content'];
                $menu_hints = $_POST['menu_hints'];
                $menu_links = $_POST['menu_links'];
                $submenu_id = $_POST['submenu_id'];

                ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                if ( get_option( 'wpdev_mc_menu_content' ) !== false  )   update_option( 'wpdev_mc_menu_content' , $menu_content );
                else                                                add_option('wpdev_mc_menu_content' , $menu_content );

                ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                if ( get_option( 'wpdev_mc_menu_hints' ) !== false  )   update_option( 'wpdev_mc_menu_hints' , $menu_hints );
                else                                                add_option('wpdev_mc_menu_hints' , $menu_hints );

                ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                if ( get_option( 'wpdev_mc_menu_links' ) !== false  )   update_option( 'wpdev_mc_menu_links' , $menu_links );
                else                                                add_option('wpdev_mc_menu_links' , $menu_links );

            }

            if( isset( $_POST['submenu_count'] ) ) {

                $sub_save = '';
                for ($i = 1 ; $i <= $_POST['submenu_count'] ; $i++) {

                    if ( $_POST['page_'.$i] != '' )      $sub_save .= 'page=' . $_POST['page_'.$i];
                    elseif ( $_POST['cat_'.$i] != '-1' ) $sub_save .= 'cat=' . $_POST['cat_'.$i];

                    $sub_save .= '|';
                } $sub_save = substr($sub_save,0,strlen($sub_save)-1);
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                if ( get_option( 'wpdev_mc_submenu_id' ) !== false  )   update_option( 'wpdev_mc_submenu_id' , $sub_save );
                else                                              add_option('wpdev_mc_submenu_id' , $sub_save );
            }
        }


        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //   I   C  O  N  S

        //Content of icons page
        function content_of_icons_page() {
            ?>
<div class="clear"><br></div>
<a href="admin.php?page=<?php echo WPDEV_CP_PLUGIN_DIRNAME . '/'. WPDEV_CP_PLUGIN_FILENAME ; ?>wpdev-cp-icons&tab=page"><?php _e('Assign icons to Pages'); ?></a> |
<a href="admin.php?page=<?php echo WPDEV_CP_PLUGIN_DIRNAME . '/'. WPDEV_CP_PLUGIN_FILENAME ; ?>wpdev-cp-icons&tab=category"><?php _e('Assign icons to Categories'); ?></a> |
<a href="admin.php?page=<?php echo WPDEV_CP_PLUGIN_DIRNAME . '/'. WPDEV_CP_PLUGIN_FILENAME ; ?>wpdev-cp-icons&tab=add_icons"><?php _e('Add / Delete icons'); ?></a> |
<!--a href="admin.php?page=<?php echo WPDEV_CP_PLUGIN_DIRNAME . '/'. WPDEV_CP_PLUGIN_FILENAME ; ?>wpdev-cp-icons&tab=settings"><?php _e('Settings'); ?></a-->
<a href="admin.php?page=<?php echo WPDEV_CP_PLUGIN_DIRNAME . '/'. WPDEV_CP_PLUGIN_FILENAME ; ?>wpdev-cp-option"><?php _e('Settings'); ?></a>
<div class="clear"><br></div>

            <?php
            $files = $this->dirList($this->icons_dir .'/');

            switch ($_GET['tab']) {

                case 'page':
                    $this->write_pages_table($files);
                    break;

                case 'category':
                    $this->write_category_table($files);
                    break;

                case 'add_icons':
                    $this->write_add_icons();
                    $this->write_delete_icons_page($files);
                    break;

                case 'settings':
                    $this->write_icons_settings();
                    break;

                default:
                    $this->write_add_icons();
                    $this->write_delete_icons_page($files);
                    break;
            }
        }


        function write_icons_settings() {
            ?>
<script type="text/javascript">
    var val1 = '<img src="<?php echo WPDEV_CP_PLUGIN_URL; ?>/img/Developer-128x128.png"><br />';
    jQuery('div.wrap div.icon32').html(val1);
    jQuery('div.wrap h2').html('<?php _e('Icons settings'); ?>');
</script>
        <?php
        }

        // ADD DELETE ICONS

        //Write icons delete page
        function write_delete_icons_page($files) {
            ?>

<div class="clear"><br></div>

<div id="ajax_respond"></div>

<div style="float:left; margin:5px;">
    <table class="widefat"   style="width:330px !important;">
        <thead>
            <tr>
                <th scope="col" width="10px" ><?php _e('#') ?></th>
                <th scope="col" width="50px" style="text-align:center"><?php _e('Icon') ?></th>
                <th scope="col" width="200px"   ><?php _e('File name') ?></th>
                <th scope="col" width="70px" ><?php _e('Operation') ?></th>
            </tr>
        </thead>
        <tbody id="the-list" class="list:cat">

                        <?php  $this->write_delete_icons_rows( $files );  ?>

        </tbody>
    </table>
</div>

<div class="clear"><br></div>
        <?php
        }

        // support function for dividing tabel into several ables
        function writeSeparator() { ?>

</tbody>
</table> 
</div>

<div style="float:left;margin:10px 5px;">

    <table class="widefat"  style="width:330px !important;" >
        <thead>
            <tr>
                <th scope="col" width="10px" ><?php _e('#') ?></th>
                <th scope="col" width="50px" style="text-align:center"><?php _e('Icon') ?></th>
                <th scope="col" width="200px"    ><?php _e('File name') ?></th>
                <th scope="col" width="70px;" ><?php _e('Operation') ?></th>
            </tr>
        </thead>
        <tbody id="the-list" class="list:cat">
                    <?php
                    }

        // Write category rows
        function write_delete_icons_rows(  $files ,  $class =''   ) {

            $i = 0;
            foreach ($files as $file) {$i++;

                $class = " class='alternate'" == $class ? '' : " class='alternate'";

                $js_class_name = $class == '' ? '' : 'alternate';
                $js =   ' onMouseOver="this.className=\'iconOver\'" onMouseOut="this.className=\''. $js_class_name .'\'" '; // onMouseDown="document.getElementById(\'del-'.$i.'\').checked= document.getElementById(\'del-'.$i.'\').checked == true ? false : true; " '; //.checked=true;

                $output = '<tr id="iconrow_'.$i.'" '.$class. $js .' >'.
                    '<div  >' ;

                $cur_icon =  '<img src="' .  $this->icons_url. '/' . $file . '" />'  ;


                if ( strlen($file)>18  ) {
                    $file_name = substr($file,0, 10) . '...' . substr($file,-11);

                } else {
                    $file_name =   $file;
                }

                $output .=  '<td style="text-align:center;vertical-align:middle;height:'.  (get_option( 'wpdev_mc_icon_size_h' ) +7 ).'px;">'.$i.'</td>'.
                    '<td style="vertical-align:middle;text-align:center" align="center">' .
                    $cur_icon .
                    '<input value="'.$this->icons_url. '/' . $file .'" id="current_icon'. $file.'" name="current_icon'.$file.'" type="hidden">
                                    </td>'.
                    '<td style="vertical-align:middle;font-weight:normal;">'. $file_name . '</td>';
                $output .=  '<th scope="row"  style="text-align:center;vertical-align:middle;padding:7px 0 8px">';
                $output .=          '<input type="button" class="button-secondary action" style="padding:1px 3px;" href="#" onMouseDown=" ajaxDeleteIcon(\''. $file .'\', \'iconrow_'. $i .'\'); "  id="del-'.$i.'"  value="' . __('Delete') . '" />';
                $output .=  '</th>';

                $output .=      "\n\t</div></tr>\n";
                echo  $output;

                //debuge( count($files) ,   intval(count($files) / 3) +1  , true);


                //if ( ($i % ( intval(count($files) / 3) + 1 ) ) == 0) {
                if ( ($i % 20 ) == 0) {
                    $this->writeSeparator();
                }

            }
        }


        // Write tabof adding icons
        function write_add_icons() {

            ?> <div class="clear"><br></div> <?php

            require_once(WPDEV_CP_PLUGIN_DIR. '/include/wpdev-flash-uploader.php' ); // Connect to flash uploader class

            $this->flash_uploader = &new wpdev_flash_uploader( 'Choose Icons to upload' );

            $this->flash_uploader->set_dir( array($this->icons_dir, $this->icons_url) );

            if(get_option( 'wpdev_mc_icon_crop' ) == 'On') $wpdev_mc_icon_crop = 1;
            else                                           $wpdev_mc_icon_crop = 0;
            $this->flash_uploader->set_sizes( get_option( 'wpdev_mc_icon_size_w' ), get_option( 'wpdev_mc_icon_size_h' ),  $wpdev_mc_icon_crop );

            $this->flash_uploader->upload_form();
        }



        // P  A  G  E

        // Write FULL category Table
        function write_pages_table($files) {

        // POST  updating ///////////////////////////////////////////////////////
            if ( isset( $_POST['update_pages_icons'] ) ) {
                global $wpdb;

                $my_query = array();


                $cats_id = get_all_page_ids();
                foreach ($cats_id as $cid) {
                    if ( isset( $_POST[ 'current_icon' . $cid ] ) ) {
                        $my_query[] = 'UPDATE '.$wpdb->posts.'
                                    SET post_icon = "'. $_POST[ 'current_icon' . $cid ] .'"
                                    WHERE ID = '. $cid  . ' ';
                    }
                }
                foreach ($my_query as $wp_q)
                    $wpdb->query($wp_q);

            } ////////////////////////////////////////////////////////////////////////

            $pages = $this->get_pages_tree();
            ?>

<script type="text/javascript">
var val1 = '<img src="<?php echo WPDEV_CP_PLUGIN_URL; ?>/img/Sites-128x128.png"><br />';
jQuery('div.wrap div.icon32').html(val1);
jQuery('div.wrap h2').html('<?php _e('Assigning icons to pages'); ?>');
</script>

<div class="wrap">
<form  action="" method="post">
    <table class="widefat" id="caticons_table">
        <thead>
            <tr>
                <th scope="col" width="170px" style="text-align:center"><?php _e('Select Icon') ?></th>
                <th scope="col" width="50px" style="text-align:center"><?php _e('Current') ?></th>
                <th scope="col" width="20%" ><?php _e('Title') ?></th>
                <th scope="col" style="text-align:center"><?php _e('Content') ?></th>
            </tr>
        </thead>
        <tbody id="the-list" class="list:cat">

                        <?php  $this->write_page_rows($pages, $files);  ?>

        </tbody>
    </table>
    <div class="clear"><br/></div>
    <input type="hidden" name="update_pages_icons" id="update_pages_icons" value="1">
    <input class="button-primary" style="float:right;" type="submit" value="<?php _e('Save Changes'); ?>" name="Submit"/>
    <div class="clear"><br></div>
</form>
</div>
    <?php  /**/
    }

        // Write category rows
        function write_page_rows( $rows, $files ,  $class ='' , $depth = '' ) {

            foreach ($rows as $row) {

                $class = " class='alternate'" == $class ? '' : " class='alternate'";

                $icon_cell = '<select class="webmenu" id="webmenu'.$row['object']->ID.'" onchange="javascript:changeIcon(this.value, \''.$row['object']->ID.'\');">';
                $icon_cell .=  '<option value="" style="height:28px;padding-top:5px;line-height:28px;font-size:12px;padding-left:40px;"></option>';
                foreach ($files as $file) {
                    $icon_cell .=  '<option value="'.$this->icons_url.'/' . $file . '"
                                                        style="background: url(\''.$this->icons_url.'/' . $file . '\') no-repeat 5px 0px; height:28px;padding-top:5px;line-height:28px;font-size:12px;padding-left:40px;"' ;

                    if ($row['object']->post_icon == $this->icons_url. '/' . $file )  $icon_cell .= ' selected="selected" ';

                    $icon_cell .=  '>' . $file . '</option>';
                }
                $icon_cell .=  '</select>';

                $cur_icon = ($row['object']->post_icon !== '') ? '<img src="' . $row['object']->post_icon . '" />' : '';


                $output = '<tr id="cat-'.$row['object']->ID.'" '.$class.' >';
                $output .=  //'<td style="text-align:center;vertical-align:middle;height:'.  (get_option( 'wpdev_mc_icon_size_h' ) +7 ).'px;">'.$row['object']->ID.'</td>'.
                    '<td style="vertical-align:middle;text-align:center;height:'.  (get_option( 'wpdev_mc_icon_size_h' ) +7 ).'px;" align="center">'.$icon_cell.'</td>'.
                    '<td style="vertical-align:middle;text-align:center" align="center">
                                                <div id="curent'.$row['object']->ID.'" >'.
                    $cur_icon .
                    '</div>
                                                <input value="'.$row['object']->post_icon.'" id="current_icon'. $row['object']->ID.'" name="current_icon'.$row['object']->ID.'" type="hidden">
                                            </td>'.
                    '<td style="vertical-align:middle;font-weight:bold;">'.$depth .  '<a href="/wp-admin/page.php?action=edit&post='.$row['object']->ID.'" >' .$row['object']->post_title.'</a></td>'.
                    '<td style="vertical-align:middle;">'.substr( $row['object']->post_content,0,85).'..</td>'.
                    "\n\t</tr>\n";
                echo  $output;

                if ( count($row['childs']) > 0 )
                    $this->write_page_rows($row['childs'], $files, $class, $depth . '&nbsp; -&nbsp;&nbsp;');
            }
        }

        // get pages tree array
        function get_pages_tree() {

            $pages = get_pages('hide_empty=0');
            for ($i = 0 ; $i < count($pages) ; $i++) {
                $pages[$i]->post_content = substr( strip_tags( $pages[$i]->post_content ),0,85 );
            }
            $pages_id=array();
            $pages_work = array();
            $pages_sort = array();

            // make temp work arrays
            foreach ($pages as $page) {
                $pages_id[ $page->ID ] =  $page->post_parent;
                $pages_work[$page->ID] =  $page;
                $pages_sort[$page->ID] = $page->menu_order;
            }
            //Sorting array based on values
            asort($pages_sort);


            // Recursive function for Gets array of all childs ID from specific node in order from childest to root
            function get_tree_node($key, $pages_id) {

                if ($pages_id[ $key ] == 0 ) return array(0);
                else {
                    $my_node = get_tree_node( $pages_id[ $key ], $pages_id);
                    array_push( $my_node, $pages_id[ $key ] );
                    return $my_node;
                }
            }

            // Here we are get for each elamnt - pathes  from root to childest node
            // Gets array of all childs ID
            $pages_fin = array();
            foreach ($pages_id as $key=>$value) {
                $pages_fin[$key] = get_tree_node($key , $pages_id);
            }/**/

            // transform childs array in string format line
            foreach ($pages_fin as $key=>$value) {
                $temp_node = '';
                foreach ($value as $v) $temp_node .= $v;
                $pages_fin[$key] = $temp_node;
            }/**/


            // this is function is needed for sorting array with strings node. At top will be longest node, its need for start working with childes yoang node
            // Its primary order function based on parents
            function sort_max_length($a,$b) {
                if ( strlen($a) == strlen($b) ) return 0;
                return ( strlen($a) < strlen($b) ) ? 1 : -1;
            }
            // Sort array
            uasort($pages_fin, 'sort_max_length');


            $new_sort_fin_array = array();  $order = 0;  $last_string = '';
            // Create here work archive for future sorting by 2 collumns
            foreach ($pages_fin as $key => $value) {
            // Here we are checking if we take other parent path node
                if ( strlen($value) != strlen($last_string) ) $order++;

                array_push($new_sort_fin_array,array('id' => $key, 'data' => $value, 'order' => $order, 'sub_order' => $pages_sort[$key]));

                $last_string = $value;
            }

            // User Function for multi array sorting
            function array_sort_func($a,$b=NULL) {
                static $keys;
                if($b===NULL) return $keys=$a;
                foreach($keys as $k) {
                    if(@$k[0]=='!') {
                        $k=substr($k,1);
                        if(@$a[$k]!==@$b[$k]) {
                            return strcmp(@$b[$k],@$a[$k]);
                        }
                    }
                    else if(@$a[$k]!==@$b[$k]) {
                            return strcmp(@$a[$k],@$b[$k]);
                        }
                }
                return 0;
            }
            // Function for multi array sorting
            function array_sort(&$array) {
                if(!$array) return $keys;
                $keys=func_get_args();
                array_shift($keys);
                array_sort_func($keys);
                usort($array,"array_sort_func");
            }
            //Sort here array by 2 collumn if first collumn equal then sort by other collumn
            array_sort($new_sort_fin_array,'order','sub_order');


            // create here Final ordered array by parents and by sort order
            $pages_fin = array();
            foreach ($new_sort_fin_array as $key => $item) {
                $pages_fin[$item['id']] = $item['data'];
            }


            $ik = array(); $wa = array();
            //generate temp array with index and keys and final work array whch will be return
            foreach ($pages_fin as $key => $value) {
                array_push($ik,$key) ; // here we cerate order in which we willwork with ID
                $wa[$key] = array(   'childs' => array() , 'object' => $pages_work[$key] ) ;
            }

            // Go throug sorted array from longest node to root And
            // set active node as child to his parent if node has parent
            for($i=0; $i< count( $ik )-1; $i++) {
                for($j = $i+1; $j<count( $ik ); $j++ ) {
                    if ( $wa[ $ik[$i]]['object']->post_parent == $ik[$j] ) {
                        $wa[ $ik[$j] ]['childs'][ $ik[$i] ] = $wa[ $ik[$i] ];
                        unset( $wa[ $ik[$i] ]);
                        break;
                    }
                }
            }
            return $wa;
        }

        // Insert images inside of standard pages
        function wp_list_pages_icons($output) {

            $my_childs = array();

            // Get list of icons /////////////////////////////////////////////////////////////////////////////////////////////////////////
            $pages = get_pages( array('child_of' => 0 , 'sort_column' => 'menu_order') );

            $ii=0; $ic=count($pages); $my_class ='';

            for ($ii = 0 ; $ii < $ic ; $ii++) { $page = $pages[$ii]; $my_class = '';

                $perma_load = get_page_link($page->ID);
                $perma_real = 'http://' .  $_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'] ;

                if ($ii == $ic-1) $my_class = ' class="last_submenu" ';
                if (  str_replace('/','',$perma_load) == str_replace('/','',$perma_real)  ) { $my_class = ' class="selected" '; $slct = $i; }

                $my_childs[] =array(
                    'class' => $my_class,
                    'link'  => get_page_link($page->ID),
                    'icon'  => $page->post_icon, // 'page-'.$page->post_name.'.jpg',    // Need to think and set goog name
                    'title' => $page->post_title
                );
            }
            // ////////////////// /////////////////////////////////////////////////////////////////////////////////////////////////////////


            foreach ($my_childs as $pg) {

                if (! empty ($pg['icon']) ) {
                    $pos = strpos($output, '"' . $pg['link'] . '"'); // Get position of link inside output
                    if ( $pos !== false) {

                        $pos = strpos($output, '>' ,$pos); // Get position of end of A attribute

                        if ( $pos !== false) {
                            $pos++;
                            $output = substr($output, 0, $pos) . '<img src="'. $pg['icon'] .'" class="page_icon" alt="'. $pg['title'] .'">' . substr($output, $pos);  //Insert icon
                        }
                    }
                }
            }



            return $output;
        }



        // C  A  T  E  G  O  R  Y

        // Write FULL category Table
        function write_category_table($files) {

        // POST  updating ///////////////////////////////////////////////////////
            if ( isset( $_POST['update_category_icons'] ) ) {

                global $wpdb;

                $my_query = array();


                $cats_id = get_all_category_ids();
                foreach ($cats_id as $cid) {
                    if ( isset( $_POST[ 'current_icon' . $cid ] ) ) {
                        $my_query[] = 'UPDATE '.$wpdb->terms.'
                                        SET term_icon = "'. $_POST[ 'current_icon' . $cid ] .'"
                                        WHERE term_id = '. $cid  . ' ';
                    }
                }
                foreach ($my_query as $wp_q)
                    $wpdb->query($wp_q);

            } ////////////////////////////////////////////////////////////////////////

            $cats = $this->get_category_tree();
            ?>
<script type="text/javascript">
    var val1 = '<img src="<?php echo WPDEV_CP_PLUGIN_URL; ?>/img/Sites-alt-128x128.png"><br />';
    jQuery('div.wrap div.icon32').html(val1);
    jQuery('div.wrap h2').html('<?php _e('Assigning icons to categories'); ?>');
</script>

<div class="wrap">
    <form  action="" method="post">
        <table class="widefat" id="caticons_table">
            <thead>
                <tr>
                    <th scope="col" width="170px" style="text-align:center"><?php _e('Select Icon') ?></th>
                    <th scope="col" width="50px" style="text-align:center"><?php _e('Current') ?></th>
                    <th scope="col" width="20%" ><?php _e('Name') ?></th>
                    <th scope="col" style="text-align:center"><?php _e('Description') ?></th>
                    <th scope="col"  width="50px" class="num" style="text-align:center"><?php _e('Posts') ?></th>
                </tr>
            </thead>
            <tbody id="the-list" class="list:cat">

                            <?php  $this->write_rows($cats, $files);  ?>

            </tbody>
        </table>
        <div class="clear"><br/></div>
        <input type="hidden" name="update_category_icons" id="update_category_icons" value="1">
        <input class="button-primary" style="float:right;" type="submit" value="<?php _e('Save Changes'); ?>" name="Submit"/>
    </form>
</div>
        <?php  /**/
        }

        // Write category rows
        function write_rows( $rows, $files ,  $class ='' , $depth = '' ) {

            foreach ($rows as $row) {

                $class = " class='alternate'" == $class ? '' : " class='alternate'";

                $icon_cell = '<select class="webmenu" id="webmenu'.$row['object']->term_id.'" onchange="javascript:changeIcon(this.value, \''.$row['object']->term_id.'\');">';
                $icon_cell .=  '<option value="" style="height:28px;padding-top:5px;line-height:28px;font-size:12px;padding-left:40px;"></option>';
                foreach ($files as $file) {
                    $icon_cell .=  '<option value="'.$this->icons_url.'/' . $file . '"
                                                        style="background: url(\''.$this->icons_url.'/' . $file . '\') no-repeat 5px 0px; height:28px;padding-top:5px;line-height:28px;font-size:12px;padding-left:40px;"' ;

                    if ($row['object']->term_icon == $this->icons_url. '/' . $file )  $icon_cell .= ' selected="selected" ';

                    $icon_cell .=  '>' . $file . '</option>';
                }
                $icon_cell .=  '</select>';

                $cur_icon = ($row['object']->term_icon !== '') ? '<img src="' . $row['object']->term_icon . '" />' : '';



                $output = '<tr id="cat-'.$row['object']->term_id.'" '.$class.' >';
                //             '<th scope="row" class="check-column" style="text-align:center;vertical-align:middle;padding:7px 0 8px">';
                //$output .=          '<input type="checkbox" name="delete[]" value="'. $row['object']->term_id.'" /></th>';



                $output .=  //'<td style="text-align:center;vertical-align:middle">'.$row['object']->term_id.'</td>'.
                    '<td style="vertical-align:middle;text-align:center;height:'.  (get_option( 'wpdev_mc_icon_size_h' ) +7 ).'px;" align="center">'.$icon_cell.'</td>'.
                    '<td style="vertical-align:middle;text-align:center" align="center">
                                                <div id="curent'.$row['object']->term_id.'" >'.
                    $cur_icon .
                    '</div>
                                                <input value="'.$row['object']->term_icon.'" id="current_icon'. $row['object']->term_id.'" name="current_icon'.$row['object']->term_id.'" type="hidden">
                                            </td>'.
                    '<td style="vertical-align:middle;font-weight:bold;">'.$depth . '<a href="/wp-admin/categories.php?action=edit&cat_ID='.$row['object']->term_id.'" >' . $row['object']->name.'</a></td>'.
                    '<td style="vertical-align:middle;">'.substr( $row['object']->description,0,75).'..</td>'.
                    '<td class="num" style="vertical-align:middle;text-align:center" align="center" >'.$row['object']->count.'</td>'.
                    "\n\t</tr>\n";
                echo  $output;

                if ( count($row['childs']) > 0 )
                    $this->write_rows($row['childs'], $files, $class, $depth . '&nbsp; -&nbsp;&nbsp;');
            }
        }

        // Get category array from wordpress native structure -- Huge functions which is have correct work but I think need some optimisation
        function get_category_tree() {

            $cats = get_categories('hide_empty=0');

            $cats_id=array();
            $cats_work = array();
            $cats_sort = array();

            // make temp work arrays
            foreach ($cats as $cat) {
                $cats_id[ $cat->term_id ] =  $cat->parent;
                $cats_work[$cat->term_id] =  $cat;
                $cats_sort[$cat->term_id] = $cat->term_order;
            }
            //Sorting array based on values
            asort($cats_sort);

            // Recursive function for Gets array of all childs ID from specific node in order from childest to root
            function get_tree_node($key, $cats_id) {

                if ($cats_id[ $key ] == 0 ) return array(0);
                else {
                    $my_node = get_tree_node( $cats_id[ $key ], $cats_id);
                    array_push( $my_node, $cats_id[ $key ] );
                    return $my_node;
                }
            }

            // Here we are get for each elamnt - pathes  from root to childest node
            // Gets array of all childs ID
            $cats_fin = array();
            foreach ($cats_id as $key=>$value) {
                $cats_fin[$key] = get_tree_node($key , $cats_id);
            }/**/

            // transform childs array in string format line
            foreach ($cats_fin as $key=>$value) {
                $temp_node = '';
                foreach ($value as $v) $temp_node .= $v;
                $cats_fin[$key] = $temp_node;
            }/**/


            // this is function is needed for sorting array with strings node. At top will be longest node, its need for start working with childes yoang node
            // Its primary order function based on parents
            function sort_max_length($a,$b) {
                if ( strlen($a) == strlen($b) ) return 0;
                return ( strlen($a) < strlen($b) ) ? 1 : -1;
            }
            // Sort array
            uasort($cats_fin, 'sort_max_length');


            $new_sort_fin_array = array();  $order = 0;  $last_string = '';
            // Create here work archive for future sorting by 2 collumns
            foreach ($cats_fin as $key => $value) {
            // Here we are checking if we take other parent path node
                if ( strlen($value) != strlen($last_string) ) $order++;

                array_push($new_sort_fin_array,array('id' => $key, 'data' => $value, 'order' => $order, 'sub_order' => $cats_sort[$key]));

                $last_string = $value;
            }

            // User Function for multi array sorting
            function array_sort_func($a,$b=NULL) {
                static $keys;
                if($b===NULL) return $keys=$a;
                foreach($keys as $k) {
                    if(@$k[0]=='!') {
                        $k=substr($k,1);
                        if(@$a[$k]!==@$b[$k]) {
                            return strcmp(@$b[$k],@$a[$k]);
                        }
                    }
                    else if(@$a[$k]!==@$b[$k]) {
                            return strcmp(@$a[$k],@$b[$k]);
                        }
                }
                return 0;
            }
            // Function for multi array sorting
            function array_sort(&$array) {
                if(!$array) return $keys;
                $keys=func_get_args();
                array_shift($keys);
                array_sort_func($keys);
                usort($array,"array_sort_func");
            }
            //Sort here array by 2 collumn if first collumn equal then sort by other collumn
            array_sort($new_sort_fin_array,'order','sub_order');

            // create here Final ordered array by parents and by sort order
            $cats_fin = array();
            foreach ($new_sort_fin_array as $key => $item) {
                $cats_fin[$item['id']] = $item['data'];
            }

            $ik = array(); $wa = array();
            //generate temp array with index and keys and final work array whch will be return
            foreach ($cats_fin as $key => $value) {
                array_push($ik,$key) ; // here we cerate order in which we willwork with ID
                $wa[$key] = array(   'childs' => array() , 'object' => $cats_work[$key] ) ;
            }

            // Go throug sorted array from longest node to root And
            // set active node as child to his parent if node has parent
            for($i=0; $i< count( $ik )-1; $i++) {
                for($j = $i+1; $j<count( $ik ); $j++ ) {
                    if ( $wa[ $ik[$i]]['object']->parent == $ik[$j] ) {
                        $wa[ $ik[$j] ]['childs'][ $ik[$i] ] = $wa[ $ik[$i] ];
                        unset( $wa[ $ik[$i] ]);
                        break;
                    }
                }
            }
            //debug1($wa,0);/**/
            return $wa;
        }

        // Insert images inside of standard categories
        function wp_list_categories_icons($output) {

            $my_childs = array();

            $categorys = get_categories('hide_empty=0&child_of=0');
            $ii=0; $ic=count($categorys); $my_class ='';

            if ($ic > 0) {

                for ($ii = 0 ; $ii < $ic ; $ii++) { $category = $categorys[$ii]; $my_class = '';

                    $perma_load = get_category_link($category->term_id);
                    $perma_real = 'http://' .  $_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'] ;

                    if ($ii == $ic-1) $my_class = ' class="last_submenu" ';
                    if (  str_replace('/','',$perma_load) == str_replace('/','',$perma_real)  ) { $my_class = ' class="selected" '; $slct = $i; }

                    $my_childs[] =array(
                        'class' => $my_class,
                        'link'  => get_category_link($category->term_id),
                        'icon'  => $category->term_icon, // 'cat-'.$category->slug.'.jpg',    // Need to think and set goog name
                        'title' => $category->name
                    );
                }
            }

            foreach ($my_childs as $pg) {

                if (! empty ($pg['icon']) ) {
                    $pos = strpos($output, '"' . $pg['link'] . '"'); // Get position of link inside output
                    if ( $pos !== false) {

                        $pos = strpos($output, '>' ,$pos); // Get position of end of A attribute

                        if ( $pos !== false) {
                            $pos++;
                            $output = substr($output, 0, $pos) . '<img src="'. $pg['icon'] .'" class="category_icon" alt="'. $pg['title'] .'">' . substr($output, $pos);  //Insert icon
                        }
                    }
                }
            }



            return $output;
        }




        // Get array of images - icons inside of this directory
        function dirList ($directory) {

        // create an array to hold directory list
            $results = array();

            // create a handler for the directory
            $handler = opendir($directory);



            // keep going until all files in directory have been read
            while ($file = readdir($handler)) {

            // if $file isn't this directory or its parent,
            // add it to the results array
                if ($file != '.' && $file != '..' && ( strpos($file, '-'. get_option( 'wpdev_mc_icon_size_w' ) . 'x' .  get_option( 'wpdev_mc_icon_size_h' ) ) !== false ) )
                    $results[] = $file;
            }

            // tidy up: close the handler
            closedir($handler);

            // done!
            return $results;

        }



        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // S  E  T  T  I  N  G  S

        // Content of settings page
        function content_of_settings_page() {

            if ( isset( $_POST['Submit'] ) ) {


                $wpdev_mc_icon_dir  = $_POST['wpdev_mc_icon_dir']; /*
                $client_cal_count = $_POST['client_cal_count'];
                $start_day_weeek  = $_POST['start_day_weeek'];
                $cal_position     = $_POST['cal_position']; /**/
                $wpdev_mc_del_on_deactive =  $_POST['wpdev_mc_del_on_deactive']; // check
                $wpdev_copyright  = $_POST['wpdev_mc_copyright'];             // check

                $wpdev_mc_icon_size_w =  $_POST['wpdev_mc_icon_size_w'];
                $wpdev_mc_icon_size_h  = $_POST['wpdev_mc_icon_size_h'];
                $wpdev_mc_icon_crop   =  $_POST['wpdev_mc_icon_crop']; // check


                ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                if ( get_option( 'wpdev_mc_icon_dir' ) !== false  )   update_option( 'wpdev_mc_icon_dir' , $wpdev_mc_icon_dir );
                else                                                  add_option('wpdev_mc_icon_dir' , $wpdev_mc_icon_dir );

                ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                if ( get_option( 'wpdev_mc_icon_size_w' ) !== false  )   update_option( 'wpdev_mc_icon_size_w' , $wpdev_mc_icon_size_w );
                else                                                     add_option('wpdev_mc_icon_size_w' , $wpdev_mc_icon_size_w );

                ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                if ( get_option( 'wpdev_mc_icon_size_h' ) !== false  )   update_option( 'wpdev_mc_icon_size_h' , $wpdev_mc_icon_size_h );
                else                                                     add_option('wpdev_mc_icon_size_h' , $wpdev_mc_icon_size_h );

                ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                if (isset( $wpdev_mc_icon_crop ))            $wpdev_mc_icon_crop = 'On';
                else                                         $wpdev_mc_icon_crop = 'Off';
                if ( get_option( 'wpdev_mc_icon_crop' )  !== false )          update_option('wpdev_mc_icon_crop' , $wpdev_mc_icon_crop );
                else                                                          add_option('wpdev_mc_icon_crop' , $wpdev_mc_icon_crop );

                ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                if (isset( $wpdev_mc_del_on_deactive ))            $wpdev_mc_del_on_deactive = 'On';
                else                                               $wpdev_mc_del_on_deactive = 'Off';
                if ( get_option( 'wpdev_mc_is_delete_on_deactive' )  !== false )          update_option('wpdev_mc_is_delete_on_deactive' , $wpdev_mc_del_on_deactive );
                else                                                                      add_option('wpdev_mc_is_delete_on_deactive' , $wpdev_mc_del_on_deactive );

                ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                if (isset( $wpdev_copyright ))                  $wpdev_copyright = 'On';
                else                                            $wpdev_copyright = 'Off';
                if ( get_option( 'wpdev_mc_copyright' )  !== false )  update_option( 'wpdev_mc_copyright' , $wpdev_copyright );
                else                                                  add_option('wpdev_mc_copyright' , $wpdev_copyright );

            } else {
                $wpdev_mc_icon_dir  = get_option( 'wpdev_mc_icon_dir' );
                $wpdev_mc_del_on_deactive =  get_option( 'wpdev_mc_is_delete_on_deactive' ); // check
                $wpdev_copyright  = get_option( 'wpdev_mc_copyright' );             // check

                $wpdev_mc_icon_size_w  = get_option( 'wpdev_mc_icon_size_w' );
                $wpdev_mc_icon_size_h =  get_option( 'wpdev_mc_icon_size_h' );
                $wpdev_mc_icon_crop    = get_option( 'wpdev_mc_icon_crop' );             // check

            }


            ?>
<div class="clear" style="height:20px;"></div>
<div id="ajax_working"></div>
<div id="poststuff" class="metabox-holder">

    <div  style="width:64%; float:left;margin-right:1%;">

        <div class='meta-box'>
            <div  class="postbox" > <h3 class='hndle'><span><?php _e('Settings'); ?></span></h3>
                <div class="inside">
                    <form  name="post_option" action="" method="post" id="post_option" >
                        <table class="form-table"><tbody>

                                <tr valign="top">
                                    <th scope="row"><label for="wpdev_mc_icon_dir" ><?php _e('Store uploads of icons in this folder'); ?>:</label></th>
                                    <td><input id="wpdev_mc_icon_dir" class="regular-text code" type="text" size="45" value="<?php echo $wpdev_mc_icon_dir; ?>" name="wpdev_mc_icon_dir"/>
                                        <span class="description"><?php printf(__('Default is %s'),'<span><b>wp-content/uploads/icons</b></span>');?></span>
                                    </td>
                                </tr>

<tr valign="top">
<th scope="row"><label for="wpdev_mc_sizes" ><?php _e('Thumbnail icons size') ?>:</label></th>
<td>
        <label for="wpdev_mc_icon_size_w"><?php _e('Width'); ?></label>
<input name="wpdev_mc_icon_size_w" type="text" id="wpdev_mc_icon_size_w" value="<?php echo $wpdev_mc_icon_size_w; ?>" class="small-text" />
        <label for="wpdev_mc_icon_size_h"><?php _e('Height'); ?></label>
<input name="wpdev_mc_icon_size_h" type="text" id="wpdev_mc_icon_size_h" value="<?php echo $wpdev_mc_icon_size_h; ?>" class="small-text"  /><br />
<input name="wpdev_mc_icon_crop" type="checkbox" id="wpdev_mc_icon_crop"  <?php if ($wpdev_mc_icon_crop == 'On') echo "checked"; ?>  value="<?php echo $wpdev_mc_icon_crop; ?>"  />
        <label for="wpdev_mc_icon_crop"><?php _e('Crop thumbnail to exact dimensions (normally thumbnails are proportional)'); ?></label>
</td>
</tr>

                                <tr valign="top">
                                    <td colspan="2"><div style="border-bottom:1px solid #cccccc;"></div></td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row"><label for="wpdev_mc_del_on_deactive" ><?php _e('Delete icons data'); ?>:</label><br><?php _e('when plugin deactivated'); ?></th>
                                    <td><input id="wpdev_mc_del_on_deactive" type="checkbox" <?php if ($wpdev_mc_del_on_deactive == 'On') echo "checked"; ?>  value="<?php echo $wpdev_mc_del_on_deactive; ?>" name="wpdev_mc_del_on_deactive"/>
                                        <span class="description"><?php _e(' Check, if you want to delete saved properties for icons during uninstalling plugin (icons files will not delete) ');?></span>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row"><label for="wpdev_copyright" ><?php _e('Copyright notice'); ?>:</label></th>
                                    <td><input id="wpdev_mc_copyright" type="checkbox" <?php if ($wpdev_copyright == 'On') echo "checked"; ?>  value="<?php echo $wpdev_copyright; ?>" name="wpdev_mc_copyright"/>
                                        <span class="description"><?php _e(' Turn On/Off copyright wpdevelop.com notice at footer of site view.');?></span>
                                    </td>
                                </tr>

                            </tbody></table>

                        <input class="button-primary" style="float:right;" type="submit" value="<?php _e('Save Changes'); ?>" name="Submit"/>
                        <div class="clear" style="height:10px;"></div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <div style="width:35%; float:left;">

        <div class='meta-box'>
            <div  class="postbox gdrgrid" > <h3 class='hndle'><span><?php _e('Information'); ?></span></h3>
                <div class="inside">
                    <p class="sub"><?php _e("Info"); ?></p>
                    <div class="table">
                        <table><tbody>
                                <tr class="first">
                                    <td class="first b" style="width: 133px;"><?php _e("Version"); ?></td>
                                    <td class="t"><?php _e("release date"); ?>: <?php echo date ("d.m.Y", filemtime(__FILE__)); ?></td>
                                    <td class="b options" style="color: red; font-weight: bold;"><?php echo WPDEV_CP_VERSION; ?></td>
                                </tr>
                            </tbody></table>
                    </div>
                    <p class="sub"><?php _e("Links"); ?></p>
                    <div class="table">
                        <table><tbody>
                                <tr class="first">
                                    <td class="first b">Plugin page</td>
                                    <td class="t"><?php _e("official plugin page"); ?></td>
                                    <td class="t options"><a href="http://plugins.wpdevelop.com/" target="_blank"><?php _e("visit"); ?></a></td>
                                </tr>
                                <tr>
                                    <td class="first b">WordPress Extend</td>
                                    <td class="t"><?php _e("wordpress plugin page"); ?></td>
                                    <td class="t options"><a href="http://wordpress.org/extend/plugins/category-page-icons" target="_blank"><?php _e("visit"); ?></a></td>
                                </tr>
                            </tbody></table>
                    </div>
                    <p class="sub"><?php _e("Author"); ?></p>
                    <div class="table">
                        <table><tbody>
                                <tr class="first">
                                    <td class="first b"><span><?php _e("Premium Support"); ?></span></td>
                                    <td class="t"><?php _e("special plugin customizations"); ?></td>
                                    <td class="t options"><a href="mailto:info@wpdevelop.com" target="_blank"><?php _e("contact"); ?></a></td>
                                </tr>
                            </tbody></table>
                    </div>

                </div>
            </div>
        </div>

    </div>

</div>
        <?php
        }


    //   C L I E N T   S I D E   /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // Head of Client side - including JS Ajax function
        function client_side_print_compose_head() {
            $this->head_print_js_css();
        // wp_print_scripts( array( 'sack' )); // Define custom A J A X  respond at Client side
        }
        ////////////////////////////////////////////////////////////////////

        //Get string of menu for showing
        function get_menu() {

            $top_menu = $this->get_menu_array();

            $i=0;$ic=count($top_menu['menu']);

            $menu_line = ' <div id="localnav"><div class="header_section"><div class="divh1">';

            foreach ($top_menu['menu'] as $tm) {$i++;

                $link_url = $tm['link'] ;

                if(  $top_menu['selected']['selected_num'] == ($i-1)  ) {
                    $menu_line .= "<span class='tophlopka selectedhlopka'><a $link_url><span>";
                } elseif ( $i ===($ic) ) {
                    $menu_line .= "<span class='tophlopka lasthlopka'><a $link_url><span>";
                } else {
                    $menu_line .= "<span class='tophlopka'><a $link_url><span>";
                }

                $menu_line .= $tm['title'];
                $menu_line .= "</span></a><div class='clear_4_menu'></div><div  class='menulabels'>".$tm['hint']."</div></span>";
            }

            return $menu_line .'</div></div></div>' ;
        }

        // Get string of submenu to show
        function get_submenu() {

            $top_menu = $this->get_menu_array();
            $my_submenu = '';

            if (! empty($top_menu['selected'])) {                            // cehck if show submenu at entered link
                if ( count($top_menu['selected']['childs']) > 0 ) {      // check if some childs of submenu here

                    $blgsd = get_bloginfo('stylesheet_directory');   //TODO: SET DIRECTORY TO ICONS

                    $my_submenu .= '
                                        <div id="column_subnav_small">
                                            <div id="subnav_small">
                                                <h2><a '. $top_menu['selected']['link'] .'>'.$top_menu['selected']['hint'].'</a></h2>
                                                <ul>';

                    $ic=count($top_menu['selected']['childs']);
                    for ($i = 0 ; $i < $ic ; $i++) { $page = $top_menu['selected']['childs'][$i]; $my_class = '';

                        $my_submenu .=  '<li '.$page['class'].'>';
                        $my_submenu .=  '   <a href="'. $page['link'] .'">';
                        // $page['icon'] = 'ichat-128x128-28x28.png';
                        if (! empty($page['icon']) )
                            $my_submenu .=  '<img  width="28" height="28"  alt="'.$page['title'].'" src="'. /*$this->icons_url .'/'. */$page['icon'].'"/>';  //TODO: SET DIRECTORY TO ICONS

                        $my_submenu .=  $page['title'];
                        $my_submenu .=  '   </a>';
                        $my_submenu .=  '</li>';
                    }

                    $my_submenu .= '
                                                </ul>
                                            </div><!--/subnav-->
                                        </div>';
                }
            }
            return $my_submenu;
        }

        // Get memo box for sidebar
        function get_memo($my_title, $my_text) {

            $my_ret_text = '<div id="column_subnav_small">
                    <div id="subnav_small">
                        <h2><a href="#">'.$my_title.'</a></h2>
                                <ul>';
            $i=0;$ic=count($my_text);
            foreach ($my_text as $txt) { $i++;
                if ($i == $ic) $my_class = 'class="last_submenu"';
                else           $my_class = '';

                $my_ret_text .= '<li style="padding:10px;" '.$my_class.' >';
                $my_ret_text .= $txt ;
                $my_ret_text .= '</li>';
            }

            $my_ret_text .= '       </ul>
                    </div>
                </div>';

            return $my_ret_text ;

        }
        ////////////////////////////////////////////////////////////////////

        // Trigered function to  the do action wpdev_menu
        function wpdev_show_menu() {
            echo $this->get_menu();
        }

        // Trigered function to  the do action wpdev_menu
        function wpdev_show_submenu() {
            echo $this->get_submenu();
        }

        // Trigered function to showing memo at sidebar
        function wpdev_show_memo($my_title, $my_text) {
            echo $this->get_memo($my_title, $my_text);
        }
        ////////////////////////////////////////////////////////////////////

        // Write copyright notice if its saved
        function wp_footer() {
            if ( ( get_option( 'wpdev_mc_copyright' )  == 'On' ) && (! defined('WPDEV_COPYRIGHT')) ) {
                printf(__('Uses wordpress plugins developed by %swww.wpdevelop.com%s'),'<a href="http://www.wpdevelop.com" target="_blank">','</a>','&amp;');
                define('WPDEV_COPYRIGHT',  1 );
            }
        }

    //   A C T I V A T I O N   A N D   D E A C T I V A T I O N    O F   T H I S   P L U G I N  ///////////////////////////////////////////////////////

        // Activate
        function wpdev_compose_activate() {

            $this->check_table_field();
         /**/  //Comment this for public versions
            add_option('wpdev_mc_menu_content', 'Home|WordPress|WP Plugins|Startups|Portfolio|Hire Me');
            add_option('wpdev_mc_menu_hints'  , 'Start here|WP tips and tricks|My WordPress plugins|My web startups|My web works|Contact now');
            add_option('wpdev_mc_menu_links'  , '|wordpress|wp-plugins|startups|portfolio|mailto:info@wpdevelop.com');
            add_option('wpdev_mc_submenu_id'  , 'page=3|cat=4|cat=5|cat=6|cat=7|');/**/
            add_option('wpdev_mc_icon_size_w' , '28');
            add_option('wpdev_mc_icon_size_h' , '28');
            add_option('wpdev_mc_icon_crop'   , 'On');
            add_option('wpdev_mc_icon_dir'    ,  esc_attr(str_replace(ABSPATH, '', get_option('upload_path')))  . '/icons'); /**/
            add_option( 'wpdev_mc_is_delete_on_deactive' ,'Off'); // check
            add_option( 'wpdev_mc_copyright','On' );              // check

        }

        // Deactivate
        function wpdev_compose_deactivate() {

            $is_delete_if_deactive =  get_option( 'wpdev_mc_is_delete_on_deactive' ); // check


            $is_delete = true;
            if ($is_delete && ( $is_delete_if_deactive == 'On' ) ) {
                delete_option('wpdev_mc_menu_content');
                delete_option('wpdev_mc_menu_hints'  );
                delete_option('wpdev_mc_menu_links'  );
                delete_option('wpdev_mc_submenu_id'  );
                delete_option('wpdev_mc_icon_size_w' );
                delete_option('wpdev_mc_icon_size_h' );
                delete_option('wpdev_mc_icon_crop'   );
                delete_option('wpdev_mc_icon_dir'    );
                delete_option('wpdev_mc_is_delete_on_deactive'    );
                delete_option('wpdev_mc_copyright'    );
            }
        }

        // Check if needs to Add one more column to the table terms for order
        function check_table_field() {

            global $wpdb;
            $wpdb->show_errors();

            $query1 = $wpdb->query("SHOW COLUMNS FROM $wpdb->terms LIKE 'term_order'");
            if ($query1 == 0) {
                $wpdb->query("ALTER TABLE $wpdb->terms ADD `term_order` INT( 4 ) NULL DEFAULT '0'");
            }

            $query1 = $wpdb->query("SHOW COLUMNS FROM $wpdb->terms LIKE 'term_icon'");
            if ($query1 == 0) {
                $wpdb->query("ALTER TABLE $wpdb->terms ADD `term_icon` varchar(255) NULL DEFAULT ''");
            }

            $query1 = $wpdb->query("SHOW COLUMNS FROM $wpdb->posts LIKE 'post_icon'");
            if ($query1 == 0) {
                $wpdb->query("ALTER TABLE $wpdb->posts ADD `post_icon` varchar(255) NULL DEFAULT ''");
            }
        }



            }
        }

        $wpdev_bk = new wpdev_compose();


?>
