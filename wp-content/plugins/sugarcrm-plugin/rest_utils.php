<?php
/* 
 * This file is for variables used in soap file.
 * like host of crm.
 */


/**
 * this function would accept data returned by sugarCRM
 * and would return name-value list to simple array
 */

function nameValuePairToSimpleArray($array){
    $my_array=array();

	foreach($array as $key=>$res_array){
		
        $my_array[$res_array->name]=$res_array->value;
		  
    }

    return $my_array;
}

/**
 * this function would accept data array of options
 * and would return selected option using in select box
 */

function GetSelectedOptions($option_array,$location=''){
	
	foreach($option_array as $key=>$options){
	$selected =($key==$location)?"selected":"";
	$sel_option =$options." ".$selected;
	$options_html .="<option value=".$sel_option.">".$options.'</option>';
	}//foreach
	
	return $options_html;
}//fn

/**
 * this function would accept data array of posted data
 * and would return name-value list array as set-entry method saving in this format
 */

function SimpleArrayTonameValuePair($array){

    $my_array=array();
	$cnt =0;
	foreach($array as $key=>$res_array)
	{
	$new_arr[$cnt]['name'] =$key;
	$new_arr[$cnt]['value'] =$res_array;
	$cnt++;
	}
    return $new_arr;
}
?>