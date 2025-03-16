<?php

interface ImageInterface {
    
    public function save();
    
    public function findById($id);
    
    public function all($filters = []);
    
    public function delete($id);
    
    public function getImageTags($imageId);
    
    public function addTag($imageId, $tagId);
   
    public function removeTag($imageId, $tagId);
    
    public function createFromBase64($userId, $title, $description, $base64Data);
    
    public function getId();
  
    public function getUserId();
 
    public function getTitle();

    public function getDescription();

    public function getFilePath();
}
?>