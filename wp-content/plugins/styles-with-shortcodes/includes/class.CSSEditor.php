<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class CSSEditor {
	var $post;
	function CSSEditor(){
		add_action('media_buttons_context',array(&$this,'media_buttons_context'));
	}
	
	function media_buttons_context($context){
        $out = '<a href="#TB_inline?width=1000&inlineId=insert_csshortcode" class="thickbox" title="'. __("Add Styles with Shortcodes", 'wpcss').'"><img src="'.WPCSS_URL."css/images/icon_shortcodes.png".'" alt="'. __("Add Styles with Shortcodes", 'wpcss') . '" /></a>';
		add_action('admin_footer',array(&$this,'add_mce_popup'));
        return $context . $out;
	}

    function add_mce_popup(){
?>
<style>
#css-mce-fields-cont {
	display:none;
}
</style>
<script>
jQuery(document).ready(function($){
	$('#cs_category').change(function(){
		DropdownChildUpdate(this.id,'cs_shortcode');
	});
	
	$('#cs_shortcode').change(function(){
		var _url = '<?php echo WPCSS_URL?>api/admin.mce_list_fields2.php';
		var _ID = this.value;
		if(_ID>0){
			$('#css-fields').load(_url,{'ID':_ID},function(){
				$('#css-mce-fields-cont').show();
			});
		}else{
			$('#css-mce-fields-cont').hide();
		}
	});
});

function _insert_csshortcode(){
	jQuery(document).ready(function($){
    	var win = window.dialogArguments || opener || parent || top;
    	var shortcode = $('#sc_shortcode').val();
		
		var arr = [];
		var _content = '';
		
		if($('.css-mce-property').length>0){
			$('.css-mce-property').each(function(){
				if('content'==$(this).attr('name')){
					_content = $(this).val();
				}else{
					if($(this).val()!=''){
						arr[arr.length] = $(this).attr('name') + '="' + $(this).val() + '"';
					}
				}
			});
		}
		_content = _content==''?' ':_content;
		var str = "[" + shortcode + ' ' + arr.join(' ') + ']' + _content + '[/' + shortcode + ']';
		win.send_to_editor(str);
	});
}

function insert_csshortcode(){
	jQuery(document).ready(function($){
    	var win = window.dialogArguments || opener || parent || top;
    	var str = '';
		if($('.mce-item').length>0){
			$('.mce-item').each(function(){
				var _val = $(this).val();
				if( $(this).hasClass('mce-escape') ){
					_val = escape(_val);
				}
				
				if( $(this).hasClass('parse-with-rel') ){
					try {
						if(''!=$(this).attr('rel')){
							eval($(this).attr('rel'));
						}
					}catch(e){
						console.log(e.description);
					}
				}
				
				if( $(this).hasClass('mce-scopentag') ){
					str += '[' + _val;
				}
				
				if( $(this).hasClass('mce-property') ){
					str += ' ' + $(this).attr('name') + '=' + '"' + _val +'"';
				}
				
				if( $(this).hasClass('mce-scclose') ){
					str += ']';
				}
			
				if( $(this).hasClass('mce-content') ){
					str += ']' + _val;
				}				
				
				if( $(this).hasClass('mce-scclosetag') ){
					str += ' [/' + _val + '] ';
				}
			});
		}
		
		send_to_editor(str);
	});
}

function csv_to_datatable(csv){
	var arr  = CSVToArray( csv );
	var str  = '<table>';
	var cols = 0;
	if(arr.length>0){
		cols = arr[0].length;
		if(cols>0){
			str+="<thead><tr>";
			for(j=0;j<cols;j++){
				str+="<th>"+arr[0][j]+"</th>";
			}
			str+="</tr></thead><tbody>";		
			if(arr.length>1){
				for(i=1;i<arr.length;i++){
					str+= i%2==0? '<tr>':'<tr class="odd">';
					if(arr[i].length==cols){
						for(j=0;j<arr[i].length;j++){
							str+="<td>"+arr[i][j]+"</td>";
						}					
					}
					str+='</tr>';
				}
			}
			str+='</tbody>';
		}
	}
	str+='</table>';
	return str;
}

function CSVToArray( strData, strDelimiter ){
    strDelimiter = (strDelimiter || ",");
    var objPattern = new RegExp( ("(\\" + strDelimiter + "|\\r?\\n|\\r|^)" + "(?:\"([^\"]*(?:\"\"[^\"]*)*)\"|" + "([^\"\\" + strDelimiter + "\\r\\n]*))"),"gi");
    var arrData = [[]];
    var arrMatches = null;
    while (arrMatches = objPattern.exec( strData )){

            var strMatchedDelimiter = arrMatches[ 1 ];
            if (
                    strMatchedDelimiter.length &&
                    (strMatchedDelimiter != strDelimiter)
                    ){
                    arrData.push( [] );

            }
            if (arrMatches[ 2 ]){
                    var strMatchedValue = arrMatches[ 2 ].replace(new RegExp( "\"\"", "g" ),"\"");
            } else {
                    var strMatchedValue = arrMatches[ 3 ];
            }
            arrData[ arrData.length - 1 ].push( strMatchedValue );
    }
    return( arrData );
}

jQuery(document).ready(function() {
(function($) {
	window.tb_position = null;
	tb_position = function() {
		if( $('#TB_window #css-mce-form').length==0 ){
			var tbWindow = $('#TB_window'), width = $(window).width(), H = $(window).height(), W = ( 720 < width ) ? 720 : width;	
		}else{
			var tbWindow = $('#TB_window'), width = $(window).width(), H = $(window).height(), W = ( 1000 < width ) ? 1000 : width;
		}
		if ( tbWindow.size() ) {
			tbWindow.width( W - 50 ).height( H - 45 );
			$('#TB_iframeContent').width( W - 50 ).height( H - 75 );
			tbWindow.css({'margin-left': '-' + parseInt((( W - 50 ) / 2),10) + 'px'});
			if ( typeof document.body.style.maxWidth != 'undefined' )
				tbWindow.css({'top':'20px','margin-top':'0'});
		};

		return $('a.thickbox').each( function() {
			var href = $(this).attr('href');
			if ( ! href ) return;
			href = href.replace(/&width=[0-9]+/g, '');
			href = href.replace(/&height=[0-9]+/g, '');
			$(this).attr( 'href', href + '&width=' + ( W - 80 ) + '&height=' + ( H - 85 ) );
		});
	};

	$(window).resize(function(){ tb_position(); });

})(jQuery);
});
</script>

        <div id="insert_csshortcode" style="display:none;">
            <div id="css-mce-form" class="wrap">
			   <div class="fieldset">
				<label class="css-mce-label">Shortcode Category</label>
				<div class="css-mce-input">
					<?php $this->category_dropdown()?>
				</div>
				<div class="clearer"></div>
			   </div>
			   
			   <div class="fieldset">
			   	<label class="css-mce-label">Shortcode</label>
				<div class="css-mce-input">
					<?php $this->shortcode_dropdown()?>
				</div>
			   	<div class="clearer"></div>
			   </div>
			   
			   <div id="css-mce-fields-cont">
				   <div id="css-fields"></div>
				   
			   </div>
            </div>

        </div>

        <?php
    }
	
	function category_dropdown($id='cs_category'){
		$categories = get_terms('csscategory');
		echo "<select id=\"$id\">";
		echo "<option value=\"\">--any--</option>";
		if(is_array($categories)&&count($categories)>0){
			foreach($categories as $c){
				echo "<option value=\"cat".$c->term_id."\">".$c->name."</option>";
			}
		}
		echo "</select>";			
	}
	
	function shortcode_dropdown($id='cs_shortcode',$echo=true){
		global $wpdb;
		
		$str = "<select id=\"$id\">";
		$str.="<option value=\"0\">--choose a shortcode--</option>";
		
		$sql = "SELECT ID as value, post_title as label FROM `{$wpdb->posts}` WHERE post_type='csshortcode' AND post_status='publish'";
		if($wpdb->query($sql)&&$wpdb->num_rows>0){
			foreach($wpdb->last_result as $row){
				$post_terms = wp_get_post_terms( $row->value, 'csscategory' );
				if(is_array($post_terms)&&count($post_terms)>0){
					$c=array();
					foreach($post_terms as $term){
						$c[]='cat'.$term->term_id;
					}
					$class = "class=\"".implode(' ',$c)."\"";
				}else{
					$class = '';
				}
				$str.= sprintf("<option %s value=\"%s\">%s</option>",$class,$row->value,htmlentities($row->label));		
			}
		}else{
			$str.="<option value=\"\">".__("--no options--",'wpcss')."</option>";
		}
		$str.= "</select>";
		if($echo)
			echo $str;
		
		return $str;	
	}
}

new CSSEditor();
?>