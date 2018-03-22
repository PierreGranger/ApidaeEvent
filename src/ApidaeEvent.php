<?php

	namespace PierreGranger ;

	use PierreGranger\ApidaeEcriture ;

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

	class ApidaeEvent extends ApidaeEcriture {

		public $mysqli ;
		private $mysqli_user ;
		private $mysqli_password ;
		private $mysqli_db ;

		private $projet_consultation_apiKey ;
		private $projet_consultation_projetId ;
		private $selection_territoires ;

		private $ressources_path ;

		private $method_elementsReference = 'json' ; // json (impossible via API pour l'instant)
		private $method_communes = 'json' ; // json|sql (impossible via API pour l'instant)
		private $method_territoires = 'api' ; // api


		public function __construct($params=null) {
			
			parent::__construct($params) ;

			if ( ! is_array($params) ) throw new \Exception('$params is not an array') ;

			if ( isset($params['mysqli_user']) ) $this->mysqli_user = $params['mysqli_user'] ; else throw new \Exception('missing mysqli_user') ;
			if ( isset($params['mysqli_password']) ) $this->mysqli_password = $params['mysqli_password'] ; else throw new \Exception('missing mysqli_password') ;
			if ( isset($params['mysqli_db']) ) $this->mysqli_db = $params['mysqli_db'] ; else throw new \Exception('missing mysqli_db') ;
			if ( ! $this->mysqli_connect() ) throw new \Exception('unabled to connect to mysqli') ;
			
			if ( isset($params['projet_consultation_apiKey']) ) $this->projet_consultation_apiKey = $params['projet_consultation_apiKey'] ; //else throw new \Exception('missing projet_consultation_apiKey') ;
			if ( isset($params['projet_consultation_projetId']) ) $this->projet_consultation_projetId = $params['projet_consultation_projetId'] ; //else throw new \Exception('missing projet_consultation_projetId') ;
			if ( isset($params['selection_territoires']) ) $this->selection_territoires = $params['selection_territoires'] ;

			if ( isset($params['ressources_path']) && is_dir($params['ressources_path']) ) $this->ressources_path = $params['ressources_path'] ;
			else $this->ressources_path = realpath(dirname(__FILE__)).'/../ressources/' ;

			if ( ! $this->getCommunes() ) throw new \Exception('Impossible de récupérer la liste des communes Apidae') ;
			if ( ! $this->getElementsReference() ) throw new \Exception('Impossible de récupérer la liste des ElementsReference') ;

			if ( $this->debugTime ) $this->debug('construct '.(microtime(true)-$start)) ;
		}

		private function mysqli_connect() {
			$this->mysqli = new \mysqli('localhost', $this->mysqli_user, $this->mysqli_password, $this->mysqli_db) ;
			if ($this->mysqli->connect_error) throw new \Exception('mysqli_connect failed') ;
			$this->mysqli->set_charset('utf8') ;
			return true ;
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
				$ret .= ' <select class="form-control chosen-select" ' ;
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

		function setTerritoires($force=false,$append=null)
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
							'count' => 200,
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
								$this->mysqli->query(' delete from apidae_territoires where id_territoire = "'.$this->mysqli->real_escape_string($r['id']).'" ') ;
								$ids_communes = Array() ;
								foreach ( $r['localisation']['perimetreGeographique'] as $c )
								{
									$ids_communes[] = $c['id'] ;
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

			if ( $this->debugTime ) $this->debug('setTerritoires '.(microtime(true)-$start)) ;
		}
		
		public function dateUs($dateFr)
		{
			if (\DateTime::createFromFormat('Y-m-d', $dateFr)) return $dateFr ;
			if (!$date = \DateTime::createFromFormat('d/m/Y', $dateFr)) return false ;
			return $date->format('Y-m-d') ;
		}

		public function verifDate($date1,$future=null)
		{
			$retour = true ;
			
			if (!\DateTime::createFromFormat('d/m/Y', $date1))
				if (!\DateTime::createFromFormat('Y-m-d', $date1))
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

	}