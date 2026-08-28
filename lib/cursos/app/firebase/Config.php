<?php
switch ($_SERVER["SERVER_NAME"]) {
    case "particular.localhost":
        $this->host = "localhost:3306";
        $this->user = "root";
        $this->pass = "";
        $this->db = "opctec";
        $this->type = "mysql";
        break;
    case "localhost":
        $this->host = "localhost:3306";
        $this->user = "root";
        $this->pass = "";
        if (getenv('USERDOMAIN') == 'MSHOME-002') {
            $this->pass = "bitnami";
        }
        $this->db = "opcte984_opctec";
        $this->type = "mysqli";
        break;
    case "localhost:8080":
        $this->host = "localhost:3306";
        $this->user = "root";
        $this->pass = "";
        if (getenv('USERDOMAIN') == 'MSHOME-002') {
            $this->pass = "bitnami";
        }
        $this->db = "opcte984_opctec";
        $this->type = "mysqli";
        break;
    case "zaqueu.ddns.net":
        $this->host = "localhost:3303";
        $this->user = "root";
        $this->pass = "root";
        $this->db = "opcte984_opctec";
        $this->type = "mysqli";
        break;
    case "www.opctec.com.br":
    case "opctec.com.br":
    case "sisevento.mostraparanaensesaude.org.br":
        $this->host = "localhost";
        $this->user = "opcte984_opctec";
        $this->pass = "cbmp#2017";
        $this->db = "opcte984_opctec";
        $this->type = "mysqli";
        $this->charset = "utf8";
        break;
    default :
        if ($_SERVER['REMOTE_ADDR'] == '189.90.70.56') {
            $this->host = "localhost:3306";
            $this->user = "root";
            $this->pass = "bitnami";
            $this->db = "opcte984_opctec";
            $this->type = "mysqli";
        } else {
            $this->host = "localhost:3306";
            $this->user = "root";
            $this->pass = "bitnami";
            $this->db = "opcte984_opctec";
            $this->type = "mysqli";
        }
        break;
}
define('DB_USERNAME', $this->user);
define('DB_PASSWORD', $this->pass);
define('DB_NAME', $this->db);
define('DB_HOST', $this->host);

//defined a new constant for firebase api key
define('FIREBASE_API_KEY', 'AAAA1ag8uZ0:APA91bFZWTW8pxxvg3gQAOExCph3CRmiDeHOc-D5Q9eEiZYSzyjI5nn381u-mjmtnuOwRG7StOyPmZR-ZrckibhiaIAkN6Xwloi9PuWF7f5h2ViRTkkMx0s3MEgYUA8CTo69xG_trJ3e');
//define('FIREBASE_API_KEY', 'AIzaSyB9Kdhyedgv32fUAQX8ckLVp-e2G-Wwej0');
