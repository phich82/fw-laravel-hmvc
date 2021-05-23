<?php

namespace Core\Notifier\Services\Implementations\Skype;

class SkypePHP
{
    /* Skype hosts */
    const CLIENTINFO_NAME = 'skype.com';
    const CLIENT_VERSION = '908/1.118.0.30//skype.com';
    const LOCKANDKEY_APPID = 'msmsgs@msnmsgr.com';
    const LOCKANDKEY_SECRET = 'Q1P7W2E4J9R8U3S5';
    const SKYPE_WEB = 'web.skype.com';
    const CONTACTS_HOST = 'api.skype.com';
    const NEW_CONTACTS_HOST = 'contacts.skype.com';
    const DEFAULT_MESSAGES_HOST_OLD = 'client-s.gateway.messenger.live.com';
    const DEFAULT_MESSAGES_HOST = 'azwcus1-client-s.gateway.messenger.live.com';
    const LOGIN_HOST = 'login.skype.com';
    const VIDEOMAIL_HOST = 'vm.skype.com';
    const XFER_HOST = 'api.asm.skype.com';
    const GRAPH_HOST = 'skypegraph.skype.com';
    const STATIC_HOST = 'static.asm.skype.com';
    const STATIC_CDN_HOST = 'static-asm.secure.skypeassets.com';
    const DEFAULT_CONTACT_SUGGESTIONS_HOST = 'peoplerecommendations.skype.com';

    /* Public user data */
    public $username;

    /* Private user data */
    private $password;
    private $registrationToken;
    private $skypeToken;
    private $hashedUserName;
    private $randId;
    private $logged = false;
    private $expiry = 0;
    private $skypeId;
    private $cachePath = '';

    private $apiDomainOld = 'client-s.gateway.messenger.live.com';
    private $apiDomain = 'azwcus1-client-s.gateway.messenger.live.com';

    /**
     * __construct
     *
     * @param string $cachePath
     * @return void
     */
    public function __construct($cachePath = null)
    {
        $this->cachePath = $cachePath ?: storage_path('cache/skype/');
    }

    /**
     * Get rand id from url
     *
     * @param  string $url
     * @return bool|string
     */
    public function getRandIdUrl($url)
    {
        $url = explode("cobrandid=", $url);
        if (!isset($url[1])) {
            return false;
        }
        $url = explode("&username", $url[1]);
        if (!isset($url[0])) {
            return false;
        }
        return $url[0];
    }

    /**
     * Login by skype account
     *
     * @param  string $username
     * @param  string $password
     * @return bool
     * @throws \Exception
     */
    public function login($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->hashedUserName = sha1($username);

        if (file_exists($this->cachePath)) {
            if (file_exists("{$this->cachePath}/auth_{$this->hashedUserName}")) {
                $auth = json_decode(file_get_contents("{$this->cachePath}/auth_{$this->hashedUserName}"), true);
                if (time() >= $auth["expiry"]) {
                    unset($auth);
                }
            }
        } elseif (!mkdir("{$this->cachePath}", 0777, true)) {
            exit(trigger_error("Skype : Unable to create the SkypePHP directoy.", E_USER_WARNING));
        }

        if (isset($auth)) {
            $this->skypeToken = $auth["skypeToken"];
            $this->registrationToken = $auth["registrationToken"];
            $this->expiry = $auth["expiry"];
            $this->skypeId = $auth["skypeId"];
            return true;
        }

        return $this->loginSkype();
    }

