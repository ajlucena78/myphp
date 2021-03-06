<?php
	class ContenidoAction extends Action
	{
		protected $menuService;
		protected $contenidoService;
		protected $contenidoFicheroService;
		protected $contenidoTextoService;
		protected $contenidoCorreoService;
		protected $contenidoEnlaceService;
		protected $contenidoImagenService;
		protected $contenidoArchivoService;
		protected $usuarioService;
		protected $contenido;
		protected $menus;
		protected $menuContenido;
		protected $menuContenidoPadre;
		protected $titulo;
		protected $MENU_01;
		protected $enlace;
		protected $imagen;
		protected $contenidos;
		protected $menuCont;
		
		public function index()
		{
			//carga del módulo adecuado
			if (isset($_GET['permalink']) and $_GET['permalink'] and !isset($_GET['referencia']))
			{
				//mediante el permalink
				$_GET['permalink'] = $_GET['permalink'];
				if (substr($_GET['permalink'], strlen($_GET['permalink']) - 1, 1) == "/")
					$_GET['permalink'] = substr($_GET['permalink'], 0, strlen($_GET['permalink']) - 1);
				$contenido = new Contenido();
				$contenido->permalink = $_GET['permalink'];
				$this->contenido = $this->contenidoService->find($contenido);
				if ($this->contenido === false)
				{
					$this->error = $this->contenidoService->error();
					return 'error';
				}
				if (!$this->contenido)
				{
					//se compruueba si es una imagen
					$this->imagen = new ContenidoImagen();
					$this->imagen->permalink = $_GET['permalink'];
					$this->imagen = $this->contenidoImagenService->find($this->imagen);
					if (!isset($this->imagen[0]) or !$this->imagen[0])
					{
						$this->error = 'No se encuentra el contenido: ' . $_GET['permalink'];
						return 'error';
					}
					else
					{
						//se trata de una imagen
						$this->imagen = $this->imagen[0];
						$this->menus = $this->menuService->menus_index();
						$this->titulo = $this->imagen->titulo;
						//se carga el contenido si estuviera asociado a uno de tipo texto
						$this->contenido = $this->imagen->contenido;
						if ($this->contenido and $this->contenido and $this->contenido->tipo == CONTENIDO_TEXTO)
						{
							if ($this->contenido->privado)
							{
								//si la imagen pertenece a una página privada se comprueba si el usuario está logado
								$this->usuarioService->check_socio();
							}
							$this->contenido = $this->contenidoTextoService->findById($this->contenido->idContenido);
						}
						else
						{
							$this->contenido = null;
						}
						return 'imagen';
					}
				}
			}
			else
			{
				//mediante la referencia
				$contenido = new Contenido();
				if (!isset($_GET['referencia']) or !$_GET['referencia'])
				{
					$referencia = 'index_';
				}
				else
				{
					$referencia = $_GET['referencia'];
				}
				$contenido->referencia = $referencia;
				$this->contenido = $this->contenidoService->find($contenido);
				if ($this->contenido === false)
				{
					$this->error = $this->contenidoService->error();
					return 'error';
				}
				if (!$this->contenido)
				{
					$this->error = 'No se encuentra el contenido de referencia: ' . $referencia;
					return 'error';
				}
			}
			$this->contenido = $this->contenido[0];
			
			//comprobación de contenido privado
			if ($this->contenido->privado)
			{
				$this->usuarioService->check_socio();
			}
			
			//tipo enlace
			if ($this->contenido->tipo == CONTENIDO_ENLACE)
			{
				$contenido = $this->contenidoEnlaceService->findById($this->contenido->idContenido);
				if ($contenido === false)
				{
					$this->error = $this->contenidoEnlaceService->error();
					return 'error';
				}
				if (!$contenido)
				{
					$this->error = 'El enlace no se encuentra';
					return 'error';
				}
				if ($contenido->tipoEnlace == 1)
				{
					//enlace directo a otra dirección
					header('HTTP/1.1 301 Moved Permanently');
					header('Location: ' . $contenido->url);
					exit();
				}
				elseif ($contenido->tipoEnlace == 2)
				{
					//duplicado de página
					$cont = new Contenido();
					$cont->permalink = $contenido->url;
					$this->contenido = $this->contenidoService->find($cont);
					if (!$this->contenido)
					{
						$this->error = 'Contenido de destino ' . $contenido->permalink . ' no encontrado.';
						return 'error';
					}
					$this->contenido = $this->contenido[0];
					$this->enlace = $contenido;
				}
			}
			
			//carga del menú al que pertenece el contenido
			$menu = new Menu();
			$menu->contenido = $this->contenido;
			$menu = $this->menuService->find($menu);
			if ($menu === false)
			{
				$this->error = $this->menuService->error();
				return 'error';
			}
			if ($menu)
			{
				$this->menuCont = $menu = $menu[0];
				if (isset($_GET['idMenu']) and ($_GET['idMenu'] += 0) > 0 and $menu->padre)
				{
					$idPadre = $_GET['idMenu'];
					$idMenuContenido = $menu->padre->idMenu;
					$menu->padre = $this->menuService->findById($idPadre);
					if ($menu->padre === false)
					{
						$this->error = $this->menuService->error();
						return 'error';
					}
					if (!$menu->padre)
					{
						$this->error = 'No se localiza el menú superior al que pertenece el contenido';
						return 'error';
					}
				}
				elseif ($menu->padre)
				{
					$idPadre = $menu->padre->idMenu;
				}
				if (count($menu->hijos) == 0 and $menu->padre and $menu->padre->idMenu)
				{
					if (!$menu->padre)
					{
						$menu = $this->menuService->findById($idMenuContenido);
						if (!$menu)
						{
							$this->error = 'No se localiza el menú padre';
							return 'error';
						}
					}
					else
						$menu = $menu->padre;
					if (!$menu->padre)
					{
						$this->MENU_01 = $menu->idMenu;
					}
					else
					{
						//carga de los submenús del abuelo
						$menuAbuelo = $menu->padre;
						if (!$menuAbuelo->padre or !$menuAbuelo->padre->idMenu)
						{
							$this->MENU_01 = $menuAbuelo->idMenu;
						}
						$this->menuContenidoPadre = $menuAbuelo;
					}
				}
				else
					$this->MENU_01 = $menu->idMenu;
				$this->menuContenido = $menu;
			}
			else
				$idPadre = 0;
			
			//carga de todas las opciones de menú del primer nivel (raíz)
			$this->menus = $this->menuService->menus_index();
			
			//carga del contenido
			$verMenu = true;
			if ($this->contenido->tipo == CONTENIDO_FICHERO)
			{
				//inclusión de fichero
				$this->contenido = $this->contenidoFicheroService->findById($this->contenido->idContenido);
				if ($this->contenido === false)
				{
					$this->error = $this->contenidoFicheroService->error();
					return 'error';
				}
				if (!$this->contenido)
				{
					$this->error = 'No se puede incluir el fichero';
					return 'error';
				}
				if (!$this->contenido->menu)
					$verMenu = false;
			}
			elseif ($this->contenido->tipo == CONTENIDO_MENSAJE)
			{
				//inclusión de fichero
				$this->contenido = $this->contenidoCorreoService->findById($this->contenido->idContenido);
				if ($this->contenido === false)
				{
					$this->error = $this->contenidoCorreoService->error();
					return 'error';
				}
				if (!$this->contenido)
				{
					$this->error = 'No se puede encontrar el mensaje';
					return 'error';
				}
				$verMenu = false;
			}
			else
			{
				//texto
				$this->contenido = $this->contenidoTextoService->findById($this->contenido->idContenido);
				if ($this->contenido === false)
				{
					$this->error = $this->contenidoTextoService->error();
					return 'error';
				}
				if (!$this->contenido)
				{
					$this->error = 'No se puede localizar la página';
					return 'error';
				}
			}
			$this->titulo = $this->contenido->descripcion;
			
			//ruta al contenido
			if (isset($idPadre) and $idPadre)
			{
				$menu = new Menu();
				$menu->idMenu = $idPadre;
			}
			else
				$menu = null;
			$ruta_view = $this->contenido->ruta($menu);
			if ($ruta_view === false)
			{
				$this->error = $this->contenido->error();
				return 'error';
			}
			return 'success';
		}
		
		public function baja()
		{
			if (!$this->usuarioService->check_usuario())
			{
				return 'inicio-sesion-adm';
			}
			if (isset($_POST['id']) and $_POST['id'] > 0)
				$id = $_POST['id'];
			elseif (isset($_GET['id']) and $_GET['id'] > 0)
				$id = $_GET['id'];
			else
			{
				$this->error = 'Falta el dato idContenido a enviar por GET o POST';
				return 'fatal';
			}
			$this->contenido = $this->contenidoService->findById($id);
			if ($this->contenido === false)
			{
				$this->error = $this->contenidoService->error();
				return 'fatal';
			}
			if (!$this->contenido)
			{
				$this->error = 'El contenido indicado para borrar no existe';
				return 'fatal';
			}
			if (isset($_POST['borrar']))
			{
				if (!$this->contenidoService->removeById($this->contenido->idContenido))
				{
					$this->error = $this->contenidoService->error();
					return 'error';
				}
				else
				{
					if ($this->contenido->tipo == CONTENIDO_ENLACE)
					{
						return 'enlaces';
					}
					elseif ($this->contenido->tipo == CONTENIDO_MENSAJE)
					{
						return 'mensajes';
					}
					elseif ($this->contenido->tipo == CONTENIDO_OFERTA)
					{
						return 'ofertas';
					}
					else
					{
						return 'paginas';
					}
				}
			}
			return 'success';
		}
		
		public function mas_fotos_movil()
		{
			if (!isset($_GET['id']) or !($_GET['id'] + 0))
				return 'error';
			if (!isset($_GET['cont']) or !($_GET['cont'] + 0))
				return 'error';
			$this->contenido = $this->contenidoTextoService->findById($_GET['id']);
			if (!$this->contenido)
				return 'error';
			return 'success';
		}
		
		public function sitemap()
		{
			$contenidos = $this->contenidoService->findAll();
			$this->contenidos = array();
			foreach ($contenidos as $contenido)
			{
				if ($contenido->tipo != CONTENIDO_MENSAJE)
					$this->contenidos[] = $contenido;
			}
			header('Content-Type:text/xml');
			return 'success';
		}
	}