<?php

namespace PhpAjaxFormDemo\Data;

/**
 * Record demo mockup
 * 
 * @package ajax-form-demo
 * 
 * @author Juan Carrión
 * 
 * @version 0.0.1
 */

class Record
{

    private $uniqueId;
    private $name;
    private $surname;

    private static $data;

    public function __construct(int $uniqueId, string $name, string $surname)
    {
        $this->uniqueId = $uniqueId;
        $this->name = $name;
        $this->surname = $surname;
    }

    public static function initDemoData() : void
    {
        self::$data = array(
            0 => new self(23, 'Pedro', 'Martínez Fernández'),
            1 => new self(98, 'Sandra', 'Alarcón Molina')
        );
    }

    public static function getAll() : array
    {
        return self::$data;
    }

    public static function existsById(int $uniqueId) : bool
    {
        return $uniqueId === 23 || $uniqueId === 98;
    }

    public static function getById(int $uniqueId) : Record
    {
        switch ($uniqueId) {
            case 23:
                return self::$data[0];
            break;
            case 98:
                return self::$data[1];
            break;
        }
    }

    public function getUniqueId() : int
    {
        return $this->uniqueId;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getSurname() : string
    {
        return $this->surname;
    }
}

?>