<?php
/**
 * Tuitter - Twitter Client Class for PHP 5
 *
 * PHP versions 5
 *
 * @author    Naohiko MORI <naohiko.mori@gmail.com>
 * @copyright 2009 Naohiko MORI <naohiko.mori@gmail.com>
 * @license   Dual licensed under the MIT and GPL licenses.
 */

Tuitter::init();

/**
 * Tuitter Main Class
 */
class Tuitter
{
	/**
	 * private members
	 */
	private $_user;
	private $_pass;
	private $_cache;
	private $_client_name;
	private $_client_version;
	private $_client_url;
	private $_retry = 3;
	private $_retryInterval = 1;

	/**
	 * Load modules Tuitter uses
	 *
	 * @access public
	 * @param  string $file relative path to the file of load module
	 */
	public static function load($file)
	{
		static $dir=null;
		if($dir===null) $dir = dirname(__FILE__);
		require_once "{$dir}/{$file}";
	}

	/**
	 * Initialize Tuitter
	 *
	 * @access public
	 */
	public static function init()
	{
		self::load("Http.php");
		self::load("Http/Request.php");
		self::load("Http/Response.php");
		self::load("XmlResult.php");
		self::load("Hash.php");
		self::load("Tweets.php");
		self::load("Tweet.php");
		self::load("DMs.php");
		self::load("DM.php");
		self::load("Users.php");
		self::load("User.php");
		self::load("IDs.php");
		self::load("ID.php");
	}

	/**
	 * Constructor
	 *
	 * @access public
	 * @param  string $user user name of twitter
	 * @param  string $pass password of twitter account
	 */
	public function __construct($user=null, $pass=null)
	{
		$this->_user = $user;
		$this->_pass = $pass;
	}

	/**
	 * Specify cache storage
	 * This storage is used for caching ids of latest received tweets,
	 * followers or DMs. So you have to specify this directory when you
	 * need incremental request.
	 *
	 * @access public
	 * @param  Tuitter_Cache_Interface $cache cache storage
	 */
	public function setCache(Tuitter_Cache_Interface $cache)
	{
		$this->_cache = $cache;
	}

	/**
	 * Specify HTTP cache storage
	 * This storage is used for HTTP caching.
	 * Using HTTP caching with ETag reduces your network resources.
	 *
	 * @access public
	 * param   Tuitter_Cache_Interface $cache cache storage
	 */
	public function setHttpCache(Tuitter_Cache_Interface $cache)
	{
		Tuitter_Http::setCache($cache);
	}

	public function setClientName($name)
	{
		$this->_client_name = $name;
	}

	public function setClientVersion($ver)
	{
		$this->_client_version = $ver;
	}

	public function setClientUrl($url)
	{
		$this->_client_url = $url;
	}

	/**
	 * Set numbers of max retry when the request to twitter has failed.
	 *
	 * @access public
	 * @param  int $retry number of max retry
	 */
	public function setRetry($retry)
	{
		$this->_retry = $retry;
	}

	/**
	 * Set interval between retries.
	 *
	 * @access public
	 * @param  int $retryInterval
	 */
	public function setRetryInterval($retryInterval)
	{
		$this->_retryInterval = $retryInterval;
	}

	/**
	 * Returns public time line
	 *
	 * @access public
	 * @param  array $opt options to request(optional)
	 * @return Tuitter_Tweets
	 */
	public function getPublicTL(array $opt=array())
	{
		$host = 'twitter.com';
		$url = '/statuses/public_timeline';
		return $this->_getTweets($url, $host, $opt, false);
	}

	/**
	 * Returns tweets of user and user's friends
	 *
	 * @access public
	 * @param  array $opt options to request(optional)
	 * @param  string $incrementalKey unique key to recognize process(optional)
	 * @return Tuitter_Tweets
	 */
	public function getFriendsTL(array $opt=array(), $incrementalKey='default')
	{
		$host = 'twitter.com';
		$url = '/statuses/friends_timeline';
		if($id=$this->_popId($opt)) $url .= "/{$id}";
		return $this->_getTweets($url, $host, $opt, $incrementalKey);
	}

	/**
	 * Returns tweets of home timeline
	 *
	 * @access public
	 * @param  array $opt options to request(optional)
	 * @param  string $incrementalKey unique key to recognize process(optional)
	 * @return Tuitter_Tweets
	 */
	public function getHomeTL($opt=array(), $incrementalKey='default')
	{
		$host = 'api.twitter.com';
		$url = '/1/statuses/home_timeline';
		return $this->_getTweets($url, $host, $opt, $incrementalKey);
	}

