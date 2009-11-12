<?php
/*
 * Description:
 * Administration User Interface Class
 * Using this class is possible to create
 * own meta boxes at plugins of WordPress
 * starting from 2.7.1 version
 *
 * this is class is redesigning class of HowTo - Metabox Showcase Plugin  - Ver 1.0
 * from Heiko Rabe
 *
 * Author: Sereda Dima
 * Author URI: http://www.wpdevelop.com/
 * Version: 0.1
 */

//avoid direct calls to this file where wp core files not present
if (!function_exists ('add_action')) {
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		exit();
}

// debuger output function // Exit only if last parameter TRUE
if (!function_exists ('debuge')) {
    function debuge() {
        $numargs = func_num_args();   $var = func_get_args();
        $makeexit = is_bool($var[count($var)-1])?$var[count($var)-1]:false;
    	echo "<div style='text-align:left;background:#ffffff;border: 1px dashed #ff9933;font-size:11px;line-height:15px;font-family:'Lucida Grande',Verdana,Arial,'Bitstream Vera Sans',sans-serif;'><pre>"; print_r ( $var ); echo "</pre></div>";
        if ($makeexit) { echo '<div style="font-size:18px;float:right;">' . get_num_queries(). '/'  . timer_stop(0, 3) . 'qps</div>'; exit;}
    }
}

/*
 *  Sample of USAGE
 *
    require_once(WP_PLUGIN_DIR. '/wpdev-menu-titles/wpdev-plugin-gui.php' );
    $gui = new wpdev_plugins_gui('wpdev-titles-','Menu titles, links and labels', 'Frontend Menu &amp; &copy;','posts');
    $gui->set_post_respond('on_save_changes');
    $gui->add_box('Sidebox 1 Title', 'on_sidebox_1_content', 'side');

     function on_save_changes() {
        if ( !current_user_can('manage_options') )
            wp_die( __('Cheatin&#8217; uh?') );
        check_admin_referer('wpdev-titles--general');

        //process here your on $_POST validation and / or option saving

        wp_redirect($_POST['_wp_http_referer']);
    }


    function on_sidebox_1_content($data) { ?>
		<ul style="list-style-type:disc;margin-left:20px;">
			<?php foreach($data as $item) { echo "<li>$item</li>"; } ?>
		</ul>
		<?php
	}
 */
