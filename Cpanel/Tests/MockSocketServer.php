<?php
class Cpanel_Tests_MockSocketServer
{
    protected $logfh;
    protected $socketfile;
    private $_socket;
    private $_tmpsocket;
    const LOG_FILE = "/tmp/mss.log";
    /** 
     * @return Cpanel_Tests_MockSocketServer
     */
    public function __construct()
    {
        $logfh = fopen(self::LOG_FILE, 'w+');
        $this->logfh = $logfh;
    }
    protected function log($msg)
    {
        if (is_resource($this->logfh)) {
            fwrite($this->logfh, $msg);
        }
    }
    /** 
     * @return void
     */
    public function listen()
    {
        //create socket and bind
        $this->setUpSocket();
        // Attempt accept of listening socket
        $this->log("Start accepting connections: " . $this->socketfile . "\n");
        $tmpsocket = socket_accept($this->_socket);
        if ($tmpsocket === false) {
            return $this->throwSocketError($socket, "Socket accept failed");
        }
        $this->log("Accepting connections.\n");
        $this->_tmpsocket = $tmpsocket;
        // Wait for data on wire
        while (true) {
            // Sit on read until we get a newline, anything before that should be a char length
            $this->log("Waiting of data.\n");
            $length = '';
            $lbuf = "";
            while ($lbuf !== "\n") {
                $lbuf = socket_read($tmpsocket, 1, PHP_NORMAL_READ);
                $length.= (string)$lbuf;
            };
            $length = (int)$length;
            $this->log("Read expected data length: '{$length}'.\n");
            // Now read up to the length specified
            $buf = "";
            $ok = socket_recv($tmpsocket, $buf, $length, MSG_WAITALL);
            if ($ok === false) {
                $this->log("Recv status: failed\n");
                return $this->throwSocketError($tmpsocket, "Socket recv failed");
            }
            $this->log("Read data: '{$buf}'.\n");
            // to map lookup for a pre-made response
            $response = $this->getResponseForClient($buf);
            if ($response == self::S_UNKNOWN) {
                $this->log("[Warning] Could not interpret request\n");
                //                continue;
                
            } elseif ($response == self::S_SHUTDOWN) {
                $this->log("Sending shutdown and closing.\n");
                break 1;
            }
            // Send client response
            $rlength = strlen($response);
            $this->log("Responding with: '{$response}'.\n");
            $write = socket_write($tmpsocket, $response, $rlength);
            if (!$write) {
                return $this->throwSocketError($tmpsocket, "Socket write failed");
            }
            $this->log("Response sent. Re-initializing listen\n");
        }
        $this->log("Closing down.\n");
        $this->cleanResources();
    }
    private function throwSocketError($socket, $msg)
    {
        $errno = socket_last_error($socket);
        $err = socket_strerror($errno);
        $this->cleanResources();
        $full_msg = "$msg [{$errno}]: {$err}";
        $this->log($full_msg);
        throw new Exception($full_msg);
    }
    private function setUpSocket()
    {
        //open socket
        $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
        if (!$socket) {
            return $this->throwSocketError($socket, "Socket could not be created");
        }
        $socketfile = $this->socketfile;
        $this->_socket = $socket;
        // Bind socket and address
        if (!(socket_bind($socket, $socketfile))) {
            return $this->throwSocketError($socket, "Socket binding failed");
        }
        $this->_socket = $socket;
        // Set socket to listen
        if (!(socket_listen($socket))) {
            return $this->throwSocketError($socket, "Socket listen failed");
        }
        $this->_socket = $socket;
    }
    /**
     * 
     * @param string $file
     * 
     * @return Cpanel_Tests_MockSocketServer
     */
    public function setSocketFile($file)
    {
        if (empty($file) || strpos($file, '.sock') === false) {
            throw new Exception("Invalid socket file: $file");
        }
        if (file_exists($file) && strpos($file, '.sock') !== false) {
            unlink($file);
        }
        $this->socketfile = $file;
        return $this;
    }
    public function cleanResources()
    {
        if ($this->_socket && is_resource($this->_socket)) {
            socket_close($this->_socket);
        }
        if ($this->_tmpsocket && is_resource($this->_tmpsocket)) {
            socket_close($this->_tmpsocket);
        }
        if (file_exists($this->socketfile) && strpos($this->socketfile, '.sock') !== false) {
            unlink($this->socketfile);
        }
        if ($this->logfh && is_resource($this->logfh)) {
            fclose($this->logfh);
        }
    }
    public function __destruct()
    {
        $this->log("Destroying mock socket server object.\n");
        $this->cleanResources();
    }
    const C_ENABLE_JSON = '<cpaneljson enable="1">';
    const S_JSON_ENABLED = "<?xml version=\"1.0\" ?>\n<cpanelresult>{\"data\":{\"result\":\"json\"}}</cpanelresult>\n";
    const C_SHUTDOWN = '<cpanelxml shutdown="1" />';
    const S_SHUTDOWN = '--SHUTDOWN--';
    const S_UNKNOWN = "<?xml version=\"1.0\" ?>\n<cpanelresult>{\"result\":0,\"error\":\"Unknown API request\"}</cpanelresult>\n";
    const C_Q_PHPINI = "<cpanelaction>\n{\"reqtype\":\"exec\",\"module\":\"PHPINI\",\"func\":\"getoptions\",\"apiversion\":\"2\",\"args\":{\"dirlist\":\"allow_url_fopen\"}}\n</cpanelaction>";
    const S_R_PHPINI = "<?xml version=\"1.0\" ?>\n<cpanelresult>{\"cpanelresult\":{\"data\":[{\"section\":\"PHP\",\"directive\":\"allow_url_fopen\",\"dirvalue\":\"On\",\"commented\":0,\"subsection\":\"Fopen wrappers\",\"info\":\"Whether to allow the treatment of URLs (like http:// or ftp://) as files.\"}],\"event\":{\"result\":1},\"module\":\"PHPINI\",\"apiversion\":2,\"func\":\"getoptions\"}}</cpanelresult>\n";
    const C_Q_CPTAG_PRINT_FOO = "<cpanel print=\"foo\">";
    const C_Q_API1_PRINT_FOO_STR = "<cpanelaction>\n{\"reqtype\":\"exec\",\"module\":\"print\",\"func\":\"\",\"apiversion\":\"1\",\"args\":\"foo\"}\n</cpanelaction>";
    const C_Q_API1_PRINT_FOO_ARRAY = "<cpanelaction>\n{\"reqtype\":\"exec\",\"module\":\"print\",\"func\":\"\",\"apiversion\":\"1\",\"args\":[\"foo\"]}\n</cpanelaction>";
    const S_R_PRINT_FOO = "<?xml version=\"1.0\" ?>\n<cpanelresult>{\"apiversion\":\"1\",\"type\":\"internal\",\"command\":\"print\",\"source\":\"internal\",\"data\":{\"result\":\"foo\"},\"event\":{\"result\":1}}</cpanelresult>\n";
    const C_Q_API1_PRINT_HOMEDIR_STR = "<cpanelaction>\n{\"reqtype\":\"exec\",\"module\":\"print\",\"func\":\"\",\"apiversion\":\"1\",\"args\":\"\$homedir\"}\n</cpanelaction>";
    const C_Q_API1_PRINT_HOMEDIR_ARRAY = "<cpanelaction>\n{\"reqtype\":\"exec\",\"module\":\"print\",\"func\":\"\",\"apiversion\":\"1\",\"args\":[\"\$homedir\"]}\n</cpanelaction>";
    const C_Q_FETCH_HOMEDIR = "<cpanel print=\"\$homedir\">";
    const S_R_PRINT_HOMEDIR = "<?xml version=\"1.0\" ?>\n<cpanelresult>{\"apiversion\":\"1\",\"type\":\"internal\",\"command\":\"print\",\"source\":\"internal\",\"data\":{\"result\":\"/home/dave\"},\"event\":{\"result\":1}}</cpanelresult>\n";
    const C_Q_API_IF_NOTHASPOSTGRES = "<cpanelaction>\n{\"reqtype\":\"if\",\"module\":\"if\",\"func\":\"if\",\"apiversion\":\"1\",\"args\":\"!\$haspostgres\"}\n</cpanelaction>";
    const S_R_INTEGER_NOTHASPOSTGRES = "<?xml version=\"1.0\" ?>\n<cpanelresult>{\"cpanelresult\":{\"data\":{\"result\":1},\"module\":\"if\",\"apiversion\":1}}\n</cpanelresult>\n";
    const C_Q_API_FEATURE_FILEMAN = "<cpanelaction>\n{\"reqtype\":\"feature\",\"module\":\"feature\",\"func\":\"feature\",\"apiversion\":\"1\",\"args\":\"fileman\"}\n</cpanelaction>";
    const S_R_INTEGER_FEATURE_FILEMAN = "<?xml version=\"1.0\" ?>\n<cpanelresult>{\"cpanelresult\":{\"data\":{\"result\":1},\"module\":\"feature\",\"apiversion\":1}}\n</cpanelresult>\n";
    public function getResponseForClient($clientRequest)
    {
        $this->log("Attempting to find response for client.\n");
        switch ($clientRequest) {
        case self::C_ENABLE_JSON:
            $this->log("Received JSON enable request\n");
            $r = self::S_JSON_ENABLED;
            break;

        case self::C_Q_PHPINI:
            $this->log("Received API2 PHPINI::getOptions request.\n");
            $r = self::S_R_PHPINI;
            break;

        case self::C_SHUTDOWN:
            $this->log("Received shutdown request from client.\n");
            $r = self::S_SHUTDOWN;
            break;

        case self::C_Q_API1_PRINT_FOO_STR:
            $this->log("Received API1 print module with string.\n");
        case self::C_Q_API1_PRINT_FOO_ARRAY:
            $this->log("Received API1 print module with array.\n");
        case self::C_Q_CPTAG_PRINT_FOO:
            $this->log("Received API1 print tag.\n");
            $r = self::S_R_PRINT_FOO;
            break;

        case self::C_Q_API1_PRINT_HOMEDIR_STR:
            $this->log("Received API1 print module with string.\n");
        case self::C_Q_API1_PRINT_HOMEDIR_ARRAY:
            $this->log("Received API1 print module with array.\n");
        case self::C_Q_FETCH_HOMEDIR:
            $this->log("Received API1 print tag.\n");
            $r = self::S_R_PRINT_HOMEDIR;
            break;

        case self::C_Q_API_IF_NOTHASPOSTGRES:
            $this->log("Received API1 if tag.\n");
            $r = self::S_R_INTEGER_NOTHASPOSTGRES;
            break;

        case self::C_Q_API_FEATURE_FILEMAN:
            $this->log("Received API1 feature tag.\n");
            $r = self::S_R_INTEGER_FEATURE_FILEMAN;
            break;

        default:
            $r = self::S_UNKNOWN;
        }
        return $r;
    }
}
?>