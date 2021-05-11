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
*/

	class ApidaeEvent extends ApidaeEcriture {

		private $projet_consultation_apiKey ;
		private $projet_consultation_projetId ;
		private $selection_territoires ;

		private $ressources_path ;

		private $method_elementsReference = 'json' ; // json (impossible via API pour l'instant)
		private $method_communes = 'json' ; // json|sql (impossible via API pour l'instant)
		private $method_territoires = 'api' ; // api

		private $mc ;
		private $mc_expiration = 2592000 ; // 2592000 = 30 jours

		public function __construct($params=null) {
			
			parent::__construct($params) ;

			if ( ! is_array($params) ) throw new \Exception('$params is not an array') ;
			
			if ( isset($params['projet_consultation_apiKey']) ) $this->projet_consultation_apiKey = $params['projet_consultation_apiKey'] ; //else throw new \Exception('missing projet_consultation_apiKey') ;
			if ( isset($params['projet_consultation_projetId']) ) $this->projet_consultation_projetId = $params['projet_consultation_projetId'] ; //else throw new \Exception('missing projet_consultation_projetId') ;
			if ( isset($params['selection_territoires']) ) $this->selection_territoires = $params['selection_territoires'] ;

			if ( isset($params['ressources_path']) && is_dir($params['ressources_path']) ) $this->ressources_path = $params['ressources_path'] ;
			else $this->ressources_path = realpath(dirname(__FILE__)).'/../ressources/' ;

			if ( ! class_exists('Memcached') ) throw new \Exception('Classe Memcached introuvable sur le serveur') ;
			$this->mc = new \Memcached() ;
			$this->mc->addServer("localhost", 11211) ;
			if ( ! $this->mc ) throw new \Exception('Memcached fail') ;
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

			if ( ! in_array(@$params['presentation'],Array('checkbox','select')) ) $params['presentation'] = 'checkbox' ;

			$params_er = Array() ;
			if ( isset($params['exclude']) ) $params_er['exclude'] = $params['exclude'] ;
			if ( isset($params['include']) ) $params_er['include'] = $params['include'] ;
			if ( isset($params['force']) ) $params_er['force'] = $params['force'] ;

			$ers = $this->getElementsReferenceByType($type,$params_er) ;
			$familles = $this->getFamillesElementsReference($ers) ;

			if ( $params['presentation'] == 'select' )
			{
				$ret .= ' <select class="form-control chosen-select" ' ;
				$ret .= ' data-placeholder=" " ' ;
				if ( @$params['type'] == 'unique' ) $ret .= ' name="'.$type.'" ' ;
				else $ret .= ' name="'.$type.'[]" multiple="multiple" ' ;
				if ( isset($params['max_selected_options']) ) $ret .= ' data-max_selected_options="'.$params['max_selected_options'].'" ' ;
				$ret .= '>' ;
					if ( @$params['type'] == 'unique' ) $ret .= "\n\t\t\t\t\t\t\t\t".'<option value="">-</option>' ;
					$famillePrec = null ;
					foreach ( $ers as $erp )
					{
						if ( isset($erp['familleCritere']) && isset($familles[$erp['familleCritere']]) && $famillePrec != $erp['familleCritere'] && $famillePrec != null ) $ret .= "\n\t\t\t\t\t\t\t\t".'</optgroup>' ;
						if ( isset($erp['familleCritere']) && isset($familles[$erp['familleCritere']]) && $famillePrec != $erp['familleCritere'] ) $ret .= "\n\t\t\t\t\t\t\t\t".'<optgroup label="'.htmlspecialchars($familles[$erp['familleCritere']]['libelleFr']).'">' ;
						// TODO : on change le fonctionnement de la boucle. On a un tableau avec les parents ($erp) et des enants possibles ($erp['enfants']).
						
						$ret .= "\n\t\t\t\t\t\t\t\t\t".'<option value="'.$erp['id'].'"' ;
						//if ( isset($enfants[$p['id']]) ) $ret .= ' style="font-weight:strong;" ' ;
							if ( isset($erp['description']) && $erp['description'] != '' ) $ret .= ' title="'.htmlspecialchars($erp['description']).'"' ;
							if ( isset($post) && is_array($post) && in_array($erp['id'],$post) ) $ret .= ' selected="selected"' ;
						$ret .= '>'.$erp['libelleFr'].'</option>' ;
						if ( isset($erp['enfants']) )
						{
							foreach ( $erp['enfants'] as $e )
							{
								$ret .= "\n\t\t\t\t\t\t\t\t\t\t".'<option value="'.$e['id'].'"' ;
								if ( isset($e['description']) && $e['description'] != '' ) $ret .= ' title="'.htmlspecialchars($e['description']).'"' ;
								if ( isset($post) && is_array($post) && in_array($e['id'],$post) ) $ret .= ' selected="selected"' ;
								$ret .= '>'.$erp['libelleFr'].' &raquo; '.$e['libelleFr'].'</option>' ;
							}
						}

						$famillePrec = @$erp['familleCritere'] ;
					}
				$ret .= '</select>' ;
			}
			elseif ( $params['presentation'] == 'checkbox' )
			{
				$ret .= ' <div class="form-group">' ;
					if ( @$params['type'] == 'unique' ) $ret .= '<option value="">-</option>' ;
					$famillePrec = null ;
					foreach ( $ers as $erp )
					{
						$ret .= '<div class="form-check form-check-inline">' ;
							$ret .= '<input class="form-check-input" type="checkbox" name="'.$type.'[]" id="'.$type.$erp['id'].'" value="'.$erp['id'].'" ' ;
								if ( isset($post) && is_array($post) && in_array($erp['id'],$post) ) $ret .= ' checked="checked"' ;
							$ret .= ' />' ;
							$ret .= '<label class="form-check-label" for="'.$type.$erp['id'].'"' ;
								if ( isset($erp['description']) && $erp['description'] != '' ) $ret .= ' title="'.htmlspecialchars($erp['description']).'" ' ;
							$ret .= '>' ;
								$ret .= $erp['libelleFr'] ;
							$ret .= '</label>' ;
						$ret .= '</div>' ;
						
						/*if ( isset($erp['enfants']) )
						{
							foreach ( $erp['enfants'] as $e )
							{
								$ret .= '<option value="'.$e['id'].'"' ;
								if ( isset($e['description']) && $e['description'] != '' ) $ret .= 'title="'.htmlspecialchars($e['description']).'" ' ;
								if ( isset($post) && is_array($post) && in_array($e['id'],$post) ) $ret .= ' selected="selected"' ;
								$ret .= '>'.$erp['libelleFr'].' &raquo; '.$e['libelleFr'].'</option>' ;
							}
						}*/

						$famillePrec = @$erp['familleCritere'] ;
					}
				$ret .= '</div>' ;
			}

			return $ret ;
		}

		public function getCommunesById(array $ids, $refresh=false)
		{
			$coms = array_filter($ids,function($id){ return preg_match('#^[0-9]+$#',$id) ; }) ;
			$cachekey = 'communesById'.md5(implode('-',$coms)) ;
			if ( ! $ret = $this->mc->get($cachekey) || $refresh === true )
			{
				$this->debug(__METHOD__.__LINE__,'mc->get failed...') ;
				$q = Array('apiKey'=>$this->projet_consultation_apiKey,'projetId'=>$this->projet_consultation_projetId,'communeIds'=>$coms) ;
				$ret = $this->curlApi('referentiel/communes','GET',Array('query'=>json_encode($q))) ;
				if ( ! is_array($ret) ) throw new \Exception(__METHOD__.__LINE__.'Impossible de récupérer les communes') ;
				$this->debug(__METHOD__.__LINE__,'mc->set...') ;
				$this->mc->set($cachekey,$ret,$this->mc_expiration) ;
			}
			return $ret ;
		}

		public function getCommunesByInsee(array $ids, $refresh=false)
		{
			$coms = array_filter($ids,function($id){ return preg_match('#^[0-9]+$#',$id) ; }) ;
			$cachekey = 'getCommunesByInsee'.md5(implode('-',$coms)) ;
			if ( ! $ret = $this->mc->get($cachekey) || $refresh === true )
			{
				$this->debug(__METHOD__.__LINE__,'mc->get failed...') ;
				$q = Array('apiKey'=>$this->projet_consultation_apiKey,'projetId'=>$this->projet_consultation_projetId,'codesInsee'=>$coms) ;
				$ret = $this->curlApi('referentiel/communes','GET',Array('query'=>json_encode($q))) ;
				if ( ! is_array($ret) ) throw new \Exception(__METHOD__.__LINE__.'Impossible de récupérer les communes') ;
				$this->debug(__METHOD__.__LINE__,'mc->set...') ;
				$this->mc->set($cachekey,$ret,$this->mc_expiration) ;
			}
			return $ret ;
		}

		public function getCommunesByTerritoire($id_territoire,$refresh=false)
		{
			if ( ! preg_match('#^[0-9]+$#',$id_territoire) ) throw new \Exception(__METHOD__.__LINE__.'$id_territoire invalide [0-9]+') ;
			$cachekey = 'territoire'.$id_territoire ;
			if ( ! ( $ret = $this->mc->get($cachekey) ) || $refresh === true )
			{
				$this->debug(__METHOD__.__LINE__.'mc->get failed...') ;
				$parameters = Array('apiKey'=>$this->projet_consultation_apiKey,'projetId'=>$this->projet_consultation_projetId,'responseFields'=>'localisation.perimetreGeographique') ;
				$tmp = $this->curlApi('objet-touristique/get-by-id','GET',$parameters,$id_territoire) ;
				if ( ! is_array($tmp) ) throw new \Exception(__METHOD__.__LINE__.'Impossible de récupérer les communes') ;
				if ( ! isset($tmp['localisation']['perimetreGeographique']) || ! is_array($tmp['localisation']['perimetreGeographique']) || sizeof($tmp['localisation']['perimetreGeographique']) == 0 ) throw new \Exception(__METHOD__.__LINE__.'Impossible de récupérer les communes') ;
				$ret = Array() ;
				foreach ( $tmp['localisation']['perimetreGeographique'] as $c )
					$ret[$c['id']] = Array('id'=>$c['id'],'codePostal'=>$c['codePostal'],'nom'=>$c['nom'],'code'=>$c['code'],'complement'=>@$c['complement']) ;
				$this->debug(__METHOD__.__LINE__,'mc->set...') ;
				$this->mc->set($cachekey,$ret,$this->mc_expiration) ;
			}
			return $ret ;
		}

		public function getOffre($id_offre,$responseFields=null,$refresh=false) {
			if ( ! preg_match('#^[0-9]+$#',$id_offre) ) throw new \Exception(__METHOD__.__LINE__.'$id_offre invalide [0-9]+') ;
			$cachekey = 'offre'.$id_offre ;
			if ( ! $ret = $this->mc->get($cachekey) || $refresh === true )
			{
				$this->debug(__METHOD__.__LINE__.'mc->get failed...') ;
				$parameters = Array('apiKey'=>$this->projet_consultation_apiKey,'projetId'=>$this->projet_consultation_projetId,'responseFields'=>$responseFields) ;
				$tmp = $this->curlApi('objet-touristique/get-by-id','GET',$parameters,$id_offre) ;
				if ( ! is_array($tmp) ) throw new \Exception(__METHOD__.__LINE__.'Impossible de récupérer l\'offre') ;
				$this->debug(__METHOD__.__LINE__,'mc->set...') ;
				$ret = $tmp ;
				$this->mc->set($cachekey,$ret,$this->mc_expiration) ;
			}
			return $ret ;
		}

		/**
		*
		*	Récupère les valeurs possibles d'un type d'éléments de référence
		*
		*	Chaque élément du tableau renvoyé comporte les champs contenus dans le json ou dans la bdd :
		*	id
		*	elementReferenceType
		*	libelleFr
		*	ordre
		*	description
		*	familleCritere
		*	familleCritere_elementReferenceType
		*	parent
		*	parent_elementReferenceType
		*
		*	@param 		$type 	string Nom du type d'élément recherché (colonne elementReferenceType en base de donnée)
		*
		*	@return 	array 	Liste des élements (Chaque élément étant un array associatif issu de la base de donnée)
		*
		**/
		public function getElementsReferenceByType($type,$params=null)
		{
			/**
			 *	TODO : trouver un moyen de récupérer par elementReferenceType en API
			 *	Actuellement on récupère les élements dans le fichier ressources/elements_reference.json, c'est crade et c'est plus à jour.
			 *
			**/

			if ( ! isset($params) || ! is_array($params) )
			{
				$params['force'] = false ;
			}

			$cachekey = 'elements_reference_'.$type.'_'.json_encode($params) ;

			//if ( ! $ret = $this->mc->get($cachekey) )
			{
				$full = file_get_contents($this->ressources_path.'/elements_reference.json') ;
				$full_array = json_decode($full,true) ;
				if ( json_last_error() !== JSON_ERROR_NONE ) throw new \Exception(__METHOD__.__LINE__.'Impossible de récupérer les élements de référence (ressource)') ;

				$ret = Array() ;
				foreach ( $full_array as $er )
				{
					if ( $er['actif'] != true ) continue ;
					if ( $er['elementReferenceType'] != $type ) continue ;
					if ( isset($params['include']) && is_array($params['include']) && ! in_array($er['id'],$params['include']) ) continue ;
					if ( isset($params['exclude']) && is_array($params['exclude']) && in_array($er['id'],$params['exclude']) ) continue ;
					
					$newEr = Array(
						'id' => $er['id'],
						'libelleFr' => $er['libelleFr'],
						'ordre' => $er['ordre']
					) ;
					if ( isset($er['description']) ) $newEr['description'] = $er['description'] ;
					if ( isset($er['familleCritere']['id']) ) $newEr['familleCritere'] = $er['familleCritere']['id'] ;
					
					if ( isset($er['parent']['id']) )
					{
						if ( ! isset($ret[$er['parent']['id']]) )
							$ret[$er['parent']['id']] = Array('enfants'=>Array()) ;
						$ret[$er['parent']['id']]['enfants'][$er['id']] = $newEr ;
					}
					else
						$ret[$er['id']] = $newEr ;
				}
				
				foreach ( $ret as $k => $v )
				{
					@uasort($ret,Array($this,'ersort')) ;
					if ( isset($ret['enfants']) ) @uasort($ret['enfants'],$this,'ersort') ;
				}
			}
			return $ret ;
		}

		private static function ersort($a,$b) { return $a['ordre'] > $b['ordre'] ; }

		private function getFamillesElementsReference($ers) {
			$cles_Familles = Array() ;
			foreach ($ers as $er )
				if ( isset($er['familleCritere']) ) $cles_Familles[] = $er['familleCritere'] ;
			if ( sizeof($cles_Familles) > 0 ) return $this->getElementsReferenceByType('FamilleCritere',Array('include'=>$cles_Familles)) ;
			return null ;
		}

		private function curlApi(string $service,string $method='POST',array $params=null,string $page=null) {
			
			$debug = $this->debug ;
			if ( ! in_array($method,Array('GET','POST')) )
				throw new \Exception(__LINE__." Invalid method for ".__METHOD__." : ".$method) ;

			try {
				$ch = curl_init();
				
				curl_setopt($ch, CURLOPT_HTTPHEADER, Array('Content-Type: application/json')); // Erreur 415 sans cette ligne
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				//curl_setopt($ch, CURLOPT_HEADER, 1) ;
				curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
				
				$url_base = $this->url_api().'api/v002/'.$service.'/' ;
				$url = $url_base ;
				if ( $page !== null && preg_match('#^[a-zA-Z0-9\@\.-]+$#',$page) ) $url .= $page ;
				else echo $page ;
				
				if ( $method == 'GET' ) $url .= '?'.http_build_query($params) ;
				curl_setopt($ch,CURLOPT_URL, $url) ;
				
				if ( $method == 'POST' )
				{
					curl_setopt($ch, CURLOPT_POST, 1) ;
					$postfields = json_encode($params) ;
					curl_setopt($ch,CURLOPT_POSTFIELDS, $postfields);
				}
				
				$response = curl_exec($ch);
				$info = curl_getinfo($ch);
				$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
				if ( $debug )
				{
					if ( $method =='POST' )
					{
						$this->debug(__METHOD__.__LINE__,$url_base) ;
						$this->debug(__METHOD__.__LINE__,json_encode($params)) ;
					}
					else
						$this->debug(__METHOD__.__LINE__,$url) ;
				}
				if (FALSE === $response) throw new \Exception(curl_error($ch), curl_errno($ch));
				//$header = substr($response, 0, $info['header_size']);
				//$body = substr($response, -$info['download_content_length']);
				$body = $response ;
				if ( $httpcode != 200 ) 
				{
					if ( $this->debug )
						throw new \Exception($url_base."\n".json_encode($params)."\n".$body, $httpcode);
					else
						throw new \Exception($url_base, $httpcode);
				}
				
				$ret = json_decode($body,true) ;
				$json_last_error = json_last_error() ;
				if ( $json_last_error !== JSON_ERROR_NONE )
				{
					if ( $this->debug )
						throw new \Exception('cURL Return is not JSON ['.$json_last_error.'] : '.$url_base."\n".json_encode($params)."\n".$body);
					else
						throw new \Exception('cURL Return is not JSON');
				}
				return $ret ;
			} catch(\Exception $e) {
				$msg = sprintf( 'Curl failed with error #%d: %s', $e->getCode(), $e->getMessage() ) ;
				echo '<div class="alert alert-warning">'.$msg.'</div>' ;
			}
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
			/*	
			// TODO : vérifier que $date1 est bien > date du jour
			if ( $future === true )
			{

			}
			// TODO : vérifier que $date est bien > $future
			elseif ( $future !== null && checkDate($future) )
			{

			}
			*/
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