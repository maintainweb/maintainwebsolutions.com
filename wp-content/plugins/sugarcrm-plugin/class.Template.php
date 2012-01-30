<?php
//Added by Hirak Chattopadhyuay
class Template{

	const FORM_SUBMIT_BTN	= 'submit';
	
	protected $frm_form;
	protected $frm_formEmail;
	protected $frm_form_items;
	protected $doc;	
	protected $formParent;
	protected $form;	
	protected $style;
	protected $script;
	protected $template_str;
	protected $form_root_div_id		= 'container';	
	protected $form_parent_div_id	= 'content';
	protected $form_title_div_id	= 'form_title';	
	protected $form_header_div_id	= 'form_header';	
	protected $form_footer_div_id	= 'form_footer';	
	protected $form_module 			= 'form_module';
	
	
	const MSG_INVALID_DATE			= 'Invalid date';
	const MSG_INVALID_TIME			= 'Invalid time';
	const MSG_EMPTY					= 'Please enter';
	const MSG_UNCHECKED				= 'Please accept';
	const MSG_RADIO_UNCHECKED		= 'Please select';
	const MSG_UNSELECTED			= 'Please select';
	const ERR_OPT_INFINITE_LOOP		= 'Infinite loop creating "option value"';


	public function __construct() {
		
	}
	
	public function addField($field_name, $field_type, $field_required = NULL, $field_elements = NULL , $field_title = NULL) 
	{
		$field	= NULL;
		$field_details	= array(
			'id'			=> $field_name,
			'name'			=> $field_name,
			'title'			=> $field_title,
			'required'		=> $field_required,
			'type'			=> $field_type,
			'options'		=> $field_elements, 
		);
		
		switch ($field_type) {
			case 'int':
			case 'currency':
			case 'float':
			case 'phone':
			case 'url':
			case 'name':
			case 'decimal':
			case 'varchar':
				$field	= $this->createTextField($field_details);
				break;
			case 'text':
				$field	= $this->createTextareaField($field_details);
			  break;
			case 'enum':
				$field	 = $this->createDropDown($field_details);
				break;
			case 'multienum':
				$field	 = $this->createMutliDropDown($field_details);
				break;
			case 'bool':
				$field	= $this->createCheckbox($field_details);
				break;
			case 'radioenum':
				$field	= $this->generateRadioButtons($field_details);
				break;
			case 'password':
				$field	= $this->createPasswordField($field_details);
				break;
			case 'date':
			case 'datetimecombo':
				$field	= $this->createDateField($field_details);
				break;
			default:
				$GLOBALS['wpr_logger']->fatal('Field - '.$field_name.': No code to handle creation of field type '.$field_type);
				break;
		}				
		return $field;
	}
	
	protected function createTextField($field_details) {
	
		$field = '<input type="text" name="'.$field_details['name'].'" id="'.$field_details['id'].'" ';
		
		if($field_details['required']=='1')
		$field .= ' class = "required" ';
		 
		$field .= ">";
		
		return $field;
	}
	
	protected function createTextareaField($field_details) {
		$field = '<textarea name="'.$field_details['name'].'" id="'.$field_details['id'].'" ';
		$field .=' cols="30" ';
		$field .=' rows= "4" ';
		
		if($field_details['required']=='1')
		$field .= ' class = "required" ';
		
		$field .='>';
		$field .='</textarea>';
		
		return $field;
	}
	
	protected function createDropDown($field_details)
	{
		
		$field = '<select name="'.$field_details['name'].'" id="'.$field_details['id'].'" ';
		
		if($field_details['required']=='1')
		$field .= ' class = "required" ';
		
		$field .=' >';
		
		$options = $this->createSelectOptions($field_details['options']);
				
		foreach($options as $option)
		{
			$field .= $option;
		}
		
		$filed .='</select>';
		
		return $field;
	}

		protected function createMutliDropDown($field_details)
	{
		
		$field = '<select multiple="multiple" name="'.$field_details['name'].'[]" id="'.$field_details['id'].'" ';
		
		if($field_details['required']=='1')
		$field .= ' class = "required" ';
		
		$field .=' >';
		
		$options = $this->createSelectOptions($field_details['options']);
				
		foreach($options as $option)
		{
			$field .= $option;
		}
		
		$filed .='</select>';
		
		return $field;
	}
	

	protected function createCheckbox($field_details) {
	
	$options = explode('|',$field_details['options']);
	unset($field);
	$count	= 0;
		foreach ($options as $name) {
			$count++;
		$field .= '<input type="checkbox" name="'.$field_details['name'].'" id="'.$field_details['id'].'" ';
		$field .=' value="'.trim(strtolower(str_replace(' ','_',$name))).'"';
		if($field_details['required']=='1')
		$field .= ' class = "required" ';

		$field .= ">";
		$field .=' '.$name.' ';
		}
		return $field;
	}
	

	protected function generateRadioButtons($field_details) 
	{
		$options = explode('|',$field_details['options']);
		unset($field);
		$count	= 0;
		foreach ($options as $name) {
			$count++;
			$field .='<input type="radio" name="'.$field_details['name'].'" ';
			$field .=' id= "'.$field_details['name'].'_radio_btn_'.$count.'"';
			$field .=' value="'.trim(strtolower(str_replace(' ','_',$name))).'"';
			
			if($count==1 && $field_details['required']=='1')
			$field .= ' class = "required" ';
			
			$field .= ">";
			$field .=' '.$name.' ';
		}
		
		return $field;
	}
	
	protected function createPasswordField() {
		
		$field = '<input type="password" name="'.$field_details['name'].'" id="'.$field_details['id'].'" ';
		
		if($field_details['required']=='1')
		$field .= ' class = "required" ';
		
		$field .= ">";
		
		return $field;

	}
	
	
	protected function createDateField($field_details) {
	$field ='<script>
	jQuery(function() {
		jQuery( "#datepicker" ).datepicker({
			showOn: "button",
			buttonImage: "/wp-content/plugins/sugarcrm-plugin/images/calendar.gif",
			buttonImageOnly: true,
			dateFormat: "yy-mm-dd",
		});
	});
</script>';
		$field .= '<div id="demo"><input type="text" name="'.$field_details['name'].'" id="datepicker" ';
		$date_format	= $field_details['options'];
		//$field .=' onchange = "if (this.value == \'\') {this.value = \''.$date_format.'\'}")';
		//$field .=' onload = "if (this.value == \'\) {this.value = \''.$date_format.'\'}")';
		$field .= ' class = "';
		if($field_details['required']=='1')
		$field .= ' required "';

	$field .= ' dateISO1"';
		$field .= " ></div>";
		return $field;
	}
	
	protected function createSelectOptions($form_elements) 
	{
		$optionValues = array();
		$options = explode('|',$form_elements);
		foreach ($options as $name) 
		{
			$optionValues[] = '<option value = "'.$name.'">'.$name.'</option>';
		}
		return $optionValues;
	}
	
	public function getOptions($field_elements) 
	{
		$options	= array();
		$option_names = explode('|',$field_elements);
		$value = 0;
		foreach($option_names as $name)
		{
			$options[$value] = $name;
			$value++;
		}
		return $options;
	}
	
	static function getCurrentURL()
	{
		$href = "http:";
		if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
		{
			$href = 'https:';
		}
	
		$href.= "//".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'];
		return $href;
	}

}