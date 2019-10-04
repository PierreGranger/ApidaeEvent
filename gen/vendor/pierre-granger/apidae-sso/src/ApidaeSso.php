<?php

	nameSpace PierreGranger ;

	use PierreGranger\ApidaeSsoException ;

/**
 * 
 * @author	Pierre Granger	<pierre@pierre-granger.fr>
 * 
 * @todo	Use $this->persist['sso']['refresh_token'] to extend the session
 * @todo	Use $this->persist['sso']['expires_in'] at login (getSsoToken) to detected when you need to use refresh_token
 * @todo	Implement http://dev.apidae-tourisme.com/fr/documentation-technique/v2/oauth/services-associes-au-sso/v002ssoutilisateurautorisationobjet-touristiquemodification
 */
class ApidaeSso {
  
	protected static $url_api = Array(
		'preprod' => 'https://api.apidae-tourisme-recette.accelance.net/',
		'prod' => 'https://api.apidae-tourisme.com/'
	) ;

	protected static $url_base = Array(
		'preprod' => 'https://base.apidae-tourisme-recette.accelance.net/',
		'prod' => 'https://base.apidae-tourisme.com/'
	) ;

	protected $type_prod = 'prod' ;

    protected $ssoClientId ;
    protected $ssoSecret ;

    protected $debug = false ;

    protected $rutime ;

    private $_config ;

	protected $timeout = 15 ; // secondes
	
	protected $defaultSsoRedirectUrl ;

    const NO_TOKEN = 1 ;
    const NO_SCOPE = 2 ;
	const NO_ERROR = 3 ;
	const NO_JSON = 4 ;
	const NO_RESPONSE = 5 ;
	const NO_BODY = 6 ;
	const NOT_CONNECTED = 7 ;

	/** */
	protected $persist ;

    public function __construct($params,&$persist) {

		if ( ! isset($params['ssoClientId']) ) throw new \Exception('missing ssoClientId') ;
		if ( ! preg_match('#^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$#',$params['ssoClientId']) ) throw new \Exception('invalid ssoClientId') ;
		$this->ssoClientId = $params['ssoClientId'] ;
			
		if ( ! isset($params['ssoSecret']) ) throw new \Exception('missing ssoSecret') ;
		if ( ! preg_match('#^[a-zA-Z0-9]{1,20}$#',$params['ssoSecret']) ) throw new \Exception('invalid ssoSecret') ;
		$this->ssoSecret = $params['ssoSecret'] ;

		//if ( ! isset($params['defaultSsoRedirectUrl']) ) throw new \Exception('missing defaultSsoRedirectUrl') ;
		if (   isset($params['defaultSsoRedirectUrl']) ) $this->defaultSsoRedirectUrl = $params['defaultSsoRedirectUrl'] ;

        if ( isset($params['timeout']) && preg_match('#^[0-9]+$#',$params['timeout']) ) $this->timeout = $params['timeout'] ;

		if ( isset($params['type_prod']) && in_array($params['type_prod'],Array('prod','preprod')) ) $this->type_prod = $params['type_prod'] ;

		if ( isset($params['debug']) ) $this->debug = $params['debug'] == true ;

		$this->_config = $params ;

		$this->rutime = Array() ;
		
		$this->persist = &$persist ;

		/*
		if ( $this->connected() )
		{
			if ( $this->refreshSsoToken() !== true )
				$this->logout() ;
		}
		*/
	}
	
	/**
	 * Generate URL for link to auth form
	 * 
	 * @param	$ssoRedirectUrl	URL to be redirected after auth. Can be null : URL will be generated from current url (see genRedirectUrl()).
	 */
	public function getSsoUrl($ssoRedirectUrl=null) {
		return $this->url_base().'/oauth/authorize/?response_type=code&client_id='.$this->ssoClientId.'&scope=sso&redirect_uri='.$this->genRedirectUrl($ssoRedirectUrl) ;
	}

