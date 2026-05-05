<?php
if (!defined('init_engine'))
{	
	header('HTTP/1.0 404 not found');
	exit;
}

class Session
{		
	public function __construct()
	{		
		// set default sessions save handler
		ini_set('session.save_handler', 'files');
        
	}

	protected function _start()
	{
	  global $config;
	  
		session_name('admin_'.$config['AuthCookieName'].'_hash');

		$secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
		if (PHP_VERSION_ID >= 70300) {
			session_set_cookie_params(array(
				'lifetime' => 0,
				'path' => '/',
				'secure' => $secureCookie,
				'httponly' => true,
				'samesite' => 'Lax'
			));
		} else {
			session_set_cookie_params(0, '/; samesite=Lax', '', $secureCookie, true);
		}
		ini_set('session.use_strict_mode', '1');
		ini_set('session.cookie_httponly', '1');

		session_start();
				
		return true;
	}

	public function register()
	{

		//Start the session if needed
		if(!isset($_SESSION))
		{
		  $this->_start();
		}

	}
	
    public function _open($save_path, $session_name)
	{	
	  return true;
    }

    public function _close() {
        return true;
    }

    public function _read($id)
	{
      return true; 
    }

    public function _write($id, $data)
	{
      return true; 
    }

    public function _destroy($id)
	{
	  return true;
    }

    public function _clean($max)
	{
   	  return true;
    }			
}