<?

	/*
	$google_api_config = [
		'application-id' => '552345224612-eias0oa0q1klu9a2q84htuo5lmrsv499.apps.googleusercontent.com',
		'service-account-name' => '552345224612-eias0oa0q1klu9a2q84htuo5lmrsv499@developer.gserviceaccount.com',
		'private-key' => file_get_contents('ECManaged-98eeb3b69588.p12'),
		'dataset-id' => 'ecmanaged-gce'
	];

	//2
	function ecm_autoload($className)
	{
		$classPath = explode('_', $className);
		if ($classPath[0] != 'Ecm' && $classPath[0] != 'Ack') {
			return;
		}
		// Drop 'Google', and maximum class file path depth in this project is 3.
		$classPath = array_slice($classPath, 1, 2);

		$filePath = MAIN_DIR . '/' . implode('/', $classPath) . '.php';

		if (file_exists($filePath)) {
			require_once($filePath);
		}
	}

	//spl_autoload_register('ecm_autoload');

	//3
	require_once MAIN_DIR . 'DatastoreService.php'; // not autoloaded yet
	require_once MAIN_DIR . 'testModel.php';

	//4
	DatastoreService::setInstance(new DatastoreService($google_api_config));
	$posts = foo::get_visits();

	print_r($posts);
	die('k');

	foo::get_visits();*/

	// redis
	require MAIN_DIR . 'lib/predis/autoload.php';
	Predis\Autoloader::register();

	$i_visits = 'nan';

	try {
		$redis = new Predis\Client(array(
			'scheme' => 'tcp',
			//'host' => '10.0.0.5',
			'host' => '104.155.23.31',
			'port' => 6379
		));
	}
	catch (Exception $e) {
		//die($e->getMessage());
	}

	//$redis->set("visits", 0);
	$redis->incr("visits");
	$i_visits = $redis->get("visits");

	// get zone
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://metadata.google.internal/computeMetadata/v1/instance/zone");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Metadata-Flavor: Google'
	));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);

	if(isset($output) && !(empty($output))) {
		$a_zone = explode('/', $output);
		$zone = end($a_zone);
	} else {
		$zone = 'unknown';
	}

	include(MAIN_DIR . 'landing.phtml');