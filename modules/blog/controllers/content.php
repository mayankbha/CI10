<?php 

class Content extends Admin_Controller
{

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('post_model');
		 
		Template::set('toolbar_title', 'Manage Your Blog');
		Template::set_block('sub_nav', 'content/sub_nav');
	}

	//--------------------------------------------------------------------

	public function index()
	{
		$posts = $this->post_model->where('deleted', 0)->find_all();
		Template::set('posts', $posts);
		Template::render();
	}

	//--------------------------------------------------------------------

	public function create()
    {
        if ($this->input->post('submit'))
        {
            $data = array(
                'title' => $this->input->post('title'),
                'slug'  => $this->input->post('slug'),
                'body'  => $this->input->post('body')
            );

            if ($this->post_model->insert($data))
            {
                Template::set_message('You post was successfully saved.', 'success');
                redirect(SITE_AREA .'/content/blog');
            }
        }

        Template::set('toolbar_title', 'Create New Post');
        Template::set_view('content/post_form');
        Template::render();
    }
	
	public function edit_post($id=null)
    {
        if ($this->input->post('submit'))
        {
            $data = array(
                'title' => $this->input->post('title'),
                'slug'  => $this->input->post('slug'),
                'body'  => $this->input->post('body')
            );

            if ($this->post_model->update($id, $data))
            {
                Template::set_message('You post was successfully saved.', 'success');
                redirect(SITE_AREA .'/content/blog');
            }
        }

        Template::set('post', $this->post_model->find($id));

        Template::set('toolbar_title', 'Edit Post');
        Template::set_view('content/post_form');
        Template::render();
    }
}