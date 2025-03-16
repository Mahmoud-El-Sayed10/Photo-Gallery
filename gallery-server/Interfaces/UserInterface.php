<?php

interface UserInterface {
    
    public function save();
    
    public function findById($id);
    
    public function findByEmail($email);
  
    public function all();
    
    public function delete($id);

    public function authenticate($email, $password);

    public function getId();
    
    public function getFullName();
    
    public function getEmail();
    
    public function getPassword();
}
?>