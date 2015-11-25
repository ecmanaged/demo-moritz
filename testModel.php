<?php

require_once './Model.php';

/**
 * Model class for feed objects
 */
class foo extends Model {

	const D_MODEL_KIND = 'demo',
		D_VISITS = 'visits',
		D_NODES = 'nodes',
		D_CONTAINERS = 'containers',
		BLOG_MAINIMG = 'main_img',
		BLOG_PUBLICATIONDATE = 'publication_date',
		BLOG_AUTHOR = 'author',
		BLOG_BODYMD = 'md',
		BLOG_TAGS = 'tags',
		BLOG_B_PUBLISH = 'b_publish';

	private
		$visits,
		$nodes,
		$containers;

	public function __construct($visits, $nodes, $containers) {
		parent::__construct();
		$this->key_name = 'foo';
		$this->visits = $visits;
		$this->nodes = $nodes;
		$this->containers = $containers;
	}

	public function getVisits() {
		return $this->visits;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getSubtitle() {
		return $this->subtitle;
	}

	public function getUrl() {
		return $this->url;
	}

	public function getMainImg() {
		return $this->main_img;
	}

	public function getPublicationDate() {
		$d = date_create($this->publication_date);
		return date_format($d, 'd M Y');
	}

	public function getAuthor() {
		return $this->author;
	}

	public function getBody() {
		return $this->body_md;
	}

	public function getTags() {
		return $this->tags;
	}

	public function getPublish() {
		return $this->b_publish;
	}

	public function get_posts() {
		$query = self::createQuery(static::getKindName());
		self::addOrder($query, self::BLOG_PUBLICATIONDATE, $direction = 'descending');
		$results = self::executeQuery($query);
		return static::extractQueryResults($results);
	}

	protected static function getKindName() {
		return self::BLOG_MODEL_KIND;
	}

	/**
	 * Generate the entity property map from the feed object fields.
	 */
	protected function getKindProperties() {
		$property_map = [];

		$property_map[self::BLOG_TITLE] =
			parent::createStringProperty($this->title, true);
		$property_map[self::BLOG_URL] =
			parent::createStringProperty($this->url, true);
		$property_map[self::BLOG_BODYMD] =
			parent::createStringProperty($this->body_md);

		$property_map[self::BLOG_SUBTITLE] =
			parent::createStringProperty($this->subtitle, true);
		$property_map[self::BLOG_PUBLICATIONDATE] =
			parent::createDateProperty($this->publication_date, true);
		$property_map[self::BLOG_AUTHOR] =
			parent::createStringProperty($this->author);

		$property_map[self::BLOG_TAGS] =
			//parent::createStringListProperty(explode(',', $this->tags), true);
			parent::createStringProperty($this->tags);

		$property_map[self::BLOG_MAINIMG] =
			parent::createStringProperty($this->main_img, true);
		$property_map[self::BLOG_B_PUBLISH] =
			parent::createBooleanProperty($this->b_publish, true);

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

		$query = parent::createQuery(self::BLOG_MODEL_KIND);
		$feed_url_filter = parent::createStringFilter(self::BLOG_TITLE,
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
			$key = self::getCacheKey($this->url);
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
		$key = self::getCacheKey($this->url);
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

			$title = $props[self::BLOG_TITLE]->getStringValue();
			$subtitle = $props[self::BLOG_SUBTITLE]->getStringValue();
			$url = $props[self::BLOG_URL]->getStringValue();
			$main_img = $props[self::BLOG_MAINIMG]->getStringValue();
			$publication_date = $props[self::BLOG_PUBLICATIONDATE]->getDateTimeValue();
			$author = $props[self::BLOG_AUTHOR]->getStringValue();
			$body_md = $props[self::BLOG_BODYMD]->getStringValue();
			//$tags = $props[self::BLOG_TAGS]->getListValue();
			$tags = $props[self::BLOG_TAGS]->getStringValue();
			$b_publish = $props[self::BLOG_B_PUBLISH]->getBooleanValue();

			$blog_model = new Ack_Bloc($title, $subtitle, $url, $main_img, $publication_date, $author, $body_md, $tags, $b_publish);
			$blog_model->setKeyId($id);
			$blog_model->setKeyName($key_name);
			// Cache this read feed.
			$blog_model->onItemWrite();

			$query_results[] = $blog_model;
		}
		return $query_results;
	}

	private static function getCacheKey($feed_url) {
		return sprintf("%s_%s", self::BLOG_MODEL_KIND, sha1($feed_url));
	}
}