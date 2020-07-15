<?php
namespace App\Model;

class App extends Model
{
    protected $table = 'app';
    protected $dates=array('created_at','updated_at');
    public function __construct()
    {
        parent::__construct();
    }

}