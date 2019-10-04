<?php
/**
 * Documentation pour le fichier ApidaeMembres.php
 * 
 * 
*/
	nameSpace PierreGranger ;

/**
 * Documentation pour la classe ApidaeMembres
 * 
 * @author	Pierre Granger	<pierre@pierre-granger.fr>
 * 
 * 
 */
class ApidaeMembres {
  
	protected static $url_api = Array(
		'preprod' => 'https://api.apidae-tourisme-recette.accelance.net/',
		'prod' => 'https://api.apidae-tourisme.com/'
	) ;

	protected static $url_base = Array(
		'preprod' => 'https://base.apidae-tourisme-recette.accelance.net/',
		'prod' => 'https://base.apidae-tourisme.com/'
	) ;

	protected $type_prod = 'prod' ;

	protected $projet_consultation_projetId = null ;
	protected $projet_consultation_apiKey = null ;

	public static $idCRT = Array(1,1157) ; // Identifiants des membres Auvergne - Rhône-Alpes Tourisme et Apidae Tourisme

	private $servicesMU = Array(
		'GET' => Array('utilisateur/get-by-id','utilisateur/get-by-mail','utilisateur/get-by-membre','utilisateur/get-all-utilisateurs','membre/get-by-id'),
		'POST' => Array('membre/get-membres')
	) ;

	protected $_config ;

	private $debug = false ;

	private $curlConnectTimeout = 120 ;
	private $curlTimeout = 120 ;

	const EXCEPTION_NOT_IN_MEMBRES = 1 ;
	const EXCEPTION_NOT_FILLEUL = 2 ;
	const EXCEPTION_NO_PERMISSION = 3 ;

	public function __construct(array $params=null) {
		
		if ( isset($params['projet_consultation_projetId']) && preg_match('#^[0-9]+$#',$params['projet_consultation_projetId']) )
			$this->projet_consultation_projetId = $params['projet_consultation_projetId'] ;
		else
			throw new \Exception('missing projet_consultation_projetId') ;
		
		if ( isset($params['projet_consultation_apiKey']) && preg_match('#^[a-zA-Z0-9]{1,20}$#',$params['projet_consultation_apiKey']) )
			$this->projet_consultation_apiKey = $params['projet_consultation_apiKey'] ;
		else
			throw new \Exception('missing projet_consultation_apiKey') ;

		if ( isset($params['type_prod']) && in_array($params['type_prod'],Array('prod','preprod')) ) $this->type_prod = $params['type_prod'] ;

		if ( isset($params['debug']) ) $this->debug = $params['debug'] == true ;

		$this->_config = $params ;

	}

	private function url_base() {
		return self::$url_base[$this->type_prod] ;
	}

	private function url_api() {
		return self::$url_api[$this->type_prod] ;
	}

	/**
	 * Récupère un Array des membres selon le $filter
	 * 
	 * @since	1.0
	 * 
	 * @return	array	Tableau associatif des membres
	 */
	public function getMembres(array $filter,array $responseFields=null)
	{
		$query = Array(
			'projetId'=>$this->projet_consultation_projetId,
			'apiKey'=>$this->projet_consultation_apiKey,
			'filter'=>$filter
		) ;
		if ( isset($responseFields) && $responseFields != null && is_array($responseFields) )
			$query['responseFields'] = $responseFields ;

		return $this->apidaeCurlMU('membre/get-membres',$query) ;
	}