	/**
	 * Returns tweets of user
	 *
	 * @access public
	 * @param  array $opt options to request(optional)
	 * @param  string $incrementalKey unique key to recognize process(optional)
	 * @return Tuitter_Tweets
	 */
	public function getUserTL($opt=array(), $incrementalKey='default')
	{
		$host = 'twitter.com';
		$url = '/statuses/user_timeline';
		if($id=$this->_popId($opt)) $url .= "/{$id}";
		return $this->_getTweets($url, $host, $opt, $incrementalKey);
	}

	/**
	 * Returns tweets of replies to user.
	 * This API has been deplicated. You should use getMentions as substitute.
	 *
	 * @access public
	 * @param  array $opt options to request(optional)
	 * @param  string $incrementalKey unique key to recognize process(optional)
	 * @return Tuitter_Tweets
	 */
	public function getReplies($opt=array(), $incrementalKey='default')
	{
		$host = 'twitter.com';
		$url = '/statuses/replies';
		return $this->_getTweets($url, $host, $opt, $incrementalKey);
	}

	/**
	 * Returns tweets of being mentioned to user.
	 *
	 * @access public
	 * @param  array $opt options to request(optional)
	 * @param  string $incrementalKey unique key to recognize process(optional)
	 * @return Tuitter_Tweets
	 */
	public function getMentions($opt=array(), $incrementalKey='default')
	{
		$host = 'twitter.com';
		$url = '/statuses/mentions';
		return $this->_getTweets($url, $host, $opt, $incrementalKey);
	}

	public function getRTbyMe($opt=array(), $incrementalKey='default')
	{
		$host = 'api.twitter.com';
		$url = '/1/statuses/retweeted_by_me';
		return $this->_getTweets($url, $host, $opt, $incrementalKey);
	}

	public function getRTtoMe($opt=array(), $incrementalKey='default')
	{
		$host = 'api.twitter.com';
		$url = '/1/statuses/retweeted_to_me';
		return $this->_getTweets($url, $host, $opt, $incrementalKey);
	}

	public function getRTofMe($opt=array(), $incrementalKey='default')
	{
		$host = 'api.twitter.com';
		$url = '/1/statuses/retweets_of_me';
		return $this->_getTweets($url, $host, $opt, $incrementalKey);
	}

	/**
	 * Returns detail of specific tweet.
	 *
	 * @access public
	 * @param  string $id tweet id
	 * @return Tuitter_Tweet
	 */
	public function getMessage($id)
	{
		$host = 'twitter.com';
		$url = "/statuses/show/{$id}";
		$res = $this->_request($url, $host);
		return new Tuitter_Tweet($this, $res->getBody());
	}

	/**
	 * Sends tweet.
	 *
	 * @access public
	 * @param  string $status tweet text
	 * @return Tuitter_Tweet sent tweet
	 */
	public function sendMessage($status, $opt=array())
	{
		$host = 'twitter.com';
		$url = '/statuses/update';
		$opt['status'] = $status;
		$res = $this->_request($url, $host, $opt, 'POST');
		return new Tuitter_Tweet($this, $res->getBody());
	}

	/**
	 * Removes specific tweet.
	 *
	 * @access public
	 * @param  string $id tweet id
	 * @return Tuitter_Tweet removed tweet
	 */
	public function deleteMessage($id)
	{
		$host = 'twitter.com';
		$url = "/statuses/destroy/{$id}";
		$res = $this->_request($url, $host, array(), 'DELETE');
		return new Tuitter_Tweet($this, $res->getBody());
	}

	public function sendRT($id)
	{
		$host = 'api.twitter.com';
		$url = "/1/statuses/retweet/{$id}";
		$res = $this->_request($url, $host, array(), 'POST');
		return new Tuitter_Tweet($this, $res->getBody());
	}

	public function getRTs($id, $opt=array())
	{
		$host = 'api.twitter.com';
		$url = '/1/statuses/retweets/'.$id;
		return $this->_getTweets($url, $host, $opt, $incrementalKey);
	}

