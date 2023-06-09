<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['module_config'] = array(
	'name'		=> 'File manager',
	'description'	=> 'File manager with CI-based upload functionality for Bonfire',
	'version'	=> '1.0.1',
	'author'	=> 'Bonfire-Modules'
);

$config['alias_config'] = array(
	'exclude_target_modules'		=> array('datatable'),
	'include_core_modules'			=> array('users' => array('models' => array('user_model.php'))),
	'target_model_field_config'		=> array('user_model' => array('id', 'username'), 'testing_model' => array('id', 'testing_test'))
);

/* CI Upload library
 * Preferences
 * 
 *	PREFERENCE	DEFAULT   OPTIONS			DESCRIPTION
 *			VALUE	
 *	upload_path	None	  None				The path to the folder where the upload should be placed. The folder must be writable and the path can be absolute or relative.
 *	allowed_types	None	  None				The mime types corresponding to the types of files you allow to be uploaded. Usually the file extension can be used as the mime type. Separate multiple types with a pipe.
 *	file_name	None	  Desired file name		If set CodeIgniter will rename the uploaded file to this name. The extension provided in the file name must also be an allowed file type.
 *	overwrite	FALSE	  TRUE/FALSE (boolean)		If set to true, if a file with the same name as the one you are uploading exists, it will be overwritten. If set to false, a number will be appended to the filename if another with the same name exists.
 *	max_size	0	  None				The maximum size (in kilobytes) that the file can be. Set to zero for no limit. Note: Most PHP installations have their own limit, as specified in the php.ini file. Usually 2 MB (or 2048 KB) by default.
 *	max_width	0	  None				The maximum width (in pixels) that the file can be. Set to zero for no limit.
 *	max_height	0	  None				The maximum height (in pixels) that the file can be. Set to zero for no limit.
 *	max_filename	0	  None				The maximum length that a file name can be. Set to zero for no limit.
 *	encrypt_name	FALSE	  TRUE/FALSE (boolean)		If set to TRUE the file name will be converted to a random encrypted string. This can be useful if you would like the file saved with a name that can not be discerned by the person uploading it.
 *	remove_spaces	TRUE	  TRUE/FALSE (boolean)		If set to TRUE, any spaces in the file name will be converted to underscores. This is recommended.
 */

/*
 * Set upload path depending on version
 * 0.7.x realpath(FCPATH) . '/../application/modules/file_manager/files//'
 * 0.6.x realpath(FCPATH) . '/bonfire/modules/file_manager/files//'
 */

/* FOR CONSIDERATION:
 *      
 *      Be aware that sending binary files to the user-agent (browser) over an encrypted connection (SSL/TLS) will fail in IE (Internet Explorer) versions 5, 6, 7, and 8 if any of the following headers is included:
        Cache-control:no-store
        Cache-control:no-cache
        See: http://support.microsoft.com/kb/323308
        Workaround: do not send those headers.
        Also, be aware that IE versions 5, 6, 7, and 8 double-compress already-compressed files and do not reverse the process correctly, so ZIP files and similar are corrupted on download.
        Workaround: disable compression (beyond text/html) for these particular versions of IE, e.g., using Apache's "BrowserMatch" directive. The following example disables compression in all versions of IE:
        BrowserMatch ".*MSIE.*" gzip-only-text/html
 */

$tmp_bonfire_version_numeric = preg_replace("/[^0-9,.]/", "", BONFIRE_VERSION);
$tmp_module_path = ($tmp_bonfire_version_numeric >= 0.7) ? "/../application/modules/file_manager/": "/bonfire/modules/file_manager/";
$tmp_module_path = realpath(FCPATH) . $tmp_module_path;

$config['upload_config'] = array(
	'upload_path'                   => $tmp_module_path.'files/',
    	'module_path'                   => $tmp_module_path,
	'allowed_types'                 => 'content_types',
	'content_types'			=> array(
						'gif'   => "image/gif",
						'jpg'   => "image/jpeg",
						'jpeg'  => "image/jpeg",
						'png'   => "image/png",
						'pdf'   => "application/pdf",
						'doc'   => "application/msword",
						'docx'  => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
						'xls'   => "application/vnd.ms-excel",
						'xlsx'  => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
						'ppt'   => "application/vnd.ms-powerpoint",
						'pptx'  => "application/vnd.openxmlformats-officedocument.presentationml.presentation",
						'odt'   => "application/vnd.openxmlformats-officedocument.presentationml.presentation",
						'zip'   => "application/zip",
						'gzip'  => "application/gzip"
					),
	'file_name'                     => '',
	'overwrite'     		=> false,
	'max_size'              	=> 0,
	'max_width'     		=> 0,
	'max_height'            	=> 0,
	'max_filename'                  => 0,
	'encrypt_name'          	=> false,
	'remove_spaces'                 => true,
	'thumb_small_width'		=> '120',
	'thumb_small_height'		=> '120',
	'thumb_medium_width'		=> '250',
	'thumb_medium_height'		=> '250',
	'thumb_large_width'		=> '800',
	'thumb_large_height'		=> '600',
);