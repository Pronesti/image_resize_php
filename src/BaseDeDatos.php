<?php

namespace Source;

class BaseDeDatos
{
    private $filePath;
    public function __construct($path)
    {
        $this->filePath = $path;
        if(!file_exists($path)){
            file_put_contents($this->filePath, null);
        }
    }

    public function read()
    {
        return explode("\\",file_get_contents($this->filePath));
    }
    public function save(array $data)
    {
        return file_put_contents($this->filePath, trim(implode("\\",$data)));
    }
}
