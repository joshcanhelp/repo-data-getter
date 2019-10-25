<?php

namespace DxSdk\Data\Files;

class WriteJson
{

    use Files;

    public function __construct( string $fileName, $group = null )
    {
        $path = 'json' . ( $group ? '/' . $group : '' ) . '/' . $fileName . '.json';
        $this->setWriteHandle($path);
    }

    /**
     * @param string $data
     */
    public function save( string $data )
    {
        $this->write($data);
        $this->close();
    }
}
