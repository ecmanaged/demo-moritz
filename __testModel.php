<?php

require_once './Model.php';

/**
 * Model class for feed objects
 */
class foo extends Model {

	const D_MODEL_KIND = 'status',
		D_AMOUNT = 'amount';

	private
		$key_name,
		$visits;

	public function __construct($visits) {
		parent::__construct();
		$this->key_name = 'visitors';
		$this->visits = $visits;
	}

	public function getVisits() {
		return $this->visits;
	}

	public function get_visits() {
		$query = self::createQuery(static::getKindName());
		$results = self::executeQuery($query);
		return static::extractQueryResults($results);
	}

	protected static function getKindName() {
		return self::D_MODEL_KIND;
	}

	/**
	 * Generate the entity property map from the feed object fields.
	 */
	protected function getKindProperties() {
		$property_map = [];

		$property_map[self::D_AMOUNT] =
			parent::createStringProperty($this->visits, true);

		return $property_map;
	}

	/**
	 * Fetch a feed object given its feed URL.  If get a cache miss, fetch from the Datastore.
	 * @param $url URL of the feed.
	 */
	public static function get($url) {
		$mc = new Memcache();
		$key = self::getCacheKey($url);
		$response = $mc->get($key);
		if ($response) {
			return [$response];
		}

		$query = parent::createQuery(self::D_MODEL_KIND);
		$feed_url_filter = parent::createStringFilter(self::D_AMOUNT,
			$url);
		$filter = parent::createCompositeFilter([$feed_url_filter]);
		$query->setFilter($filter);
		$results = parent::executeQuery($query);
		$extracted = self::extractQueryResults($results);
		return $extracted;
	}

	/**
	 * This method will be called after a Datastore put.
	 */
	protected function onItemWrite() {
		$mc = new Memcache();
		try {
			$key = self::getCacheKey($this->key_name);
			$mc->add($key, $this, 0, 120);
		}
		catch (Google_Cache_Exception $ex) {
			syslog(LOG_WARNING, "in onItemWrite: memcache exception");
		}
	}

	/**
	 * This method will be called prior to a datastore delete
	 */
	protected function beforeItemDelete() {
		$mc = new Memcache();
		$key = self::getCacheKey(self::D_AMOUNT);
		$mc->delete($key);
	}

	/**
	 * Extract the results of a Datastore query into FeedModel objects
	 * @param $results Datastore query results
	 */
	protected static function extractQueryResults($results) {
		$query_results = [];
		foreach($results as $result) {
			$id = @$result['entity']['key']['path'][0]['id'];
			$key_name = @$result['entity']['key']['path'][0]['name'];
			$props = $result['entity']['properties'];

			$visits = $props[self::D_AMOUNT]->getStringValue();

			$blog_model = new foo($visits);
			$blog_model->setKeyId($id);
			$blog_model->setKeyName($key_name);
			// Cache this read feed.
			$blog_model->onItemWrite();

			$query_results[] = $blog_model;
		}
		return $query_results;
	}

	private static function getCacheKey($feed_url) {
		return sprintf("%s_%s", self::D_MODEL_KIND, sha1($feed_url));
	}
}