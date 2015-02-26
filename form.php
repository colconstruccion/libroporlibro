<?php 
class Form extends CI_Controller{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('libros_modelo');
		$this->load->library('session');
	}
	
	public function index()
	{
		$this->load->helper(array('form','url'));
		$this->load->library('form_validation');
		$this->form_validation->set_message('required','Por favor ponga el/la %s');
		$this->form_validation->set_message('valid_email','Su correo electronico no es valido');
		$this->form_validation->set_message('min_length[5]', 'su clave debe tener almenos 5 caracteres');
		$this->form_validation->set_rules('nombre','Nombre','required');
		$this->form_validation->set_rules('email','Correo electronico','trim|required|matches[email2]|valid_email|is_unique[usuarios.email]');
		$this->form_validation->set_rules('email2','Confirmacion de Correo','trim|required');
		$this->form_validation->set_rules('clave','Clave','trim|required|min_length[5]|max_length[12]|md5');

		if ($this->form_validation->run() == FALSE)
		{
			$this->load->view('templates/header');
			$this->load->view('templates/nav');
			$this->load->view('nuevousuario');
			$this->load->view('templates/footer');
		}else
		{
			$this->libros_modelo->add_usuario();
			$this->load->view('templates/header');
			$this->load->view('templates/navegacion');
			$this->load->view('usuarioexito');
			$this->load->view('templates/footer');
		}
	}

	public function entrar(){
		$this->load->helper (array('form','url'));
		$this->load->library('form_validation');
		$this->form_validation->set_message('required', 'Por favor ponga el/la %s');
		$this->form_validation->set_message('valid_email','Por favor ponga un correo electronico valido');
		$this->form_validation->set_rules('email','Correo electronico','trim|required|valid_email');
		$this->form_validation->set_rules('clave','Clave','trim|required');
		if ($this->form_validation->run() == FALSE)
		{
			$this->load->view('templates/header');
			$this->load->view('templates/nav');
			$this->load->view('entrar');
			$this->load->view('templates/footer');
		}else
		{
			$data['usuario'] = $this->libros_modelo->validar();
			if (empty($data['usuario']))
			{
				$this->load->view('templates/header');
				$this->load->view('templates/nav');
				$this->load->view('entrar');
				$this->load->view('templates/footer');
			}else
			{
				$usuarioid = $data['usuario']['usuarioid'];
				$nombre = $data['usuario']['nombre'];
				$email = $data['usuario']['email'];
				$this->session->set_userdata('usuarioid', $usuarioid);
				$this->session->set_userdata('nombre', $nombre);
				$this->session->set_userdata('email', $email);

				$this->load->view('templates/header');
				$this->load->view('templates/navegacion');
				$this->load->view('libros/newlibro');
				$this->load->view('templates/footer');
			}
		}
	}

		public function editarview($libroid)
		{
			$this->load->helper (array('form','url'));
			$this->load->library('form_validation');
			$this->form_validation->set_rules('titulo','Titulo', 'required');
			$this->form_validation->set_rules('autor','Autor','required');
			$this->form_validation->set_rules('editorial','Editorial','required');
			$data['libros'] = $this->libros_modelo->show_libros($libroid);

			if ($this->form_validation->run() == FALSE)
			{
				$this->load->view('templates/header');
				$this->load->view('templates/navegacion');
				$this->load->view('editarview',$data);
				$this->load->view('templates/footer');
			}			
		}

		public function editarlibro()
		{
			$this->load->helper(array('form','url'));
			$statusid = $this->libros_modelo->update_libro();
			$this->load->view('templates/header');
			$this->load->view('templates/navegacion');
			if ($statusid == '0')
				$this->load->view('libros/estadolibro');
			else
				$this->load->view('libroactualizado');
			$this->load->view('templates/footer');
		}

		public function borrarlibro()
		{
			$this->load->helper(array('form','url'));
			$tranid = $this->libros_modelo->borrar_libro();
			$this->load->view('templates/header');
			$this->load->view('templates/navegacion');
			if ($tranid == 3){
			$this->load->view('libroReposo');
			}else{
			$this->load->view('libroactualizado');
			}
			$this->load->view('templates/footer');
		}

		public function reenviarclave(){
			$this->load->helper(array('form','url'));
			$this->load->library('form_validation');
			$this->form_validation->set_rules('email', 'Correo Electronico', 'required');
			if ($this->form_validation->run() == FALSE){
				$this->load->view('templates/header');
				$this->load->view('templates/nav');
				$this->load->view('reenviarclave');
				$this->load->view('templates/footer');
			}
			else{
				$data['mensaje'] = $this->libros_modelo->reenviar_clave();
				$this->load->view('templates/header');
				$this->load->view('templates/nav');
				$this->load->view('nuevaclave',$data);
				$this->load->view('templates/footer');
			}	
		}

		public function nuevaclave(){
			$this->load->helper(array('form','url'));
			$this->load->library('form_validation');
			$this->form_validation->set_message('required', 'Por favor ponga el/la %s');
			$this->form_validation->set_message('valid_email','Por favor ponga un correo electronico valido');
			$this->form_validation->set_rules('email','Correo electronico','trim|required|valid_email');
			$this->form_validation->set_rules('clave','Clave','trim|required');
			if ($this->form_validation->run() == FALSE){
				$this->load->view('templates/header');
				$this->load->view('templates/nav');
				$this->load->view('nuevaclave');
				$this->load->view('templates/footer');
			}
			else{
				$data['usuario'] = $this->libros_modelo->verificar();
				if(empty($data['usuario'])){
					$info['mensaje'] = 'No encontramos su correo electronico';
					$this->load->view('templates/header');
					$this->load->view('templates/nav');
					$this->load->view('nuevaclave',$info);
					$this->load->view('templates/footer');
				}else{
					$usuarioid = $data['usuario']['usuarioid'];
					$nombre = $data['usuario']['nombre'];
					$email = $data['usuario']['email'];
					$this->session->set_userdata('usuarioid',$usuarioid);
					$this->session->set_userdata('nombre',$nombre);
					$this->session->set_userdata('email',$email);

					$this->load->view('templates/header');
					$this->load->view('templates/nav');
					$this->load->view('cambiarclave');
					$this->load->view('templates/footer');
				}
			}
		}

		public function cambiarclave(){
			$this->load->helper(array('form','url'));
			$this->load->library('form_validation');
			$this->form_validation->set_message('required','Por favor ponga la %s');
			$this->form_validation->set_rules('clave','Clave','trim|required|min_length[5]|max_length[12]|md5');
			if ($this->form_validation->run() == FALSE){
				$this->load->view('templates/header');
				$this->load->view('templates/nav');
				$this->load->view('cambiarclave');
				$this->load->view('templates/footer');
			}else{
				$this->libros_modelo->cambiar_clave();
				$this->load->view('templates/header');
				$this->load->view('templates/navegacion');
				$this->load->view('libros/newlibro');
				$this->load->view('templates/footer');
			}
		}
}
?>