if (!class_exists('wpdev_gui_cust')) {
    //class that reperesent the complete plugin
    class wpdev_gui_cust {

        var $menu_type;
        var $menu_title;
        var $page_title;
        var $parent_page;
        var $pagehook;
        var $gui_html_id_prefix;
        var $html_id_atribute;
        var $page_boxes;
        var $page_content;
        var $file_path;
        var $icon_url;
        var $icon_url_big;
        //constructor of class, PHP4 compatible construction for backward compatibility
        //
        //@param string $gui_html_id:  ID of the html elemnts at the plugin page have to be unical
        //@param string $page_title:  Title of the page
        //@param string $menu_title:  menu title name
        //@param string $menu_type:  'dashboard', 'posts', 'media', 'links', 'pages', 'comments', 'theme', 'users', 'management', 'options', default option create main menu item
        function wpdev_gui_cust($gui_html_id, $file_path, $page_title, $menu_title, $menu_type = '', $icon_name='',$page_parent='') {
            $this->html_id_atribute = 1;
            $this->page_boxes = array();
            $this->page_content = array();
            $this->gui_html_id_prefix =$gui_html_id;
            $this->page_title = $page_title;
            $this->menu_title = $menu_title;
            $this->menu_type  = $menu_type;
            $this->file_path = $file_path;  // early was __FILE__
            if ($icon_name!=='')
                $this->icon_url = $icon_name ;

            if ($page_parent!=='')
                $this->parent_page = $page_parent ;
            //$this->page_content = 'DADA';
            //register callback for admin menu  setup
            add_action('admin_menu', array(&$this, 'on_admin_menu'));
        }


        // add list of interace boxes
        //
        //@param string $title_box:  Title of the box
        //@param string function_show_content:  name of the function which will print content of the box
        //@param string type_box:  normal, additional, side
        //@param string always_on_screen:  true, false
        function add_box( $title_box,$function_show_content,$type_box ='normal' , $always_on_screen = false ) {
            array_push( $this->page_boxes, array( (string) $this->gui_html_id_prefix . ($this->html_id_atribute++), $title_box, $function_show_content, $type_box, $always_on_screen ) );
        }

        // add page content function - 1 content area
        //
        //@param string function_show_content:  name of the function which will print content of the box
        function add_content( $function_show_content ) {
            $this->page_content =    $function_show_content;
        }

        function set_icon( $icon ) {
            if ( is_array($icon)  ) {
                $this->icon_url =    $icon[0]  ;
                $this->icon_url_big =    $icon[1]  ;
            } else
            $this->icon_url =    $icon  ;
        }
        // this function set respond function for the POST request from this page interface
        function set_post_respond($function_name){
            //register the callback been used if options of page been submitted and needs to be processed
            add_action('admin_post_save_'.$this->gui_html_id_prefix.'_general', $function_name /*array(&$this, 'on_save_changes')*/);
        }


        //extend the admin menu, here we create the new plugin page & set hook to load all boxes and scripts
        function on_admin_menu() {
            //add our own adminpage
            $icon_url = '';
            if ($this->icon_url !='') $icon_url = $this->icon_url;
            switch ( $this->menu_type ) {
                case 'dashboard' :  $this->pagehook = add_dashboard_page ( $this->page_title, $this->menu_title, 8, $this->file_path . $this->gui_html_id_prefix, array(&$this, 'on_show_page') ); $this->icon_name='index'; break;
                case 'posts' :      $this->pagehook = add_posts_page     ( $this->page_title, $this->menu_title, 8, $this->file_path . $this->gui_html_id_prefix, array(&$this, 'on_show_page') ); $this->icon_name='edit'; break;
                case 'media' :      $this->pagehook = add_media_page     ( $this->page_title, $this->menu_title, 8, $this->file_path . $this->gui_html_id_prefix, array(&$this, 'on_show_page') ); $this->icon_name='upload'; break;
                case 'links' :      $this->pagehook = add_links_page     ( $this->page_title, $this->menu_title, 8, $this->file_path . $this->gui_html_id_prefix, array(&$this, 'on_show_page') ); $this->icon_name='link-manager'; break;
                case 'pages' :      $this->pagehook = add_pages_page     ( $this->page_title, $this->menu_title, 8, $this->file_path . $this->gui_html_id_prefix, array(&$this, 'on_show_page') ); $this->icon_name='edit-pages'; break;
                case 'comments' :   $this->pagehook = add_comments_page  ( $this->page_title, $this->menu_title, 8, $this->file_path . $this->gui_html_id_prefix, array(&$this, 'on_show_page') ); $this->icon_name='edit-comments'; break;
                case 'theme' :      $this->pagehook = add_theme_page     ( $this->page_title, $this->menu_title, 8, $this->file_path . $this->gui_html_id_prefix, array(&$this, 'on_show_page') ); $this->icon_name='themes'; break;
                case 'users' :      $this->pagehook = add_users_page     ( $this->page_title, $this->menu_title, 8, $this->file_path . $this->gui_html_id_prefix, array(&$this, 'on_show_page') ); $this->icon_name='users'; break;
                case 'management' : $this->pagehook = add_management_page( $this->page_title, $this->menu_title, 8, $this->file_path . $this->gui_html_id_prefix, array(&$this, 'on_show_page') ); $this->icon_name='tools'; break;
                case 'options' :    $this->pagehook = add_options_page   ( $this->page_title, $this->menu_title, 8, $this->file_path . $this->gui_html_id_prefix, array(&$this, 'on_show_page') ); $this->icon_name='options-general'; break;
                case 'submenu' :    $this->pagehook = add_submenu_page($this->parent_page, $this->page_title, $this->menu_title, 8, $this->file_path . $this->gui_html_id_prefix, array(&$this, 'on_show_page')  ); $this->icon_name='options-general';break;
                case 'object':      $this->pagehook = add_object_page( $this->page_title, $this->menu_title, 8, $this->file_path . $this->gui_html_id_prefix, array(&$this, 'on_show_page'), $icon_url  ); $this->icon_name='options-general';break;
                default: $this->pagehook = add_menu_page( $this->page_title, $this->menu_title, 8, $this->file_path . $this->gui_html_id_prefix, array(&$this, 'on_show_page'), $icon_url  ); $this->icon_name='options-general';
            }

            //register  callback gets call prior your own page gets rendered
            add_action('load-'.$this->pagehook, array(&$this, 'on_load_page'));
        }


        //will be executed if wordpress core detects this page has to be rendered
        function on_load_page() { 
            //ensure, that the needed javascripts been loaded to allow drag/drop, expand/collapse and hide/show of boxes
            wp_enqueue_script('common');
            wp_enqueue_script('wp-lists');
            wp_enqueue_script('postbox');

            //add several metaboxes now, all metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
            foreach ($this->page_boxes as $mybox)
                if ( $mybox[4] == false ) // chek only those boxes which can hided
                    add_meta_box($mybox[0],$mybox[1],$mybox[2],$this->pagehook,$mybox[3],'core');
        }


        //executed to show the plugins complete admin page
        function on_show_page() {
        
          if (! $this->page_content) {
            //contet boxes added at start of page rendering can't be switched on/off,
            //may be needed to ensure that a special box is always available
            foreach ($this->page_boxes as $mybox)
                if ( $mybox[4] == true ) // chek only those boxes which always visible
                    add_meta_box($mybox[0],$mybox[1],$mybox[2],$this->pagehook,$mybox[3],'core');

            //TODO: define some data can be given to each metabox during rendering
            $data = array();
            ?>
            <div id="<?php echo $this->gui_html_id_prefix; ?>-general" class="wrap">
            <?php   if ($this->icon_url_big !='') {
                        echo '<div class="icon32" style="margin:10px 25px 10px 10px;"><img src="'.$this->icon_url_big.'"><br /></div>' ;
                    } else {
                    screen_icon($this->icon_name);
                    }
            ?>
            <h2><?php _e($this->page_title); ?></h2>
            <form action="admin-post.php" method="post">
                <?php wp_nonce_field($this->gui_html_id_prefix . '-general'); ?>
                <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
                <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
                <input type="hidden" name="action" value="save_<?php echo $this->gui_html_id_prefix; ?>_general" />

                <div id="poststuff" class="metabox-holder has-right-sidebar">
                    <div id="side-info-column" class="inner-sidebar">
                        <?php do_meta_boxes($this->pagehook, 'side', $data); ?>
                    </div>
                    <div id="post-body" class="has-sidebar">
                        <div id="post-body-content" class="has-sidebar-content">
                            <?php //TODO: create some do_action which have to show some content at top of the page  ?>
                            <?php do_meta_boxes($this->pagehook, 'normal', $data); ?>
                            <?php //TODO: create some do_action which have to connect to static show content function  ?>
                            <?php do_meta_boxes($this->pagehook, 'additional', $data); ?>
                            <?php //TODO: create some do_action which have to show some content at bottom of the page  ?>
                            <p style="float:right;">
                                <input type="submit" value="Save All Changes" class="button-primary" name="Submit"/>
                            </p>
                        </div>
                    </div>
                    <br class="clear"/>
                </div>
            </form>
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
          } else { ?>

            <div id="<?php echo $this->gui_html_id_prefix; ?>-general" class="wrap">
            <?php
                if ($this->icon_url_big !='') {
                    echo '<div class="icon32" style="margin:10px 25px 10px 10px;"><img src="'.$this->icon_url_big.'"><br /></div>' ;
                } else {
                    screen_icon($this->icon_name);
                }
            ?>
            <h2><?php _e($this->page_title); ?></h2>
                <?php call_user_func( $this->page_content ); ?>
            </div>

     <?php
          }
        }

    }
}
?>
