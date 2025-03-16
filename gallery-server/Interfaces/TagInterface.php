<?php

interface TagInterface {
 
    public function save();
    
    public function findById($id);
    
    public function findByName($name);
    
    public function all($withCount = false);
    
    public function delete($id);
    
    public function findOrCreate($name);
    
    public function getTagsForImage($imageId);
    
    public function getId();

    public function getName();
}
?>