	/**
	 * Returns user's followings
	 *
	 * @access public
	 * @param  array $opt options to request(optional)
	 * @param  string $incrementalKey unique key to recognize process(optional)
	 * @return Tuitter_Users following users
	 */
	public function getFollowings($opt=array(), $incrementalKey='default')
	{
		$host = 'twitter.com';
		$url = '/statuses/friends';
		if($id=$this->_popId($opt)) $url .= "/{$id}";
		$res = $this->_request($url, $host, $opt);
		$users = new Tuitter_Users($this, $res->getBody());
		$this->_treatIncrementalUsers($users, $incrementalKey, $url);
		return $users;
	}

	/**
	 * Returns user's followers
	 *
	 * @access public
	 * @param  array $opt options to request(optional)
	 * @param  string $incrementalKey unique key to recognize process(optional)
	 * @return Tuitter_Users followers
	 */
	public function getFollowers($opt=array(), $incrementalKey='default')
	{
		$host = 'twitter.com';
		$url = '/statuses/followers';
		if($id=$this->_popId($opt)) $url .= "/{$id}";
		$res = $this->_request($url, $host, $opt);
		$users = new Tuitter_Users($this, $res->getBody());
		$this->_treatIncrementalUsers($users, $incrementalKey, $url);
		return $users;
	}

	/**
	 * Returns specific user's status
	 *
	 * @access public
	 * @param  string $id user id
	 * @return Tuitter_User
	 */
	public function getUser($screen_name)
	{
		$host = 'twitter.com';
		$url = "/users/show";
		$opt = array('screen_name' => $screen_name);
		$res = $this->_request($url, $host, $opt);
		return new Tuitter_User($this, $res->getBody());
	}

	public function getUserStatusById($id)
	{
		$host = 'twitter.com';
		$url = "/users/show";
		$opt = array('user_id' => $id);
		$res = $this->_request($url, $host, $opt);
		return new Tuitter_User($this, $res->getBody());
	}

	public function searchUsers($q)
	{
		$host = 'api.twitter.com';
		$url = '/1/users/search';
		$opt['q'] = $q;
		$res = $this->_request($url, $host, $opt);
		$users = new Tuitter_Users($this, $res->getBody());
		$this->_treatIncrementalUsers($users, $incrementalKey, $url);
		return $users;
	}

	/**
	 * Returns direct messages to user
	 *
	 * @access public
	 * @param  array $opt options to request(optional)
	 * @param  string $incrementalKey unique key to recognize process(optional)
	 * @return Tuitter_DMs
	 */
	public function getDMs($opt=array(), $incrementalKey='default')
	{
		$host = 'twitter.com';
		$url = '/direct_messages';
		return $this->_getDMs($url, $host, $opt, $incrementalKey);
	}

	/**
	 * Returns direct messages by user
	 *
	 * @access public
	 * @param  array $opt options to request(optional)
	 * @param  string $incrementalKey unique key to recognize process(optional)
	 * @return Tuitter_DMs
	 */
	public function getSentDMs($opt=array(), $incrementalKey='default')
	{
		$host = 'twitter.com';
		$url = '/direct_messages/sent';
		return $this->_getDMs($url, $host, $opt, $incrementalKey);
	}

	/**
	 * Sends direct message
	 *
	 * @access public
	 * @param  string $id id of user who send to
	 * @param  string $text message
	 * @return Tuitter_DM sent message
	 */
	public function sendDM($screen_name, $text)
	{
		$host = 'twitter.com';
		$url = '/direct_messages/new';
		$opt['screen_name'] = $screen_name;
		$opt['text'] = $text;
		$res = $this->_request($url, $host, $opt, 'POST');
		return new Tuitter_DM($this, $res->getBody());
	}

	public function sendDMbyId($id, $text)
	{
		$host = 'twitter.com';
		$url = '/direct_messages/new';
		$opt['user_id'] = $id;
		$opt['text'] = $text;
		$res = $this->_request($url, $host, $opt, 'POST');
		return new Tuitter_DM($this, $res->getBody());
	}

	/**
	 * Removes direct message
	 *
	 * @access public
	 * @param  string $id id of dm which would be removed
	 * @return Tuitter_DM removed message
	 */
	public function deleteDM($id)
	{
		$host = 'twitter.com';
		$url = "/direct_messages/destroy/{$id}";
		$res = $this->_request($url, $host, array(), 'DELETE');
		return new Tuitter_DM($this, $res->getBody());
	}

