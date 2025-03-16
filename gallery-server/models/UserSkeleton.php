<?php

class UserSkeleton {
    protected static $id;
    protected static $fullname;
    protected static $email;
    protected static $password;

public static function create($fullname, $email, $password, $id = null){
    self::$id = $id;
    self::$fullname = $fullname;
    self::$email = $email;
    self::$password = $password;
    }

public static function getId(){
    return self::$id;
    }

public static function getFullName(){
    return self::$fullname;
    }

public static function getEmail(){
    return self::$email;
    }

public static function clear(){
    self::$id = null;
    self::$fullname = null;
    self::$email = null;
    self::$password = null;
    }
}

?>