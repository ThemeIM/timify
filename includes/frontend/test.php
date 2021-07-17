<?php
class Test1 {
    protected $a1;
    public function __construct()
    {
        $this->a1="demo text";
    }
}
class Test2 extends Test1 {
    public function __construct()
    {
        parent::__construct();
        //var_dump($this->a1);
    }
}
new Test2();