	/**
	 * Récupère la liste des filleuls selon l'idParrain
	 * 
	 * @param int $idParrain	int	Identifiant du membre parrain
	 * @return array Tableau associatif des membres filleuls
	 */
	public function getFilleuls(int $idParrain,array $types=null)
	{
		
		$filter = Array('idParrain'=>$idParrain) ;
		if ( $types == null || ! is_array($types) ) $types = Array('Contributeur Généraliste') ;
		if ( is_array($types) && sizeof($types) > 0 ) $filter['types'] = $types ;
		$responseFields = Array("UTILISATEURS") ;
		return $this->getMembres($filter,$responseFields) ;
	}
	/**
     * Récupération d'un utilisateur par son identifiant, via le service get-by-id de l'API Membres/utilisateurs d'Apidae
     * @param int	$id_user
     * @return array 
     */
	public function getUserById(int $id_user)
	{
		if ( ! preg_match('#^[0-9]+$#',$id_user) ) throw new \Exception(__LINE__." Invalid id_user for getUserById : ".$id_user) ;

		$query = Array(
			//'projetId'=>$this->projet_consultation_projetId,
			'projetId'=>$this->projet_consultation_projetId,
			'apiKey'=>$this->projet_consultation_apiKey
		) ;

		return $this->apidaeCurlMU('utilisateur/get-by-id',$query,$id_user) ;
	}
	public function getUtilisateur($var) { return $this->getUser($var) ; }

	public function getUser($var) {
		if ( is_int($var) ) return $this->getUserById($var) ;
		elseif ( ( strpos($var,'@') ) !== false ) return $this->getUserByMail($var) ;
		return false ;
	}

	/**
     * Récupération d'un utilisateur par son adresse mail, via le service get-by-mail de l'API Membres/utilisateurs d'Apidae
     * @param string	$mail_user
     * @return array 
     */
	public function getUserByMail(string $mail_user)
	{
		if ( false === filter_var($mail_user, FILTER_VALIDATE_EMAIL) ) throw new \Exception(__LINE__." Invalid mail_user for getUserByMail : ".$mail_user) ;

		$params = Array(
			//'projetId'=>$this->projet_consultation_projetId,
			'projetId'=>$this->projet_consultation_projetId,
			'apiKey'=>$this->projet_consultation_apiKey
		) ;

		return $this->apidaeCurlMU('utilisateur/get-by-mail',$params,$mail_user) ;
	}

	/**
     * Récupération de la liste des utilisateurs d'un membre par son identifiant, via le service get-by-membre de l'API Membres/utilisateurs d'Apidae
     * @param int	$id_membre
     * @return array 
     */
	public function getUsersByMember(int $id_membre)
	{
		if ( ! preg_match('#^[0-9]+$#',$id_membre) ) throw new \Exception(__LINE__.' Invalid id_membre for '.__FUNCTION__.' : '.$id_membre) ;

		$params = Array(
			//'projetId'=>$this->projet_consultation_projetId,
			'projetId'=>$this->projet_consultation_projetId,
			'apiKey'=>$this->projet_consultation_apiKey
		) ;

		return $this->apidaeCurlMU('utilisateur/get-by-membre',$params,$id_membre) ;
	}

	/**
	 *	Récupère un membre en fonction de son identifiant
	 * 	@param int $id_membre
	 * 	@return array
	 */
	public function getMembreById(int $id_membre,array $responseFields=null)
	{
		if ( ! preg_match('#^[0-9]+$#',$id_membre) ) throw new \Exception(__LINE__.' Invalid id_membre for '.__FUNCTION__.' : '.$id_membre) ;

		$query = Array(
			'projetId'=>$this->projet_consultation_projetId,
			'apiKey'=>$this->projet_consultation_apiKey
		) ;
		if ( isset($responseFields) && $responseFields != null && is_array($responseFields) )
			$query['responseFields'] = $responseFields ;

		return $this->apidaeCurlMU('membre/get-by-id',$query,$id_membre) ;
	}
	public function getMembre(int $id_membre,array $responseFields=null) { return $this->getMembreById($id_membre,$responseFields) ; }

