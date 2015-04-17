<?PHP

class User{
    public $data;
    private $userId, $userName, $email, $hash, $isAdmin;

    public function __construct($sqlRow){
        $this->userId = $sqlRow['userId'];
        $this->userName = $sqlRow['userName'];
        $this->email = $sqlRow['email'];
        $this->hash = $sqlRow['passHash'];
        $this->isAdmin = $sqlRow['admin'] == 1;

        $this->data['username_clean'] = $this->userName;
        $this->data['user_id'] = $this->userId;
        $this->data['user_email'] = $this->email;
        $this->data['group_id'] = 0; // Groups are not yet implemented
        $this->data['session_id'] = make_session_hash($this->userName, $this->hash);
    }

    public function configure_session(){
        /* Set PHP Session vars */
        $session_id = make_session_hash($this->userName, $this->hash);

        session_start();
        $_SESSION['login_user'] = $this->userName;
        $_SESSION['login_session'] = $session_id;
    }

    public function end_session(){
        session_start();
        $_SESSION = array();
        session_destroy();
    }

    public function is_admin(){
        return $this->isAdmin;
    }
}

function make_session_hash($user, $hash){
    return hash("sha512", $user.$hash);
}

?>
