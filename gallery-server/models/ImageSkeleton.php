<?php

class ImageSkeleton {
    protected static $id;
    protected static $userId;
    protected static $title;
    protected static $description;
    protected static $filePath;
    
    public static function create($userId, $title, $description, $filePath, $id = null) {
        self::$id = $id;
        self::$userId = $userId;
        self::$title = $title;
        self::$description = $description;
        self::$filePath = $filePath;
    }
    
    public static function getId() {
        return self::$id;
    }
    
    public static function getUserId() {
        return self::$userId;
    }
    
    public static function getTitle() {
        return self::$title;
    }

    public static function getDescription() {
        return self::$description;
    }
    
    public static function getFilePath() {
        return self::$filePath;
    }

    public static function clear() {
        self::$id = null;
        self::$userId = null;
        self::$title = null;
        self::$description = null;
        self::$filePath = null;
    }
}
?>