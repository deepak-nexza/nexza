<?php 
namespace App\Repositories\Event;


interface EventInterface {


    public function getAll();


    public function find($id);


    public function delete($id);

}