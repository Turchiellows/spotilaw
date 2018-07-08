<?php
  class Connect {
    protected $database;
    protected $username;
    protected $password;
    protected $connect;

    function __construct() {
      $this->database = "spotilaw";
      $this->username = "root";
      $this->password = "spotilaw";
    }

    function connecting() {
      if(!$this->connect) {
        $this->connect = mysqli_connect('127.0.0.1', $this->username, $this->password);
        if($this->connect) {
          $this->selectDatabase();
        }else{
  //         echo "<font color=red>(11) Error Connecting {$this->database}</font><br/>";
        }
      }

      return $this->connect;
    }

    function selectDatabase() {
      $this->connecting();
      if(mysqli_select_db($this->connect, $this->database)){
        //mysqli_query($_connect,"SET NAMES 'utf8'");
        //mysqli_query($_connect,'SET character_set_connection=utf8');
        //mysqli_query($_connect,'SET character_set_client=utf8');
        //mysqli_query($_connect,'SET character_set_results=utf8');
        //mysqli_query($_connect,"SET time_zone='Brazil/West'");
      }else{
  //           echo "<font color=red>(11) Error Selecting {$this->database}</font><br/>";
      }
    }

    function query($query) {
      $result = null;
      if($query) {
        $this->connecting();
        if($this->connect) {
          $result = mysqli_query($this->connect, $query);
        }else{
  //         echo "<font color=red>(6) Error Connecting {$this->database}</font><br/>";
        }
      }	
      return $result;
    }

    function close() {
      if($this->connect) {
        @mysqli_close($this->connect);
        $this->connect = NULL;
      }
    }

    function __destruct() {
      $this->close();
    }
  }
?>