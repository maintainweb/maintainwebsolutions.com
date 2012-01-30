<?php
/*
Plugin Name: Gravity Forms Meta Recovery Utility
Plugin URI: http://www.gravityforms.com
Description: Utility to restore corrupted Gravity Forms serialized meta.
Version: 0.1
Author: rocketgenius
Author URI: http://www.rocketgenius.com

------------------------------------------------------------------------
Copyright 2009 rocketgenius

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

add_action('init',  array('GFMetaRecovery', 'init'));

class GFMetaRecovery {

    private static $path = "gravityformsmetarecovery/metarecovery.php";
    private static $url = "http://www.gravityforms.com";
    private static $slug = "gravityformsmetarecovery";
    private static $version = "0.1";
    private static $min_gravityforms_version = "1.0";

    //Plugin starting point. Will load appropriate files
    public static function init(){

        if(!self::is_gravityforms_supported()){
           return;
        }

        if(is_admin()){
            //loading translations
            load_plugin_textdomain('gravityformsmetarecovery', FALSE, '/gravityformsmetarecovery/languages' );

            //creates the subnav left menu
            add_filter("gform_addon_navigation", array('GFMetaRecovery', 'create_menu'));
        }
    }


    public static function create_menu($menus){

        // Adding submenu if user has access
       $menus[] = array("name" => "gf_metarecovery", "label" => __("Meta Recovery", "gravityformsmetarecovery"), "callback" =>  array("GFMetaRecovery", "metarecovery_page"), "permission" => "level_7");
       return $menus;
    }



    public static function metarecovery_page(){

        if(RGForms::post("gf_metarecovery_submit")){
            global $wpdb;
            $table_name =  RGFormsModel::get_meta_table_name();

            $form_id = RGForms::post("gf_metarecovery_form_id");
            $meta = $wpdb->get_var($wpdb->prepare("SELECT display_meta FROM $table_name WHERE form_id=%d", $form_id));

            //fixing meta
            $meta = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $meta);

            //if successfull, store new meta
            $obj = unserialize($meta);
            if($obj){
                RGFormsModel::update_form_meta($form_id, $obj);
                $is_success = true;
            }
            else{
                $is_failure = true;
            }
        }

        ?>
        <style>
            .left_header{float:left; width:200px;}
            .margin_vertical_10{margin: 10px 0;}
        </style>
        <div class="wrap">
            <form method="post">

                <h2><?php _e("Meta Recovery Utility", "gravityformsmetarecovery") ?></h2>

                <?php
                if($is_success){
                    ?>
                    <div class="updated fade" style="padding:6px"><?php echo sprintf(__("Meta recovered successfully. %sGo to form editor%s", "gravityformsmetarecovery"), "<a href='?page=gf_edit_forms&id={$form_id}'>", "</a>") ?></div>
                    <?php
                }
                if($is_failure){
                    ?>
                    <div class="error" style="padding:6px"><?php echo __("This form's meta could not be recovered.", "gravityformsmetarecovery"); ?></div>
                    <?php
                }
                ?>
                <div class="margin_vertical_10">
                    <label for="gf_metarecovery_form_id" class="left_header"><?php _e("Gravity Form", "gravityforms_feed"); ?> </label>
                    <select name="gf_metarecovery_form_id" id="gf_metarecovery_form_id">
                        <option value=""><?php _e("Select a form", "gravityforms_feed"); ?> </option>
                        <?php
                        $forms = RGFormsModel::get_forms();
                        foreach($forms as $form){
                            ?>
                            <option value="<?php echo absint($form->id) ?>"><?php echo esc_html($form->title) ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="margin_vertical_10">
                    <input type="submit" class="button-primary" name="gf_metarecovery_submit" value="<?php _e("Submit", "gravityformsmetarecovery"); ?>" />
                </div>

            </form>
        </div>
        <?php

    }



    private static function is_gravityforms_installed(){
        return class_exists("RGForms");
    }

    private static function is_gravityforms_supported(){
        if(class_exists("GFCommon")){
            $is_correct_version = version_compare(GFCommon::$version, self::$min_gravityforms_version, ">=");
            return $is_correct_version;
        }
        else{
            return false;
        }
    }

    //Returns the url of the plugin's root folder
    protected function get_base_url(){
        return plugins_url(null, __FILE__);
    }

    //Returns the physical path of the plugin's root folder
    protected function get_base_path(){
        $folder = basename(dirname(__FILE__));
        return WP_PLUGIN_DIR . "/" . $folder;
    }


}
?>
