<?php 
if(session_id() ==="")
session_start();
require_once('DBConnection.php');
/**
 * Login Registration Class
 */
Class Master extends DBConnection{
    function __construct(){
        parent::__construct();
    }
    function __destruct(){
        parent::__destruct();
    }
    function save_settings(){
        foreach($_POST as $k => $v){
            if(!in_array($k, ['formToken']) && !is_array($_POST[$k]) && !is_numeric($v)){
                $_POST[$k] = $this->escapeString($v);
            }
        }
        extract($_POST);
        $allowedToken = $_SESSION['formToken']['wallet_management'];
        if(!isset($formToken) || (isset($formToken) && $formToken != $allowedToken)){
            $resp['status'] = 'failed';
            $resp['msg'] = "Security Check: Form Token is invalid.";
        }else{
            $user_id = $_SESSION['user_id'];
            $columns = [];
            $values = [];
            foreach($_POST as $k => $v){
                if(!is_array($_POST[$k]) && !in_array($k, ['formToken'])){
                    $columns[] = $k;
                    $values[] = $v;
                }
            }
            if(empty($columns) && empty($values)){
               $resp['status'] = 'failed';
               $resp['msg'] = "No data has been sent.";
            }else{
                foreach($columns as $k => $v){
                    $setting_id = "";
                    $check = $this->query("SELECT setting_id FROM `settings` where `user_id` = '{$user_id}' and `name` = '{$v}'");
                    $settingsData = $check->fetchArray();
                    if(!empty($settingsData)){
                        $setting_id = $settingsData['setting_id'];
                    }
                    if(!empty($setting_id)){
                        $sql = "UPDATE `settings` set `value` = '{$values[$k]}' where `setting_id` = '{$setting_id}'";
                    }else{
                        $sql = "INSERT INTO `settings` (`user_id`, `name`, `value`) VALUES ('{$user_id}', '{$v}', '{$values[$k]}')";
                    }
                    $qry = $this->query($sql);
                    if(!$qry){
                        $resp['status'] = 'failed';
                        $resp['msg'] = "Error: ".$this->lastErrorMsg();
                        break;
                    }
                }
                $resp['status'] = 'success';
                $resp['msg'] = "Wallet Data has been updated successfully.";
            }
        }
        return json_encode($resp);
    }
    function save_employee(){
        if(!isset($_POST['user_id']))
        $_POST['user_id'] = $_SESSION['user_id'];
        foreach($_POST as $k => $v){
            if(!in_array($k, ['formToken']) && !is_array($_POST[$k]) && !is_numeric($v)){
                $_POST[$k] = $this->escapeString($v);
            }
        }
        extract($_POST);
        $allowedToken = $_SESSION['formToken']['employee-form'];
        if(!isset($formToken) || (isset($formToken) && $formToken != $allowedToken)){
            $resp['status'] = 'failed';
            $resp['msg'] = "Security Check: Form Token is invalid.";
        }else{
            $check = $this->querySingle("SELECT COUNT(`employee_id`) FROM `employee_list` where `code` = '{$code}'". (($employee_id > 0) ? " and `employee_id` != '{$employee_id}' " : "" ));
            if($check > 0){
                $resp['status'] = 'failed';
                $resp['msg'] = "Employee Code Already Exists.";
            }else{
                if(empty($employee_id)){
                    $sql = "INSERT INTO `employee_list` (`code`, `firstname`, `middlename`, `lastname`, `email`, `contact`, `department`, `designation`, `status`) VALUES ('{$code}', '{$firstname}', '{$middlename}', '{$lastname}', '{$email}', '{$contact}', '{$department}', '{$designation}', '{$status}')";
                }else{
                    $sql = "UPDATE `employee_list` set `code` = '{$code}', `firstname` = '{$firstname}', `middlename` = '{$middlename}', `lastname` = '{$lastname}', `email` = '{$email}', `contact` = '{$contact}', `department` = '{$department}', `designation` = '{$designation}', `status` = '{$status}' where `employee_id` = '{$employee_id}'";
                }
                $qry = $this->query($sql);
                if($qry){
                    if(empty($employee_id)){
                        $employee_id = $this->lastInsertRowID();
                    }
                    $error = "";
                    // $this->query("DELETE FROM `leave_priv_list` where `employee_id` = '{$employee_id}'");
                    $data="";
                    if(!isset($leave_priv_id)){
                        $leave_priv_id = [];
                    }
                    $still_active_ids = [];
                    foreach($leave_priv_name as $k => $v){
                        if(empty($leave_priv_id[$k])){
                            if(!empty($data)) $data .= ", ";
                            $data .= "('{$employee_id}', '{$v}', '{$leave_priv_credits[$k]}')";
                        }else{
                            $still_active_ids[] = $leave_priv_id[$k];
                            $this->query("UPDATE `leave_priv_list` set `name` = '{$v}', `credits` = '{$leave_priv_credits[$k]}' where `leave_priv_id` = '{$leave_priv_id[$k]}'");
                        }
                    }
                    if(count($still_active_ids) > 0){
                        $still_active_ids = implode(",", $still_active_ids);
                        $this->query("DELETE FROM `leave_priv_list` where `leave_priv_id` NOT IN ({$still_active_ids})");
                    }
                    if(!empty($data)){
                        $sql2 = "INSERT INTO `leave_priv_list` (`employee_id`, `name`, `credits`) VALUES {$data}";
                        $insert2 = $this->query($sql2);
                        if(!$insert2){
                            $error = $this->lastErrorMsg();
                        }
                    }
                    if(empty($error)){
                        $resp['status'] = 'success';
                        if(empty($employee_id))
                        $resp['msg'] = 'New Employee has been addedd successfully';
                        else
                        $resp['msg'] = 'Employee Data has been updated successfully';
                        $_SESSION['message']['success'] = $resp['msg'];
                    }else{
                        $resp['status'] = 'failed';
                        $resp['msg'] = $error;
                    }
                }else{
                    $resp['status'] = 'failed';
                    $resp['msg'] = 'Error:'. $this->lastErrorMsg(). ", SQL: {$sql}";
                }
            }
        }
        return json_encode($resp);
    }
    function update_employee_status(){
        extract($_POST);
        $allowedToken = $_SESSION['formToken']['employeeDetails'];
        if(!isset($formToken) || (isset($formToken) && $formToken != $allowedToken)){
            $resp['status'] = 'failed';
            $resp['msg'] = "Security Check: Token is invalid.";
        }else{
            $sql = "UPDATE `employee_list` set `status` = '{$status}' where `employee_id` = '{$employee_id}'";
            $update = $this->query($sql);
            if($update){
                $resp['status'] = 'success';
                $resp['msg'] = "employee status has been updated successfully";
                $_SESSION['message']['success'] = $resp['msg'];
            }else{
                $resp['status'] = 'failed';
                $resp['msg'] = $this->lastErrorMsg();
            }
        }
        return json_encode($resp);
    }
    function delete_employee(){
        extract($_POST);
        $allowedToken = $_SESSION['formToken']['employees'];
        if(!isset($token) || (isset($formToken) && $formToken != $allowedToken)){
            $resp['status'] = 'failed';
            $resp['msg'] = "Security Check: Token is invalid.";
        }else{
            $sql = "DELETE FROM `employee_list` where `employee_id` = '{$id}'";
            $delete = $this->query($sql);
            if($delete){
                $resp['status'] = 'success';
                $resp['msg'] = 'The employee data has been deleted successfully';
                $_SESSION['message']['success'] = $resp['msg'];
            }else{
                $resp['status'] = 'failed';
                $resp['msg'] = $this->lastErrorMsg();
            }
        }
        return json_encode($resp);
    }
    function save_application(){
        if(!isset($_POST['user_id']))
        $_POST['user_id'] = $_SESSION['user_id'];
        foreach($_POST as $k => $v){
            if(!in_array($k, ['formToken']) && !is_array($_POST[$k]) && !is_numeric($v)){
                $_POST[$k] = $this->escapeString($v);
            }
        }
        extract($_POST);
        $allowedToken = $_SESSION['formToken']['application-form'];
        if(!isset($formToken) || (isset($formToken) && $formToken != $allowedToken)){
            $resp['status'] = 'failed';
            $resp['msg'] = "Security Check: Form Token is invalid.";
        }else{
            if(empty($application_id)){
                $sql = "INSERT INTO `application_list` (`employee_id`, `leave_priv_id`, `from`, `to`, `type`, `remarks`, `status`) VALUES ('{$employee_id}', '{$leave_priv_id}', '{$from}', '{$to}', '{$type}', '{$remarks}', '{$status}')";
            }else{
                $sql = "UPDATE `application_list` set `from` = '{$from}', `to` = '{$to}', `remarks` = '{$remarks}', `type` = '{$type}', `status` = '{$status}' where `application_id` = '{$application_id}'";
            }
            $qry = $this->query($sql);
            if($qry){
                $resp['status'] = 'success';
                if(empty($application_id))
                $resp['msg'] = 'New Application has been addedd successfully';
                else
                $resp['msg'] = 'Application Data has been updated successfully';
                $_SESSION['message']['success'] = $resp['msg'];
            }else{
                $resp['status'] = 'failed';
                $resp['msg'] = 'Error:'. $this->lastErrorMsg(). ", SQL: {$sql}";
            }
        }
        return json_encode($resp);
    }
    function delete_application(){
        extract($_POST);
        $allowedToken = $_SESSION['formToken']['applications'];
        if(!isset($token) || (isset($formToken) && $formToken != $allowedToken)){
            $resp['status'] = 'failed';
            $resp['msg'] = "Security Check: Token is invalid.";
        }else{
            $sql = "DELETE FROM `application_list` where `application_id` = '{$id}'";
            $delete = $this->query($sql);
            if($delete){
                $resp['status'] = 'success';
                $resp['msg'] = 'The application data has been deleted successfully';
                $_SESSION['message']['success'] = $resp['msg'];
            }else{
                $resp['status'] = 'failed';
                $resp['msg'] = $this->lastErrorMsg();
            }
        }
        return json_encode($resp);
    }
    function get_leave_privs(){
        extract($_POST);
        $sql = "SELECT * FROM `leave_priv_list` where `employee_id` = '{$employee_id}'";
        $qry = $this->query($sql);
        $data = [];
        while($row = $qry->fetchArray(SQLITE3_ASSOC)){
            $used_qry = $this->query("SELECT * FROM `application_list` where `leave_priv_id` = '{$row['leave_priv_id']}' and strftime('%Y') = '". date('Y')."' and `status` = 1 ");
            $used = 0;
            while($urow = $used_qry->fetchArray(SQLITE3_ASSOC)){
                $used += (((strtotime($urow['to']) - strtotime($urow['from'])) / (60 * 60 * 24)) + 1) / $urow['type'];
            }
            $row['available'] = $row['credits'] - $used;
            $data[] = $row;
        }
        return json_encode($data);
    }
    function total_pending(){
        $date = new DateTime(date("Y-m-d"), new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone('Asia/Manila'));
        $year = $date->format("Y");
        $sql = "SELECT count(application_id) FROM `application_list` where strftime('%Y', `date_created`) = '{$year}'";
        $qry = $this->querySingle($sql);
        return $qry ?? 0;
    }
}
$a = isset($_GET['a']) ?$_GET['a'] : '';
$master = new Master();
switch($a){
    case 'save_settings':
        echo $master->save_settings();
    break;
    case 'save_employee':
        echo $master->save_employee();
    break;
    case 'update_employee_status':
        echo $master->update_employee_status();
    break;
    case 'delete_employee':
        echo $master->delete_employee();
    break;
    case 'save_application':
        echo $master->save_application();
    break;
    case 'get_leave_privs':
        echo $master->get_leave_privs();
    break;
    case 'delete_application':
        echo $master->delete_application();
    break;
    case 'get_leave_privs':
        echo $master->get_leave_privs();
    break;
    default:
    // default action here
    break;
}