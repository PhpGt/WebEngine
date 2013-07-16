<?php class Hybrid_Providers_Dummy extends Hybrid_Provider_Model {
    private $_userID;

    function initialize() {
        // Temporary logging:
        error_log("Initialising Dummy hybridauth provider - providerID is " . $this->providerId);
        error_log("user->profile is " . serialize($this->user->profile));
        error_log("is connected?: " . $this->isUserConnected());
        if(!isset($_SESSION["PhpGt_Auth"])) {
           $_SESSION["PhpGt_Auth"] = array();
        }
    }

    function loginBegin() {
        // capture the userID that we want to use
        if(!isset($this->params["ID_User"])) {
            $this->params["ID_User"] = $_SESSION["User"]["ID"];
        }
        $this->_userID = $this->params["ID_User"];
        $_SESSION["PhpGt_Auth"]["ID_User"] = $this->_userID;

        $endPoint = Hybrid_Auth::storage()->get( "hauth_session.{$this->providerId}.hauth_endpoint" );
        error_log("Redirecting to " . $endPoint);
        Hybrid_Auth::redirect( $endPoint ); 
    }

    function loginFinish() {
        error_log("Finishing login");
        if(!isset($this->params["ID_User"])) {
            $this->params["ID_User"] = $_SESSION["User"]["ID"];
        }
        $this->_userID = $this->params["ID_User"];
        $this->user->profile->identifier = $this->_userID;
        $this->user->profile->displayName = "Dummy user " . $this->_userID;

        // store a dummy token (straight-map back to the appUserID)
        $this->token( "access_token", $this->_userID );

        // set user connected locally
        error_log("setting connected flag!");
        $this->setUserConnected();
    }

    function getUserProfile()
    {
        $this->user->profile->identifier = $this->_userID;
        return $this->user->profile;
    }
}# 