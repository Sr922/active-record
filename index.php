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

    static public function printHeaders($record){
        foreach ($record as $key => $value) {
            echo "<th>".$key."</th>";
        }
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
        // echo $sql;
        $db = dbConn::getConnection();
        $statement = $db->prepare($sql);
        $status = $statement->execute();
        
        if($status == 1&& $this->id == ''){
            $this->id = $db->lastInsertId();
        }
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
        $sql = "INSERT INTO $tableName (" . $columnString . ") VALUES (" . $valueString . ")";
        return $sql;
    }
    private function update() {
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
                $updateString = $updateString. $key . "= '". $value . "',";
            }
        }
        $updateString = rtrim($updateString, ',');
        $sql = "UPDATE $tableName  SET  ".$updateString." WHERE id=". $this->id;
        return $sql;
        
    }
    public function delete() {
        $tableName = get_called_class();
        if(get_called_class() == 'account')
            $tableName = 'accounts';
        else
            $tableName = 'todos';
        $sql = "DELETE FROM ".$tableName." WHERE id=".$this->id."";
        $db = dbConn::getConnection();
        $statement = $db->prepare($sql);
        $status = $statement->execute();
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
            accounts::printHeaders($all_accounts[0]);
        ?>
    </tr>

<?php 
    accounts::printAll($all_accounts);
       
?>
<table border="0">
    <tr><th>Select One Account record </th></tr>
    <tr COLSPAN=2 BGCOLOR="#55ff00">
        <?php
            accounts::printHeaders($one_account);
        ?>
    </tr>

<?php 
    accounts::printOne($one_account); 
?>
<table border="0">
    <tr><th>Select all Todos records</th></tr>
    <tr COLSPAN=2 BGCOLOR="#55ff00">
        <?php
            todos::printHeaders($all_todos[0]);
        ?>
    </tr>

<?php 
    todos::printAll($all_todos);
?>
<table border="0">
    <tr><th>Select one todo record</th></tr>
    <tr COLSPAN=2 BGCOLOR="#55ff00">
        <?php
            todos::printHeaders($one_todo);
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
            accounts::printHeaders($all_accounts);
        ?>
    </tr>

<?php 
    accounts::printAll($all_accounts);

$record = new todo();
$record->id = '';
$record->owneremail = $new_account->email;
$record->ownerid = $new_account->id;
$record->createddate = '2017-11-13';
$record->duedate = '2017-11-16';
$record->message = 'Updating todos';
$record->isdone = 0;
$record->save();
$all_todos = todos::findAll();
?>
<table border="0">
    <tr><th>Newly Insterted record is at the bottom</th></tr>
    <tr COLSPAN=2 BGCOLOR="#55ff00">
        <?php
            todos::printHeaders($all_todos);
        ?>
    </tr>

<?php 
    todos::printAll($all_todos);

$new_account = accounts::findOne(9);
?>
<table border="0">
    <tr><th>Before Accounts Update </th></tr>
    <tr COLSPAN=2 BGCOLOR="#55ff00">
        <?php
            accounts::printHeaders($new_account);
        ?>
    </tr>

<?php 
    accounts::printOne($new_account);

$new_account->email='newUpdatedEmail@gmail.com';
$new_account->save();

?>
<table border="0">
    <tr><th>After Accounts Update </th></tr>
    <tr COLSPAN=2 BGCOLOR="#55ff00">
        <?php
            accounts::printHeaders($new_account);
        ?>
    </tr>

<?php 
    accounts::printOne($new_account);

$record=todos::findOne(4);
?>
<table border="0">
    <tr><th>Before Todos Update </th></tr>
    <tr COLSPAN=2 BGCOLOR="#55ff00">
        <?php
            todos::printHeaders($record);
        ?>
    </tr>

<?php 
    todos::printOne($record);

$record->isdone=1;
$record->save();

?>
<table border="0">
    <tr><th>After Todos Update </th></tr>
    <tr COLSPAN=2 BGCOLOR="#55ff00">
        <?php
            todos::printHeaders($record);
        ?>
    </tr>

<?php 
    todos::printOne($record);

$new_account = accounts::findOne(31);
$all_accounts = accounts::findAll();
?>
<table border="0">
    <tr><th>Before deleting in Accounts</th></tr>
    <tr COLSPAN=2 BGCOLOR="#55ff00">
        <?php
            accounts::printHeaders($all_accounts);
        ?>
    </tr>

<?php 
    accounts::printAll($all_accounts);

$new_account->delete();
$all_accounts = accounts::findAll();
?>
<table border="0">
    <tr><th>After deleting in Accounts</th></tr>
    <tr COLSPAN=2 BGCOLOR="#55ff00">
        <?php
            accounts::printHeaders($all_accounts);
        ?>
    </tr>

<?php 
    accounts::printAll($all_accounts);

$record = todos::findOne(10);
$all_todos = todos::findAll();
?>
<table border="0">
    <tr><th>Before deleting in Todos</th></tr>
    <tr COLSPAN=2 BGCOLOR="#55ff00">
        <?php
            todos::printHeaders($all_todos);
        ?>
    </tr>

<?php 
    todos::printAll($all_todos);

$record->delete();

$all_todos = todos::findAll();
?>
<table border="0">
    <tr><th>After deleting in Todos</th></tr>
    <tr COLSPAN=2 BGCOLOR="#55ff00">
        <?php
            todos::printHeaders($all_todos);
        ?>
    </tr>

<?php 
    todos::printAll($all_todos);

//print_r($record);
?>