	/**
	 * Follows user
	 *
	 * @access public
	 * @param  string $id user id
	 * @return Tuitter_User following user
	 */
	public function follow($screen_name)
	{
		$host = 'twitter.com';
		$url = "/friendships/create";
		$opt = array('screen_name' => $screen_name);
		$res = $this->_request($url, $host, $opt, 'POST');
		return new Tuitter_User($this, $res->getBody());
	}

	public function followById($user_id)
	{
		$host = 'twitter.com';
		$url = "/friendships/create";
		$opt = array('user_id' => $user_id);
		$res = $this->_request($url, $host, $opt, 'POST');
		return new Tuitter_User($this, $res->getBody());
	}

	/**
	 * Stops following user
	 *
	 * @access public
	 * @param  string $id user id
	 * @return Tuitter_User stop following user
	 */
	public function unfollow($screen_name)
	{
		$host = 'twitter.com';
		$url = "/friendships/destroy";
		$opt = array('screen_name' => $screen_name);
		$res = $this->_request($url, $host, $opt, 'POST');
		return new Tuitter_User($this, $res->getBody());
	}

	public function unfollowById($user_id)
	{
		$host = 'twitter.com';
		$url = "/friendships/destroy";
		$opt = array('user_id' => $user_id);
		$res = $this->_request($url, $host, $opt, 'POST');
		return new Tuitter_User($this, $res->getBody());
	}

	/**
	 * Tests for the existence of friendship between two users.
	 *
	 * @access public
	 * @param  string $id_a the id or screen name of the subject user
	 * @param  string $id_b the id or screen name of the user to test for following
	 * @return bool
	 */
	public function isFollowing($id_b, $id_a=null)
	{
		$host = 'twitter.com';
		$url = '/friendships/exists';
		if($id_a===null){
			$id_a = $this->_user;
		}
		$opt['user_a'] = $id_a;
		$opt['user_b'] = $id_b;
		$res = $this->_request($url, $host, $opt);
		$hash = new Tuitter_Hash($this, $res->getBody());
		return ($hash->friends=='true');
	}

	public function isFollowed($id_a, $id_b=null)
	{
		$host = 'twitter.com';
		$url = '/friendships/exists';
		if($id_b===null){
			$id_b = $this->_user;
		}
		$opt['user_a'] = $id_a;
		$opt['user_b'] = $id_b;
		$res = $this->_request($url, $host, $opt);
		$hash = new Tuitter_Hash($this, $res->getBody());
		return ($hash->friends=='true');
	}

	/**
	 * Returns IDs for every followings.
	 *
	 * @access public
	 * @param  array $opt options to request(optional)
	 * @return Tuitter_IDs
	 */
	public function getFollowingIDs($opt=array())
	{
		$host = 'twitter.com';
		$url = '/friends/ids';
		if($id=$this->_popId($opt)) $url .= "/{$id}";
		$res = $this->_request($url, $host, $opt);
		return new Tuitter_IDs($this, $res->getBody());
	}

	/**
	 * Returns IDs for every followers.
	 *
	 * @access public
	 * @param  array $opt options to request(optional)
	 * @return Tuitter_IDs
	 */
	public function getFollowerIDs($opt=array())
	{
		$host = 'twitter.com';
		$url = '/followers/ids';
		if($id=$this->_popId($opt)) $url .= "/{$id}";
		$res = $this->_request($url, $host, $opt);
		return new Tuitter_IDs($this, $res->getBody());
	}

	public function getFavorites($opt=array())
	{
		$host = 'twitter.com';
		$url = '/favorites';
		if($id=$this->_popId($opt)) $url .= "/{$id}";
		return $this->_getTweets($url, $host, $opt, false);
	}

	public function markFavorite($id)
	{
		$host = 'twitter.com';
		$url = "/favorites/create/{$id}";
		$res = $this->_request($url, $host, array(), 'POST');
		return new Tuitter_Tweet($this, $res->getBody());
	}

	public function unmarkFavorite($id)
	{
		$host = 'twitter.com';
		$url = "/favorites/destroy/{$id}";
		$res = $this->_request($url, $host, array(), 'POST');
		return new Tuitter_Tweet($this, $res->getBody());
	}

	private function _popId(&$opt)
	{
		$id = null;
		if(isset($opt['id'])){
			$id = $opt['id'];
			unset($opt['id']);
		}
		return $id;
	}

