<?php
Class Disburse {
    private $db;
    public function __construct(){
        $this->db = new Database();
        $this->inputMenu();
    }

    public function inputMenu(){
        echo "Type 'request' to send disbursement request,\nType 'check' to check disbursement status\nType 'exit' to exit the application: ";
        $input_menu= fopen("php://stdin","r");
        $menu = trim(fgets($input_menu));
        $this->processMenu(strtolower($menu));
    }

    public function processMenu($menu){
        switch($menu){
            case 'request':
                $this->inputRequest();
                break;
            case 'check':
                $this->inputCheck();
                break;
            case 'exit':
                $this->exitDisburse();
                break;
            default:
                $msg = "Your request is unavailable. Press enter to go back to main menu \n";
                $this->messageBox($msg);
        }
    }

    public function messageBox($msg){
        echo $msg;
        $input_failed= fopen("php://stdin","r");
        $f = trim(fgets($input_failed));
        $this->inputMenu();
    }

    public function inputRequest(){
        echo "Type Bank Code: ";
        $input_bank_code= fopen("php://stdin","r");
        $bank_code = strtolower(trim(fgets($input_bank_code)));
        echo "Type Account Number: ";
        $input_account_number= fopen("php://stdin","r");
        $account_number = trim(fgets($input_account_number));
        echo "Type Amount: ";
        $input_amount= fopen("php://stdin","r");
        $amount = trim(fgets($input_amount));
        echo "Type Remark: ";
        $input_remark= fopen("php://stdin","r");
        $remark = trim(fgets($input_remark));
        $array=array($bank_code=>'string', $account_number=>'int', $amount=>'int', $remark=>'string');
        $errmessage = "Request failed due to incomplete data \n Press Enter to go back to main menu \n";
        $action ='sendData';
        $data = "bank_code=".$bank_code."&account_number=".$account_number."&amount=".$amount."&remark=".$remark.""; 
        $this->validateInput($array, $action, $data, $errmessage);
    }

    public function getData(){
        $this->db->query('SELECT * FROM transaction');
        return $this->db->resultSet();
    }

    public function inputCheck(){
        if($result = $this->getData()){
            echo "Transaction list \n";
            echo "-------------------------------------------------------------------------------------------- \n";

            $mask = "%-20.20s %12.12s %-10.10s %-5.5s %-20.20s %-25.25s \n";
            printf($mask, 'Transaction Id', 'Amount', 'Status', 'Bank Code', 'Account Number', 'remark');
            printf($mask, '--------------------', '-------------', '----------', '------------', '--------------------', '--------------------');
            
            foreach($result as $r){
                printf($mask, $r['id'], $r['amount'], $r['status'], $r['bank_code'], $r['account_number'], $r['remark']);
            }
            echo "-------------------------------------------------------------------------------------------- \n";
            echo "Type transaction Id:";
            $input_id= fopen("php://stdin","r");
            $id = trim(fgets($input_id));
            $array = array($id=>'int');
            $errmessage = "Transaction Id is invalid. Press enter to go back to main menu \n";
            $action ='checkData';
            $data = $id;
            $this->validateInput($array, $action, $data, $errmessage);
        }
        else{
            $msg = "No transaction available to be checked. Press enter to go back to main menu \n";
            $this->messageBox($msg);
        }
    }

    public function validateInput($array, $action, $data, $msg){
        $f=0;
        foreach($array as $k=>$v){
            if($v==='string'){
                if($k===''){
                    $f++;
                }
            }
            elseif($v==='int'){
                if($k<1){
                    $f++;
                }
            }  
        }
        if($f===0){
            $this->$action($data);
            // echo "pass";
        }
        else{
            $this->messageBox($msg);
        }
    }

    public function request($url, $method, $data=""){
        $secret_key = "HyzioY7LP6ZoO7nTYKbG8O4ISkyWnX1JvAEVAhtWKZumooCzqp41";
        $encoded_auth = base64_encode($secret_key.":");
        $curl = curl_init();
    
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => $method,
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            "content-type: application/x-www-form-urlencoded",
            "Authorization: Basic ".$encoded_auth
          ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $data = json_decode($response, true);
        curl_close($curl);
        
        return $data;
    }

    public function sendData($data){
   
        $url = "https://nextar.flip.id/disburse";
        if($result = $this->request($url,"POST", $data)){
            // print_r($result);
            $this->save($result);
        }
        else{
            echo "error";
        }      
    }

    public function save($data){
        $query = "INSERT INTO transaction VALUES (:id, :amount, :status, :timestamp, :bank_code, :account_number, :beneficiary_name, :remark, :receipt, :time_served, :fee)";
        $this->db->query($query);
        foreach($data as $k => $v){
            $this->db->bind($k, $v);
        }
        $this->db->execute();
        if($this->db->row()){
            echo "Transaction request succeeded.\n";
            echo "------------------------------\n";
            foreach($data as $k=>$v){
                echo "\t".$k." : ".$v. "\n";
            }
            echo "------------------------------\n";
            $msg = "Transaction detail saved to database. Press enter to go back to main menu"; 
            $this->messageBox($msg);
        }
    }
        
    public function getTransById($id){
        $this->db->query("SELECT * FROM transaction WHERE id=:id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function checkData($id){
        if($this->getTransById($id)){
            
            $url = "https://nextar.flip.id/disburse/".$id;
            $result = $this->request($url,"GET",'');
            if($result){

                $this->update($result);
            }
            else{
                echo "error";
            }
        }
        else{
            $msg = "Transaction Id is not exist. Press enter to go back to main menu \n";
            $this->messageBox($msg);
        }
    }

    public function update($data){
        $query = "UPDATE transaction SET status=:status, timestamp=:timestamp, receipt=:receipt, time_served=:time_served WHERE id=:id";
        $this->db->query($query);
        $this->db->bind('status', $data['status']);
        $this->db->bind('timestamp', $data['timestamp']);
        $this->db->bind('receipt', $data['receipt']);
        $this->db->bind('time_served', $data['time_served']);
        $this->db->bind('id', $data['id']);
        $this->db->execute();
        if($this->db->row()){
            echo "Transaction status checked and updated.\n";
            echo "------------------------------\n";
            foreach($data as $k=>$v){
                echo "\t".$k." : ".$v. "\n";
            }
            echo "------------------------------\n";
            $msg = "Press enter to go back to main menu"; 
            $this->messageBox($msg);
        }
        else{
            echo "gagal disimpan";
            print_r($data);
        }
    }

    
    public function exitDisburse(){
        exit('You have closed the application.');
    }
}
?>
