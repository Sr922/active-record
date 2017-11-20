<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);


define('DATABASE', 'sr922');
define('USERNAME', 'sr922');
define('PASSWORD', 'EasV4ALf');
define('CONNECTION', 'sql.njit.edu');

class dbConn{

    //variable to hold connection object.
    protected static $db;

    //private construct - class cannot be instatiated externally.
    private function __construct() {

        try {
            // assign PDO object to db variable
            self::$db = new PDO( 'mysql:host=' . CONNECTION .';dbname=' . DATABASE, USERNAME, PASSWORD );
            self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }
        catch (PDOException $e) {
            //Output error - would normally log this to error file rather than output to user.
            echo "Connection Error: " . $e->getMessage();
        }

    }

    // get connection function. Static method - accessible without instantiation
    public static function getConnection() {

        //Guarantees single instance, if no connection object exists then create one.
        if (!self::$db) {
            //new connection object.
            new dbConn();
        }

        //return connection.
        return self::$db;
    }
}


class collection {
    static public function create() {
      $model = new static::$modelName;

      return $model;
    }

    static public function findAll() {

        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName;
        $statement = $db->prepare($sql);
        $statement->execute();
        $class = static::$modelName;
        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $recordsSet =  $statement->fetchAll();
        return $recordsSet;
    }

    static public function printAll($records){
        for($i=0;$i<count($records);$i++){
            echo "<tr>";
            foreach ($records[$i] as $key => $value) {
                echo "<td>".$value."</td>";
            } 
            echo "</tr>";
        }
    }

    static public function printOne($record){
        echo "<tr>";
        foreach ($record as $key => $value) {
            echo "<td>".$value."</td>";
        }
        echo "</tr>";
    }

    static public function findOne($id) {

        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE id =' . $id;
        $statement = $db->prepare($sql);
        $statement->execute();
        $class = static::$modelName;
        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $recordsSet =  $statement->fetchAll();
        return $recordsSet[0];
    }
}

class accounts extends collection {
    protected static $modelName = 'account';
}
class todos extends collection {
    protected static $modelName = 'todo';
}
class model {
    protected $tableName;
    public function save()
    {
        if ($this->id == '') {
            $sql = $this->insert();
        } else {
            $sql = $this->update();
        }
        $db = dbConn::getConnection();
        $statement = $db->prepare($sql);
        $status = $statement->execute();
        
        if($status == 1){
            $this->id = $db->lastInsertId();
        }
        // $tableName = get_called_class();

        // $array = get_object_vars($this);
        // $columnString = implode(',', $array);
        // $valueString = ":".implode(',:', $array);
        // echo "INSERT INTO $tableName (" . $columnString . ") VALUES (" . $valueString . ")</br>";

        // echo 'I just saved record: ' . $this->id;
    }

    private function insert() {
        $tableName = get_called_class();
        if(get_called_class() == 'account')
            $tableName = 'accounts';
        else
            $tableName = 'todos';
        $array = get_object_vars($this);
        $columnString ='';
        $valueString = '';
        foreach ($array as $key => $value) {
            if($key != 'id' && $key != 'tableName'){
                $columnString = $columnString . $key . ",";
                $valueString = $valueString . "'" .$value . "',";
            }
        }
        $columnString = rtrim($columnString, ',');
        $valueString = rtrim($valueString, ',');
        $sql = "INSERT INTO $tableName (" . $columnString . ") VALUES (" . $valueString . ");";
        return $sql;
    }
    private function update() {
        $sql = 'update';
        echo 'I just updated record' . $this->id;
        return $sql;
        
    }
    public function delete() {

        echo 'I just deleted record' . $this->id;
    }
}

class account extends model {
    public $id;
    public $email;
    public $fname;
    public $lname;
    public $phone;
    public $birthday;
    public $gender;
    public $password;

    public function __construct()
    {
        $this->tableName = 'accounts';
    }
}

class todo extends model {
    public $id;
    public $owneremail;
    public $ownerid;
    public $createddate;
    public $duedate;
    public $message;
    public $isdone;


    public function __construct()
    {
        $this->tableName = 'todos';
	
    }
}

$all_accounts = accounts::findAll();
$one_account = accounts::findOne(1);
$all_todos = todos::findAll();
$one_todo = todos::findOne(1);
?>
<table border="0">
    <tr><th>Select all Account records</th></tr>
    <tr COLSPAN=2 BGCOLOR="#55ff00">
        <?php
            foreach ($all_accounts[0] as $key => $value) {
                echo "<th>".$key."</th>";
            }
        ?>
    </tr>

<?php 
    accounts::printAll($all_accounts);
       
?>
<table border="0">
    <tr><th>Select One Account record </th></tr>
    <tr COLSPAN=2 BGCOLOR="#55ff00">
        <?php
            foreach ($one_account as $key => $value) {
                echo "<th>".$key."</th>";
            }
        ?>
    </tr>

<?php 
    accounts::printOne($one_account); 
?>
<table border="0">
    <tr><th>Select all Todos records</th></tr>
    <tr COLSPAN=2 BGCOLOR="#55ff00">
        <?php
            foreach ($all_todos[0] as $key => $value) {
                echo "<th>".$key."</th>";
            }
        ?>
    </tr>

<?php 
    todos::printAll($all_todos);
?>
<table border="0">
    <tr><th>Select one todo record</th></tr>
    <tr COLSPAN=2 BGCOLOR="#55ff00">
        <?php
            foreach ($one_todo as $key => $value) {
                echo "<th>".$key."</th>";
            }
        ?>
    </tr>

<?php 
    todos::printOne($one_todo);


$new_account = new account();
$new_account->id = '';
$new_account->email = 'test1@gmail.com';
$new_account->fname = 'Test';
$new_account->lname = 'User1';
$new_account->phone = '87612367890';
$new_account->birthday= '1993-03-06';
$new_account->gender = 'female';
$new_account->password = '1234';
$new_account->save();
$all_accounts = accounts::findAll();
?>
<table border="0">
    <tr><th>New Insterted record is at the bottom</th></tr>
    <tr COLSPAN=2 BGCOLOR="#55ff00">
        <?php
            foreach ($all_accounts[0] as $key => $value) {
                echo "<th>".$key."</th>";
            }
        ?>
    </tr>

<?php 
    accounts::printAll($all_accounts);
       




// $record = new todo();
// $record->id = '';
// $record->owneremail = $new_account->email;
// $record->ownerid = $new_account->id;
// $record->createddate = '2017-11-13';
// $record->duedate = '2017-11-16';
// $record->message = 'Updating todos';
// $record->isdone = 0;
// $record->save();

//print_r($record);
?>
