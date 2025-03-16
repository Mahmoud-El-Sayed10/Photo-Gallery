<?php

class TagSkeleton {
    protected static $id;
    protected static $name;
    
    public static function create($name, $id = null) {
        self::$id = $id;
        self::$name = $name;
    }
    
    public static function getId() {
        return self::$id;
    }
    
    public static function getName() {
        return self::$name;
    }
    
    public static function clear() {
        self::$id = null;
        self::$name = null;
    }
}
?>