<?php
class Libros extends CI_Controller{
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('libros_modelo');
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->library('pagination');
		$this->load->helper('form');
	}

	public function newlibro()
		{
			$this->load->library('form_validation');

			$data['title'] = 'Agregue nuevo libro';

			$this->form_validation->set_rules('titulo','Titulo', 'required');
			$this->form_validation->set_rules('autor','Autor','required');
			$this->form_validation->set_rules('editorial','Editorial', 'required');
			$this->form_validation->set_rules('descripcion','Descripcion','required|callback_Descripcion_revisar');
			$this->form_validation->set_message('required', 'Se requiere esta informacion');
			$usuarioid = $this->session->userdata('usuarioid');
			$pdf = $this->input->post('archivo');

			if (($this->form_validation->run() === FALSE) && ($usuarioid))
			{
				$this->load->view('templates/header',$data);
				$this->load->view('templates/navegacion');
				$this->load->view('libros/newlibro');
				$this->load->view('templates/footer');
			}else if($pdf == 2){
				$config['upload_path'] = './libros_digitales/';
				$config['allowed_types'] = 'pdf|PDF';
				$config['max_size'] = '5000';
				$this->load->library('upload', $config);
				if (! $this->upload->do_upload())
				{
					$error = array('error' => $this->upload->display_errors());
					$this->load->view('libros/newlibro',$error);
				}else
				{
					//obtiene la informacion del archivo que se subio al servidor
					$data = array('upload_data' => $this->upload->data());
					$url = $data['upload_data']['full_path'];
					//obtiene el id del libro que se subio y un mensaje
					$data['info'] = $this->libros_modelo->add_libros();
					$libroid = $data['info']['last_id'];
					$datos['mensaje'] = $data['info']['mensaje'];
					//Mete el url y el libroid a una tabla que los relaciona
					$this->libros_modelo->add_libro_pdf($libroid,$url);
					$this->load->view('templates/header');
					$this->load->view('templates/navegacion');
					$this->load->view('libros/exito',$datos);
					$this->load->view('templates/footer');
				}
			}
			else if ($usuarioid)
			{
				$info['mensaje'] = $this->libros_modelo->add_libros();
				$this->load->view('templates/header');
				$this->load->view('templates/navegacion');
				$this->load->view('libros/exito',$info);
				$this->load->view('templates/footer');
			}else
			{
				$this->load->view('templates/header');
				$this->load->view('templates/nav');
				$this->load->view('entrar');
				$this->load->view('templates/footer');
			}
		}

	public function Descripcion_revisar($desc){
		if (strlen($desc) < 10)
		{
			$this->form_validation->set_message('Descripcion_revisar','La %s debe tener mayor informacion');
		}
		else
		{
			return true;
		}
	}	

	public function index()
		{	
	
			$config['base_url'] = 'http://comovaelmio.com/index.php/libros/index';
			$config['total_rows'] = $this->libros_modelo->contar_textos();
			$limit = $config['per_page'] = 20;
			$config['uri_segment'] = 3;
			$config['first_link'] = 'Primera';
			$config['last_link'] = 'Ultima';
			$this->pagination->initialize($config);
			$offset = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

			$data['libros'] = $this->libros_modelo->show_textos($limit,$offset);
			$usuarioid = $this->session->userdata('usuarioid');
			if ($usuarioid)
			{
				$this->load->view('templates/header');
				$this->load->view('templates/navegacion');
				$this->load->view('libros/index',$data);
				$this->load->view('templates/footer');
			}else
			{
				$this->load->view('templates/header');
				$this->load->view('templates/nav');
				$this->load->view('libros/index',$data);
				$this->load->view('templates/footer');
			}
		}

	public function view($libroid)
		{
			$data['libros'] = $this->libros_modelo->show_libros($libroid);
			$usuarioid = $this->session->userdata('usuarioid');
			$this->load->helper('form');

			if ($usuarioid)
			{
				$this->session->set_userdata('textoid',$libroid);
				$this->load->view('templates/header');
				$this->load->view('templates/navegacion');
				$this->load->view('libros/view',$data);
				$this->load->view('templates/footer');
			}else
			{
				$this->load->view('templates/header');
				$this->load->view('templates/nav');
				$this->load->view('libros/view',$data);
				$this->load->view('templates/footer');
			}
		}		

	public function biblioteca()
	{

			$usuarioid = $this->session->userdata('usuarioid');
			// This is pagination//
			$config['base_url'] = 'http://comovaelmio.com/index.php/libros/biblioteca/';
			$config['total_rows'] = $this->libros_modelo->contar_biblioteca($usuarioid);
			$limit = $config['per_page'] = 20;
			$config['uri_segment'] = 3;
			$config['first_link'] = 'Primera';
			$config['last_link'] = 'Ultima';
			$this->pagination->initialize($config);
			$offset = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
			// Use the form helper because navegacion has a form in it//
			$this->load->helper('form');
			$this->load->library('form_validation');
			$data['libros'] = $this->libros_modelo->show_biblioteca($usuarioid,$limit,$offset);

				$this->load->view('templates/header');
				$this->load->view('templates/navegacion');
				if (empty($data['libros'])){
					$this->load->view('libros/newlibro');
				}else
				{
				$this->load->view('libros/biblioteca',$data);
				}
				$this->load->view('templates/footer');
	}	

	public function mibiblioteca()
	{
		$usuarioid = $this->session->userdata('usuarioid');

		$config['base_url'] = 'http://comovaelmio.com/index.php/libros/mibiblioteca/';
		$config['total_rows'] = $this->libros_modelo->contar_biblioteca($usuarioid);
		$limit = $config['per_page'] = 20;
		$config['uri_segment'] = 3;
		$config['first_link'] = 'Primera';
		$config['last_link'] = 'Ultima';
		$this->pagination->initialize($config);
		$offset = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
		
		$data['libros'] = $this->libros_modelo->show_biblioteca($usuarioid,$limit,$offset);

				$this->load->view('templates/header');
				$this->load->view('templates/navegacion');
				$this->load->view('libros/mibiblioteca',$data);
				$this->load->view('templates/footer');
	}

	public function intercambio($libroid)
	{
		#informacion del modelo
		$data['cuadernos'] = $this->libros_modelo->inter_libro($libroid);
		$correo = $data['cuadernos']['correo'];
		$nombre2 = $data['cuadernos']['nombre2'];
		$titulo = $data['cuadernos']['titulo'];
		$titulo2 = $data['cuadernos']['titulo2'];

		$textoid = $this->session->userdata['textoid'];
	
		#email del que inicia el intercambio
		$email = $this->session->userdata('email');
		$nombre = $this->session->userdata('nombre');
		#mandar email de inicio de intercambio
		$this->load->library('email');
		#Email a la persona a que tiene el libro
		$this->email->from('admin@comovaelmio.com','comovaelmio.com/libros/index.php/libros');
		$this->email->to($correo);
		$this->email->subject('Te han propuesto un intercambio de libros');
		$this->email->message($nombre.' te ha propuesto intercambiar'."\t".$titulo.' por '.$titulo2."\t".', por favor dirigete a la bandeja de intercambios http://comovaelmio.com/ para aceptar o rechazar el intercambio');
		$this->email->send();
		$this->email->clear();
		#email a la persona que inicia el intercambio
		$this->email->from('admin@comovaelmio.com','comovaelmio.com/libros/index.php/libros');
		$this->email->to($email);
		$this->email->subject('Has iniciado un intercambio');
		$this->email->message('Le has propuesto a '.$nombre2.' intercambiar '."\t".$titulo.' por '.$titulo2.', ahora debes esperar a que el otro lector aprube el intercambio');
		$this->email->send();

		$this->session->unset_userdata('textoid');

		$this->load->view('templates/header');
		$this->load->view('templates/navegacion');
		$this->load->view('librointercambiado');
		$this->load->view('templates/footer');
		
	}

	public function intercambios()
	{
		$data['libros'] = $this->libros_modelo->show_intercambios();
		// Use the form helper because navegacion has a form in it//
			$this->load->library('form_validation');
			
		if (empty($data['libros'])){
			$this->load->view('templates/header');
			$this->load->view('templates/navegacion');
			$this->load->view('libros/newlibro');
			$this->load->view('templates/footer');
		}
		else
		{
			$this->load->view('templates/header');
			$this->load->view('templates/navegacion');
			$this->load->view('libros/intercambios',$data);
			$this->load->view('templates/footer');
		}
	}

	public function libroscategorias($catid)
	{
		$config['base_url'] = 'http://comovaelmio.com/index.php/libros/libroscategorias';
		$config['total_rows'] = $this->libros_modelo->contar_libroscategorias($catid);
		$limit = $config['per_page'] = 20;
		$config['uri_segment'] = 3;
		$config['first_link'] = 'Primera';
		$config['last_link'] = 'Ultima';
		$this->pagination->initialize($config);
		$offset = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
		$usuarioid = $this->session->userdata('usuarioid');
		$data['libros'] = $this->libros_modelo->show_categorias($catid);

		// Use the form helper because navegacion has a form in it//
			$this->load->library('form_validation');
		if (empty($data['libros']) && (!empty($usuarioid))){
			$this->load->view('templates/header');
			$this->load->view('templates/navegacion');
			$this->load->view('libros/estadolibro');
			$this->load->view('templates/footer');
		}		
		else if (empty($data['libros'])){
			$this->load->view('templates/header');
			$this->load->view('templates/nav');
			$this->load->view('libros/estadolibro');
			$this->load->view('templates/footer');
		}
		else if ($usuarioid)
		{
			$this->load->view('templates/header');
			$this->load->view('templates/navegacion');
			$this->load->view('libros/libroscategorias',$data);
			$this->load->view('templates/footer');
		}else
		{
			$this->load->view('templates/header');
			$this->load->view('templates/nav');
			$this->load->view('libros/libroscategorias',$data);
			$this->load->view('templates/footer');
		}
	}

	public function logout()
	{
		$this->load->helper('form');
		$this->load->view('templates/header');
		$this->load->view('templates/nav');
		$this->load->view('libros/logout');
		$this->load->view('templates/footer');
		$this->session->sess_destroy();
	}

	public function aprobar($productoid)
	{
		//informacion de biblioteca_libros de quien inicio el intercambio
		$data['libros'] = $this->libros_modelo->aprobar_libro($productoid);
		//email de quien aprueba el intercambio
		$correo = $this->session->userdata('email');
		$nombre = $this->session->userdata('nombre');

		$libroid = $data['libros']['libroid'];
		$textoid = $data['libros']['textoid'];
		$usuarioid = $data['libros']['usuarioid'];

		#conseguir email del que el que pidio el libro
		$pregunta = $this->db->get_where('usuarios', array('usuarioid' => $usuarioid));
		$renglon = $pregunta->row_array();
		$email = $renglon['email'];

		#mandar email de aprobacion de intercambio
		$this->load->library('email');
		#Email a la persona a que tiene el libro
		$this->email->from('admin@comovaelmio.com','comovaelmio.com/index.php/libros');
		$this->email->to($correo);
		$this->email->subject('Has aprobado el intercambio');
		$this->email->message('Puedes comunicarte al email'."\t".$email."\t".'para cuadrar los detalles del intercambio de libros');
		$this->email->send();
		$this->email->clear();
		#email a la persona que inicia el intercambio
		$this->email->from('admin@comovaelmio.com','comovaelmio.com/index.php/libros');
		$this->email->to($email);
		$this->email->subject('Han aprobado el intercambio de libros');
		$this->email->message($nombre."\t".'ha aprobado el intercambio de libros, por favor comunicate con la lectora al correo '."\t".$correo."\t".' para hacer el intercambio');
		$this->email->send();

		$this->load->view('templates/header');
		$this->load->view('templates/navegacion');
		$this->load->view('libros/aprobado');
		$this->load->view('templates/footer');
	}

	public function buscar()
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		$data['libros'] = $this->libros_modelo->buscar_libro();
		
		if (empty($data['libros'])){
			$this->load->view('templates/header');
			$this->load->view('templates/nav');
			$this->load->view('estadolibro');
			$this->load->view('templates/footer');
		}else
		{
			$this->load->view('templates/header');
			$this->load->view('templates/nav');
			$this->load->view('libros/buscar',$data);
			$this->load->view('templates/footer');
		}
	}

	public function noaprobar($productoid)
	{
		//informacion de quien inicio el intercambio y titulo de los libros
		$data['cuadernos'] = $this->libros_modelo->noaprobar_libro($productoid);
		$email = $data['cuadernos']['email'];
		$titulo = $data['cuadernos']['titulo'];
		$titulo2 = $data['cuadernos']['titulo2'];
		//email de quien aprueba el intercambio
		$correo = $this->session->userdata('email');
		$nombre = $this->session->userdata('nombre');

		#mandar email de aprobacion de intercambio
		$this->load->library('email');
		#Email a la persona a que tiene el libro
		$this->email->from('admin@comovaelmio.com','comovaelmio.com/index.php/libros');
		$this->email->to($correo);
		$this->email->subject('Has desistido del intercambio');
		$this->email->message('Has decidido no hacer el intercambio de'."\t".$titulo."\t".'por'."\t".$titulo2.', agradecemos tu participacion.');
		$this->email->send();
		$this->email->clear();
		#email a la persona que inicia el intercambio
		$this->email->from('admin@comovaelmio.com','comovaelmio.com/index.php/libros');
		$this->email->to($email);
		$this->email->subject('El intercambio de libros se ha anulado');
		$this->email->message($nombre."\t".'no ha aprobado el intercambio de' ."\t".$titulo."\t".'por'."\t".$titulo2.', por favor intenta un nuevo intercambio, agradecemos tu participacion');
		$this->email->send();

		$this->load->view('templates/header');
		$this->load->view('templates/navegacion');
		$this->load->view('libros/noaprobar');
		$this->load->view('templates/footer');
	}

	public function desistir($productoid)
	{
		//informacion de quien inicio el intercambio y titulo de los libros
		$data['cuadernos'] = $this->libros_modelo->noaprobar_libro($productoid);
		$email = $data['cuadernos']['email'];
		$nombre = $data['cuadernos']['nombre'];
		$titulo = $data['cuadernos']['titulo'];
		//email de a quien le propusieron el intercambio
		$correo = $data['cuadernos']['correo'];
		//$nombre2 = $data['cuadernos']['nombre2'];
		$titulo2 = $data['cuadernos']['titulo2'];

		#mandar email de aprobacion de intercambio
		$this->load->library('email');
		#Email a la persona a que tiene el libro
		$this->email->from('admin@comovaelmio.com','comovaelmio.com/index.php/libros');
		$this->email->to($correo);
		$this->email->subject('El intercambio de libros se ha anulado');
		$this->email->message($nombre.' ha desistido de hacer el intercambio de' ."\t".$titulo."\t".'por'."\t".$titulo2.', agradecemos tu participacion.');
		$this->email->send();
		$this->email->clear();
		#email a la persona que inicia el intercambio
		$this->email->from('admin@comovaelmio.com','comovaelmio.com/index.php/libros');
		$this->email->to($email);
		$this->email->subject('El intercambio de libros se ha anulado');
		$this->email->message('Has decidido no hacer el intercambio de libros, agradecemos tu participacion');
		$this->email->send();

		$this->load->view('templates/header');
		$this->load->view('templates/navegacion');
		$this->load->view('libros/noaprobar');
		$this->load->view('templates/footer');
	}

	public function video()
	{
		$usuarioid = $this->session->userdata('usuarioid');
		$this->load->view('templates/header');
		$this->load->view('templates/nav');
		if ($usuarioid){
		$this->load->view('video');
		}else
		{
		$this->load->view('video2');	
		}
		$this->load->view('templates/footer');
	}	

}

?>
