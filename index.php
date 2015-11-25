<?

	//require_once './DatastoreService.php'; // not autoloaded yet
	//require_once './testModel.php';
	require_once './Bloc.php';

	$google_api_config = [
		'application-id' => '552345224612-eias0oa0q1klu9a2q84htuo5lmrsv499.apps.googleusercontent.com',
		'service-account-name' => '552345224612-eias0oa0q1klu9a2q84htuo5lmrsv499@developer.gserviceaccount.com',
		'private-key' => file_get_contents('ECManaged-98eeb3b69588.p12'),
		'dataset-id' => 'ecmanaged-gce'
	];

	DatastoreService::setInstance(new DatastoreService($google_api_config));

	$posts = Ecm_Bloc::get_posts();
	print_r($posts);
	die('k');

	foo::get_visits();

	$i_visits = 1;

	include('./landing.phtml');