	private function _getTweets($url, $host, $opt, $incrementalKey)
	{
		$opt = $this->_setIncrementalOpt($incrementalKey, $url, $opt);
		$res = $this->_request($url, $host, $opt);
		$t = new Tuitter_Tweets($this, $res->getBody());
		$this->_putIncrementalId($incrementalKey, $url, $t);
		return $t;
	}

	private function _getDMs($url, $host, $opt, $incrementalKey)
	{
		$opt = $this->_setIncrementalOpt($incrementalKey, $url, $opt);
		$res = $this->_request($url, $host, $opt);
		$t = new Tuitter_DMs($this, $res->getBody());
		$this->_putIncrementalId($incrementalKey, $url, $t);
		return $t;
	}

	private function _getUsers($url, $host, $opt)
	{
		$res = $this->_request($url, $host, $opt);
		$t = new Tuitter_Users($this, $res->getBody());
		return $t;
	}

	private function _setIncrementalOpt($key, $url, $opt)
	{
		if($key and $this->_cache){
			$fullKey = sha1($this->_user.'@'.$url.'/'.$key).'.since_id';
			if($since_id = $this->_cache->get($fullKey)){
				$opt['since_id'] = $since_id;
			}
		}
		return $opt;
	}

	private function _putIncrementalId($key, $url, $tweets)
	{
		if($key and $this->_cache){
			if($max_id = $tweets->getMaxId()){
				$fullKey = sha1($this->_user.'@'.$url.'/'.$key).'.since_id';
				$this->_cache->put($fullKey, $max_id);
			}
		}
	}

	private function _treatIncrementalUsers(Tuitter_Users &$users, $key, $url)
	{
		if($key and $this->_cache){
			$cacheKey = sha1($this->_user.'@'.$url.'/'.$key).'.users';
			$v = $this->_cache->get($cacheKey);
			$cached = ($v ? unserialize($v) : array());
			$users->intersect($cached);
			foreach($users as $user){
				$cached[$user->id] = true;
			}
			$this->_cache->put($cacheKey, serialize($cached));
		}
	}

	private function _request($url, $host, $opt=array(), $method='GET', $auth=true)
	{
		$req = new Tuitter_Http_Request("{$url}.xml", $host);
		if($auth) $req->setBasicAuth($this->_user, $this->_pass);
		$headers = array();
		if($this->_client_name)
			$headers['X-Twitter-Client'] = $this->_client_name;
		if($this->_client_version)
			$headers['X-Twitter-Version'] = $this->_client_version;
		if($this->_client_url)
			$headers['X-Twitter-URL'] = $this->_client_url;
		$req->setHeaders($headers);
		$req->setPrms($opt);
		$res = Tuitter_Http::send($req, $method);
		$retry = $this->_retry;
		while($res->getStatus()>=500 and $retry--){
			sleep($this->_retryInterval);
			$res = Tuitter_Http::send($req, $method);
		}
		if($res->getStatus()>=400){
			throw new Exception($this->_getErrMsgByHttp($res));
		}
		return $res;
	}

	private function _getErrMsgByHttp(Tuitter_Http_Response $res)
	{
		$sts = $res->getStatus();
		$msg = "Twitter returned HTTP code '{$sts}'. ";
		switch($sts){
			case 400:
				break;
			case 401:
				$msg .= 'Authentication credentials were missing or incorrect.';
				break;
			case 403:
				$msg .= 'The request is understood, but it has been refused.  An accompanying error message will explain why. This code is used when requests are being denied due to update limits.';
				break;
			case 404:
				$msg .= 'The URI requested is invalid or the resource requested, such as a user, does not exists.';
				break;
			case 406:
				$msg .= 'Returned by the Search API when an invalid format is specified in the request.';
				break;
			case 500:
				$msg .= 'Something is broken.  Please post to the group so the Twitter team can investigate.';
				break;
			case 502:
				$msg .= 'Twitter is down or being upgraded.';
				break;
			case 503:
				$msg .= 'The Twitter servers are up, but overloaded with requests. Try again later. The search and trend methods use this to indicate when you are being rate limited.';
				break;
			default:
				$msg .= 'Unexpected error has occurred.';
		}
		$hash = new Tuitter_Hash($this, $res->getBody());
		if($hash->error){
			$msg .= ' The error message is "'.html_entity_decode($hash->error, ENT_QUOTES, 'UTF-8').'".';
		}
		return $msg;
	}
}
