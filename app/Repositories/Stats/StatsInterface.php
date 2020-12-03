<?php 
namespace App\Repositories\Stats;


interface StatsInterface {


    public function getAll();


    public function find($id);


    public function delete($id);

}