<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Content extends Admin_Controller
{
	public $upload_config;
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('file_manager_files_model');
		$this->load->model('file_manager_alias_model');
		$this->load->model('file_manager_settings_model');
		
		$this->lang->load('file_manager');
		
		$this->load->library('helper_lib');
		
		$this->load->config('config');
		$this->upload_config = $this->config->item('upload_config');

		
		// Solves issue in dev version 0.7 (not an issue in 0.6 stable)
		$this->file_manager_files_model->set_table('file_manager_files');
		$this->file_manager_alias_model->set_table('file_manager_alias');
		$this->file_manager_settings_model->set_table('file_manager_settings');
		
		Template::set_block('sub_nav', 'content/_sub_nav');
	}

	public function index()
	{
		$this->auth->restrict('file_manager.Content.View');
		
                Template::set('datatableOptions', array('headers' => 'Thumbnail, Name, Description, Tags, Public, Extension, Download'));
                $datatableData = $this->file_manager_files_model->select('id, id as thumbnail, file_name, description, tags, public, extension')->find_all();

		if(is_array($datatableData))
		{
			foreach($datatableData as $temp_key => $temp_value)
			{
				$datatableData[$temp_key]->sha1_checksum = '<a target="_blank" class="btn btn-mini" href="' . site_url(SITE_AREA .'/widget/file_manager/download/' . $temp_value->id) . '"><i class="icon-download-alt">&nbsp;</i> Download</a>';
				$datatableData[$temp_key]->file_name = '<a href="' . site_url(SITE_AREA .'/content/file_manager/edit/' . $temp_value->id) . '">' . $datatableData[$temp_key]->file_name . "</a>";

				// Only display thumbnail if record extension is of image type
				$allowed_image_extensions = $this->helper_lib->get_allowed_image_extensions();

				$datatableData[$temp_key]->thumbnail = '<img src="' . site_url(SITE_AREA .'/content/file_manager/view_image/thumbnail/' . $temp_value->id) . '" />';
				
				$datatableData[$temp_key]->public = $datatableData[$temp_key]->public ? lang('file_manager_yes') : lang('file_manager_no');

				if($this->icon_exists($datatableData[$temp_key]->extension) !== false) {
					$datatableData[$temp_key]->extension = '<img src="' . site_url(SITE_AREA .'/content/file_manager/icon/' . $temp_value->extension) . '.png" />';
				}
			}
		}

		if (!extension_loaded('gd') || !function_exists('gd_info'))
		{
			$error_messages = (isset($error_messages)) ? $error_messages : $this->session->flashdata('error_messages');
			$error_messages[] = array('message_type' => 'info', 'message' => "PHP module <strong>GD</strong> is <strong>not installed</strong>, thumbnails will not be displayed as a result.<br /> To install GD on Ubuntu system run 'sudo apt-get install php5-gd' or see <a href=\"http://php.net/image\">http://php.net/image</a> for more info");
		}

		$error_messages = (isset($error_messages)) ? $error_messages : $this->session->flashdata('error_messages');
		$delete_failed_alias_existed = $this->session->flashdata('delete_failed_alias_existed');
		if($delete_failed_alias_existed) $error_messages[] = $delete_failed_alias_existed;
		
		// Sets the active_tab setting to #edit_file (resets active_tab when returning from editing a file)
		$this->file_manager_settings_model->get_active_tab('#edit_file');
		
		Template::set('error_messages', $error_messages);
		Template::set('datatableData', $datatableData);
                Template::set('toolbar_title', lang('file_manager_toolbar_title_index'));
		Template::render();
	}

	public function list_aliases()
	{
		$this->auth->restrict('file_manager.Content.View');

		// Using no targets, get all aliases
		$alias_records = $this->file_manager_alias_model->get_aliases();
		if($alias_records)
		{
			foreach($alias_records as $alias_key => $alias_record)
			{
				// Make edit link of file_name
				$alias_records[$alias_key]->file_name = anchor(SITE_AREA . '/content/file_manager/alias_edit/' . $alias_record->id, $alias_record->file_name);

				// file_id isn't used here
				unset($alias_records[$alias_key]->file_id, $alias_records[$alias_key]->description, $alias_records[$alias_key]->override_description);
			}
		}

		Template::set('toolbar_title', lang('file_manager_manage_aliases'));
		Template::Set('datatableOptions', array('headers' => 'File name, Override, Tags, Override, Public, Override, Target module, Target model, Target model row id'));
		Template::set('datatableData', $alias_records);

		Template::render();
	}

	public function import()
	{
		$this->auth->restrict('file_manager.Content.Create');

                Template::set('datatableOptions', array(
                    'headers' => ''.lang('file_manager_folder').', '.lang('file_manager_file_name').', '.lang('file_manager_size').', '.lang('file_manager_date').', '));

		$datatableData = array();
		$this->load->helper('file');

		$import_dir = get_dir_file_info(realpath(FCPATH).'/../application/modules/file_manager/file-import/', $top_level_only = FALSE);
		if(is_array($import_dir))
		{
			foreach ($import_dir as $row)
			{
				$datatableData[] = array($rowObj->column = str_replace('file-import','-',basename ( $row['relative_path'] )), $row['name'], round(($row['size']/1024)).' kB', date('Y-m-d H:i:s', $row['date']), '<a href="?" class="btn btn-mini"><i class="icon-ok">&nbsp;</i> '.lang('file_manager_import_file').'</a> <a href="?" class="btn btn-mini"><i class="icon-download-alt">&nbsp;</i> '.lang('file_manager_download').'</a> <a href="?" class="btn btn-mini"><i class="icon-search">&nbsp;</i> '.lang('file_manager_show').'</a>');
			}
		}

		Template::set('datatableData', $datatableData);
                Template::set('toolbar_title', lang('file_manager_toolbar_title_import'));
		Template::render();
	}

	public function create()
	{
		$this->auth->restrict('file_manager.Content.Create');

		Template::set('toolbar_title', lang('file_manager_toolbar_title_create'));
		Template::render();
	}

	public function get_active_tab()
	{
		// jQuery ajax get response function!

		// Missing security features, filter get with available tabs

		// Attempting to set active_tab sent from ajax function (views/content/init_tabs.php)
		$active_tab = isset($_GET['active_tab']) ? $_GET['active_tab'] : '#edit_file';
		
		$this->file_manager_settings_model->get_active_tab($active_tab);
	}
	
	public function edit()
        {
		$id = $this->uri->segment(5);

		$active_tab = $this->file_manager_settings_model->get_active_tab();

		if (empty($id))
		{
			Template::set_message(lang('file_manager_invalid_id'), 'error');
			redirect(SITE_AREA .'/content/file_manager');
		}

		if (isset($_POST['save']))
		{
			$this->auth->restrict('file_manager.Content.Edit');

			if ($this->save_file_manager_files('update', $id))
			{
				Template::set_message(lang('file_manager_edit_success'), 'success');
			} else
			{
				Template::set_message(lang('file_manager_edit_failure') . $this->file_manager_files_model->error, 'error');
			}
		}
		else if(isset($_POST['save_alias']))
		{
			$this->auth->restrict('file_manager.Content.Create');

			if ($this->save_file_manager_alias('insert', $id))
			{
				$active_tab = '#view_alias';
				Template::set_message(lang('file_manager_alias_create_success'), 'success');
			} else
			{
				$active_tab = '#create_alias';
				Template::set_message(lang('file_manager_alias_create_failure') . $this->file_manager_alias_model->error, 'error');
			}
		}
		else if(isset($_POST['delete']))
		{
			$this->auth->restrict('file_manager.Content.Delete');
			
			$sha1_checksum = implode('', (array) $this->file_manager_files_model->select('sha1_checksum')->find($id));
			$delete_path = $this->upload_config['upload_path'] . $sha1_checksum;

			if ($this->file_manager_files_model->delete($id))
			{
				unlink($delete_path);
				// duplicate code, exists in function callback_unlink_files
				unlink($delete_path . '_thumb');

				if($this->file_manager_alias_model->find_by('file_id', $id))
				{
					if($this->file_manager_alias_model->delete_where(array('file_id' => $id)))
					{
						Template::set_message(lang('file_manager_delete_success'), 'success');
						redirect(SITE_AREA .'/content/file_manager');
					} else
					{
						Template::set_message(lang('file_manager_delete_alias_failure') . $this->file_manager_alias_model->error, 'error');
					}
				} else
				{
					Template::set_message(lang('file_manager_delete_success'), 'success');
					redirect(SITE_AREA .'/content/file_manager');
				}
			} else
			{
				Template::set_message(lang('file_manager_delete_failure') . $this->file_manager_files_model->error, 'error');
			}
		}
		else if(isset($_POST['delete_alias']))
		{
			$this->auth->restrict('file_manager.Content.Delete');

			$active_tab = '#view_alias';

			$checked = $this->input->post('checked');
			if (is_array($checked) && count($checked))
			{
				foreach ($checked as $alias_id)
				{
					if($this->file_manager_alias_model->delete_where(array('id' => $alias_id)))
					{
						$template_message = lang('file_manager_alias_delete_success');
						$template_message_type = 'success';
					}
					else
					{
						$template_message = lang('file_manager_alias_delete_failure') . $this->file_manager_alias_model->error;
						$template_message_type = 'error';
						break;
					}
				}

				Template::set_message($template_message, $template_message_type);
			}
		}
		
		// Get active_tab from alias_edit return(cancel button) and index view(edit link)
		$flashdata_active_tab = $this->session->flashdata('flashdata_active_tab');
		if($flashdata_active_tab)
		{
			$active_tab = $flashdata_active_tab;
		}

		// Used in content/create_alias view
		$available_module_models = $this->helper_lib->get_available_module_models();
		Template::set('module_models', $available_module_models);

		// Set data for tab: #view_aliases
		Template::set('alias_records', $this->file_manager_alias_model->get_aliases(null, null, null, $id));

		// Initiate js for chained select, tabs and modal window
		Assets::add_js($this->load->view('content/js', array('active_tab' => $active_tab), true), 'inline');
		
		// Set data for tab: #edit_file
		Template::set('file_record', $this->file_manager_files_model->find($id));
		
                Template::set('toolbar_title', lang('file_manager_toolbar_title_edit'));

		Template::render();
        }

	public function alias_edit()
	{
		$file_id = $this->uri->segment(5);
		$id = $this->uri->segment(6);
		
		if (empty($file_id) && empty($id))
		{
			Template::set_message(lang('file_manager_alias_invalid_id'), 'error');
			redirect(SITE_AREA .'/content/file_manager/list_aliases');
		}
		
		if(!empty($file_id) && empty($id))
		{
			$id = $file_id;
			$file_id = false;
		}

		if($file_id)
		{
			$this->session->set_flashdata('flashdata_active_tab', '#view_alias');
		}
		
		if (isset($_POST['save_alias']))
		{
			$this->auth->restrict('file_manager.Content.Edit');

			if ($this->save_file_manager_alias('update', $id))
			{
				Template::set_message(lang('file_manager_alias_edit_success'), 'success');
			} else
			{
				Template::set_message(lang('file_manager_alias_edit_failure') . $this->file_manager_files_model->error, 'error');
			}
		}

		Assets::add_js($this->load->view('content/js', array('call_model_row_id_ajax' => true), true), 'inline');

		$available_module_models = $this->helper_lib->get_available_module_models();
		Template::set('module_models', $available_module_models);
		
		Template::set('alias_record', $this->file_manager_alias_model->find_by('id', $id));
		Template::set('file_id', $file_id);
		Template::set('id', $id);

		Template::set('toolbar_title', lang('file_manager_alias_edit_heading'));
		Template::render();
	}

	public function get_alias_target_model_row_id_data()
	{
		$output = '';
		
		$module = $_GET['module'];
		$model = $_GET['model'];
		
		$this->load->model($_GET['module'] . '/' . $_GET['model']);
		
		$table_fields_data = $this->helper_lib->get_target_model_row_table_fields($module, $model);
		$table_fields = $table_fields_data['table_fields'];
		$error = $table_fields_data['error'];

		if($error === false)
		{

		$model_row_id_data = $this->$model->select($table_fields[0] . ', ' . $table_fields[1])->find_all();

			$output = '{';
			foreach($model_row_id_data as $data)
			{
				$data = (array) $data;

				if($output != '{') $output .= ', ';
				$output .= '"' . $data[$table_fields[0]] . '": "' .$data[$table_fields[1]] . '"';
			}
			$output .= '}';

		}
		else
		{
			// handle output better! user can still submit value -1.
			$output = '{"-1": "ERROR:' .$error . '"}';
		}
		
		echo $output;
	}

	public function do_upload()
	{
		$files_array = array();

		// Notice: Can this function be restricted to calls from create controller
		$this->auth->restrict('file_manager.Content.Create');

		// Set files_array with name and path information from multiple file input element
		foreach($_FILES['userfile'] as $assoc_key => $array_value)
		{
			foreach($array_value as $num_key => $value)
			{
				$files_array[$num_key][$assoc_key] = $value;
			}
		}

		// Set allowed types in config item from content_types index, separated by pipes as requested CI upload library
		if(is_array($this->upload_config['content_types'])) $this->upload_config['allowed_types'] = implode('|', array_keys($this->upload_config['content_types']));

		// Convert config item to suitable config variable
		foreach($this->upload_config as $setting => $value)
		{
			$config[$setting] = $value;
		}

		$error_messages = null;
		$return_data_array = array();

		// Perform separate upload and db insert of each file from file input element
		for($i=0; $i<count($files_array); $i++)
		{
			// Set global variable to current upload
			$_FILES['userfile'] = $files_array[$i];

			// Collect return data from each upload
			$return_data_array[$i] = $this->perform_upload($config);

			if(isset($return_data_array[$i]['error']['file_exists'])) $error_messages[] = array('message_type' => '-info', 'message' => $return_data_array[$i]['error']['file_exists']);
			if(isset($return_data_array[$i]['error']['file_exists_both'])) $error_messages[] = array('message_type' => '', 'message' => $return_data_array[$i]['error']['file_exists_both']);
			if(isset($return_data_array[$i]['error']['upload'])) $error_messages[] = array('message_type' => '-error', 'message' => $return_data_array[$i]['error']['upload']);
		}
		
		// Check to see if there is nothing but errors to fail
		$message_types = array();
		$only_error = false;
		foreach($error_messages as $error_message)
		{
			$message_types[] = $error_message['message_type'];
		}
		
		if(in_array('-error', $message_types))
		{
			$only_error = true;
			foreach($message_types as $message_type_key => $message_type)
			{
				if($message_type != '-error')
				{
					$only_error = false;
				}
			}
		}

		if(!$only_error) Template::set_message('Upload complete', 'success');

		$this->session->set_flashdata('error_messages', $error_messages);
		
		redirect(SITE_AREA . '/content/file_manager');
	}
	
	public function thumbnail_exist($file_id)
	{
		$this->load->model('file_manager_files_model');
		$record = $this->file_manager_files_model->select('sha1_checksum, file_name, extension')->find_by('id', $file_id);

		$file_path = null;
		if($record)
		{
			$path_parts = pathinfo($record->sha1_checksum);
			$file_name  = $path_parts['basename'];
			$file_path  = $this->upload_config['upload_path'].$file_name;
			if(file_exists($file_path."_thumb"))
			{
				return $file_path;
			}
		}
		return false;
	}
	
	// View images and thumbnails, create thumbnails on demand
	public function view_image($check_exist=false)
	{	
		$file_id = $this->uri->segment(5);
		$thumbnail = $this->uri->segment(6);

		if(empty($thumbnail))
		{
			$thumbnail = false;
		}
		else
		{
			$file_id = $thumbnail;
			$thumbnail = true;
		}

		$this->load->model('file_manager_files_model');
		$record = $this->file_manager_files_model->select('sha1_checksum, file_name, extension')->find_by('id', $file_id);

		$file_path = null;
		if($record)
		{
			$path_parts = pathinfo($record->sha1_checksum);
			$file_name  = $path_parts['basename'];
			$file_path  = $this->upload_config['upload_path'].$file_name;
		}

		if(file_exists($file_path))
		{
			$content_types = $this->upload_config['content_types'];

			// Restrict none image extensions
			$allowed_image_extensions = $this->helper_lib->get_allowed_image_extensions();
			if(!in_array($record->extension, $allowed_image_extensions)) $this->load->vars(array('error' => 'The file is not an image'));

			if($thumbnail)
			{
				if(!file_exists($file_path."_thumb"))
				{
					$generate_thumbnail = $this->generate_thumbnail($file_path, 'small', 'image');
					if(!$generate_thumbnail) $this->load->vars(array('error' => 'Thumbnail could not be generated'));
				}

				if(!file_exists($file_path."_thumb")) $this->load->vars(array('error' => 'Thumbnail is missing'));
				
				$file_path .= '_thumb';
			}
			
			$this->load->view('content/view_image', array('file_path' => $file_path, 'content_type' => $content_types[$record->extension]));
		}
	}

	// CONTINUE HERE! This function seems only to be used by icon and does the same as view_image()
	// Confirm this and remove the function, change any calls to view_image (also, remove icon_exists function)
	public function icon()
	{
		$image = $this->uri->segment(5);
		$file_path  = $this->icon_exists($image, "");
		if(file_exists($file_path)) {
			$this->load->vars(array(
				'file_path'         => $file_path,
				'content_type'      => "image/png",
				'attachment_name'   => $image
			));
			$this->load->view('content/view_image');
		}
	}

	public function callback_unlink_files($delete_id)
	{
		$delete_data = $this->file_manager_files_model->select('sha1_checksum')->find_by('id', $delete_id);
		
		// Set whether or not to delete files with aliases
		$delete_aliases = (isset($_POST['delete_has_aliases']) && $_POST['delete_has_aliases'] == '1') ? false : true;
		
		// Search for aliases
		$alias_exists = $this->file_manager_alias_model->find_by('file_id', $delete_id);

		// If aliases exists, and delete option set to don't delete with aliases, set a message and return
		if($alias_exists && !$delete_aliases)
		{
			$this->session->set_flashdata('delete_failed_alias_existed', array('message_type' => '-info', 'message' => 'Notice! Some or all of the selected files were not deleted by choice because some of or all the files have aliases.'));
			return;
		}

		// Delete files db row
		$this->file_manager_files_model->delete($delete_id);

		// Delete files and thumbnails when deleting files
		$this->load->config('file_manager/config');
		$delete_path = $this->upload_config['upload_path'] . $delete_data->sha1_checksum;
		unlink($delete_path);
		unlink($delete_path . '_thumb');
	}
	
	private function generate_thumbnail($path, $size='small', $type='image')
	{
		// Check that size is valid
		if(!in_array($size, array("small", "medium", "large")))
		{
			return false;
			
		}

		// Get and set size in pixels from config
		$thumb_size_width	= "thumb_".$size."_width";
		$thumb_size_height	= "thumb_".$size."_height";
		$width			= $this->upload_config[$thumb_size_width];
		$height			= $this->upload_config[$thumb_size_height];

		if($type == 'image')
		{
			$config = array(
			    'image_library'	=> 'gd2',
			    'create_thumb'	=> true,
			    'maintain_ratio'	=> true,
			    'source_image'	=> $path,
			    'width'		=> $width,
			    'height'		=> $height);

			$this->load->library('image_lib', $config);
			$this->image_lib->resize();
		}
		elseif($type == 'pdf')
		{
			exec("convert  -resize '20%' -density 150 ".$path."[0] ".$path."_thumb.jpg");
			exec("cp ".$path."_thumb.jpg ".$path."_thumb");
		}
		
		
		return $path . '_thumb';
	}

	private function icon_exists($extension, $add = ".png")
	{
		$file_path  = $this->upload_config['module_path']."assets/images/Free-file-icons/32px/".$extension.$add;
		if(file_exists($file_path))
		{
			return $file_path;
		}

		return 0;
	}

	private function convert_client_filename ($filename, $extension)
	{
		$client_filename = 0;
		// Remove extension from filename
		$client_filename = preg_replace('/'.$extension.'$/', '', $filename);
		$client_filename = str_replace('_', ' ', $client_filename);
		$client_filename = str_replace('+', ' ', $client_filename);
		$client_filename = str_replace('  ', ' ', $client_filename);
		$client_filename = ucfirst($client_filename);
		return $client_filename;
	}

	private function perform_upload($config)
	{
		// Declaring contents of return
		$return = array('error' => null, 'file_exists_db' => null, 'file_exists_hdd' => null, 'return_data' => null);

		// Preventing incorrect file names
		$config['file_name'] = md5(rand(20000, 90000));

		// Load CodeIgniter's upload library, config variable describes the upload environment
		$this->load->library('upload', $config);

		// Perform upload
		if (!$this->upload->do_upload())
		{
			// Notice: Check to see why these parameters can't be set with abstract call to Template class
			// Triggered by violation of configured limitations
			$return['error']['upload'] = $this->upload->display_errors();
			$this->upload->error_msg = '';
		}
		else
		{
			// Get information about the upload such as names, types, sizes and so on
			$upload_data = $this->upload->data();

			// Get checksum to check if file already exist and if not, as a file naming convention
                        $sha1_checksum = sha1_file($upload_data['full_path']);

			// Get data from database if the file already exists: the file has been renamed and moved and added to db
                        $db_data = $this->file_manager_files_model->select('id, file_name, description, tags, owner_user_id, public, sha1_checksum, extension, created')->find_by('sha1_checksum', $sha1_checksum);

			// Set whether file exists in db
			$file_exists_db = $db_data ? true : false;
			$return['file_exists_db'] = $file_exists_db;

			// Set whether the file exists in hdd
			$file_exists_hdd = file_exists($upload_data['file_path']."/".$sha1_checksum) ? true : false;
			$return['file_exists_hdd'] = $file_exists_hdd;

			$insert_data = array();

			if(!$file_exists_hdd)
			{
				// Rename file from temporarily md5 generated value to sha1 checksum
				rename($upload_data['full_path'], $upload_data['file_path']."/".$sha1_checksum);
			}

			if(!$file_exists_db)
			{
				// Set data to insert to db
				$insert_data = array(
					'id'		=> NULL,
					'file_name'	=> $this->security->sanitize_filename(basename($this->convert_client_filename($upload_data['client_name'], $upload_data['file_ext']))),
					'description'	=> '',
					'tags'		=> '',
					'owner_user_id'	=> $this->current_user->id,
					'public'	=> 0,
					'sha1_checksum'	=> $sha1_checksum,
					'extension'	=> substr($upload_data['file_ext'], 1),
					'created'	=> date("Y-m-d H:i:s")
				);
			}

			if($file_exists_db && $file_exists_hdd)
			{
				// If the file exists as sha1_checksum, the uploaded temporary file is deleted
				unlink($upload_data['full_path']);
			}

			// If the file doesn't exists, the file info is added to db
			// Set db ID with old db ID or mysql_insert_id from model->insert
                        $db_data_id = ($file_exists_db) ? $db_data->id : $this->file_manager_files_model->insert($insert_data);

			// Set the return_data
			$return_data = ($file_exists_db) ? (array) $db_data : $insert_data;

			// Set error messages if file exists in any or all storage types
			if($file_exists_db && $file_exists_hdd)
			{
				$return['error']['file_exists_both'] = 'The file exists in both database and on hdd';
			}
			elseif($file_exists_db || $file_exists_hdd) 
			{
				$return['error']['file_exists'] = 'The file exists in ' . ($file_exists_db ? 'db' : 'hdd') . ', created a new ' . ($file_exists_db ? 'file' : 'db record') . ' (' . ($file_exists_db ? $upload_data['client_name'] : $db_data_id) . ')';
			}
			
			// Add db row ID to the return_data // CHECK TO SEE IF THIS IS NECESSARY
			$return_data['database_row_id'] = $db_data_id;

			// Add return_data to return
			$return['return_data'] = $return_data;
		}

		return $return;
	}

	private function save_file_manager_files($type='insert', $id=0)
	{
		if ($type == 'update') {
			$_POST['id'] = $id;
		}

		$this->form_validation->set_rules('file_name','File name','required|max_length[255]');
		$this->form_validation->set_rules('description','Description','');
		$this->form_validation->set_rules('tags','Tags','max_length[255]');
		$this->form_validation->set_rules('public','Public','max_length[255]');

		if ($this->form_validation->run() === FALSE)
		{
			return FALSE;
		}

		$data = array();
		$data['file_name']      = $this->input->post('file_name');
		$data['description']    = ($this->input->post('description')) ? $this->input->post('description') : '';
		$data['tags']           = ($this->input->post('tags')) ? $this->input->post('tags') : '';
		$data['public']         = $this->input->post('public');

		if ($type == 'update')
		{
			$return = $this->file_manager_files_model->update($id, $data);
		}

		return $return;
	}

	private function save_file_manager_alias($type='insert', $id=0)
	{
		if($type == 'update') {
			$_POST['id'] = $id;
		}

		if($type == 'insert')
		{
			$file_id = $id;
		}

		$this->form_validation->set_rules('alias_override_file_name','Override file name','max_length[255]');
		$this->form_validation->set_rules('alias_override_description','Description','');
		$this->form_validation->set_rules('alias_override_tags','Tags','max_length[255]');
		$this->form_validation->set_rules('alias_override_public','Public','max_length[255]');
		$this->form_validation->set_rules('alias_target_module','Target module','required|max_length[255]');
		$this->form_validation->set_rules('alias_target_model','Target model','max_length[255]');
		$this->form_validation->set_rules('alias_target_model_row_id','Target model row id','max_length[11]');

		if($this->form_validation->run() === FALSE)
		{
			return FALSE;
		}

		$data = array();
		if($type == 'insert') $data['file_id'] = $file_id;
		$data['override_file_name']	= $this->input->post('alias_override_file_name');
		$data['override_description']	= ($this->input->post('alias_override_description')) ? $this->input->post('alias_override_description') : '';
		$data['override_tags']		= ($this->input->post('alias_override_tags')) ? $this->input->post('alias_override_tags') : '';
		$data['override_public']	= $this->input->post('alias_override_public');
		$data['target_module']		= $this->input->post('alias_target_module');
		$data['target_model']		= ($this->input->post('alias_target_model')) ? $this->input->post('alias_target_model') : '';
		$data['target_model_row_id']	= ($this->input->post('alias_target_model_row_id') ? $this->input->post('alias_target_model_row_id') : 0);

		if($type == 'insert')
		{
			$id = $this->file_manager_alias_model->insert($data);

			if (is_numeric($id))
			{
				$return = $id;
			} else
			{
				$return = FALSE;
			}
		}
		else if($type == 'update')
		{
			$return = $this->file_manager_alias_model->update($id, $data);
		}

		return $return;
	}
}