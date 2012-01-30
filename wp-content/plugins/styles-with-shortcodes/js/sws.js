function DropdownChildUpdate(parent_sel,child_sel){
	jQuery(document).ready(function($){
		child_clone_sel = child_sel + '_clone';
		if($('#'+child_clone_sel).length==0){
			$('#'+child_sel).clone().attr('id',child_clone_sel).hide().appendTo('body');
		}	
	
		var parent_id = $('#'+parent_sel).val();
		if(parent_id==''){
			_sel = '#'+child_clone_sel+' option';
		}else{
			_sel = '#'+child_clone_sel+' option.'+parent_id;
		}
		
		var child_val = $('#'+child_sel).val();
		$('#'+child_sel).empty();
		$('#'+child_sel).append( $('#'+child_clone_sel+' option:first').clone() );
		$(_sel).each(function(i,inp){
			$('#'+child_sel).append( $(this).clone() );	
		});
		$('#'+child_sel).val(child_val);			
	});
}

function load_ui_theme(o,apiurl){
	jQuery(document).ready(function($){
		$('link[rel=stylesheet][title=ui-theme]').each(function(i,s){
			$(s).remove();
		});
		
		$.post(apiurl,{'style': $(o).val() },function(data){
			$(data).attr('title','ui-theme').appendTo('body');
		});
	});
}