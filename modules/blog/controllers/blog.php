<?php 

class Blog extends Front_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->model('post_model');
	}

	//--------------------------------------------------------------------

	public function index()
	{
		$this->load->helper('typography');

		$posts = $this->post_model->order_by('created_on', 'asc')
								  ->limit(5)
								  ->find_all();

		Template::set('posts', $posts);

		Template::render();
	}

	//--------------------------------------------------------------------
	
	
}