	/**
	 * Gestion des appels cURL aux API membres et utilisateurs d'Apidae
	 * 
	 * @param	string	$service	Service (chemin relatif)
	 * @param	array	$params	Liste de paramètres qui seront envoyées en cURL : en POST elles seront converties via json_encode, en GET elles seront converties via http_build_query.
	 */
	private function apidaeCurlMU(string $service,array $params=null,string $page=null)
	{
		$debug = $this->debug ;

		$method = null ;
		if ( in_array($service,$this->servicesMU['GET']) ) $method = 'GET' ;
		elseif ( in_array($service,$this->servicesMU['POST']) ) $method = 'POST' ;
		else throw new \Exception(__LINE__." Invalid function for apidaeCurl : ".$service) ;
		
			$ch = curl_init();
			
			//curl_setopt($ch, CURLOPT_HTTPHEADER, Array('Content-Type: application/json')); // Erreur 415 sans cette ligne
			//curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain')); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			//curl_setopt($ch, CURLOPT_HEADER, 1) ;
			

			$url_base = $this->url_api().'api/v002/'.$service.'/' ;
			$url = $url_base ;
			if ( $page !== null && preg_match('#^[a-zA-Z0-9\@\.-]+$#',$page) ) $url .= $page ;
			
			if ( $method == 'GET' ) $url .= '?'.http_build_query($params) ;
			curl_setopt($ch,CURLOPT_URL, $url) ;
			
			if ( $method == 'POST' )
			{
				curl_setopt($ch, CURLOPT_POST, 1) ;
				$postfields = 'query='.json_encode($params) ;
				curl_setopt($ch,CURLOPT_POSTFIELDS, $postfields);
			}
			
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->curlConnectTimeout); 
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->curlTimeout); //timeout in seconds

			if ( $debug )
			{
				echo '<pre style="background:black;color:white;">' ;
					echo $method . PHP_EOL ; 
					if ( $method =='POST' )
					{
						echo $url.PHP_EOL ;
						echo $postfields ;
					}
					else
						echo $url ;
				echo '</pre>' ;
			}

			$response = curl_exec($ch);
			$info = curl_getinfo($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	

			if (FALSE === $response) throw new \Exception(curl_error($ch), curl_errno($ch));

			//$header = substr($response, 0, $info['header_size']);
			//$body = substr($response, -$info['download_content_length']);
			$body = $response ;

			if ( $httpcode != 200 ) 
			{
				if ( $this->debug )
					throw new \Exception($url_base.PHP_EOL.json_encode($params).PHP_EOL.$body, $httpcode);
				else
					throw new \Exception($url_base, $httpcode); // On affichage l'url_base et non $url parce qu'il peut contenir l'apiKey et projetId
			}
			
			$ret = json_decode($body,true) ;

			$json_last_error = json_last_error() ;
			if ( $json_last_error !== JSON_ERROR_NONE )
			{
				if ( $this->debug )
					throw new \Exception('cURL Return is not JSON ['.$json_last_error.'] : '.$url_base.PHP_EOL.json_encode($params).PHP_EOL.$body);
				else
					throw new \Exception('cURL Return is not JSON');
			}

			return $ret ;

	}

	/**
	 * L'utilisateur a-t-il les droits demandés ?
	 * @param	$utilisateurApidae	Array	Utilisateur Apidae défini par l'authentification SSO
	 * @param	$droits	Array	Droits qu'on va rechercher sur cet utilisateur
	 * 							$droits['membres']	Array	Le membre de l'utilisateur est-il dans la liste $droits['membres'] ? (liste d'identifiants numériques)
	 * 							$droits['filleuls']	Integer	Le membre de l'utilisateur est-il filleur du membre $droits['filleuls'] ?
	 * 							$droits['permissions']	Array	L'utilisateur a-t-il les permissions $droits['permissions'] ? (liste de string)
	 * 
	 */
	public function droits($utilisateurApidae,$droits) {

		$return = true ;

		foreach ( $droits as $droit => $valeurs )
		{
			if ( $droit == 'membres' )
			{
				if ( ! in_array($utilisateurApidae['membre']['id'],$valeurs) )
					return false ;
			}

			if ( $droit == 'filleuls' )
			{
				$filleuls = $this->getFilleuls($valeurs) ;
				if ( ! in_array($utilisateurApidae['membre']['id'],$filleuls) )
					return false ;
			}

			if ( $droit == 'permissions' )
			{
				$usr = $this->getUserById($utilisateurApidae['id']) ;
				foreach ( $valeurs as $p )
				{
					if ( ! in_array($p,$valeurs) )
						return false ;
				}
			}
		}

		return $return ;

	}

}