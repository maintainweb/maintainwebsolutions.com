<?php

class frm_Forms {

		var $id;
		var $name;
		var $description;
		var $deleted;
		var $form_name;
		var $label_alignment;
		var $no_duplicate;
		var $no_duplicate_type;
		var $no_duplicate_message;
		var $captcha;
		var $no_hot_linking;
		var $allowed_hot_linking_domain;
		var $text_before_form;
		var $text_after_form;
		var $redirect_to_link;
		var $confirmation_message;
		var $delivery_method;
		var $form_target;
		var $template_html;
		var $template_css;
		
		var $fields = array (
			'id', 
			'name', 
			'description', 
			'deleted', 
			'form_name', 
			'label_alignment', 
			'no_duplicate', 
			'no_duplicate_type', 
			'no_duplicate_message', 
			'captcha', 
			'no_hot_linking', 
			'allowed_hot_linking_domain', 
			'text_before_form', 
			'text_after_form', 
			'redirect_to_link', 
			'confirmation_message', 
			'delivery_method', 
			'form_target',
			'template_html',
			'template_css', 
		);
		
		static $special_chars	= array(' ', ';', ':', '"', "'", '\\', '/', '-', '>', '<');
		
}

class frm_FormItems {

		var $id;
		var $name;
		var $description;
		var $deleted;
		var $field_type;
		var $field_name;
		var $field_required;
		var $field_title;
		var $field_elements;
		var $field_default;
		var $field_order;
		var $frm_forms_id_c;
		var $field_form;
		var $form_item_options;
		var $module_field;
		
		var $fields	= array (
	    	'id', 
			'name', 
			'description', 
			'deleted', 
			'field_type', 
			'field_name', 
			'field_required', 
			'field_title', 
			'field_elements',
			'field_default', 
			'field_order', 
			'frm_forms_id_c', 
			'field_form', 
			'form_item_options', 
			'module_field',
		);
		
		static $special_chars	= array(' ', ';', ':', '"', "'", '\\', '/', '-', '>', '<');
		
}

class frm_FormsEmail {

    var $id;
		var $name;
		var $email_to;
		var $email_cc;
		var $email_bcc;
		var $subject;
		var $email_template;
		var $auto_response_from_name;
		var $auto_response_from_email;
		var $auto_response_sub;
		var $auto_response_message;
		var $email_format;
		var $use_phpmailer;
		var $use_smtp;
		var $smtp_host;
		var $smtp_user;
		var $smtp_password;
		var $smtp_port;
		var $smtp_security;
		var $smtp_debug;
		var $frm_forms_id_c;
		
		var $fields	= array (
			'id', 
			'name', 
			'email_to', 
			'email_cc', 
			'email_bcc', 
			'subject', 
			'email_template', 
			'auto_response_from_name', 
			'auto_response_from_email', 
			'auto_response_sub', 
			'auto_response_message', 
			'email_format', 
			'use_phpmailer', 
			'use_smtp', 
			'smtp_host', 
			'smtp_user', 
			'smtp_password', 
			'smtp_port', 
			'smtp_security', 
			'smtp_debug', 
			'frm_forms_id_c', 
		);
}