	private function genRedirectUrl($ssoRedirectUrl=null) {

		if ( $ssoRedirectUrl == null )
		{
			if ( isset($_SERVER) && isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI']) )
				$ssoRedirectUrl = 'http'.(isset($_SERVER['HTTPS'])?'s':'').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ;
			else
				$ssoRedirectUrl = $this->defaultSsoRedirectUrl ;
		}
		
		if ( $ssoRedirectUrl == null ) throw new \Exception(__METHOD__.' : Unable to generate ssoRedirectUrl :(') ;

		$query = null ;
		$url = parse_url($ssoRedirectUrl) ;

		if ( isset($url['query']) )
		{
			parse_str($url['query'],$query) ;
			unset($query['code']) ; // Removing ?code=XYZ from current URL
			unset($query['logout']) ; // Removing ?logout=1 from current URL
		}
		$ssoRedirectUrl = $url['scheme'].'://'.$url['host'].$url['path'] . ( ( is_array($query) && sizeof($query) > 0 )  ? '?' . http_build_query($query) : '' ) ;

		return $ssoRedirectUrl ;
	}

	/**
	 * After authentification user is redirected with an additional ?code=XZY Get parameter.
	 * We need to use it to get a token from SSO API
	 * @link	http://dev.apidae-tourisme.com/fr/documentation-technique/v2/oauth/single-sign-on
	 * @param	$code	code given in $_GET['code'] after the user login. User is redirected to $ssoRedirectUrl with this code.
	 */
	public function getSsoToken($code,$ssoRedirectUrl=null) {
		$url = $this->url_api().'/oauth/token' ;
		$ch = curl_init() ;
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt($ch, CURLOPT_USERPWD, $this->ssoClientId.":".$this->ssoSecret);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout); 
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout); //timeout in seconds
		$POSTFIELDS = "grant_type=authorization_code&code=".$code.'&redirect_uri='.urlencode($this->genRedirectUrl($ssoRedirectUrl)) ;
		curl_setopt($ch, CURLOPT_POSTFIELDS,$POSTFIELDS);
		
		try {
			$response = curl_exec($ch);
		} catch(\Exception $e) {
			throw new ApidaeSsoException($e->getMessage(),$e->getCode(),null,Array('url',$url,'POSTFIELDS',$POSTFIELDS,'body'=>$body,'header'=>$header)) ;
		}

		if ( curl_error($ch) )
			throw new ApidaeSsoException(curl_error($ch),self::NO_RESPONSE) ;

		if ( FALSE === $response )
			throw new ApidaeSsoException('no response',self::NO_RESPONSE) ;

