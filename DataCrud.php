<?

	require 'creditoClass.php';


	class DataGridEasyUi {
		private $table;
		private $id;
		private $listColumn = array(); //Nombre de columnas
		private $cantVariables; //Cantidad de Datos retornados desde la url
		private $request = array(); //Valores en la URL
		protected $query; 
		private $con;
		private $listRows = array();
		private $page; 
		private $rows;


        //Actualiza la pagina del datagrid
		public function setPage($p){
			$this->page = $p;
		}


		//Actualiza la cantidad de fila del datagrid
		public function setRows($r){
			$this->rows = $r;
		}

		public function __construct(){
			@ $this->con = new Mysqli("localhost","root","admin123","coopfuna");
			if (!$this->con->connect_errno){
				$this->request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
				$this->table = $this->request[0];
				$this->cantVariables = count($this->request);
			}
			else {
				echo "Problema al conectar a la base de datos";
			}
			
		}

		/*public function verifiedMultipleTables(){
			$requestIni = substr($this->request[1],0,1);
			if ($requestIni && "["){
				$this->table = substr($this->request[1],0,strlen($this->request[1])-1);
				$tables[] = explode(",", $this->table);
				foreach ($tables as $key => $value) {
					
				}
			}
		}*/

		//Retorna los registros buscados
		public function get(){
			if ($this->verifiedId()){
				$this->getRegistro();
				$cantRows = 1;
			}
			else {
				$this->getTable();
				$cantRows = $this->getCountRegistros();
			}
			$query = $this->con->query($this->query);

			while($this->listRows[] = $query->fetch_assoc());
			array_pop($this->listRows);

			return "{\"total\":\"".$cantRows."\",\"rows\":".json_encode($this->listRows)."}";
		}



		//Retorna un solo registro
		public function getRegistro(){
			$this->getDataNameColumn();
			$idReg = $this->listColumn[0];
			
			$this->query = "SELECT * FROM $this->table WHERE $idReg = $this->id ORDER BY $idReg DESC";
		}

		//Retorna la cantidad de registros
		public function getCountRegistros(){
			$query = $this->con->query("SELECT COUNT(*) AS CantidadRows FROM $this->table");
			$reg = $query->fetch_assoc();
			return $reg['CantidadRows'];

		}

		//Retorna TODOS los registros de la tabla
		public function getTable(){
			$offset = ($this->page - 1) * $this->rows;
			$this->query = "SELECT * FROM $this->table LIMIT $offset,$this->rows";
		}

		//Verifica que la cantidad de variable en la url sean mas de uno
		//En dicho caso asigna el valor de ID
		public function verifiedId(){
			if($this->cantVariables > 1) {
				$this->id = $this->request[1];
				return 1;
			}	
			return 0;
		}

		// Inserta un nuevo registro en la tabla
		public function set($data = array()){
			$this->getDataNameColumn();
			$columnName = implode(",", $this->listColumn);
			$valueData = implode(",", $data);
			$this->query = "INSERT INTO $this->table($columnName) VALUES($valueData)";
		}



		//Edita la tabla en cuestion en base al id
		public function edit($data){
			$this->getDataNameColumn();
			$idReg = $this->listColumn[0];
			$addQuery = array();
			$this->query = "UPDATE $this->table SET ";

			foreach ($data as $columnName => $value) {
				$addQuery[] = "$columnName = $value";
			}
			$this->query .= implode(",", $addQuery);
			$this->query .= " WHERE $idReg = $this->id";


		}

		//Elimina en base a la table el id Pasado
		public function remove($id){
			$this->id = $id;
			
			$this->getDataNameColumn();
			$idReg = $this->listColumn[0];
			$this->query = "DELETE FROM $this->table WHERE $idReg = $this->id";
			$consulta = $this->con->query($this->query);

			if ($consulta != 0){
				return 1;	
			}
			return 0;

		}


		//Retorna los nombre de todas las columnas de una tabla
		private function getDataNameColumn(){
			$this->query = "SELECT * FROM $this->table";
			$query = $this->con->query($this->query);
			$registro = $query->fetch_assoc();
			foreach ($registro as $columnName => $value) {
				$this->listColumn[] = $columnName;
			}
		}

	}

?>
