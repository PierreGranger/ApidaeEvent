<?php

namespace PierreGranger ;

use ApidaePHP\Client;
use PierreGranger\ApidaeEcriture ;
use Memcached ;
use Exception ;

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

		private string $projet_consultation_apiKey ;
		private int $projet_consultation_projetId ;
		private $selection_territoires ;

		private $ressources_path ;

		private string $method_elementsReference = 'json' ; // json (impossible via API pour l'instant)
		private string $method_communes = 'json' ; // json|sql (impossible via API pour l'instant)
		private string $method_territoires = 'api' ; // api

		protected $mc ;
		protected $mc_expiration = 86400 ; // 2592000 = 30 jours, 86400 = 24h

		protected Client $client ;

		protected array $config ;

		protected string $env = 'prod' ;

		private array $language ;

		public const DEFAULT_LANGUAGE = ['lang' => 'fr', 'locale' => 'fr_FR', 'codeLibelle' => 'Fr'] ;

		public const ACCEPTED_LANGUAGES = [
			'fr' => ['lang' => 'fr', 'locale' => 'fr_FR', 'codeLibelle' => 'Fr'],
			'en' => ['lang' => 'en', 'locale' => 'en_GB', 'codeLibelle' => 'En', 'deepL' => 'en-GB'],
			//'de' => ['lang' => 'de', 'locale' => 'de_DE', 'codeLibelle' => 'De'] // Allemand utilisé pour debug seulement (toutes valeurs à ! pour bien voir les oublis)
		];

		private const ELEMENTS_REFERENCE_URL = [
			'prod' => 		'https://static.apidae-tourisme.com/filestore/exports-referentiel/elements_reference.json',
			'dev' => 		'https://static.apidae-tourisme.dev/filestore/exports-referentiel/elements_reference.json',
			'cooking' => 	'https://static.apidae-tourisme.cooking/filestore/exports-referentiel/elements_reference.json'
		] ;

		private const URL_API = [
			'prod' => 'https://api.apidae-tourisme.com',
			'dev' => 'https://api.apidae-tourisme.dev',
			'cooking' => 'https://api.apidae-tourisme.cooking',
		] ;
	
		private const TYPE_OBJET = 'FETE_ET_MANIFESTATION' ;
		
		public function __construct(array $params=null) {
			
			parent::__construct($params) ;

			if ( ! is_array($params) ) throw new Exception('$params is not an array') ;
			if ( ! isset($params['env']) || ! in_array($params['env'],['prod','dev','cooking']) ) $params['env'] = 'prod' ;
			$this->env = $params['env'] ;
			
			if ( isset($params['projet_consultation_apiKey']) ) $this->projet_consultation_apiKey = $params['projet_consultation_apiKey'] ; //else throw new Exception('missing projet_consultation_apiKey') ;
			if ( isset($params['projet_consultation_projetId']) ) $this->projet_consultation_projetId = $params['projet_consultation_projetId'] ; //else throw new Exception('missing projet_consultation_projetId') ;
			if ( isset($params['selection_territoires']) ) $this->selection_territoires = $params['selection_territoires'] ;

			if ( isset($params['ressources_path']) && is_dir($params['ressources_path']) ) $this->ressources_path = $params['ressources_path'] ;
			else $this->ressources_path = realpath(dirname(__FILE__)).'/../ressources/' ;

			if ( isset($params['lang']) && isset(self::ACCEPTED_LANGUAGES[$params['lang']]) ) {
				$this->language = self::ACCEPTED_LANGUAGES[$params['lang']] ;
			} else {
				$this->language = self::DEFAULT_LANGUAGE ;
			}

			try {
				$this->mc = new Memcached() ;
				$this->mc->addServer("localhost", 11211) ;
			} catch ( Exception $e ) {
				die('Unabled to start Memcached') ;
			}

			$this->client = new Client([
				'apiKey' => $params['projet_consultation_apiKey'],
				'projetId' => $params['projet_consultation_projetId'],
				'env' => $params['env']
			]) ;
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
						if ( isset($erp['familleCritere']) && isset($familles[$erp['familleCritere']]) && $famillePrec != $erp['familleCritere'] ) $ret .= "\n\t\t\t\t\t\t\t\t".'<optgroup label="'.htmlspecialchars($this->libelleEr($familles[$erp['familleCritere']])).'">' ;
						// TODO : on change le fonctionnement de la boucle. On a un tableau avec les parents ($erp) et des enants possibles ($erp['enfants']).
						
						$ret .= "\n\t\t\t\t\t\t\t\t\t".'<option value="'.$erp['id'].'"' ;
						//if ( isset($enfants[$p['id']]) ) $ret .= ' style="font-weight:strong;" ' ;
							if ( isset($erp['description']) && $erp['description'] != '' ) $ret .= ' title="'.htmlspecialchars($erp['description']).'"' ;
							if ( isset($post) && is_array($post) && in_array($erp['id'],$post) ) $ret .= ' selected="selected"' ;
						$ret .= '>'.$this->libelleEr($erp).'</option>' ;
						if ( isset($erp['enfants']) )
						{
							foreach ( $erp['enfants'] as $e )
							{
								$ret .= "\n\t\t\t\t\t\t\t\t\t\t".'<option value="'.$e['id'].'"' ;
								if ( isset($e['description']) && $e['description'] != '' ) $ret .= ' title="'.htmlspecialchars($e['description']).'"' ;
								if ( isset($post) && is_array($post) && in_array($e['id'],$post) ) $ret .= ' selected="selected"' ;
								$ret .= '>'.$this->libelleEr($erp).' &raquo; '.$this->libelleEr($e).'</option>' ;
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
								$ret .= $this->libelleEr($erp) ;
							$ret .= '</label>' ;
						$ret .= '</div>' ;
						
						$famillePrec = @$erp['familleCritere'] ;
					}
				$ret .= '</div>' ;
			}

			return $ret ;
		}

		// public function getCommunesById(array $ids, $refresh=false)
		// {
		// 	$coms = array_filter($ids,function($id){ return preg_match('#^[0-9]+$#',$id) ; }) ;
		// 	$cachekey = 'communesById'.md5(implode('-',$coms)) ;
		// 	if ( ( $ret = $this->get($cachekey) ) === false || $refresh === true )
		// 	{
		// 		$this->debug(__METHOD__.' : mc->get failed [refresh='.$refresh.']...') ;
		// 		$ret = $this->client->referentielCommunes(['query' => ['communeIds'=>$coms]]) ;
		// 		if ( ! is_array($ret) && preg_match('#^Guzzle.*Result$#',get_class($ret)) ) $ret = $ret->toArray() ;
		// 		$this->debug(__METHOD__.' : mc->set...[expiration='.$this->mc_expiration.']') ;
		// 		$this->set($cachekey,$ret,$this->mc_expiration) ;
		// 	}
		// 	return $ret ;
		// }

		/**
		 * @param array<int> $ids Liste de codes INSEE
		 * @param bool $refresh
		 * @return array Liste de communes
		 */
		public function getCommunesByInsee(array $ids, bool $refresh=false)
		{
			$insees = array_filter($ids,function($id){ return preg_match('#^[0-9]+$#',$id) ; }) ;
			$cachekey = 'getCommunesByInsee'.md5(implode('-',$insees)) ;
			if ( ( $ret = $this->get($cachekey) ) === false || $refresh === true )
			{
				$this->debug(__METHOD__.' : mc->get failed [refresh='.$refresh.']...') ;
				$ret = $this->client->referentielCommunes(['query' => ['codesInsee' => $insees]]) ;
				if ( ! is_array($ret) && preg_match('#^Guzzle.*Result$#',get_class($ret)) ) $ret = $ret->toArray() ;
				$this->debug(__METHOD__.' : mc->set...[expiration='.$this->mc_expiration.']') ;
				$this->set($cachekey,$ret,$this->mc_expiration) ;
			}
			return $ret ;
		}

		/**
		 * @param int $id_territoire Identifiant d'une offre territoire sur Apidae
		 * @return array Tableau contenant la liste des communes du territoire $id_territoire
		 */
		public function getCommunesByTerritoire(int $id_territoire,bool $refresh=false)
		{
			if ( ! preg_match('#^[0-9]+$#',$id_territoire) ) throw new Exception(__METHOD__.__LINE__.'$id_territoire invalide [0-9]+') ;
			$cachekey = 'territoire'.$id_territoire ;
			if ( ( $ret = $this->get($cachekey) ) === false || $refresh === true )
			{
				$this->debug(__METHOD__.' : mc->get failed [refresh='.$refresh.']...') ;
				$tmp = $this->client->objetTouristiqueGetById(['id' => $id_territoire,'responseFields' => 'localisation.perimetreGeographique']) ;
				if ( ! is_array($tmp) && preg_match('#^Guzzle.*Result$#',get_class($tmp)) ) $tmp = $tmp->toArray() ;
				if ( ! isset($tmp['type']) ) throw new Exception(__METHOD__.__LINE__.'Impossible de récupérer les communes') ;
				if ( ! isset($tmp['localisation']['perimetreGeographique']) || ! is_array($tmp['localisation']['perimetreGeographique']) || sizeof($tmp['localisation']['perimetreGeographique']) == 0 ) throw new Exception(__METHOD__.__LINE__.'Impossible de récupérer les communes') ;
				$ret = Array() ;
				foreach ( $tmp['localisation']['perimetreGeographique'] as $c )
					$ret[$c['id']] = Array('id'=>$c['id'],'codePostal'=>$c['codePostal'],'nom'=>$c['nom'],'code'=>$c['code'],'complement'=>@$c['complement']) ;
				$this->debug(__METHOD__.' : mc->set...[expiration='.$this->mc_expiration.']') ;
				$this->set($cachekey,$ret,$this->mc_expiration) ;
			}
			return $ret ;
		}

		public function getOffre(int $id_offre,string $responseFields=null,bool $refresh=false) {
			if ( ! preg_match('#^[0-9]+$#',$id_offre) ) throw new Exception(__METHOD__.__LINE__.'$id_offre invalide [0-9]+') ;
			$cachekey = 'offre'.$id_offre ;
			if ( ( $ret = $this->get($cachekey) ) === false || $refresh === true )
			{
				$this->debug(__METHOD__.' : mc->get failed [refresh='.$refresh.']...') ;
				$ret = $this->client->objetTouristiqueGetById(['id' => $id_offre,'responseFields' => $responseFields]) ;
				if ( ! is_array($ret) && preg_match('#^Guzzle.*Result$#',get_class($ret)) ) $ret = $ret->toArray() ;
				if ( ! is_array($ret) ) throw new Exception(__METHOD__.__LINE__.'Impossible de récupérer l\'offre') ;
				$this->debug(__METHOD__.' : mc->set...[expiration='.$this->mc_expiration.']') ;
				$this->set($cachekey,$ret,$this->mc_expiration) ;
			}
			return $ret ;
		}

		private $ers = null ;
		private function getElementsReference() {

			if ( $this->ers !== null ) return $this->ers ;
			$file = $this->ressources_path.'elements_reference.json' ;
			
			if ( ! file_exists($file) ) {
				return false ;
			}
			$tmp = file_get_contents($file) ;

			$this->ers = json_decode($tmp,true) ;
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				return false ;
			}

			return $this->ers ;
		}

		private $interdits = [] ;
		public function getElementsReferenceInterdictions(array $elementReferenceTypes, $refresh=false) {
			$ret = [] ;
			foreach ( $elementReferenceTypes as $elementReferenceType ) {
				if ( ! isset($this->interdits[$elementReferenceType]) ) {
					$file = $this->ressources_path.'elements_reference_interdits_'.$elementReferenceType.'.json' ;
					if ( file_exists($file) ) {
						$tmp = @file_get_contents($file) ;
						$tmp = json_decode($tmp,true) ;
						if ( json_last_error() === JSON_ERROR_NONE ) {
							$this->interdits[$elementReferenceType] = $tmp ;
						}
					}
				}
				if ( isset($this->interdits[$elementReferenceType]) ) {
					$ret = $ret + $this->interdits[$elementReferenceType] ;
				}
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
		*	@return 	bool|array 	Liste des élements (Chaque élément étant un array associatif issu de la base de donnée)
		*
		**/
		public function getElementsReferenceByType(string $type,array $params=null)
		{
			$elementsReference = $this->getElementsReference() ;
			if ( ! is_array($elementsReference) ) {
				return false ;
			}
			
			// Pour gérer les interdictions de critères, on doit savoir quel type objet on cherche à écrire
			$typeObjet = self::TYPE_OBJET ;
			if ( isset($params['typeObjet']) && is_array($params['typeObjet']) ) {
				$typeObjet = $params['typeObjet'] ;
			}

			$interdictions = $this->getElementsReferenceInterdictions([$type]) ;

			$ret = [] ;
			foreach ( $elementsReference as $er )
			{
				if ( $er['actif'] != true ) continue ;
				if ( $er['elementReferenceType'] != $type ) continue ;
				if ( isset($interdictions[$er['id']]['typesInterdits']) && in_array($typeObjet, $interdictions[$er['id']]['typesInterdits']) ) {
					continue ;
				}
				if ( isset($params['include']) && is_array($params['include']) && ! in_array($er['id'],$params['include']) ) continue ;
				if ( isset($params['exclude']) && is_array($params['exclude']) && in_array($er['id'],$params['exclude']) ) continue ;
				
				$newEr = [
					'id' => $er['id'],
					'libelleFr' => $this->libelleEr($er, 'fr'),
					'ordre' => $er['ordre']
				] ;
				if ( isset($er['description']) ) $newEr['description'] = $er['description'] ;
				if ( isset($er['familleCritere']['id']) ) $newEr['familleCritere'] = $er['familleCritere']['id'] ;
				
				if ( isset($er['parent']['id']) )
				{
					if ( ! isset($ret[$er['parent']['id']]) ) $ret[$er['parent']['id']] = ['enfants'=>[]] ;
					$ret[$er['parent']['id']]['enfants'][$er['id']] = $newEr ;
				}
				else
					$ret[$er['id']] = $newEr ;
			}

			uasort($ret,[$this,'triOrdre']) ;

			foreach ( $ret as $k => &$v )
				$this->triEnfants($v) ;
			unset($v) ;

			return $ret ;
		}

		private function triEnfants(array &$elementReference) {
			if ( isset($elementReference['enfants']) )
			{
				uasort($elementReference['enfants'],[$this,'triOrdre']) ;
				foreach ( $elementReference['enfants'] as &$enfant ) {
					$this->triEnfants($enfant) ;
				}
				unset($enfant) ;
			}
			unset($elementReference) ;
		}

		private static function triOrdre($a,$b) { return (int)$a['ordre'] - (int)$b['ordre'] ; }

		/**
		 * @param array<array> $ers Tableau d'éléments de références
		 * @return null|array
		 */
		private function getFamillesElementsReference(array $ers) {
			$cles_Familles = Array() ;
			foreach ($ers as $er )
				if ( isset($er['familleCritere']) ) $cles_Familles[] = $er['familleCritere'] ;
			if ( sizeof($cles_Familles) > 0 ) return $this->getElementsReferenceByType('FamilleCritere',Array('include'=>$cles_Familles)) ;
			return null ;
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
			if ( ! preg_match('#^([0-9]{2}):([0-9]{2})$#',$h,$match) ) return false ;
			if ( (int) $match[1] < 0 || (int) $match[1] > 24 ) return false ;
			if ( (int) $match[2] < 0 || (int) $match[2] > 60 ) return false ;
			return true ;
		}

		public function flush()
		{
			return $this->mc->flush() ;
		}

		protected function get(string $cachekey) {
			return $this->mc->get($cachekey) ;
		}

		protected function set(string $cachekey, $ret, $expiration=null) {
			return $this->mc->set($cachekey,$ret) ;
		}

		public function libelleEr($er, $codeLibelle = 'Fr') {
			if ( isset($codeLibelle) && isset($er['libelle'.$codeLibelle]) ) return $er['libelle'.$codeLibelle] ;
			elseif ( isset($er['libelle'.$this->language['codeLibelle']]) ) return $er['libelle'.$this->language['codeLibelle']] ;
			return $er['libelleFr'] ;
		}
		

		// https://www.php.net/manual/en/function.file-put-contents.php#84180
		private function file_force_contents($dir, $contents){
			$parts = explode('/', $dir);
			$file = array_pop($parts);
			$dir = '';
			foreach($parts as $part)
				if(!is_dir($dir .= "/$part")) mkdir($dir);
			file_put_contents("$dir/$file", $contents);
		}

		public function cacheErs($refresh = false) {
			$tmp = file_get_contents(self::ELEMENTS_REFERENCE_URL[$this->env]) ;
			json_decode($tmp) ;
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				die('Impossible de récupérer les éléments de référence...') ;
			}
			$this->file_force_contents($this->ressources_path.'elements_reference.json', $tmp) ;
			$this->cacheErsInterdictions($refresh) ;
		}

		private function cacheErsInterdictions($refresh = false) {

			$elementReference = $this->getElementsReference($refresh) ;
			// Critères interdits
			// https://apidae-tourisme.zendesk.com/agent/tickets/34957
			// 31/07/2024
			$elementReferenceIds = [] ;
			foreach ( $elementReference as $er ) {
				$elementReferenceIds[$er['elementReferenceType']][] = $er['id'] ;
			}

			foreach ( $elementReferenceIds as $elementReferenceType => $ids ) {
				$query = [
					'apiKey' => $this->projet_consultation_apiKey,
					'projetId' => $this->projet_consultation_projetId,
					'elementReferenceIds' => $ids
				] ;
				$tmp = file_get_contents(self::URL_API[$this->env].'/api/v002/referentiel/criteres-interdits/?query='.json_encode($query)) ;
				$res = json_decode($tmp,true) ;
				if ( json_last_error() == JSON_ERROR_NONE ) {
					$interdictions = [] ;
					foreach ( $res as $r ) {
						$interdictions[$r['critereId']] = [] ;
						foreach ( $r as $k => $v ) {
							if ( $k != 'critereId' ) {
								$interdictions[$r['critereId']][$k] = $v ;
							}
						}
					}
					$this->file_force_contents($this->ressources_path.'elements_reference_interdits_'.$elementReferenceType.'.json', json_encode($interdictions, JSON_PRETTY_PRINT)) ;
				}
			}
		}

	}