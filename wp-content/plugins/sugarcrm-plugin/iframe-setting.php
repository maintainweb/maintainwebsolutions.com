<style type="text/css">
.crm-settings-panel {
	background-color:#F1F1F1;
	display:block;
	margin:0;
	padding:10px;
}
.slide {
	width:100%;
	margin:0;
}
.crm-settings-link-wrap:link, .crm-settings-link-wrap:visited {
	float:right;
	height:22px;
	padding:0;
	margin:0 6px 0 0;
	font-family:"Lucida Grande", Verdana, Arial, "Bitstream Vera Sans", sans-serif;
	-moz-border-radius-bottomleft:3px;
	-moz-border-radius-bottomright:3px;
	-webkit-border-bottom-left-radius:3px;
	-webkit-border-bottom-right-radius:3px;
	color: #606060;
	text-decoration:none;
	padding:0 10px;
	font-size:10px;
	background:#e3e3e3;
}
.crmform {
	width:100%;
	margin:0;
	color:#999;
	font-family:Arial, Helvetica, sans-serif;
}
.crmform label {
	width:15%;
	color: #000000;
	font-size: 12px;
}
.crmform input[type=text] {
	width:25%;
}
</style>

<div class="crm-settings-panel">
  <h3>SugarCRM Settings</h3>
  <div class="crmform">
    <div class="metabox-prefs">
      <form action="" method="post">
        <label for="crm_url">URL : </label>
        <input type="text" name="crm_url" id="crm_url" value="<?php echo wp_specialchars(stripslashes($crm_option['url']), 1) ?>" size="20" />
        <br>
        <label for="crm_user">Username : </label>
        <input type="text" name="crm_user" id="crm_user" value="<?php echo wp_specialchars(stripslashes($crm_option['username']), 1) ?>" size="20" />
        <br>
        <label for="crm_pwd">Password : </label>
        <input type="password" name="crm_pwd" id="crm_pwd" size="20" />
        <br>
        <div id="crm_config_error" class="error"></div>
        <input type="submit" name="crm-settings-save" id="crm-settings-save" value="Save" class="button" />
        <input type="hidden" name="redirect_to" value="<?php echo $_SERVER['REQUEST_URI']; ?>" />
      </form>
    </div>
  </div>
</div>

<!--LOGIN BUTTON TEXT-->
