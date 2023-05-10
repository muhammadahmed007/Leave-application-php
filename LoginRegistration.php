<?php 
session_start();
require_once('DBConnection.php');
/**
 * Login Registration Class
 */
Class LoginRegistration extends DBConnection{
    function __construct(){
        parent::__construct();
    }
    function __destruct(){
        parent::__destruct();
    }
    function login(){
        /**
         * Login Form
         * 
         */
        //Extracting Post array to variables.
        extract($_POST);
        // Retrieving Allowed Token
        $allowedToken = $_SESSION['formToken']['login'];
        if(!isset($formToken) || (isset($formToken) && $formToken != $allowedToken)){
            // throw new ErrorException("Security Check: Form Token is valid.");
            $resp['status'] = 'failed';
            $resp['msg'] = "Security Check: Form Token is invalid.";
        }else{
            // Query Statement
            $sql = "SELECT * FROM user_list where username = :username ";
            // Preparing Query Statement
            $stmt = $this->prepare($sql);
            // binding Query Value/s
            $stmt->bindValue(':username', $username, SQLITE3_TEXT);
            // Executing Query
            $result = $stmt->execute();
            // Fetching Result
            $data = $result->fetchArray();
            if(!empty($data)){
                // print_r($data['password']);exit;
                //Verifying Password
                $password_verify = password_verify($password, $data['password']);
                if($password_verify){
                    if($data['status'] == 1){
                        // Login Success
                        $resp['status'] = "success";
                        $resp['msg'] = "Login successfully.";
                        foreach($data as $k => $v){
                            if(!is_numeric($k) && !in_array($k, ['password']))
                            $_SESSION[$k] = $v;
                        }
                    }elseif($data['status'] == 0){
                        // Pending
                        $resp['status'] = "failed";
                        $resp['msg'] = "Your account is still on subject for approval status.";
                    }elseif($data['status'] == 2){
                        // Denied
                        $resp['status'] = "failed";
                        $resp['msg'] = "Your account has been denied to access the system. Please contact the management to settle.";
                    }
                    elseif($data['status'] == 3){
                        // Blocked
                        $resp['status'] = "failed";
                        $resp['msg'] = "Your account has been blocked. Please contact the management to settle.";
                    }else{
                        $resp['status'] = "failed";
                        $resp['msg'] = "Invalid Status. Please contact the management to settle.";
                    }
                   
                }else{
                    // Invalid Password
                    $resp['status'] = "failed";
                    $resp['msg'] = "Invalid username or password.";
                }
            }else{
                // Invalid Username
                $resp['status'] = "failed";
                $resp['msg'] = "Invalid username or password.";
            }
        }
        return json_encode($resp);
    }
    function logout(){
        // Destroying user session
        session_destroy();
        header("location:./");
    }
    function save_user(){
        /**
         * Save User Form Action
         */

         //escaping input values
        foreach($_POST as $k => $v){
            if(!in_array($k, ['user_id', 'formToken']) && !is_numeric($v) && !is_array($_POST[$k])){
                $_POST[$k] = $this->escapeString($v);
            }
        }
        //extracting Post array values
        extract($_POST);
        $allowedToken = $_SESSION['formToken']['user-form'];
        if(!isset($formToken) || (isset($formToken) && $formToken != $allowedToken)){
            // throw new ErrorException("Security Check: Form Token is valid.");
            $resp['status'] = 'failed';
            $resp['msg'] = "Security Check: Form Token is invalid.";
        }else{
            if(empty($user_id)){
                // Table column 
                $dbColumn = "(`fullname`, `username`, `password`, `status`, `type`)";
                // Encypting Password
                $password = password_hash($password, PASSWORD_DEFAULT);
                // Table Values
                $values = "('{$fullname}', '{$username}', '{$password}', '{$status}}', '{$type}')";
                // insertion Query Statement
                $sql = "INSERT INTO `user_list` {$dbColumn} VALUES {$values}";
            }else{
                $data = "";
                foreach($_POST as $k => $v){
                    if(in_array($k, ['fullname', 'username', 'status', 'type'])){
                        if(!empty($data)) $data .= ", ";
                        $data .=  "`{$k}` = '{$v}'";
                    }
                }
                if(isset($password) && !empty($password)){
                    $password = password_hash($password, PASSWORD_DEFAULT);
                    if(!empty($data)) $data .= ", ";
                        $data .=  "`password` = '{$password}'";
                }
                $sql = "UPDATE `user_list` set {$data} where `user_id` = '{$user_id}'";
            }
            
            // Executing Save Query
            $save = $this->query($sql);
            if($save){
                // Successfull insertion
                $resp['status'] = 'success';
                if(empty($user_id))
                $resp['msg'] = "New User has been added successfully.";
                else
                $resp['msg'] = "User Details have been updated successfully.";
            }else{
                // Insertion Failed
                $resp['status'] = 'failed';
                $resp['msg'] = "Error: ".$this->lastErrorMsg();
            }
        }
        echo json_encode($resp);
    }
    function update_user(){
        /**
         * Update User Form Action
         */

        //extracting Post array values
        extract($_POST);
        $allowedToken = $_SESSION['formToken']['manage_user'];
        if(!isset($formToken) || (isset($formToken) && $formToken != $allowedToken)){
            // throw new ErrorException("Security Check: Form Token is valid.");
            $resp['status'] = 'failed';
            $resp['msg'] = "Security Check: Form Token is invalid.";
        }else{
            //update data
            $data = "`status` = '{$status}'";
            $data .= ",`type` = '{$type}'";
            
            // UPDATE Query Statement
            $sql = "UPDATE `user_list` set {$data} where `user_id` = '{$user_id}'";
            // Executing update Query
            $update = $this->query($sql);
            if($update){
                // Successfull Update
                $resp['status'] = 'success';
                $resp['msg'] = "User Account has been updated successfully.";

            }else{
                // Update Failed
                $resp['status'] = 'failed';
                $resp['msg'] = "Error: ".$this->lastErrorMsg();
            }
        }
        echo json_encode($resp);
    }
}
$a = isset($_GET['a']) ?$_GET['a'] : '';
$LG = new LoginRegistration();
switch($a){
    case 'login':
        echo $LG->login();
    break;
    case 'logout':
        echo $LG->logout();
    break;
    case 'save_user':
        echo $LG->save_user();
    break;
    case 'update_user':
        echo $LG->update_user();
    break;
    default:
    // default action here
    break;
}