		try {
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$header = substr($response, 0, $header_size);
			$body = substr($response, $header_size);

			if ( FALSE === $body )
				throw new ApidaeSsoException('no token',self::NO_BODY,null,Array('header'=>$header,'body'=>$body,'httpCode'=>$httpCode)) ;
			
			$token_sso = $body ;
			$token_sso = json_decode($token_sso,true) ;

			if ( json_last_error() !== JSON_ERROR_NONE )
				throw new ApidaeSsoException('no json',self::NO_JSON,null,$token_sso) ;

			if ( ! isset($token_sso['scope']) )
			{
				if ( isset($token_sso['error']) )
					throw new ApidaeSsoException('no scope',self::NO_SCOPE,null,$token_sso) ;
				else
					throw new ApidaeSsoException('no error',self::NO_ERROR,null,$token_sso) ;
			}

			$this->persist['sso'] = $token_sso ;


		} catch (ApidaeSsoException $e) {

			echo '<pre>' ;
			if ( $this->debug )
			{
				$details = $e->getDetails() ;
				if ( is_array($details) )
				{
					echo '[DEBUG ON] details :' ;
					echo '<ul>' ;
					foreach ( $details as $k => $v )
						echo '<li>'.$k.' : '.$v.'</li>' ;
					echo '</ul>' ;
				}
			}

			if ( $e->getCode() == self::NO_SCOPE ) // Todo : show explicit error messages
			{
				trigger_error($e,E_USER_ERROR) ;
			}
			elseif ( $e->getCode() == self::NO_ERROR ) // Todo : show explicit error messages
			{
				trigger_error($e,E_USER_ERROR) ;
			}
			echo '</pre>' ;
		} catch (\Exception $e) {
			echo '<pre>' ; trigger_error($e,E_USER_ERROR) ; echo '</pre>' ;
		}
	}

	/**
	 * After authentification user is redirected with an additional ?code=XZY Get parameter.
	 * We need to use it to get a token from SSO API
	 * @link	http://dev.apidae-tourisme.com/fr/documentation-technique/v2/oauth/single-sign-on
	 * @param	$code	code given in $_GET['code'] after the user login. User is redirected to $ssoRedirectUrl with this code.
	 */
	public function refreshSsoToken($ssoRedirectUrl=null) {

		if ( ! $this->connected() )
			throw new ApidaeSsoException(curl_error($ch),self::NOT_CONNECTED) ;

		$url = $this->url_api().'/oauth/token' ;
		$ch = curl_init() ;
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt($ch, CURLOPT_USERPWD, $this->ssoClientId.":".$this->ssoSecret);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout); 
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout); //timeout in seconds
		$POSTFIELDS = "grant_type=refresh_token&refresh_token=".$this->persist['sso']['refresh_token'].'&redirect_uri='.urlencode($this->genRedirectUrl($ssoRedirectUrl)) ;
		curl_setopt($ch, CURLOPT_POSTFIELDS,$POSTFIELDS);
		
		try {
			$response = curl_exec($ch);
		} catch(\Exception $e) {
			throw new ApidaeSsoException($e->getMessage(),$e->getCode(),null,Array('url',$url,'POSTFIELDS',$POSTFIELDS,'body'=>$body,'header'=>$header)) ;
		}

		if ( curl_error($ch) )
			throw new ApidaeSsoException(curl_error($ch),self::NO_RESPONSE) ;

		if ( FALSE === $response )
			throw new ApidaeSsoException('no response',self::NO_RESPONSE) ;

		try {
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$header = substr($response, 0, $header_size);
			$body = substr($response, $header_size);

			if ( FALSE === $body )
				throw new ApidaeSsoException('no token',self::NO_BODY,null,Array('header'=>$header,'body'=>$body,'httpCode'=>$httpCode)) ;
			
			$token_sso = $body ;
			$token_sso = json_decode($token_sso,true) ;

			if ( json_last_error() !== JSON_ERROR_NONE )
				throw new ApidaeSsoException('no json',self::NO_JSON,null,$token_sso) ;

			if ( ! isset($token_sso['scope']) )
			{
				if ( isset($token_sso['error']) )
					throw new ApidaeSsoException('no scope',self::NO_SCOPE,null,$token_sso) ;
				else
					throw new ApidaeSsoException('no error',self::NO_ERROR,null,$token_sso) ;
			}

			echo '<pre>'.print_r($token_sso,true).'</pre>' ;

			$this->persist['sso'] = $token_sso ;


		} catch (ApidaeSsoException $e) {

			echo '<pre>' ;
			if ( $this->debug )
			{
				$details = $e->getDetails() ;
				if ( is_array($details) )
				{
					echo '[DEBUG ON] details :' ;
					echo '<ul>' ;
					foreach ( $details as $k => $v )
						echo '<li>'.$k.' : '.$v.'</li>' ;
					echo '</ul>' ;
				}
			}

			if ( $e->getCode() == self::NO_SCOPE ) // Todo : show explicit error messages
			{
				trigger_error($e,E_USER_ERROR) ;
			}
			elseif ( $e->getCode() == self::NO_ERROR ) // Todo : show explicit error messages
			{
				trigger_error($e,E_USER_ERROR) ;
			}
			echo '</pre>' ;
		} catch (\Exception $e) {
			echo '<pre>' ; trigger_error($e,E_USER_ERROR) ; echo '</pre>' ;
		}

		return true ;
	}

	/**
	 * get connected user profil as an array.
	 * @link	http://dev.apidae-tourisme.com/fr/documentation-technique/v2/oauth/services-associes-au-sso/v002ssoutilisateurprofil
	 */
	public function getUserProfile() {
		
		if ( ! $this->connected() ) return false ;

		$userprofile = false ;

		if ( isset($this->persist['user']) ) $userprofile = $this->persist['user'] ;
		else
		{
			$url = $this->url_base()."/api/v002/sso/utilisateur/profil" ;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer ".$this->persist['sso']['access_token']));
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout); 
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
			
			try {
				$response = curl_exec($ch);

				$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
				$header = substr($response, 0, $header_size);
				$body = substr($response, $header_size);

				if (FALSE === $response)
					throw new \Exception(curl_error($ch), curl_errno($ch));
				else
				{
					$userprofile = json_decode($body,true) ;
					$this->persist['user'] = $userprofile ;
				}
			} catch(Exception $e) {
				if ( $this->debug )
				{
					echo '<h3>$url</h3><pre>'.print_r($url,true).'</pre>' ;
					echo '<h3>$POSTFIELDS</h3><pre>'.print_r($POSTFIELDS,true).'</pre>' ;
					echo '<h3>$body</h3><pre>'.print_r($body,true).'</pre>' ;
					echo '<h3>$header</h3><pre>'.print_r($header,true).'</pre>' ;
				}
				trigger_error(sprintf(
					'Curl failed with error #%d: %s',
					$e->getCode(), $e->getMessage()),
					E_USER_ERROR);
			}
		}
		return $userprofile ;
	}

	/**
	 * 
	 */
	public function logout() {
		foreach ( $this->persist as $k => $v )
			unset($this->persist[$k]) ;
	}

	/**
	 * Is the current user connected ?
	 * @return	bool	clear enough
	 */
	public function connected() {
		return isset($this->persist['sso']) ;
	}

	private function url_base() { return self::$url_base[$this->type_prod] ; }
	private function url_api() { return self::$url_api[$this->type_prod] ; }

    public function ruStart($nom='Time') {
        $rutime[$nom] = microtime('true') ;
    }

    public function ruShow($nom='Time') {
        $execution_time = round( microtime('true') - $this->rutime[$nom],2 ) ;
        $echo = $nom.' : '.$execution_time.'s' ;
        echo '<pre style="font-size:0.8em;">'.$echo.'</pre>' ;
    }
}