    /**
     * Request login from skype host
     *
     * @return bool
     * @throws \Exception
     */
    private function loginSkype()
    {
        // 1. Get PPFT & PPSX info
        $loginForm = $this->web("https://login.skype.com/login/oauth/microsoft?client_id=578134&redirect_uri=https%3A%2F%2Fweb.skype.com%2F&username={$this->username}", "GET", [], true, true);
        preg_match("`urlPost:'(.+)',`isU", $loginForm, $loginURL);
        $loginURL = $loginURL[1];
        $this->randId = $this->getRandIdUrl($loginURL);
        preg_match("`name=\"PPFT\" id=\"(.+)\" value=\"(.+)\"`isU", $loginForm, $ppft);
        $ppft = $ppft[2];

        preg_match("`t:\'(.+)\',A`isU", $loginForm, $ppsx);
        $ppsx = $ppsx[1];

        preg_match_all('`Set-Cookie: (.+)=(.+);`isU', $loginForm, $cookiesArray);
        $cookies = "";
        for ($i = 0; $i <= count($cookiesArray[1])-1; $i++) {
            $cookies .= "{$cookiesArray[1][$i]}={$cookiesArray[2][$i]}; ";
        }

        // 2. Get NAP, ANON & t info
        $post = [
            "loginfmt" => $this->username,
            "login" => $this->username,
            "passwd" => $this->password,
            "type" => 11,
            "PPFT" => $ppft,
            "PPSX" => $ppsx,
            "NewUser" => (int)1,
            "LoginOptions" => 3,
            "FoundMSAs" => "",
            "fspost" => (int)0,
            "i2" => (int)1,
            "i16" => "",
            "i17" => (int)0,
            "i18" => "__DefaultLoginStrings|1,__DefaultLogin_Core|1,",
            "i19" => 556374,
            "i21" => (int)0,
            "i13" => (int)0
        ];

        $loginForm = $this->web($loginURL, "POST", $post, true, true, $cookies);

        preg_match("`<input type=\"hidden\" name=\"NAP\" id=\"NAP\" value=\"(.+)\">`isU", $loginForm, $NAP);
        preg_match("`<input type=\"hidden\" name=\"ANON\" id=\"ANON\" value=\"(.+)\">`isU", $loginForm, $ANON);
        preg_match("`<input type=\"hidden\" name=\"t\" id=\"t\" value=\"(.+)\">`isU", $loginForm, $t);

        if (!isset($NAP[1]) || !isset($ANON[1]) || !isset($t[1])) {
            exit(trigger_error("Skype : Authentication failed for {$this->username}", E_USER_WARNING));
        }

        $NAP = $NAP[1];
        $ANON = $ANON[1];
        $t = $t[1];

        preg_match_all('`Set-Cookie: (.+)=(.+);`isU', $loginForm, $cookiesArray);
        $cookies = "";
        for ($i = 0; $i <= count($cookiesArray[1])-1; $i++) {
            $cookies .= "{$cookiesArray[1][$i]}={$cookiesArray[2][$i]}; ";
        }

        // 3. Get t info
        $post = [
            "NAP" => $NAP,
            "ANON" => $ANON,
            "t" => $t
        ];

        $loginForm = $this->web("https://lw.skype.com/login/oauth/proxy?client_id=578134&redirect_uri=https://web.skype.com/&site_name=lw.skype.com&wa=wsignin1.0", "POST", $post, true, true, $cookies);

        preg_match("`<input type=\"hidden\" name=\"t\" value=\"(.+)\"/>`isU", $loginForm, $t);
        $t = $t[1];

        // 4. Get skype token
        $post = [
            "t" => $t,
            "site_name" => "lw.skype.com",
            "oauthPartner" => 999,
            "form" => "",
            "client_id" => 578134,
            "redirect_uri" => "https://web.skype.com/"
        ];

        $login = $this->web("https://login.skype.com/login/microsoft?client_id=578134&redirect_uri=https://web.skype.com/", "POST", $post);

        // Extract skype token
        preg_match("`<input type=\"hidden\" name=\"skypetoken\" value=\"(.+)\"/>`isU", $login, $skypeToken);
        $this->skypeToken = $skypeToken[1];

        // Extract skype id
        preg_match("`<input type=\"hidden\" name=\"skypeId\" value=\"(.+)\"/>`isU", $login, $skypeId);
        $this->skypeId = $skypeId[1];

        // 5. Login to skype
        $login = $this->web("https://{$this->apiDomain}/v1/users/ME/endpoints", "POST", "{}", true);

        // Extract registration token
        preg_match("`registrationToken=(.+);`isU", $login, $registrationToken);
        $this->registrationToken = $registrationToken[1];

        // 6. Cache logged in info
        $expiry = time() + 21600;

        $cache = [
            "skypeToken" => $this->skypeToken,
            "registrationToken" => $this->registrationToken,
            "expiry" => $expiry,
            "skypeId" => $this->skypeId,
        ];

        $this->expiry = $expiry;
        $this->logged = true;

        file_put_contents("{$this->cachePath}/auth_{$this->hashedUserName}", json_encode($cache));

        return true;
    }

