<?php
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");

global $base; /* A Multisite ``$base`` configuration? */
$ws_plugin__s2member_temp_s_base = (!empty ($base)) ? $base : c_ws_plugin__s2member_utils_urls::parse_url (network_home_url ("/"), PHP_URL_PATH);
/* This works on Multisite installs too. The function ``network_home_url ()`` defaults to ``home_url ()`` on standard WordPress® installs. */
/* Do NOT use ``site`` URL. Must use the `home` URL here, because that's what WordPress® uses in its own `mod_rewrite` implementation. */
?>

Options +FollowSymLinks -MultiViews -Indexes

<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteBase <?php echo $ws_plugin__s2member_temp_s_base . "\n"; ?>

	RewriteCond %{ENV:s2member_file_ms_scan} !^complete$
	RewriteCond %{THE_REQUEST} ^(?:GET|HEAD)(?:[\ ]+)(?:<?php echo preg_quote ($ws_plugin__s2member_temp_s_base, " "); ?>)([_0-9a-zA-Z\-]+/)(?:wp-content/)
	RewriteRule ^(.*)$ - [E=s2member_file_ms_scan:complete,E=s2_blog:%1]

	RewriteCond %{ENV:s2member_file_download_scan} !^complete$
	RewriteRule ^(.*)$ - [E=s2member_file_download_scan:complete,E=s2member_file_download:$1]

	RewriteCond %{ENV:s2member_file_download} ^(.*?)(?:s2member-file-stream/)(.+)$
	RewriteRule ^(.*)$ - [N,E=s2member_file_download:%1%2,E=s2member_file_stream:&s2member_file_stream=yes]

	RewriteCond %{ENV:s2member_file_download} ^(.*?)(?:s2member-file-stream-(.+?)/)(.+)$
	RewriteRule ^(.*)$ - [N,E=s2member_file_download:%1%3,E=s2member_file_stream:&s2member_file_stream=%2]

	RewriteCond %{ENV:s2member_file_download} ^(.*?)(?:s2member-file-inline/)(.+)$
	RewriteRule ^(.*)$ - [N,E=s2member_file_download:%1%2,E=s2member_file_inline:&s2member_file_inline=yes]

	RewriteCond %{ENV:s2member_file_download} ^(.*?)(?:s2member-file-inline-(.+?)/)(.+)$
	RewriteRule ^(.*)$ - [N,E=s2member_file_download:%1%3,E=s2member_file_inline:&s2member_file_inline=%2]

	RewriteCond %{ENV:s2member_file_download} ^(.*?)(?:s2member-file-storage-(.+?)/)(.+)$
	RewriteRule ^(.*)$ - [N,E=s2member_file_download:%1%3,E=s2member_file_storage:&s2member_file_storage=%2]

	RewriteCond %{ENV:s2member_file_download} ^(.*?)(?:s2member-file-remote/)(.+)$
	RewriteRule ^(.*)$ - [N,E=s2member_file_download:%1%2,E=s2member_file_remote:&s2member_file_remote=yes]

	RewriteCond %{ENV:s2member_file_download} ^(.*?)(?:s2member-file-remote-(.+?)/)(.+)$
	RewriteRule ^(.*)$ - [N,E=s2member_file_download:%1%3,E=s2member_file_remote:&s2member_file_remote=%2]

	RewriteCond %{ENV:s2member_file_download} ^(.*?)(?:s2member-file-ssl/)(.+)$
	RewriteRule ^(.*)$ - [N,E=s2member_file_download:%1%2,E=s2member_file_ssl:&s2member_file_ssl=yes]

	RewriteCond %{ENV:s2member_file_download} ^(.*?)(?:s2member-file-ssl-(.+?)/)(.+)$
	RewriteRule ^(.*)$ - [N,E=s2member_file_download:%1%3,E=s2member_file_ssl:&s2member_file_ssl=%2]

	RewriteCond %{ENV:s2member_file_download} ^(.*?)(?:s2member-file-download-key-(.+?)/)(.+)$
	RewriteRule ^(.*)$ - [N,E=s2member_file_download:%1%3,E=s2member_file_download_key:&s2member_file_download_key=%2]

	RewriteCond %{ENV:s2member_file_download} ^(.*?)(?:s2member-skip-confirmation/)(.+)$
	RewriteRule ^(.*)$ - [N,E=s2member_file_download:%1%2,E=s2member_skip_confirmation:&s2member_skip_confirmation=yes]

	RewriteCond %{ENV:s2member_file_download} ^(.*?)(?:s2member-skip-confirmation-(.+?)/)(.+)$
	RewriteRule ^(.*)$ - [N,E=s2member_file_download:%1%3,E=s2member_skip_confirmation:&s2member_skip_confirmation=%2]

	RewriteRule ^(.*)$ %{ENV:s2_blog}?s2member_file_download=%{ENV:s2member_file_download}%{ENV:s2member_file_stream}%{ENV:s2member_file_inline}%{ENV:s2member_file_storage}%{ENV:s2member_file_remote}%{ENV:s2member_file_ssl}%{ENV:s2member_file_download_key}%{ENV:s2member_skip_confirmation} [QSA,L]
</IfModule>

<IfModule !mod_rewrite.c>
	deny from all
</IfModule>

<?php unset ($ws_plugin__s2member_temp_s_base); ?>