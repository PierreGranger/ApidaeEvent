<?php

/**
*	Class permettant de faciliter la génération d'un questionnaire de saisie de FMA libre pour Apidae
*
*	Cette classe facilite la génération du questionnaire en automatisant la récupération de certains éléments (elements_reference, communes)
*	ainsi que la récupération des tokens de l'API d'écriture.
*	Actuellement cette classe est incapable de générer le questionnaire web elle même, par manque de tant.
*	Idéalement à terme l'idée serait de pouvoir : 
*	*	Proposer le choix des champs du formulaire à la personne qui installe le questionnaire (ex: titre, adresse 1... etc), soit par une interface graphique soit via un fichier de configuration
*	*	Générer automatiquement la partie HTML du formulaire en fonction de la configuration ci-dessus
*	Pour l'instant la partie HTML est entièrement gérée à la main en dehors de cette classe et n'est donc pratiquement pas administrable.
*
* @author  Pierre Granger <pierre@pierre-granger.fr>
*
* @since 1.0
*
* @param int    $example  This is an example function/method parameter description.
* @param string $example2 This is a second example.
*/

	class ApidaeEvent {

		private static $url_api = Array(
			'preprod' => 'http://api.sitra2-vm-preprod.accelance.net/',
			'prod' => 'http://api.apidae-tourisme.com/'
		) ;

		private static $url_base = Array(
				'preprod' => 'http://sitra2-vm-preprod.accelance.net/',
				'prod' => 'https://base.apidae-tourisme.com/'
		) ;

		public $mysqli ;
		private $mysqli_user ;
		private $mysqli_password ;
		private $mysqli_db ;

		private $projet_consultation_apiKey ;
		private $projet_consultation_projetId ;
		private $selection_territoires ;

		private $projet_ecriture_clientId ;
		private $projet_ecriture_secret ;

		private $type_prod = 'prod' ;

		private $ressources_path ;

		public $skipValidation = 'false' ;

		private $method_elementsReference = 'json' ; // json (impossible via API pour l'instant)
		private $method_communes = 'json' ; // json|sql (impossible via API pour l'instant)
		private $method_territoires = 'api' ; // api

		public $debug ;
		public $statuts_api_ecriture = Array('CREATION_VALIDATION_SKIPPED','CREATION_VALIDATION_ASKED','MODIFICATION_VALIDATION_SKIPPED','MODIFICATION_VALIDATION_ASKED','MODIFICATION_NO_DIFF','DEMANDE_SUPPRESSION_SENT','NO_ACTION') ;

		private static $modes = Array('CREATION','MODIFICATION','DEMANDE_SUPPRESSION') ;

		private $_config ;

		public $debugTime = false ;

		public $last_id = null ;

		public function __construct($params=null) {
			
			if ( $this->debugTime ) $start = microtime(true) ;

			if ( ! is_array($params) ) throw new Exception('$params is not an array') ;

			if ( isset($params['mysqli_user']) ) $this->mysqli_user = $params['mysqli_user'] ; else throw new Exception('missing mysqli_user') ;
			if ( isset($params['mysqli_password']) ) $this->mysqli_password = $params['mysqli_password'] ; else throw new Exception('missing mysqli_password') ;
			if ( isset($params['mysqli_db']) ) $this->mysqli_db = $params['mysqli_db'] ; else throw new Exception('missing mysqli_db') ;
			if ( ! $this->mysqli_connect() ) throw new Exception('unabled to connect to mysqli') ;
			
			if ( isset($params['projet_consultation_apiKey']) ) $this->projet_consultation_apiKey = $params['projet_consultation_apiKey'] ; //else throw new Exception('missing projet_consultation_apiKey') ;
			if ( isset($params['projet_consultation_projetId']) ) $this->projet_consultation_projetId = $params['projet_consultation_projetId'] ; //else throw new Exception('missing projet_consultation_projetId') ;
			if ( isset($params['selection_territoires']) ) $this->selection_territoires = $params['selection_territoires'] ;

			if ( isset($params['debug']) && $params['debug'] == true ) $this->debug = $params['debug'] ; else $this->debug = false ;

			if ( isset($params['type_prod']) && in_array($params['type_prod'],Array('prod','preprod')) ) $this->type_prod = $params['type_prod'] ;

			if ( isset($params['ressources_path']) && is_dir($params['ressources_path']) ) $this->ressources_path = $params['ressources_path'] ;
			else $this->ressources_path = realpath(dirname(__FILE__)).'/ressources/' ;

			if ( ! $this->getCommunes() ) throw new Exception('Impossible de récupérer la liste des communes Apidae') ;
			if ( ! $this->getElementsReference() ) throw new Exception('Impossible de récupérer la liste des ElementsReference') ;

			if ( isset($params['projet_ecriture_clientId']) ) $this->projet_ecriture_clientId = $params['projet_ecriture_clientId'] ; else throw new Exception('missing projet_ecriture_clientId') ;
			if ( isset($params['projet_ecriture_secret']) ) $this->projet_ecriture_secret = $params['projet_ecriture_secret'] ; else throw new Exception('missing projet_ecriture_secret') ;

			if ( isset($params['skipValidation']) ) $this->skipValidation = ( $params['skipValidation'] ) ? true : false ;

			$this->_config = $params ;

			if ( $this->debugTime ) $this->debug('construct '.(microtime(true)-$start)) ;
		}

		private function mysqli_connect() {
			$this->mysqli = new mysqli('localhost', $this->mysqli_user, $this->mysqli_password, $this->mysqli_db) ;
			if ($this->mysqli->connect_error) throw new Exception('mysqli_connect failed') ;
		    $this->mysqli->set_charset('utf8') ;
		    return true ;
		}

		public function url_base() {
			return self::$url_base[$this->type_prod] ;
		}

		public function enregistrer($fieldlist,$root,$medias=null,$proprietaireId=null,$clientId=null,$secret=null,$action='CREATION',$idFiche=null,$token=null) {

			$ko = Array() ;

			$fields = Array('root') ;
			$params = Array() ;

			if ( ! in_array($action,self::$modes) )
			{
				throw new Exception('Action '.$action.' invalide') ;	
				return false ;
			}
			
			$params['mode'] = $action ;
			if ($params['mode']=='MODIFICATION' || $params['mode']=='DEMANDE_SUPPRESSION') {
				$params['id'] = $idFiche;
			}
			$params['skipValidation'] = $this->skipValidation ? 'true' : 'false' ;

			if ($params['mode'] != 'DEMANDE_SUPPRESSION')
			{
				$params['type'] = $root['type'] ;
				$params['root'] = json_encode($root) ;
				$params['fields'] = json_encode($fields) ;
				$params['root.fieldList'] = json_encode($fieldlist) ;

				if (!empty($proprietaireId))
					$params['proprietaireId'] = $proprietaireId;

				if ( isset($medias) && is_array($medias) )
					foreach ( $medias as $k_media => $media )
						$params[$k_media] = $media ;
			}
			
			$this->debug($fieldlist,'$fieldList') ;
			$this->debug($root,'$root') ;
			$this->debug($params,'$params') ;

			try {
				$token_ecriture = null ;

				$token_ecriture = $this->gimme_token($clientId,$secret) ;
				$this->debug($token_ecriture,'$token_ecriture') ;

				if ( ! $token_ecriture )
				{
					throw new Exception('Impossible de récupérer le token d\'écriture pour '.$clientId) ;
				}

				if ( ! isset($token_ecriture->access_token) )
				{
					throw new Exception('Le token d\'écriture n\'a pas pu être récupéré pour '.$clientId) ;	
				}
			}
			catch(Exception $e) {
				$msg = sprintf( 'Curl failed with error #%d: %s', $e->getCode(), $e->getMessage() ) ;
				if ( $this->debug ) echo '<div class="alert alert-warning">'.$msg.'</div>' ;
				return Array('errorCode'=>$e->getCode(),'message'=>$e->getMessage()) ;
			}
			
			try {
				
				$ch = curl_init();
				
				curl_setopt($ch,CURLOPT_URL, self::$url_api[$this->type_prod].'api/v002/ecriture/');
				
				$header = Array() ;
				$header[] = "Authorization: Bearer ".$token_ecriture->access_token ;
				curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
				curl_setopt($ch,CURLOPT_POSTFIELDS, ($params));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
				// http://dev.apidae-tourisme.com/fr/documentation-technique/v2/oauth/authentification-avec-un-token-oauth2
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
				
				$result = curl_exec($ch);
			
				if (FALSE === $result) throw new Exception(curl_error($ch), curl_errno($ch));
				
				$result = json_decode($result,true) ;
				if ( isset($result['id']) )
					$this->last_id = $result['id'] ;
				
			} catch(Exception $e) {
				$msg = sprintf( 'Curl failed with error #%d: %s', $e->getCode(), $e->getMessage() ) ;
				if ( $this->debug ) echo '<div class="alert alert-warning">'.$msg.'</div>' ;
			}
			
			curl_close($ch);

			$this->debug($result,'$result') ;
			
			if ( ! is_array($result) )
			{
				$ko[] = __LINE__.$result ;
			}
			elseif ( isset($result['errorType']) )
			{
				$ko[] = __LINE__.$result['errorType'] ;
				$ko[] = __LINE__.$result['message'] ;
			}

			if ( sizeof($ko) > 0 ) return $ko ;
			return true ;
		}

		public function ajouter($fieldlist,$root,$medias=null,$clientId=null,$secret=null,$token=null) {
			return $this->enregistrer($fieldlist,$root,$medias,$clientId,$secret,$action='CREATION',null,$token);
		}

		public function modifier($fieldlist,$root,$idFiche,$medias=null,$clientId=null,$secret=null,$token=null) {
			return $this->enregistrer($fieldlist,$root,$medias,$clientId,$secret,$action='MODIFICATION',$idFiche,$token);
		}

		public function supprimer($idFiche,$clientId=null,$secret=null,$token=null) {
			return $this->enregistrer(null,null,null,$clientId,$secret,$action='DEMANDE_SUPPRESSION',$idFiche,$token);
		}

		public function enregistrerDonneesPrivees($idFiche,$cle,$valeur,$lng='fr')
		{
			$donneesPrivees = Array('objetsTouristiques'=>Array()) ;

			/* Pour chaque objet touristique à modifer on peut avoir 1 ou plusieurs descriptifs privés à modifier. On va les stocker dans $descriptifsPrives. */
			$descriptifsPrives = Array() ;

			$descriptifsPrives[] = Array(
				'nomTechnique' => $cle,
				'descriptif' => Array(
					'libelle'.ucfirst($lng) => $valeur
				)
			) ;

			/* Pour chaque objet à modifier on ajoute une entrée dans $donneesPrivees['objetsTouristiques'] */
			$donneesPrivees['objetsTouristiques'][] = Array(
				'id' => $idFiche,
				'donneesPrivees' => $descriptifsPrives
			) ;

			/* On a construit notre tableau en php : on l'encode en json pour l'envoyer à l'API. */
			$POSTFIELDS = Array('donneesPrivees'=>json_encode($donneesPrivees)) ;

			try {
				$token_ecriture = null ;

				$token_ecriture = $this->gimme_token() ;
				$this->debug($token_ecriture,'$token_ecriture') ;

				if ( ! $token_ecriture )
				{
					throw new Exception('Impossible de récupérer le token d\'écriture') ;
				}

				if ( ! isset($token_ecriture->access_token) )
				{
					throw new Exception('Le token d\'écriture n\'a pas pu être récupéré pour') ;	
				}
			}
			catch(Exception $e) {
				$msg = sprintf( 'Curl failed with error #%d: %s', $e->getCode(), $e->getMessage() ) ;
				if ( $this->debug ) echo '<div class="alert alert-warning">'.$msg.'</div>' ;
				return Array('errorCode'=>$e->getCode(),'message'=>$e->getMessage()) ;
			}

			try {

				$ch = curl_init() ;
				curl_setopt($ch,CURLOPT_URL, self::$url_api[$this->type_prod].'api/v002/donnees-privees/');
				$header = Array() ;
				$header[] = "Authorization: Bearer ".$token_ecriture->access_token ;
				curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
				curl_setopt($ch,CURLOPT_POSTFIELDS, $POSTFIELDS);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
				
				$result = curl_exec($ch);
			
				if (FALSE === $result) throw new Exception(curl_error($ch), curl_errno($ch));
				
				$json_result = json_decode($result) ;
		 		$is_json =  ( json_last_error() == JSON_ERROR_NONE ) ;
		 		
				if ( ! $is_json )
				{
					return false ;
				}

				if ( $json_result->status == 'MODIFICATION_DONNEES_PRIVEES' )
				{
					return true ;
				}
				else
					return $json_result->status.' - '.$json_result->message ;

				curl_close($ch);

				return true ;
				
			} catch(Exception $e) {

				trigger_error(sprintf(
					'Curl failed with error #%d: %s',
					$e->getCode(), $e->getMessage()),
					E_USER_ERROR);

			}
		}

		/**
		*	Génère le code HTML pour un type d'élément de référence
		*
		*	Le code généré proposera des cases à cocher multiples.
		*	
		*	@param 	$type 	string 	
		**/
		public function formHtmlCC($type,$params=null,$post=null) {
			$ret = null ;

			if ( ! is_array($params) ) $params = Array() ;

			if ( $this->debugTime ) $start = microtime(true) ;

			if ( ! in_array(@$params['presentation'],Array('kilometre','select')) ) $params['presentation'] = 'kilometre' ;

			$familles = Array() ;
			$parents = Array() ;
			$enfants = Array() ;

			$rq = $this->mysqli->query(' select id, libelleFr, ordre, parent, familleCritere, description from apidae_elements_reference where elementReferenceType = "'.$this->mysqli->real_escape_string($type).'" order by ordre asc ') or die($this->mysqli->error) ;
			while ( $e = $rq->fetch_assoc() )
			{
				if ( $e['parent'] == null )
					$parents[$e['familleCritere']][] = $e ;
				else
					$enfants[$e['parent']][] = $e ;
				if ( $e['familleCritere'] != null ) $familles[$e['familleCritere']] = null ;
			}

			if ( sizeof($familles) > 0 )
			{
				$rq = $this->mysqli->query(' select id, libelleFr, ordre from apidae_elements_reference where id in ('.implode(',',array_keys($familles)).') order by ordre asc ') or die($this->mysqli->error) ;
				while ( $e = $rq->fetch_assoc() )
				{
					$familles[$e['id']] = $e ;
				}
			}
			array_push($familles,Array('id'=>null)) ;

			if ( $params['presentation'] == 'select' )
			{
				$ret .= ' <select class="form-control chosen" ' ;
				$ret .= ' data-placeholder=" " ' ;
				if ( @$params['type'] == 'unique' ) $ret .= ' name="'.$type.'" ' ;
				else $ret .= ' name="'.$type.'[]" multiple="multiple" ' ;
				if ( isset($params['max_selected_options']) ) $ret .= ' data-max_selected_options="'.$params['max_selected_options'].'" ' ;
				$ret .= '>' ;
					if ( @$params['type'] == 'unique' ) $ret .= '<option value="">-</option>' ;
					foreach ( $familles as $f )
					{
						if ( ! isset($parents[$f['id']]) ) continue ;
						if ( $f['id'] != null ) $ret .= '<optgroup label="'.htmlspecialchars($f['libelleFr']).'">' ;
							foreach ( $parents[$f['id']] as $p )
							{
								$ret .= '<option value="'.$p['id'].'"' ;
								//if ( isset($enfants[$p['id']]) ) $ret .= ' style="font-weight:strong;" ' ;
									if ( isset($p['description']) && $p['description'] != '' ) $ret .= 'title="'.htmlspecialchars($p['description']).'" ' ;
									if ( isset($post) && is_array($post) && in_array($p['id'],$post) ) $ret .= ' selected="selected"' ;
								$ret .= '>'.$p['libelleFr'].'</option>' ;
								if ( isset($enfants[$p['id']]) )
								{
									foreach ( $enfants[$p['id']] as $e )
									{
										$ret .= '<option value="'.$e['id'].'"' ;
										if ( isset($e['description']) && $e['description'] != '' ) $ret .= 'title="'.htmlspecialchars($e['description']).'" ' ;
										if ( isset($post) && is_array($post) && in_array($e['id'],$post) ) $ret .= ' selected="selected"' ;
										$ret .= '>'.$p['libelleFr'].' &raquo; '.$e['libelleFr'].'</option>' ;
									}
								}
							}
						if ( $f['id'] != null ) $ret .= '</optgroup>' ;
					}
				$ret .= '</select>' ;
			}
			elseif ( $params['presentation'] == 'kilometre' )
			{
				foreach ( $familles as $f )
				{
					if ( ! isset($parents[$f['id']]) ) continue ;
					if ( $f['id'] !== null )
					{
						$ret .= '<fieldset>' ;
							$ret .= '<legend>'.$f['libelleFr'].'</legend>' ;
					}

					foreach ( $parents[$f['id']] as $p )
					{
						$ret .= '<label class="label"' ;
							if ( isset($p['description']) ) $ret .= ' title="'.htmlentities($p['description']).'" ' ;
						$ret .= '>' ;
							$ret .= '<input type="checkbox" name="'.$type.'['.$p['id'].']" value="'.$p['id'].'" ' ;
							if ( isset($post) && is_array($post) && in_array($p['id'],$post) ) $ret .= ' checked="checked" ' ;
							$ret .= '/> '.$p['libelleFr'] ;
						$ret .= '</label>' ;
						if ( isset($enfants[$p['id']]) )
						{
							$ret .= '<fieldset>' ;
							foreach ( $enfants[$p['id']] as $e )
							{
								$ret .= '<label class="label"' ;
									if ( isset($e['description']) ) $ret .= ' title="'.htmlentities($e['description']).'" ' ;
								$ret .= '>' ;
									if ( @$params['type'] == 'unique' )
									{
										$ret .= '<input type="radio" name="'.$type.'['.$e['id'].']" value="'.$e['id'].'" ' ;
										if ( isset($post) && is_array($post) && in_array($e['id'],$post) ) $ret .= ' checked="checked" ' ;
										$ret .= ' />' ;
									}
									else
									{
										$ret .= '<input type="checkbox" name="'.$type.'['.$e['id'].']" value="'.$e['id'].'" ' ;
										if ( isset($post) && is_array($post) && in_array($e['id'],$post) ) $ret .= ' checked="checked" ' ;
										$ret .= ' />' ;
									}
									$ret .= ' '.$p['libelleFr'].' > '.$e['libelleFr'] ;
								$ret .= '</label>' ;
							}
							$ret .= '</fieldset>' ;
						}
					}

					if ( $f['id'] !== null ) $ret .= '</fieldset>' ;
				}
			}	

			if ( $this->debugTime ) $this->debug('formHtmlCC('.$type.') '.(microtime(true)-$start)) ;

			return $ret ;
		}

		/**
		*	Récupère la liste des communes d'Apidae
		*	
		*	On préfère récupérer la liste complète, même si c'est un peu gros, pour ne pas avoir à tester plus tard si telle ou telle commune est présente en fonction des besoins du formulaire.
		*
		*	Problème : actuellement, l'API ne permet de récupérer les communes que par lots de 500, et en passant en paramètre leur ID ou leur code INSEE.
		*	Pour l'instant on se base donc sur un export fixe parce qu'il serait démesuré de créer un projet de consultation, avec un export et une notification juste pour récupérer les communes.
		*	
		**/
		public function getCommunes($force=false) {

			if ( $this->debugTime ) $start = microtime(true) ;

			$rq = $this->mysqli->query(' select count(*) as nb from apidae_communes ') ;

			if ( ! $rq )
			{
				$this->mysqli->query('
					CREATE TABLE `apidae_communes` (
					  `id` int(11) PRIMARY KEY NOT NULL,
					  `code` varchar(20) NOT NULL,
					  `nom` varchar(255) NOT NULL,
					  `pays_id` int(11) NOT NULL,
					  `codePostal` varchar(10) NOT NULL
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;
				') ;
				$rq = $this->mysqli->query(' select count(*) as nb from apidae_communes ') ;
			}
			if ( $d = $rq->fetch_assoc() )
			{
				if ( $d['nb'] < 10 || $force )
				{
					$this->mysqli->query(' truncate table apidae_communes ') ;
					if ( $this->method_communes == 'json' )
					{
						$json = json_decode(file_get_contents($this->ressources_path.'/communes.json'),true) ;
						foreach ( $json as $element )
						{
							$sets = Array() ;
							$sets[] = ' id = "'.$this->mysqli->real_escape_string($element['id']).'" ' ;
							$sets[] = ' code = "'.$this->mysqli->real_escape_string($element['code']).'" ' ;
							$sets[] = ' nom = "'.$this->mysqli->real_escape_string($element['nom']).'" ' ;
							$sets[] = ' pays_id = "'.$this->mysqli->real_escape_string($element['pays']['id']).'" ' ;
							$sets[] = ' codePostal = "'.$this->mysqli->real_escape_string($element['codePostal']).'" ' ;
							$sql = ' insert into apidae_communes set '.implode(', ',$sets) ;
							$this->mysqli->query($sql) ;
						}
					}
					elseif ( $this->method_communes == 'sql' )
					{
						$sql = file_get_contents($this->ressources_path.'/apidae_communes.sql') ;
						$rq = $this->mysqli->multi_query($sql) or die($this->mysqli->error) ;
					}
					elseif ( $this->method_communes == 'api' )
					{
						return false ;
					}
				}
			}

			if ( $this->debugTime ) $this->debug('getCommunes '.(microtime(true)-$start)) ;

			$rq = $this->mysqli->query(' select count(*) as nb from apidae_communes ') ;
			if ( $d = $rq->fetch_assoc() )
				if ( $d['nb'] > 10 ) return true ;

			return false ;
		}

		/**
		*
		*	Récupère les valeurs possibles d'un type d'éléments de référence
		*
		*	Chaque élément du tableau renvoyé comporte les champs contenus dans le json ou dans la bdd :
		*	id
			elementReferenceType
			libelleFr
			ordre
			description
			familleCritere
			familleCritere_elementReferenceType
			parent
			parent_elementReferenceType
		*
		*	@param 		$type 	string Nom du type d'élément recherché (colonne elementReferenceType en base de donnée)
		*
		*	@return 	array 	Liste des élements (Chaque élément étant un array associatif issu de la base de donnée)
		*
		**/
		public function getElementsReference($type=null,$force=false,$filtres=null)
		{
			if ( $this->debugTime ) $start = microtime(true) ;

			$rq = $this->mysqli->query(' select count(*) as nb from apidae_elements_reference ') ;
			if ( ! $rq )
			{
				$this->mysqli->query('
					CREATE TABLE IF NOT EXISTS `apidae_elements_reference` (
					  `id` int(11) NOT NULL,
					  `elementReferenceType` varchar(255) NOT NULL,
					  `libelleFr` varchar(255) NOT NULL,
					  `ordre` int(11) NOT NULL,
					  `description` varchar(255) DEFAULT NULL,
					  `familleCritere` int(11) DEFAULT NULL,
					  `familleCritere_elementReferenceType` varchar(255) DEFAULT NULL,
					  `parent` int(11) DEFAULT NULL,
					  `parent_elementReferenceType` varchar(255) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;
				') or die($this->mysqli->error) ;
				$rq = $this->mysqli->query(' ALTER TABLE  `apidae_elements_reference` ADD INDEX (  `elementReferenceType` ) ') or die($this->mysqli->error) ;
				$rq = $this->mysqli->query(' select count(*) as nb from apidae_elements_reference ') or die($this->mysqli->error) ;
			}
			if ( $d = $rq->fetch_assoc() )
			{
				if ( $d['nb'] == 0 || $force )
				{
					$this->mysqli->query(' truncate table apidae_elements_reference ') ;
					if ( $this->method_elementsReference == 'json' )
					{
						$json = json_decode(file_get_contents($this->ressources_path.'/elements_reference.json'),true) ;
						foreach ( $json as $element )
						{
							if ( $element['actif'] !== true ) continue ;
							$sets = Array() ;
							$sets[] = ' elementReferenceType = "'.$this->mysqli->real_escape_string($element['elementReferenceType']).'" ' ;
							$sets[] = ' id = "'.$this->mysqli->real_escape_string($element['id']).'" ' ;
							$sets[] = ' libelleFr = "'.$this->mysqli->real_escape_string($element['libelleFr']).'" ' ;

							$sets[] = ' ordre = "'.$this->mysqli->real_escape_string($element['ordre']).'" ' ;
							if ( isset($element['description']) ) $sets[] = ' description = "'.$this->mysqli->real_escape_string($element['description']).'" ' ;
							if ( isset($element['parent']) )
							{
								$sets[] = ' parent = "'.$this->mysqli->real_escape_string($element['parent']['id']).'" ' ;
								$sets[] = ' parent_elementReferenceType = "'.$this->mysqli->real_escape_string($element['parent']['elementReferenceType']).'" ' ;
							}
							if ( isset($element['familleCritere']) )
							{
								$sets[] = ' familleCritere = "'.$this->mysqli->real_escape_string($element['familleCritere']['id']).'" ' ;
								$sets[] = ' familleCritere_elementReferenceType = "'.$this->mysqli->real_escape_string($element['familleCritere']['elementReferenceType']).'" ' ;
							}
							
							$this->mysqli->query(' insert into apidae_elements_reference set '.implode(', ',$sets)) ;
						}
					}
				}
			}

			if ( $this->debugTime ) $this->debug('getElementsReference '.(microtime(true)-$start)) ;

			if ( $type == null )
			{
				$rq = $this->mysqli->query(' select count(*) as nb from apidae_elements_reference ') ;
				if ( $d = $rq->fetch_assoc() )
					if ( $d['nb'] > 10 ) return true ;

				return false ;
			}

			$sql = ' select * from apidae_elements_reference where elementReferenceType = "'.$this->mysqli->real_escape_string($type).'" ' ;
			if ( is_array($filtres) )
			{
				$filtres_ok = array_filter($filtres,'is_numeric') ;
				if ( sizeof($filtres_ok) > 0 ) $sql .= ' and id in ('.implode(',',$filtres_ok).') ' ;
			}
			
			$sql .= ' order by ordre asc ' ;

			$rq = $this->mysqli->query($sql) ;
			if ( ! $rq ) return false ;
			if ( $rq->num_rows == 0 ) return false ;
			$er = Array() ;
			while ( $d = $rq->fetch_assoc() )
			{
				$er[$d['id']] = $d ;
			}
			return $er ;
		}

		function getTerritoires($force=false,$append=null)
		{
			if ( $this->debugTime ) $start = microtime(true) ;

			$rq = $this->mysqli->query(' select count(*) as nb from apidae_territoires ') ;
			if ( ! $rq )
			{
				$this->mysqli->free_result() ;
				$this->mysqli->multi_query('
					CREATE TABLE `apidae_territoires` (
					  `id_territoire` int(11) NOT NULL,
					  `id_commune` int(11) NOT NULL
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;
					ALTER TABLE `apidae_territoires`
  					ADD PRIMARY KEY (`id_territoire`,`id_commune`);
				') or die($this->mysqli->error) ;
				$rq = $this->mysqli->query(' select count(*) as nb from apidae_territoires ') or die($this->mysqli->error) ;
			}
			if ( $d = $rq->fetch_assoc() )
			{
				if ( $d['nb'] == 0 || $force )
				{
					if ( $this->method_territoires == 'api' )
					{
						$url = self::$url_api[$this->type_prod] . 'api/v002/recherche/list-objets-touristiques/';
						$fields = array(
							'apiKey' => $this->projet_consultation_apiKey,
							'projetId' => $this->projet_consultation_projetId,
							'count' => 20,
							'responseFields' => Array('id','localisation.perimetreGeographique.id')
						);

						if ( $this->selection_territoires !== null && $append == null )
							$fields['selectionIds'] = Array($this->selection_territoires) ;
						else
						{
							$territoires = Array() ;
							if ( isset($append) && is_array($append) )
								foreach ( $append as $t )
									if ( is_numeric($t) )
										$territoires[] = $t ;
							if ( isset($this->_config['territoire']) && is_numeric($this->_config['territoire']) ) $territoires[] = $this->_config['territoire'] ;
							if ( isset($this->_config['membres']) )
								foreach ( $this->_config['membres'] as $m )
									if ( isset($m['id_territoire']) && is_numeric($m['id_territoire']) )
										$territoires[] = $m['id_territoire'] ;
							$fields['identifiants'] = $territoires ;
						}

						$url = $url.'?query='.json_encode($fields) ;
						if ( ! ( $json = json_decode(file_get_contents($url),true) ) ) return false ;
						
						if ( ! isset($json['objetsTouristiques']) ) return false ;
						
						foreach ( $json['objetsTouristiques'] as $r )
						{
							if ( $r['type'] !== 'TERRITOIRE' ) continue ;
							if ( isset($r['localisation']['perimetreGeographique']) )
							{
								foreach ( $r['localisation']['perimetreGeographique'] as $c )
								{
									$sets = Array() ;
									$sets[] = ' id_territoire = "'.$this->mysqli->real_escape_string($r['id']).'" ' ;
									$sets[] = ' id_commune = "'.$this->mysqli->real_escape_string($c['id']).'" ' ;
									$this->mysqli->query(' insert into apidae_territoires set '.implode(', ',$sets).' on duplicate key update '.implode(', ',$sets)) ;
								}
							}
						}

						//close connection
						//curl_close($ch);
					}
				}
			}

			if ( $this->debugTime ) $this->debug('getTerritoires '.(microtime(true)-$start)) ;
		}




		function gimme_token($clientId=null,$secret=null)
		{
			$clientId = ( $clientId != null ) ? $clientId : $this->projet_ecriture_clientId ;
			$secret = ( $secret != null ) ? $secret : $this->projet_ecriture_secret ;

			$ch = curl_init() ;
			// http://stackoverflow.com/questions/15729167/paypal-api-with-php-and-curl
			curl_setopt($ch,CURLOPT_URL, self::$url_api[$this->type_prod].'oauth/token');
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($ch, CURLOPT_USERPWD, $clientId.":".$secret);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
			
			try {
				$token = curl_exec($ch);

				if ( $token === false ) throw new Exception(curl_error($ch), curl_errno($ch));
				else return json_decode($token) ;
			} catch(Exception $e) {
				trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
				return false ;
			}
			
			return false ;
		}

		public function dateUs($dateFr)
		{
			if (DateTime::createFromFormat('Y-m-d', $dateFr)) return $dateFr ;
			if (!$date = DateTime::createFromFormat('d/m/Y', $dateFr)) return false ;
			return $date->format('Y-m-d') ;
		}

		public function verifDate($date1,$future=null)
		{
			$retour = true ;
			
			if (!DateTime::createFromFormat('d/m/Y', $date1))
				if (!DateTime::createFromFormat('Y-m-d', $date1))
					return false ;
				
			// TODO : vérifier que $date1 est bien > date du jour
			if ( $future === true )
			{

			}
			// TODO : vérifier que $date est bien > $future
			elseif ( $future !== null && checkDate($future) )
			{

			}
			return $retour ;
		}

		public function verifTime($h)
		{
			if ( ! $match = preg_match('#^([0-9]{2}):([0-9]{2})$#',$h) ) return false ;
			if ( (int) $match[1] < 0 || (int) $match[1] > 24 ) return false ;
			if ( (int) $match[2] < 0 || (int) $match[2] > 60 ) return false ;
			return true ;
		}

		public function debug($var,$titre=null)
		{
			if ( ! $this->debug ) return ;
			echo '<p style="font-size:16px;font-weight:bold ;">[debug] '.(($titre!==null)?$titre:'').' / '.gettype($var).'</p>' ;
			echo '<textarea style="color:white;background:black;font-family:monospace;font-size:0.8em;width:100%;height:50px;">' ;
				if ( is_array($var) || is_object($var) || gettype($var) == 'boolean' ) echo var_dump($var) ;
				elseif ( $this->isJson($var) ) echo json_encode($var,JSON_PRETTY_PRINT) ;
				else echo $var ;
			echo '</textarea>' ;
		}

		// https://stackoverflow.com/questions/6041741/fastest-way-to-check-if-a-string-is-json-in-php
		private function isJson($string) {
			json_decode($string);
			return (json_last_error() == JSON_ERROR_NONE);
		}

		public function alerte($sujet,$msg,$mailto=null)
		{
			if ( ! filter_var($this->_config['mail_admin'], FILTER_VALIDATE_EMAIL) ) return false ;

			$from = $this->_config['mail_admin'] ;
			$to = $this->_config['mail_admin'] ;

			if ( isset($mailto) && $mailto != null && filter_var($mailto, FILTER_VALIDATE_EMAIL) )
				$to = $mailto ;

			$endline = "\n" ;
			$h1 = strip_tags(get_class($this).' - '.$sujet) ;
			$sujet = $h1 ;
			
			if ( is_array($msg) )
			{
				$new_msg = null ;
				if ( isset($msg['message']) )
				{
					$new_msg .= $msg['message'] ;
					unset($msg['message']) ;
				}
				unset($msg['x']) ; unset($msg['y']) ;
				$tble = '<table style="clear:both; background:#FFF ; font-size:11px ; margin-bottom:20px ;" border="1" cellspacing="0" cellpadding="6">' ;
				foreach ( $msg as $key => $value )
				{
					$tble .= '<tr>' ;
						$tble .= '<th><strong>'.ucfirst($key).'</strong></th>' ;
						$tble .= '<td>' ;
							if ( ! is_array($value) ) $tble .= stripslashes(nl2br($value)) ;
							else
							{
								$tble .= '<pre>'.print_r($value,true).'</pre>' ;
							}
						$tble .= '</td>' ;
					$tble .= '</tr>' ;
				}
				$tble .= '</table>' ;
				$new_msg .= $tble ;
				$msg = $new_msg ;
			}

			$message_html = '<html style="text-align : center; margin : 0; padding:0 ; font-family:Verdana ;font-size:10px ;">'.$endline  ;
				$message_html .= '<div style="text-align:left ;">'.$endline ;
					$message_html .= '<div>'.$msg.'</div>'.$endline ;
				$message_html .= '</div>'.$endline ;
			$message_html .= '</html>'.$endline ;
			
			$message_texte = strip_tags(nl2br($message_html)) ;
			
			$boundary = md5(time()) ;
			
			$entete = Array() ;
			$entete['From'] = $from . '<'.$from.'>' ;
			$entete['Date'] = @date("D, j M Y G:i:s O") ;
			$entete['X-Mailer'] = 'PHP'.phpversion() ;
			$entete['MIME-Version'] = '1.0' ;
			$entete['Content-Type'] = 'multipart/alternative; boundary="'.$boundary.'"' ;
			
			$message = $endline ;
			$message .= $endline."--".$boundary.$endline ;
			$message .= "Content-Type: text/plain; charset=\"utf-8\"".$endline ;
			$message .= "Content-Transfer-Encoding: 8bit".$endline ;
			$message .= $endline.strip_tags(nl2br($message_html)) ;
			$message .= $endline.$endline."--".$boundary.$endline ;
			$message .= "Content-Type: text/html; charset=\"utf-8\"".$endline ;
			$message .= "Content-Transfer-Encoding: 8bit;".$endline ;
			$message .= $endline.$message_html ;
			$message .= $endline.$endline."--".$boundary."--";
			
			$header = null ;
			foreach ( $entete as $key => $value )
			{
				$header .= $key . ' : ' . $value . $endline ;
			}
			
			if ( ! preg_match("#\r#i",$to) && ! preg_match("#\n\r#i",$to) && ! preg_match("#\r#i",$from) && ! preg_match("#\n\r#i",$from) )
				$ret = @mail($to,$sujet,$message,$header) ;
			else
				$ret = false ;
			
			return $ret ;
		}

	}