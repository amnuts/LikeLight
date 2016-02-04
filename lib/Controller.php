<?php

namespace LikeLight;

use Facebook\Exceptions\FacebookAuthenticationException;
use Facebook\Facebook;
use Phpiwire\Board;

class Controller
{
    /** @var  Config */
    protected $config;
    /** @var  Facebook */
    protected $facebookApi;
    /** @var Items */
    protected $items;
    /** @var Rpi */
    protected $board;

    protected $checkType = 'posts';
    protected $itemCount = 0;

    public function __construct(Config $config = null)
    {
        session_start();
        if ($config === null || !$config instanceof Config) {
            $this->config = new Config($this);
        } else {
            $this->config = $config;
        }
        $this->board = new Board($this->config);
    }

    /**
     * Perform some basic setup.
     *
     * This doesn't need to be done for login or callback so moved here so
     * as not to require extra processing where not require.
     *
     * @return $this
     */
    public function init()
    {
        $this->setCheckPosts();
        $this->setItemCount();
        $this->items = new Items($this);
        return $this;
    }

    /**
     * Instantiate and return the Facebook object
     *
     * @return Facebook
     */
    public function fb()
    {
        if ($this->facebookApi === null) {
            $this->facebookApi = new Facebook((array)$this->config->get('fb_config'));
            if (!empty($this->config->get('fb_token')->long)) {
                $this->facebookApi->setDefaultAccessToken($this->config->get('fb_token')->long);
            }

        }
        return $this->facebookApi;
    }

    /**
     * A like check for only items you have posted
     *
     * @return $this
     */
    public function setCheckPosts()
    {
        $this->checkType = 'posts';
        return $this;
    }

    /**
     * A like check for anything on your feed
     *
     * @return $this
     */
    public function setCheckFeed()
    {
        $this->checkType = 'feed';
        return $this;
    }

    /**
     * Set number of items that need to be tracked, 0 for no limit
     *
     * @param int $count
     * @return $this
     */
    public function setItemCount($count = 20)
    {
        $this->itemCount = (int)$count;
        if ($this->itemCount < 0) {
            $this->itemCount = 0;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->checkType;
    }

    /**
     * @return int
     */
    public function getMaxCount()
    {
        return $this->itemCount;
    }

    /**
     * Get authentication callback url
     *
     * @return string
     */
    public function getCallbackUrl()
    {
        $helper = $this->fb()->getRedirectLoginHelper();
        return $helper->getLoginUrl($this->config->get('callback_url') . '/callback.php');
    }

    /**
     * Handle oauth2 return from Facebook and save tokens
     *
     * @return $this
     */
    public function handleAuthCallback()
    {
        $accessToken = $this->fb()->getRedirectLoginHelper()->getAccessToken();
        $oa2 = $this->fb()->getOAuth2Client();
        $this->config->setTokens((string)$accessToken, (string)$oa2->getLongLivedAccessToken($accessToken));
        return $this;
    }

    /**
     * Validate if you have an access token and it is valid if exists
     *
     * @return bool
     */
    public function hasValidToken()
    {
        if (empty($this->config->get('fb_token')->long)) {
            return false;
        }
        try {
            $session = $this->fb()->get('/debug_token?input_token=' . $this->config->get('fb_token')->long)
                ->getGraphSessionInfo();
        } catch (FacebookAuthenticationException $e) {
            return false;
        }
        return !(empty($session) || empty($session->getUserId()));
    }

    /**
     * @return null|Items
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return $this
     */
    public function getInitialItems()
    {
        if ($this->items !== null) {
            $this->items->getInitialItems();
        }
        return $this;
    }

    /**
     * @param null $since
     * @return string
     */
    public function getEndpoint($since = null)
    {
        $params = ['fields' => 'id'];
        if ($since) {
            $params['since'] = $since;
            $params['__previous'] = 1;
        }
        if ($this->getMaxCount()) {
            $params['limit'] = $this->getMaxCount();
        }
        return '/me/' . $this->getType() . '?' . http_build_query($params);
    }

    /**
     * @return Rpi
     */
    public function getBoard()
    {
        return $this->board;
    }
}