    /**
     * Request http
     *
     * @param  string $url
     * @param  string $method
     * @param  string|array $post
     * @param  bool $showHeaders
     * @param  bool $follow
     * @param  string $customCookies
     * @param  array $customHeaders
     * @return mixed
     * @throws \Exception
     */
    private function web($url, $method = "GET", $post = [], $showHeaders = false, $follow = true, $customCookies = "", $customHeaders = [])
    {
        if (!function_exists("curl_init")) {
            exit(trigger_error("Skype : cURL is required", E_USER_WARNING));
        }

        if (!empty($post) && is_array($post)) {
            $post = http_build_query($post);
        }

        if ($this->logged && time() >= $this->expiry) {
            $this->logged = false;
            $this->loginSkype();
        }

        // Add skype token & registeration token after logged in
        $headers = $customHeaders;
        if (isset($this->skypeToken)) {
            $headers[] = "X-Skypetoken: {$this->skypeToken}";
            $headers[] = "Authentication: skypetoken={$this->skypeToken}";
        }

        if (isset($this->registrationToken)) {
            $headers[] = "RegistrationToken: registrationToken={$this->registrationToken}";
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        if (!empty($post)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }
        if ($customCookies) {
            curl_setopt($curl, CURLOPT_COOKIE, $customCookies);
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36");
        curl_setopt($curl, CURLOPT_HEADER, $showHeaders);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $follow);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

    /**
     * Logout from skype
     *
     * @return bool
     */
    public function logout()
    {
        if (!$this->logged) {
            return true;
        }

        // Clear user info in cache
        unlink("{$this->cachePath}/auth_{$this->hashedUserName}");
        unset($this->skypeToken);
        unset($this->registrationToken);

        return true;
    }

    /**
     * TODO: Not test yet
     * Get unread messages
     *
     * @return void
     */
    public function getUnreadMessages()
    {
        //$this->randId = "2befc4b5-19e3-46e8-8347-77317a16a5a5";
        //$req = json_decode($this->web("https://{$this->apiDomain}/v1/users/ME/endpoints/%7B".$this->randId."%7D"), true);
        return false;
    }

    /**
     * Get conversations list
     *
     * @return array
     */
    public function getConversationsList()
    {
        return json_decode($this->web("https://{$this->apiDomain}/v1/users/ME/conversations?view=supportsExtendedHistory%7Cmsnp24Equivalent&pageSize=25&startTime=1&targetType=Passport%7CSkype%7CLync%7CThread%7CAgent%7CShortCircuit%7CPSTN%7CSmsMms%7CFlxt%7CNotificationStream%7CCast%7CCortanaBot%7CModernBots%7CsecureThreads%7CInviteFree"), true);
    }

    /**
     * Get local UserId
     *
     * @return string|bool
     */
    public function getLocalUserId()
    {
        $req = json_decode($this->web("https://{$this->apiDomain}/v1/users/ME/endpoints"), true);
        if (isset($req[0]['id'])) {
            return $req[0]['id'];
        }
        return false;
    }

    /**
     * Get skype id from url
     *
     * @param  string $url
     * @return string
     */
    private function skypeId($url)
    {
        // $url = explode(":", $url, 2);
        // return end($url);
        return $url;
    }

    private function timestamp() {
        return str_replace(".", "", microtime(1));
    }

    /**
     * Send message to user
     *
     * @param  string $user [SkypeID]
     * @param  mixed $message
     * @return bool
     */
    public function sendMessage($user, $message)
    {
        $user = $this->skypeId($user);
        $mode = strstr($user, "thread.skype") ? 19 : 8;
        $messageID = $this->timestamp();
        $post = [
            "content" => $message,
            "messagetype" => "RichText",
            "contenttype" => "text",
            "clientmessageid" => $messageID
        ];

        $response = json_decode($this->web("https://{$this->apiDomain}/v1/users/ME/conversations/$mode:$user/messages", "POST", json_encode($post)), true);

        return isset($response["OriginalArrivalTime"]) ? $messageID : 0;
    }

    /**
     * Get messages list by skype id
     *
     * @param  string $user
     * @param  int $size
     * @return array
     */
    public function getMessagesList($user, $size = 100)
    {
        $user = $this->skypeId($user);
        if ($size > 199 or $size < 1) {
            $size = 199;
        }
        $mode = strstr($user, "thread.skype") ? 19 : 8;

        $response = json_decode($this->web("https://{$this->apiDomain}/v1/users/ME/conversations/$mode:$user/messages?startTime=0&pageSize=$size&view=msnp24Equivalent&targetType=Passport|Skype|Lync|Thread"), true);

        return !isset($response["message"]) ? $response["messages"] : [];
    }

    /**
     * Create group
     *
     * @param  array $users
     * @param  string $topic
     * @param  string $skypeIdAdmin
     * @return string
     */
    public function createGroup($users = [], $topic = '', $skypeIdAdmin = '')
    {
        $members = [];

        foreach ($users as $user) {
            $members["members"][] = ["id" => "8:{$this->skypeId($user)}", "role" => "User"];
        }

        $skypeIdAdmin = $skypeIdAdmin ?: $this->skypeId ?: $this->readMyProfile('username');
        // $members["members"][] = ["id" => "8:{$this->username}", "role" => "Admin"];
        $members["members"][] = ["id" => "8:{$skypeIdAdmin}", "role" => "Admin"];

        $response = $this->web("https://{$this->apiDomain}/v1/threads", "POST", json_encode($members), true);
        preg_match("`19\:(.+)\@thread.skype`isU", $response, $group);

        $group = isset($group[1]) ? "{$group[1]}@thread.skype" : "";

        if (!empty($topic) && !empty($group)) {
            $this->setGroupTopic($group, $topic);
        }

        return $group;
    }

    /**
     * Set group topic
     *
     * @param  string $group
     * @param  string $topic
     * @return void
     */
    public function setGroupTopic($group, $topic)
    {
        $group = $this->skypeId($group);
        $post = [
            "topic" => $topic
        ];

        $response = $this->web("https://{$this->apiDomain}/v1/threads/19:$group/properties?name=topic", "PUT", json_encode($post));
    }

    /**
     * Get group information
     *
     * @param  string $group
     * @return array
     */
    public function getGroupInfo($group)
    {
        $group = $this->skypeId($group);
        $response = json_decode($this->web("https://{$this->apiDomain}/v1/threads/19:$group?view=msnp24Equivalent", "GET"), true);

        return !isset($response["code"]) ? $response : [];
    }

    /**
     * Add member to group
     *
     * @param  string $user
     * @param  string $group
     * @return bool
     */
    public function addUserToGroup($user, $group)
    {
        $user = $this->skypeId($user);
        $post = [
            "role" => "User"
        ];

        $response = $this->web("https://{$this->apiDomain}/v1/threads/19:$group/members/8:$user", "PUT", json_encode($post));

        return empty($response);
    }

    /**
     * TODO: Not permission for executing action
     * Kick user from group
     *
     * @param  mixed $user
     * @param  mixed $group
     * @return bool
     */
    public function kickUser($user, $group)
    {
        $user = $this->skypeId($user);
        $response = $this->web("https://{$this->apiDomain}/v1/threads/19:$group/members/8:$user", "DELETE");

        return empty($response);
    }

    /**
     * TODO: Not permission for executing action
     * Leave from group
     *
     * @param  string $group
     * @return bool
     */
    public function leaveGroup($group)
    {
        $skypeId = $this->skypeId ?: $this->readMyProfile('username');
        return $this->kickUser($skypeId, $group);
    }

    /**
     * TODO: not test yet
     * ifGroupHistoryDisclosed
     *
     * @param  string $group
     * @param  mixed $historydisclosed
     * @return bool
     */
    public function ifGroupHistoryDisclosed($group, $historydisclosed)
    {
        $group = $this->skypeId($group);
        $post = [
            "historydisclosed" => $historydisclosed
        ];

        $response = $this->web("https://{$this->apiDomain}/v1/threads/19:$group/properties?name=historydisclosed", "PUT", json_encode($post));

        return empty($response);
    }

    /**
     * Get contacts list
     *
     * @return array
     */
    public function getContactsList()
    {
        $profile = $this->readMyProfile();

        if (!isset($profile['username'])) {
            return [];
        }

        $response = json_decode($this->web("https://edge.skype.com/pcs-df/contacts/v2/users/{$profile['username']}?reason=default"), true);

        return isset($response['contacts']) ? $response['contacts'] : [];
    }

    /**
     * TODO: NOT FOUND
     * Get profiles by the given contact list
     *
     * @param  mixed $list
     * @return void
     */
    public function readProfile($list)
    {
        $contacts = "";
        foreach ($list as $contact) {
            $contacts .= "contacts[]=$contact&";
        }

        $response = json_decode($this->web("https://api.skype.com/users/self/contacts/profiles", "POST", $contacts), true);

        return !empty($response) ? $response : [];
    }

    /**
     * Get my profile
     *
     * @param  string $key
     * @return mixed
     */
    public function readMyProfile($key = null)
    {
        $response = json_decode($this->web("https://api.skype.com/users/self/profile"), true);

        $profile = !empty($response) ? $response : [];

        if (array_key_exists($key, $profile)) {
            return $profile[$key];
        }

        return $profile;
    }

    /**
     * Search users
     *
     * @param  string $username
     * @return array
     */
    public function search($username)
    {
        $username = $this->skypeId($username);
        $response = json_decode($this->web("https://skypegraph.skype.com/search/v1.1/namesearch/swx/?requestid=skype.com-1.63.51&searchstring=$username"), true);

        return !empty($response) ? $response : [];
    }

    /**
     * TODO: NOT FOUND
     * Add contact
     *
     * @param  string $username
     * @param  string $greeting
     * @return void
     */
    public function addContact($username, $greeting = "Hello, I would like to add you to my contacts.")
    {
        $username = $this->skypeId($username);
        $post = [
            "greeting" => $greeting
        ];
        $response = $this->web("https://api.skype.com/users/self/contacts/auth-request/$username", "PUT", $post);
        $response = json_decode($response, true);

        return isset($response["code"]) && $response["code"] == 20100;
    }

    /**
     * Join to skype
     *
     * @param  string $id
     * @return void
     */
    public function skypeJoin($id)
    {
        $post = [
            "shortId" => $id,
            "type" => "wl"
        ];
        $group = $this->web("https://join.skype.com/api/v2/conversation/", "POST", json_encode($post), false, false, false, ["Content-Type: application/json"]);
        $group = json_decode($group, true);

        if (!isset($group["Resource"])) {
            return '';
        }

        $group = str_replace("19:", "", $group["Resource"]);

        $skypeId = $this->skypeId ?: $this->readMyProfile('username');

        return $this->addUserToGroup($skypeId, $group);
    }
}
