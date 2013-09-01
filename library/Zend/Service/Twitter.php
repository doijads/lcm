<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Twitter
<<<<<<< HEAD
 * @copyright  Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Twitter.php 25288 2013-03-13 13:36:39Z matthew $
 */

/**
 * @see Zend_Http_Client
 */
require_once 'Zend/Http/Client.php';

/**
 * @see Zend_Http_CookieJar
 */
require_once 'Zend/Http/CookieJar.php';
=======
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Twitter.php 23877 2011-04-28 20:17:01Z ralph $
 */

/**
 * @see Zend_Rest_Client
 */
require_once 'Zend/Rest/Client.php';

/**
 * @see Zend_Rest_Client_Result
 */
require_once 'Zend/Rest/Client/Result.php';
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa

/**
 * @see Zend_Oauth_Consumer
 */
require_once 'Zend/Oauth/Consumer.php';

/**
<<<<<<< HEAD
 * @see Zend_Oauth_Token_Access
 */
require_once 'Zend/Oauth/Token/Access.php';

/**
 * @see Zend_Service_Twitter_Response
 */
require_once 'Zend/Service/Twitter/Response.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Twitter
 * @copyright  Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Twitter
{
    /**
     * Base URI for all API calls
     */
    const API_BASE_URI = 'https://api.twitter.com/1.1/';

    /**
     * OAuth Endpoint
     */
    const OAUTH_BASE_URI = 'https://api.twitter.com/oauth';
=======
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Twitter
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Twitter extends Zend_Rest_Client
{
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa

    /**
     * 246 is the current limit for a status message, 140 characters are displayed
     * initially, with the remainder linked from the web UI or client. The limit is
     * applied to a html encoded UTF-8 string (i.e. entities are counted in the limit
     * which may appear unusual but is a security measure).
     *
     * This should be reviewed in the future...
     */
    const STATUS_MAX_CHARACTERS = 246;

    /**
<<<<<<< HEAD
     * @var array
     */
    protected $cookieJar;
=======
     * OAuth Endpoint
     */
    const OAUTH_BASE_URI = 'http://twitter.com/oauth';

    /**
     * @var Zend_Http_CookieJar
     */
    protected $_cookieJar;
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa

    /**
     * Date format for 'since' strings
     *
     * @var string
     */
<<<<<<< HEAD
    protected $dateFormat = 'D, d M Y H:i:s T';

    /**
     * @var Zend_Http_Client
     */
    protected $httpClient = null;
=======
    protected $_dateFormat = 'D, d M Y H:i:s T';

    /**
     * Username
     *
     * @var string
     */
    protected $_username;
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa

    /**
     * Current method type (for method proxying)
     *
     * @var string
     */
<<<<<<< HEAD
    protected $methodType;

    /**
     * Oauth Consumer
     *
     * @var Zend_Oauth_Consumer
     */
    protected $oauthConsumer = null;
=======
    protected $_methodType;

    /**
     * Zend_Oauth Consumer
     *
     * @var Zend_Oauth_Consumer
     */
    protected $_oauthConsumer = null;
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa

    /**
     * Types of API methods
     *
     * @var array
     */
<<<<<<< HEAD
    protected $methodTypes = array(
        'account',
        'application',
        'blocks',
        'directmessages',
        'favorites',
        'friendships',
        'search',
        'statuses',
        'users',
=======
    protected $_methodTypes = array(
        'status',
        'user',
        'directMessage',
        'friendship',
        'account',
        'favorite',
        'block'
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    );

    /**
     * Options passed to constructor
     *
     * @var array
     */
<<<<<<< HEAD
    protected $options = array();

    /**
     * Username
     *
     * @var string
     */
    protected $username;
=======
    protected $_options = array();

    /**
     * Local HTTP Client cloned from statically set client
     *
     * @var Zend_Http_Client
     */
    protected $_localHttpClient = null;
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa

    /**
     * Constructor
     *
<<<<<<< HEAD
     * @param  null|array|Zend_Config $options
     * @param  null|Zend_Oauth_Consumer $consumer
     * @param  null|Zend_Http_Client $httpClient
     */
    public function __construct($options = null, Zend_Oauth_Consumer $consumer = null, Zend_Http_Client $httpClient = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        if (!is_array($options)) {
            $options = array();
        }

        $this->options = $options;

        if (isset($options['username'])) {
            $this->setUsername($options['username']);
        }

        $accessToken = false;
        if (isset($options['accessToken'])) {
            $accessToken = $options['accessToken'];
        } elseif (isset($options['access_token'])) {
            $accessToken = $options['access_token'];
        }

        $oauthOptions = array();
        if (isset($options['oauthOptions'])) {
            $oauthOptions = $options['oauthOptions'];
        } elseif (isset($options['oauth_options'])) {
            $oauthOptions = $options['oauth_options'];
        }
        $oauthOptions['siteUrl'] = self::OAUTH_BASE_URI;

        $httpClientOptions = array();
        if (isset($options['httpClientOptions'])) {
            $httpClientOptions = $options['httpClientOptions'];
        } elseif (isset($options['http_client_options'])) {
            $httpClientOptions = $options['http_client_options'];
        }

        // If we have an OAuth access token, use the HTTP client it provides
        if ($accessToken && is_array($accessToken)
            && (isset($accessToken['token']) && isset($accessToken['secret']))
        ) {
            $token = new Zend_Oauth_Token_Access();
            $token->setToken($accessToken['token']);
            $token->setTokenSecret($accessToken['secret']);
            $accessToken = $token;
        }
        if ($accessToken && $accessToken instanceof Zend_Oauth_Token_Access) {
            $oauthOptions['token'] = $accessToken;
            $this->setHttpClient($accessToken->getHttpClient($oauthOptions, self::OAUTH_BASE_URI, $httpClientOptions));
            return;
        }

        // See if we were passed an http client
        if (isset($options['httpClient']) && null === $httpClient) {
            $httpClient = $options['httpClient'];
        } elseif (isset($options['http_client']) && null === $httpClient) {
            $httpClient = $options['http_client'];
        }
        if ($httpClient instanceof Zend_Http_Client) {
            $this->httpClient = $httpClient;
        } else {
            $this->setHttpClient(new Zend_Http_Client(null, $httpClientOptions));
        }

        // Set the OAuth consumer
        if ($consumer === null) {
            $consumer = new Zend_Oauth_Consumer($oauthOptions);
        }
        $this->oauthConsumer = $consumer;
=======
     * @param  array $options Optional options array
     * @return void
     */
    public function __construct($options = null, Zend_Oauth_Consumer $consumer = null)
    {
        $this->setUri('http://api.twitter.com');
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (!is_array($options)) {
            $options = array();
        }
        $options['siteUrl'] = self::OAUTH_BASE_URI;

        $this->_options = $options;
        if (isset($options['username'])) {
            $this->setUsername($options['username']);
        }
        if (isset($options['accessToken'])
        && $options['accessToken'] instanceof Zend_Oauth_Token_Access) {
            $this->setLocalHttpClient($options['accessToken']->getHttpClient($options));
        } else {
            $this->setLocalHttpClient(clone self::getHttpClient());
            if ($consumer === null) {
                $this->_oauthConsumer = new Zend_Oauth_Consumer($options);
            } else {
                $this->_oauthConsumer = $consumer;
            }
        }
    }

    /**
     * Set local HTTP client as distinct from the static HTTP client
     * as inherited from Zend_Rest_Client.
     *
     * @param Zend_Http_Client $client
     * @return self
     */
    public function setLocalHttpClient(Zend_Http_Client $client)
    {
        $this->_localHttpClient = $client;
        $this->_localHttpClient->setHeaders('Accept-Charset', 'ISO-8859-1,utf-8');
        return $this;
    }

    /**
     * Get the local HTTP client as distinct from the static HTTP client
     * inherited from Zend_Rest_Client
     *
     * @return Zend_Http_Client
     */
    public function getLocalHttpClient()
    {
        return $this->_localHttpClient;
    }

    /**
     * Checks for an authorised state
     *
     * @return bool
     */
    public function isAuthorised()
    {
        if ($this->getLocalHttpClient() instanceof Zend_Oauth_Client) {
            return true;
        }
        return false;
    }

    /**
     * Retrieve username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     * Set username
     *
     * @param  string $value
     * @return Zend_Service_Twitter
     */
    public function setUsername($value)
    {
        $this->_username = $value;
        return $this;
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    }

    /**
     * Proxy service methods
     *
     * @param  string $type
<<<<<<< HEAD
     * @return Twitter
     * @throws Exception\DomainException If method not in method types list
     */
    public function __get($type)
    {
        $type = strtolower($type);
        $type = str_replace('_', '', $type);
        if (!in_array($type, $this->methodTypes)) {
            require_once 'Zend/Service/Twitter/Exception.php';
=======
     * @return Zend_Service_Twitter
     * @throws Zend_Service_Twitter_Exception If method not in method types list
     */
    public function __get($type)
    {
        if (!in_array($type, $this->_methodTypes)) {
            include_once 'Zend/Service/Twitter/Exception.php';
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
            throw new Zend_Service_Twitter_Exception(
                'Invalid method type "' . $type . '"'
            );
        }
<<<<<<< HEAD
        $this->methodType = $type;
=======
        $this->_methodType = $type;
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
        return $this;
    }

    /**
     * Method overloading
     *
     * @param  string $method
     * @param  array $params
     * @return mixed
<<<<<<< HEAD
     * @throws Exception\BadMethodCallException if unable to find method
     */
    public function __call($method, $params)
    {
        if (method_exists($this->oauthConsumer, $method)) {
            $return = call_user_func_array(array($this->oauthConsumer, $method), $params);
            if ($return instanceof Zend_Oauth_Token_Access) {
                $this->setHttpClient($return->getHttpClient($this->options));
            }
            return $return;
        }
        if (empty($this->methodType)) {
            require_once 'Zend/Service/Twitter/Exception.php';
=======
     * @throws Zend_Service_Twitter_Exception if unable to find method
     */
    public function __call($method, $params)
    {
        if (method_exists($this->_oauthConsumer, $method)) {
            $return = call_user_func_array(array($this->_oauthConsumer, $method), $params);
            if ($return instanceof Zend_Oauth_Token_Access) {
                $this->setLocalHttpClient($return->getHttpClient($this->_options));
            }
            return $return;
        }
        if (empty($this->_methodType)) {
            include_once 'Zend/Service/Twitter/Exception.php';
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
            throw new Zend_Service_Twitter_Exception(
                'Invalid method "' . $method . '"'
            );
        }
<<<<<<< HEAD

        $test = str_replace('_', '', strtolower($method));
        $test = $this->methodType . $test;
        if (!method_exists($this, $test)) {
            require_once 'Zend/Service/Twitter/Exception.php';
=======
        $test = $this->_methodType . ucfirst($method);
        if (!method_exists($this, $test)) {
            include_once 'Zend/Service/Twitter/Exception.php';
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
            throw new Zend_Service_Twitter_Exception(
                'Invalid method "' . $test . '"'
            );
        }

        return call_user_func_array(array($this, $test), $params);
    }

    /**
<<<<<<< HEAD
     * Set HTTP client
     *
     * @param Zend_Http_Client $client
     * @return self
     */
    public function setHttpClient(Zend_Http_Client $client)
    {
        $this->httpClient = $client;
        $this->httpClient->setHeaders(array('Accept-Charset' => 'ISO-8859-1,utf-8'));
        return $this;
    }

    /**
     * Get the HTTP client
     *
     * Lazy loads one if none present
     *
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        if (null === $this->httpClient) {
            $this->setHttpClient(new Zend_Http_Client());
        }
        return $this->httpClient;
    }

    /**
     * Retrieve username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username
     *
     * @param  string $value
     * @return self
     */
    public function setUsername($value)
    {
        $this->username = $value;
        return $this;
    }

    /**
     * Checks for an authorised state
     *
     * @return bool
     */
    public function isAuthorised()
    {
        if ($this->getHttpClient() instanceof Zend_Oauth_Client) {
            return true;
        }
        return false;
    }

    /**
     * Verify Account Credentials
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function accountVerifyCredentials()
    {
        $this->init();
        $response = $this->get('account/verify_credentials');
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Returns the number of api requests you have left per hour.
     *
     * @todo   Have a separate payload object to represent rate limits
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function applicationRateLimitStatus()
    {
        $this->init();
        $response = $this->get('application/rate_limit_status');
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Blocks the user specified in the ID parameter as the authenticating user.
     * Destroys a friendship to the blocked user if it exists.
     *
     * @param  integer|string $id       The ID or screen name of a user to block.
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function blocksCreate($id)
    {
        $this->init();
        $path     = 'blocks/create';
        $params   = $this->createUserParameter($id, array());
        $response = $this->post($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Un-blocks the user specified in the ID parameter for the authenticating user
     *
     * @param  integer|string $id       The ID or screen_name of the user to un-block.
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function blocksDestroy($id)
    {
        $this->init();
        $path   = 'blocks/destroy';
        $params = $this->createUserParameter($id, array());
        $response = $this->post($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Returns an array of user ids that the authenticating user is blocking
     *
     * @param  integer $cursor  Optional. Specifies the cursor position at which to begin listing ids; defaults to first "page" of results.
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function blocksIds($cursor = -1)
    {
        $this->init();
        $path = 'blocks/ids';
        $response = $this->get($path, array('cursor' => $cursor));
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Returns an array of user objects that the authenticating user is blocking
     *
     * @param  integer $cursor  Optional. Specifies the cursor position at which to begin listing ids; defaults to first "page" of results.
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function blocksList($cursor = -1)
    {
        $this->init();
        $path = 'blocks/list';
        $response = $this->get($path, array('cursor' => $cursor));
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Destroy a direct message
     *
     * @param  int $id ID of message to destroy
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function directMessagesDestroy($id)
    {
        $this->init();
        $path     = 'direct_messages/destroy';
        $params   = array('id' => $this->validInteger($id));
        $response = $this->post($path, $params);
        return new Zend_Service_Twitter_Response($response);
=======
     * Initialize HTTP authentication
     *
     * @return void
     */
    protected function _init()
    {
        if (!$this->isAuthorised() && $this->getUsername() !== null) {
            require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Twitter session is unauthorised. You need to initialize '
                . 'Zend_Service_Twitter with an OAuth Access Token or use '
                . 'its OAuth functionality to obtain an Access Token before '
                . 'attempting any API actions that require authorisation'
            );
        }
        $client = $this->_localHttpClient;
        $client->resetParameters();
        if (null == $this->_cookieJar) {
            $client->setCookieJar();
            $this->_cookieJar = $client->getCookieJar();
        } else {
            $client->setCookieJar($this->_cookieJar);
        }
    }

    /**
     * Set date header
     *
     * @param  int|string $value
     * @deprecated Not supported by Twitter since April 08, 2009
     * @return void
     */
    protected function _setDate($value)
    {
        if (is_int($value)) {
            $date = date($this->_dateFormat, $value);
        } else {
            $date = date($this->_dateFormat, strtotime($value));
        }
        $this->_localHttpClient->setHeaders('If-Modified-Since', $date);
    }

    /**
     * Public Timeline status
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function statusPublicTimeline()
    {
        $this->_init();
        $path = '/1/statuses/public_timeline.xml';
        $response = $this->_get($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Friend Timeline Status
     *
     * $params may include one or more of the following keys
     * - id: ID of a friend whose timeline you wish to receive
     * - count: how many statuses to return
     * - since_id: return results only after the specific tweet
     * - page: return page X of results
     *
     * @param  array $params
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return void
     */
    public function statusFriendsTimeline(array $params = array())
    {
        $this->_init();
        $path = '/1/statuses/friends_timeline';
        $_params = array();
        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
                case 'count':
                    $count = (int) $value;
                    if (0 >= $count) {
                        $count = 1;
                    } elseif (200 < $count) {
                        $count = 200;
                    }
                    $_params['count'] = (int) $count;
                    break;
                case 'since_id':
                    $_params['since_id'] = $this->_validInteger($value);
                    break;
                case 'page':
                    $_params['page'] = (int) $value;
                    break;
                case 'max_id':
                    $_params['max_id'] = $this->_validInteger($value);
                    break;
                case 'include_rts':
                case 'trim_user':
                case 'include_entities':
                    $_params[strtolower($key)] = $value ? '1' : '0';
                    break;                    
                default:
                    break;
            }
        }
        $path .= '.xml';
        $response = $this->_get($path, $_params);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * User Timeline status
     *
     * $params may include one or more of the following keys
     * - id: ID of a friend whose timeline you wish to receive
     * - since_id: return results only after the tweet id specified
     * - page: return page X of results
     * - count: how many statuses to return
     * - max_id: returns only statuses with an ID less than or equal to the specified ID
     * - user_id: specifies the ID of the user for whom to return the user_timeline
     * - screen_name: specfies the screen name of the user for whom to return the user_timeline
     * - include_rts: whether or not to return retweets
     * - trim_user: whether to return just the user ID or a full user object; omit to return full object
     * - include_entities: whether or not to return entities nodes with tweet metadata
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function statusUserTimeline(array $params = array())
    {
        $this->_init();
        $path = '/1/statuses/user_timeline';
        $_params = array();
        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
                case 'id':
                    $path .= '/' . $value;
                    break;
                case 'page':
                    $_params['page'] = (int) $value;
                    break;
                case 'count':
                    $count = (int) $value;
                    if (0 >= $count) {
                        $count = 1;
                    } elseif (200 < $count) {
                        $count = 200;
                    }
                    $_params['count'] = $count;
                    break;
                case 'user_id':
                    $_params['user_id'] = $this->_validInteger($value);
                    break;
                case 'screen_name':
                    $_params['screen_name'] = $this->_validateScreenName($value);
                    break;
                case 'since_id':
                    $_params['since_id'] = $this->_validInteger($value);
                    break;
                case 'max_id':
                    $_params['max_id'] = $this->_validInteger($value);
                    break;
                case 'include_rts':
                case 'trim_user':
                case 'include_entities':
                    $_params[strtolower($key)] = $value ? '1' : '0';
                    break;
                default:
                    break;
            }
        }
        $path .= '.xml';
        $response = $this->_get($path, $_params);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Show a single status
     *
     * @param  int $id Id of status to show
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function statusShow($id)
    {
        $this->_init();
        $path = '/1/statuses/show/' . $this->_validInteger($id) . '.xml';
        $response = $this->_get($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Update user's current status
     *
     * @param  string $status
     * @param  int $in_reply_to_status_id
     * @return Zend_Rest_Client_Result
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Zend_Service_Twitter_Exception if message is too short or too long
     */
    public function statusUpdate($status, $inReplyToStatusId = null)
    {
        $this->_init();
        $path = '/1/statuses/update.xml';
        $len = iconv_strlen(htmlspecialchars($status, ENT_QUOTES, 'UTF-8'), 'UTF-8');
        if ($len > self::STATUS_MAX_CHARACTERS) {
            include_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Status must be no more than '
                . self::STATUS_MAX_CHARACTERS
                . ' characters in length'
            );
        } elseif (0 == $len) {
            include_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Status must contain at least one character'
            );
        }
        $data = array('status' => $status);
        if (is_numeric($inReplyToStatusId) && !empty($inReplyToStatusId)) {
            $data['in_reply_to_status_id'] = $inReplyToStatusId;
        }
        $response = $this->_post($path, $data);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Get status replies
     *
     * $params may include one or more of the following keys
     * - since_id: return results only after the specified tweet id
     * - page: return page X of results
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function statusReplies(array $params = array())
    {
        $this->_init();
        $path = '/1/statuses/mentions.xml';
        $_params = array();
        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
                case 'since_id':
                    $_params['since_id'] = $this->_validInteger($value);
                    break;
                case 'page':
                    $_params['page'] = (int) $value;
                    break;
                default:
                    break;
            }
        }
        $response = $this->_get($path, $_params);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Destroy a status message
     *
     * @param  int $id ID of status to destroy
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function statusDestroy($id)
    {
        $this->_init();
        $path = '/1/statuses/destroy/' . $this->_validInteger($id) . '.xml';
        $response = $this->_post($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * User friends
     *
     * @param  int|string $id Id or username of user for whom to fetch friends
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function userFriends(array $params = array())
    {
        $this->_init();
        $path = '/1/statuses/friends';
        $_params = array();

        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
                case 'id':
                    $path .= '/' . $value;
                    break;
                case 'page':
                    $_params['page'] = (int) $value;
                    break;
                default:
                    break;
            }
        }
        $path .= '.xml';

        $response = $this->_get($path, $_params);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * User Followers
     *
     * @param  bool $lite If true, prevents inline inclusion of current status for followers; defaults to false
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function userFollowers($lite = false)
    {
        $this->_init();
        $path = '/1/statuses/followers.xml';
        if ($lite) {
            $this->lite = 'true';
        }
        $response = $this->_get($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Show extended information on a user
     *
     * @param  int|string $id User ID or name
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function userShow($id)
    {
        $this->_init();
        $path = '/1/users/show.xml';
        $response = $this->_get($path, array('id'=>$id));
        return new Zend_Rest_Client_Result($response->getBody());
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    }

    /**
     * Retrieve direct messages for the current user
     *
<<<<<<< HEAD
     * $options may include one or more of the following keys
     * - count: return page X of results
     * - since_id: return statuses only greater than the one specified
     * - max_id: return statuses with an ID less than (older than) or equal to that specified
     * - include_entities: setting to false will disable embedded entities
     * - skip_status:setting to true, "t", or 1 will omit the status in returned users
     *
     * @param  array $options
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function directMessagesMessages(array $options = array())
    {
        $this->init();
        $path   = 'direct_messages';
        $params = array();
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'count':
                    $params['count'] = (int) $value;
                    break;
                case 'since_id':
                    $params['since_id'] = $this->validInteger($value);
                    break;
                case 'max_id':
                    $params['max_id'] = $this->validInteger($value);
                    break;
                case 'include_entities':
                    $params['include_entities'] = (bool) $value;
                    break;
                case 'skip_status':
                    $params['skip_status'] = (bool) $value;
=======
     * $params may include one or more of the following keys
     * - since_id: return statuses only greater than the one specified
     * - page: return page X of results
     *
     * @param  array $params
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function directMessageMessages(array $params = array())
    {
        $this->_init();
        $path = '/1/direct_messages.xml';
        $_params = array();
        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
                case 'since_id':
                    $_params['since_id'] = $this->_validInteger($value);
                    break;
                case 'page':
                    $_params['page'] = (int) $value;
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
                    break;
                default:
                    break;
            }
        }
<<<<<<< HEAD
        $response = $this->get($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Send a direct message to a user
     *
     * @param  int|string $user User to whom to send message
     * @param  string $text Message to send to user
     * @throws Exception\InvalidArgumentException if message is empty
     * @throws Exception\OutOfRangeException if message is too long
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function directMessagesNew($user, $text)
    {
        $this->init();
        $path = 'direct_messages/new';

        $len = iconv_strlen($text, 'UTF-8');
        if (0 == $len) {
            require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Direct message must contain at least one character'
            );
        } elseif (140 < $len) {
            require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Direct message must contain no more than 140 characters'
            );
        }

        $params         = $this->createUserParameter($user, array());
        $params['text'] = $text;
        $response       = $this->post($path, $params);
        return new Zend_Service_Twitter_Response($response);
=======
        $response = $this->_get($path, $_params);
        return new Zend_Rest_Client_Result($response->getBody());
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    }

    /**
     * Retrieve list of direct messages sent by current user
     *
<<<<<<< HEAD
     * $options may include one or more of the following keys
     * - count: return page X of results
     * - page: return starting at page
     * - since_id: return statuses only greater than the one specified
     * - max_id: return statuses with an ID less than (older than) or equal to that specified
     * - include_entities: setting to false will disable embedded entities
     *
     * @param  array $options
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function directMessagesSent(array $options = array())
    {
        $this->init();
        $path   = 'direct_messages/sent';
        $params = array();
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'count':
                    $params['count'] = (int) $value;
                    break;
                case 'page':
                    $params['page'] = (int) $value;
                    break;
                case 'since_id':
                    $params['since_id'] = $this->validInteger($value);
                    break;
                case 'max_id':
                    $params['max_id'] = $this->validInteger($value);
                    break;
                case 'include_entities':
                    $params['include_entities'] = (bool) $value;
=======
     * $params may include one or more of the following keys
     * - since_id: return statuses only greater than the one specified
     * - page: return page X of results
     *
     * @param  array $params
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function directMessageSent(array $params = array())
    {
        $this->_init();
        $path = '/1/direct_messages/sent.xml';
        $_params = array();
        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
                case 'since_id':
                    $_params['since_id'] = $this->_validInteger($value);
                    break;
                case 'page':
                    $_params['page'] = (int) $value;
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
                    break;
                default:
                    break;
            }
        }
<<<<<<< HEAD
        $response = $this->get($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Mark a status as a favorite
     *
     * @param  int $id Status ID you want to mark as a favorite
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function favoritesCreate($id)
    {
        $this->init();
        $path     = 'favorites/create';
        $params   = array('id' => $this->validInteger($id));
        $response = $this->post($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Remove a favorite
     *
     * @param  int $id Status ID you want to de-list as a favorite
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function favoritesDestroy($id)
    {
        $this->init();
        $path     = 'favorites/destroy';
        $params   = array('id' => $this->validInteger($id));
        $response = $this->post($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Fetch favorites
     *
     * $options may contain one or more of the following:
     * - user_id: Id of a user for whom to fetch favorites
     * - screen_name: Screen name of a user for whom to fetch favorites
     * - count: number of tweets to attempt to retrieve, up to 200
     * - since_id: return results only after the specified tweet id
     * - max_id: return results with an ID less than (older than) or equal to the specified ID
     * - include_entities: when set to false, entities member will be omitted
     *
     * @param  array $params
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function favoritesList(array $options = array())
    {
        $this->init();
        $path = 'favorites/list';
        $params = array();
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'user_id':
                    $params['user_id'] = $this->validInteger($value);
                    break;
                case 'screen_name':
                    $params['screen_name'] = $value;
                    break;
                case 'count':
                    $params['count'] = (int) $value;
                    break;
                case 'since_id':
                    $params['since_id'] = $this->validInteger($value);
                    break;
                case 'max_id':
                    $params['max_id'] = $this->validInteger($value);
                    break;
                case 'include_entities':
                    $params['include_entities'] = (bool) $value;
                    break;
                default:
                    break;
            }
        }
        $response = $this->get($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Create friendship
     *
     * @param  int|string $id User ID or name of new friend
     * @param  array $params Additional parameters to pass
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function friendshipsCreate($id, array $params = array())
    {
        $this->init();
        $path    = 'friendships/create';
        $params  = $this->createUserParameter($id, $params);
        $allowed = array(
            'user_id'     => null,
            'screen_name' => null,
            'follow'      => null,
        );
        $params = array_intersect_key($params, $allowed);
        $response = $this->post($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Destroy friendship
     *
     * @param  int|string $id User ID or name of friend to remove
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function friendshipsDestroy($id)
    {
        $this->init();
        $path     = 'friendships/destroy';
        $params   = $this->createUserParameter($id, array());
        $response = $this->post($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Search tweets
     *
     * $options may include any of the following:
     * - geocode: a string of the form "latitude, longitude, radius"
     * - lang: restrict tweets to the two-letter language code
     * - locale: query is in the given two-letter language code
     * - result_type: what type of results to receive: mixed, recent, or popular
     * - count: number of tweets to return per page; up to 100
     * - until: return tweets generated before the given date
     * - since_id: return resutls with an ID greater than (more recent than) the given ID
     * - max_id: return results with an ID less than (older than) the given ID
     * - include_entities: whether or not to include embedded entities
     *
     * @param  string $query
     * @param  array $options
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function searchTweets($query, array $options = array())
    {
        $this->init();
        $path = 'search/tweets';

        $len = iconv_strlen($query, 'UTF-8');
        if (0 == $len) {
            require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Query must contain at least one character'
            );
        }

        $params = array('q' => $query);
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'geocode':
                    if (!substr_count($value, ',') !== 2) {
                        require_once 'Zend/Service/Twitter/Exception.php';
                        throw new Zend_Service_Twitter_Exception(
                            '"geocode" must be of the format "latitude,longitude,radius"'
                        );
                    }
                    list($latitude, $longitude, $radius) = explode(',', $value);
                    $radius = trim($radius);
                    if (!preg_match('/^\d+(mi|km)$/', $radius)) {
                        require_once 'Zend/Service/Twitter/Exception.php';
                        throw new Zend_Service_Twitter_Exception(
                            'Radius segment of "geocode" must be of the format "[unit](mi|km)"'
                        );
                    }
                    $latitude  = (float) $latitude;
                    $longitude = (float) $longitude;
                    $params['geocode'] = $latitude . ',' . $longitude . ',' . $radius;
                    break;
                case 'lang':
                    if (strlen($value) > 2) {
                        require_once 'Zend/Service/Twitter/Exception.php';
                        throw new Zend_Service_Twitter_Exception(
                            'Query language must be a 2 character string'
                        );
                    }
                    $params['lang'] = strtolower($value);
                    break;
                case 'locale':
                    if (strlen($value) > 2) {
                        require_once 'Zend/Service/Twitter/Exception.php';
                        throw new Zend_Service_Twitter_Exception(
                            'Query locale must be a 2 character string'
                        );
                    }
                    $params['locale'] = strtolower($value);
                    break;
                case 'result_type':
                    $value = strtolower($value);
                    if (!in_array($value, array('mixed', 'recent', 'popular'))) {
                        require_once 'Zend/Service/Twitter/Exception.php';
                        throw new Zend_Service_Twitter_Exception(
                            'result_type must be one of "mixed", "recent", or "popular"'
                        );
                    }
                    $params['result_type'] = $value;
                    break;
                case 'count':
                    $value = (int) $value;
                    if (1 > $value || 100 < $value) {
                        require_once 'Zend/Service/Twitter/Exception.php';
                        throw new Zend_Service_Twitter_Exception(
                            'count must be between 1 and 100'
                        );
                    }
                    $params['count'] = $value;
                    break;
                case 'until':
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                        require_once 'Zend/Service/Twitter/Exception.php';
                        throw new Zend_Service_Twitter_Exception(
                            '"until" must be a date in the format YYYY-MM-DD'
                        );
                    }
                    $params['until'] = $value;
                    break;
                case 'since_id':
                    $params['since_id'] = $this->validInteger($value);
                    break;
                case 'max_id':
                    $params['max_id'] = $this->validInteger($value);
                    break;
                case 'include_entities':
                    $params['include_entities'] = (bool) $value;
                    break;
                default:
                    break;
            }
        }
        $response = $this->get($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Destroy a status message
     *
     * @param  int $id ID of status to destroy
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function statusesDestroy($id)
    {
        $this->init();
        $path = 'statuses/destroy/' . $this->validInteger($id);
        $response = $this->post($path);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Friend Timeline Status
     *
     * $options may include one or more of the following keys
     * - count: number of tweets to attempt to retrieve, up to 200
     * - since_id: return results only after the specified tweet id
     * - max_id: return results with an ID less than (older than) or equal to the specified ID
     * - trim_user: when set to true, "t", or 1, user object in tweets will include only author's ID.
     * - contributor_details: when set to true, includes screen_name of each contributor
     * - include_entities: when set to false, entities member will be omitted
     * - exclude_replies: when set to true, will strip replies appearing in the timeline
     *
     * @param  array $params
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function statusesHomeTimeline(array $options = array())
    {
        $this->init();
        $path = 'statuses/home_timeline';
        $params = array();
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'count':
                    $params['count'] = (int) $value;
                    break;
                case 'since_id':
                    $params['since_id'] = $this->validInteger($value);
                    break;
                case 'max_id':
                    $params['max_id'] = $this->validInteger($value);
                    break;
                case 'trim_user':
                    if (in_array($value, array(true, 'true', 't', 1, '1'))) {
                        $value = true;
                    } else {
                        $value = false;
                    }
                    $params['trim_user'] = $value;
                    break;
                case 'contributor_details:':
                    $params['contributor_details:'] = (bool) $value;
                    break;
                case 'include_entities':
                    $params['include_entities'] = (bool) $value;
                    break;
                case 'exclude_replies':
                    $params['exclude_replies'] = (bool) $value;
                    break;
                default:
                    break;
            }
        }
        $response = $this->get($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Get status replies
     *
     * $options may include one or more of the following keys
     * - count: number of tweets to attempt to retrieve, up to 200
     * - since_id: return results only after the specified tweet id
     * - max_id: return results with an ID less than (older than) or equal to the specified ID
     * - trim_user: when set to true, "t", or 1, user object in tweets will include only author's ID.
     * - contributor_details: when set to true, includes screen_name of each contributor
     * - include_entities: when set to false, entities member will be omitted
     *
     * @param  array $options
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function statusesMentionsTimeline(array $options = array())
    {
        $this->init();
        $path   = 'statuses/mentions_timeline';
        $params = array();
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'count':
                    $params['count'] = (int) $value;
                    break;
                case 'since_id':
                    $params['since_id'] = $this->validInteger($value);
                    break;
                case 'max_id':
                    $params['max_id'] = $this->validInteger($value);
                    break;
                case 'trim_user':
                    if (in_array($value, array(true, 'true', 't', 1, '1'))) {
                        $value = true;
                    } else {
                        $value = false;
                    }
                    $params['trim_user'] = $value;
                    break;
                case 'contributor_details:':
                    $params['contributor_details:'] = (bool) $value;
                    break;
                case 'include_entities':
                    $params['include_entities'] = (bool) $value;
=======
        $response = $this->_get($path, $_params);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Send a direct message to a user
     *
     * @param  int|string $user User to whom to send message
     * @param  string $text Message to send to user
     * @return Zend_Rest_Client_Result
     * @throws Zend_Service_Twitter_Exception if message is too short or too long
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     */
    public function directMessageNew($user, $text)
    {
        $this->_init();
        $path = '/1/direct_messages/new.xml';
        $len = iconv_strlen($text, 'UTF-8');
        if (0 == $len) {
            throw new Zend_Service_Twitter_Exception(
                'Direct message must contain at least one character'
            );
        } elseif (140 < $len) {
            throw new Zend_Service_Twitter_Exception(
                'Direct message must contain no more than 140 characters'
            );
        }
        $data = array('user' => $user, 'text' => $text);
        $response = $this->_post($path, $data);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Destroy a direct message
     *
     * @param  int $id ID of message to destroy
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function directMessageDestroy($id)
    {
        $this->_init();
        $path = '/1/direct_messages/destroy/' . $this->_validInteger($id) . '.xml';
        $response = $this->_post($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Create friendship
     *
     * @param  int|string $id User ID or name of new friend
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function friendshipCreate($id)
    {
        $this->_init();
        $path = '/1/friendships/create/' . $id . '.xml';
        $response = $this->_post($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Destroy friendship
     *
     * @param  int|string $id User ID or name of friend to remove
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function friendshipDestroy($id)
    {
        $this->_init();
        $path = '/1/friendships/destroy/' . $id . '.xml';
        $response = $this->_post($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Friendship exists
     *
     * @param int|string $id User ID or name of friend to see if they are your friend
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_result
     */
    public function friendshipExists($id)
    {
        $this->_init();
        $path = '/1/friendships/exists.xml';
        $data = array('user_a' => $this->getUsername(), 'user_b' => $id);
        $response = $this->_get($path, $data);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Verify Account Credentials
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     *
     * @return Zend_Rest_Client_Result
     */
    public function accountVerifyCredentials()
    {
        $this->_init();
        $response = $this->_get('/1/account/verify_credentials.xml');
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * End current session
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return true
     */
    public function accountEndSession()
    {
        $this->_init();
        $this->_get('/1/account/end_session');
        return true;
    }

    /**
     * Returns the number of api requests you have left per hour.
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function accountRateLimitStatus()
    {
        $this->_init();
        $response = $this->_get('/1/account/rate_limit_status.xml');
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Fetch favorites
     *
     * $params may contain one or more of the following:
     * - 'id': Id of a user for whom to fetch favorites
     * - 'page': Retrieve a different page of resuls
     *
     * @param  array $params
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function favoriteFavorites(array $params = array())
    {
        $this->_init();
        $path = '/1/favorites';
        $_params = array();
        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
                case 'id':
                    $path .= '/' . $this->_validInteger($value);
                    break;
                case 'page':
                    $_params['page'] = (int) $value;
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
                    break;
                default:
                    break;
            }
        }
<<<<<<< HEAD
        $response = $this->get($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Public Timeline status
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function statusesSample()
    {
        $this->init();
        $path = 'statuses/sample';
        $response = $this->get($path);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Show a single status
     *
     * @param  int $id Id of status to show
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function statusesShow($id)
    {
        $this->init();
        $path = 'statuses/show/' . $this->validInteger($id);
        $response = $this->get($path);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Update user's current status
     *
     * @todo   Support additional parameters supported by statuses/update endpoint
     * @param  string $status
     * @param  null|int $inReplyToStatusId
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\OutOfRangeException if message is too long
     * @throws Exception\InvalidArgumentException if message is empty
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function statusesUpdate($status, $inReplyToStatusId = null)
    {
        $this->init();
        $path = 'statuses/update';
        $len = iconv_strlen(htmlspecialchars($status, ENT_QUOTES, 'UTF-8'), 'UTF-8');
        if ($len > self::STATUS_MAX_CHARACTERS) {
            require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Status must be no more than '
                . self::STATUS_MAX_CHARACTERS
                . ' characters in length'
            );
        } elseif (0 == $len) {
            require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Status must contain at least one character'
            );
        }

        $params = array('status' => $status);
        $inReplyToStatusId = $this->validInteger($inReplyToStatusId);
        if ($inReplyToStatusId) {
            $params['in_reply_to_status_id'] = $inReplyToStatusId;
        }
        $response = $this->post($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * User Timeline status
     *
     * $options may include one or more of the following keys
     * - user_id: Id of a user for whom to fetch favorites
     * - screen_name: Screen name of a user for whom to fetch favorites
     * - count: number of tweets to attempt to retrieve, up to 200
     * - since_id: return results only after the specified tweet id
     * - max_id: return results with an ID less than (older than) or equal to the specified ID
     * - trim_user: when set to true, "t", or 1, user object in tweets will include only author's ID.
     * - exclude_replies: when set to true, will strip replies appearing in the timeline
     * - contributor_details: when set to true, includes screen_name of each contributor
     * - include_rts: when set to false, will strip native retweets
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function statusesUserTimeline(array $options = array())
    {
        $this->init();
        $path = 'statuses/user_timeline';
        $params = array();
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'user_id':
                    $params['user_id'] = $this->validInteger($value);
                    break;
                case 'screen_name':
                    $params['screen_name'] = $this->validateScreenName($value);
                    break;
                case 'count':
                    $params['count'] = (int) $value;
                    break;
                case 'since_id':
                    $params['since_id'] = $this->validInteger($value);
                    break;
                case 'max_id':
                    $params['max_id'] = $this->validInteger($value);
                    break;
                case 'trim_user':
                    if (in_array($value, array(true, 'true', 't', 1, '1'))) {
                        $value = true;
                    } else {
                        $value = false;
                    }
                    $params['trim_user'] = $value;
                    break;
                case 'contributor_details:':
                    $params['contributor_details:'] = (bool) $value;
                    break;
                case 'exclude_replies':
                    $params['exclude_replies'] = (bool) $value;
                    break;
                case 'include_rts':
                    $params['include_rts'] = (bool) $value;
                    break;
                default:
                    break;
            }
        }
        $response = $this->get($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Search users
     *
     * $options may include any of the following:
     * - page: the page of results to retrieve
     * - count: the number of users to retrieve per page; max is 20
     * - include_entities: if set to boolean true, include embedded entities
     *
     * @param  string $query
     * @param  array $options
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function usersSearch($query, array $options = array())
    {
        $this->init();
        $path = 'users/search';

        $len = iconv_strlen($query, 'UTF-8');
        if (0 == $len) {
            require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Query must contain at least one character'
            );
        }

        $params = array('q' => $query);
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'count':
                    $value = (int) $value;
                    if (1 > $value || 20 < $value) {
                        require_once 'Zend/Service/Twitter/Exception.php';
                        throw new Zend_Service_Twitter_Exception(
                            'count must be between 1 and 20'
                        );
                    }
                    $params['count'] = $value;
                    break;
                case 'page':
                    $params['page'] = (int) $value;
                    break;
                case 'include_entities':
                    $params['include_entities'] = (bool) $value;
                    break;
                default:
                    break;
            }
        }
        $response = $this->get($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }


    /**
     * Show extended information on a user
     *
     * @param  int|string $id User ID or name
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function usersShow($id)
    {
        $this->init();
        $path     = 'users/show';
        $params   = $this->createUserParameter($id, array());
        $response = $this->get($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Initialize HTTP authentication
     *
     * @return void
     * @throws Exception\DomainException if unauthorised
     */
    protected function init()
    {
        if (!$this->isAuthorised() && $this->getUsername() !== null) {
            require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Twitter session is unauthorised. You need to initialize '
                . __CLASS__ . ' with an OAuth Access Token or use '
                . 'its OAuth functionality to obtain an Access Token before '
                . 'attempting any API actions that require authorisation'
            );
        }
        $client = $this->getHttpClient();
        $client->resetParameters();
        if (null === $this->cookieJar) {
            $cookieJar = $client->getCookieJar();
            if (null === $cookieJar) {
                $cookieJar = new Zend_Http_CookieJar();
            }
            $this->cookieJar = $cookieJar;
            $this->cookieJar->reset();
        } else {
            $client->setCookieJar($this->cookieJar);
        }
=======
        $path .= '.xml';
        $response = $this->_get($path, $_params);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Mark a status as a favorite
     *
     * @param  int $id Status ID you want to mark as a favorite
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function favoriteCreate($id)
    {
        $this->_init();
        $path = '/1/favorites/create/' . $this->_validInteger($id) . '.xml';
        $response = $this->_post($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Remove a favorite
     *
     * @param  int $id Status ID you want to de-list as a favorite
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function favoriteDestroy($id)
    {
        $this->_init();
        $path = '/1/favorites/destroy/' . $this->_validInteger($id) . '.xml';
        $response = $this->_post($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Blocks the user specified in the ID parameter as the authenticating user.
     * Destroys a friendship to the blocked user if it exists.
     *
     * @param integer|string $id       The ID or screen name of a user to block.
     * @return Zend_Rest_Client_Result
     */
    public function blockCreate($id)
    {
        $this->_init();
        $path = '/1/blocks/create/' . $id . '.xml';
        $response = $this->_post($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Un-blocks the user specified in the ID parameter for the authenticating user
     *
     * @param integer|string $id       The ID or screen_name of the user to un-block.
     * @return Zend_Rest_Client_Result
     */
    public function blockDestroy($id)
    {
        $this->_init();
        $path = '/1/blocks/destroy/' . $id . '.xml';
        $response = $this->_post($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Returns if the authenticating user is blocking a target user.
     *
     * @param string|integer $id    The ID or screen_name of the potentially blocked user.
     * @param boolean $returnResult Instead of returning a boolean return the rest response from twitter
     * @return Boolean|Zend_Rest_Client_Result
     */
    public function blockExists($id, $returnResult = false)
    {
        $this->_init();
        $path = '/1/blocks/exists/' . $id . '.xml';
        $response = $this->_get($path);

        $cr = new Zend_Rest_Client_Result($response->getBody());

        if ($returnResult === true)
            return $cr;

        if (!empty($cr->request)) {
            return false;
        }

        return true;
    }

    /**
     * Returns an array of user objects that the authenticating user is blocking
     *
     * @param integer $page         Optional. Specifies the page number of the results beginning at 1. A single page contains 20 ids.
     * @param boolean $returnUserIds  Optional. Returns only the userid's instead of the whole user object
     * @return Zend_Rest_Client_Result
     */
    public function blockBlocking($page = 1, $returnUserIds = false)
    {
        $this->_init();
        $path = '/1/blocks/blocking';
        if ($returnUserIds === true) {
            $path .= '/ids';
        }
        $path .= '.xml';
        $response = $this->_get($path, array('page' => $page));
        return new Zend_Rest_Client_Result($response->getBody());
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    }

    /**
     * Protected function to validate that the integer is valid or return a 0
<<<<<<< HEAD
     *
     * @param  $int
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return integer
     */
    protected function validInteger($int)
=======
     * @param mixed $int
     * @return integer
     */
    protected function _validInteger($int)
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    {
        if (preg_match("/(\d+)/", $int)) {
            return $int;
        }
        return 0;
    }

    /**
     * Validate a screen name using Twitter rules
     *
     * @param string $name
<<<<<<< HEAD
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    protected function validateScreenName($name)
    {
        if (!preg_match('/^[a-zA-Z0-9_]{0,20}$/', $name)) {
=======
     * @throws Zend_Service_Twitter_Exception
     * @return string
     */
    protected function _validateScreenName($name)
    {
        if (!preg_match('/^[a-zA-Z0-9_]{0,15}$/', $name)) {
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
            require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Screen name, "' . $name
                . '" should only contain alphanumeric characters and'
                . ' underscores, and not exceed 15 characters.');
        }
        return $name;
    }

    /**
<<<<<<< HEAD
     * Call a remote REST web service URI
     *
     * @param  string $path The path to append to the URI
     * @param  Zend_Http_Client $client
     * @throws Zend_Http_Client_Exception
     * @return void
     */
    protected function prepare($path, Zend_Http_Client $client)
    {
        $client->setUri(self::API_BASE_URI . $path . '.json');

        /**
         * Do this each time to ensure oauth calls do not inject new params
         */
        $client->resetParameters();
=======
     * Call a remote REST web service URI and return the Zend_Http_Response object
     *
     * @param  string $path            The path to append to the URI
     * @throws Zend_Rest_Client_Exception
     * @return void
     */
    protected function _prepare($path)
    {
        // Get the URI object and configure it
        if (!$this->_uri instanceof Zend_Uri_Http) {
            require_once 'Zend/Rest/Client/Exception.php';
            throw new Zend_Rest_Client_Exception(
                'URI object must be set before performing call'
            );
        }

        $uri = $this->_uri->getUri();

        if ($path[0] != '/' && $uri[strlen($uri) - 1] != '/') {
            $path = '/' . $path;
        }

        $this->_uri->setPath($path);

        /**
         * Get the HTTP client and configure it for the endpoint URI.
         * Do this each time because the Zend_Http_Client instance is shared
         * among all Zend_Service_Abstract subclasses.
         */
        $this->_localHttpClient->resetParameters()->setUri((string) $this->_uri);
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    }

    /**
     * Performs an HTTP GET request to the $path.
     *
     * @param string $path
     * @param array  $query Array of GET parameters
     * @throws Zend_Http_Client_Exception
     * @return Zend_Http_Response
     */
<<<<<<< HEAD
    protected function get($path, array $query = array())
    {
        $client = $this->getHttpClient();
        $this->prepare($path, $client);
        $client->setParameterGet($query);
        $response = $client->request(Zend_Http_Client::GET);
        return $response;
=======
    protected function _get($path, array $query = null)
    {
        $this->_prepare($path);
        $this->_localHttpClient->setParameterGet($query);
        return $this->_localHttpClient->request(Zend_Http_Client::GET);
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    }

    /**
     * Performs an HTTP POST request to $path.
     *
     * @param string $path
     * @param mixed $data Raw data to send
     * @throws Zend_Http_Client_Exception
     * @return Zend_Http_Response
     */
<<<<<<< HEAD
    protected function post($path, $data = null)
    {
        $client = $this->getHttpClient();
        $this->prepare($path, $client);
        $response = $this->performPost(Zend_Http_Client::POST, $data, $client);
        return $response;
=======
    protected function _post($path, $data = null)
    {
        $this->_prepare($path);
        return $this->_performPost(Zend_Http_Client::POST, $data);
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
    }

    /**
     * Perform a POST or PUT
     *
     * Performs a POST or PUT request. Any data provided is set in the HTTP
     * client. String data is pushed in as raw POST data; array or object data
     * is pushed in as POST parameters.
     *
     * @param mixed $method
     * @param mixed $data
     * @return Zend_Http_Response
     */
<<<<<<< HEAD
    protected function performPost($method, $data, Zend_Http_Client $client)
    {
=======
    protected function _performPost($method, $data = null)
    {
        $client = $this->_localHttpClient;
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
        if (is_string($data)) {
            $client->setRawData($data);
        } elseif (is_array($data) || is_object($data)) {
            $client->setParameterPost((array) $data);
        }
        return $client->request($method);
    }

<<<<<<< HEAD
    /**
     * Create a parameter representing the user
     *
     * Determines if $id is an integer, and, if so, sets the "user_id" parameter.
     * If not, assumes the $id is the "screen_name".
     *
     * @param  int|string $id
     * @param  array $params
     * @return array
     */
    protected function createUserParameter($id, array $params)
    {
        if ($this->validInteger($id)) {
            $params['user_id'] = $id;
            return $params;
        }

        $params['screen_name'] = $this->validateScreenName($id);
        return $params;
    }
=======
>>>>>>> 11dbc85715960d0a16f57d59a3db15f5d571b6fa
}
