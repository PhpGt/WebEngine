<?php class Hybrid_Providers_Dummy extends Hybrid_Provider_Model {
    private $_userID;

    function initialize() {
    }

    function loginBegin() {
        $endPoint = Hybrid_Auth::storage()->get( "hauth_session.{$this->providerId}.hauth_endpoint" );
        Hybrid_Auth::redirect( $endPoint ); 
    }

    function loginFinish() {
        if(isset($this->params["Error_Code"])) {
            throw new Exception( "Authentication failed! {$this->providerId} returned error "
                . $this->params["Error_Code"] . " as requested", $this->params["Error_Code"]);
        }

        // capture the userID that we want to use
        if(!isset($this->params["ID_User"])) {
            $this->params["ID_User"] = Session::get("PhpGt.User.ID");
        }
        $this->_userID = $this->params["ID_User"];
        Session::set("PhpGt.Auth.ID_User", $this->_userID);

        if(!isset($this->params["ID_User"])) {
            $this->params["ID_User"] = Session::get("PhpGt.User.ID");
        }
        $this->_userID = $this->params["ID_User"];
        $this->user->profile->identifier = $this->_userID;
        $this->user->profile->displayName = "Dummy user " . $this->_userID;

        // store a dummy token (straight-map back to the appUserID)
        $this->token( "access_token", $this->_userID );

        // set user connected locally
        $this->setUserConnected();
    }

    function getUserProfile()
    {
        $this->user->profile->identifier = $this->_userID;
        return $this->user->profile;
    }
}# 