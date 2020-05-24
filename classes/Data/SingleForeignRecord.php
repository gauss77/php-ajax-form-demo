<?php

namespace PhpAjaxFormDemo\Data;

use JsonSerializable;

/**
 * Single foreign record (nationality) demo mockup. Used for demonstating 
 * single-value foreign attribute and HTML single 'select' tag population and 
 * selection.
 * 
 * @package ajax-form-demo
 * 
 * @author Juan Carrión
 * 
 * @version 0.0.1
 */

class SingleForeignRecord
    implements JsonSerializable
{

    /**
     * SingleForeignRecord attributes.
     * 
     * @var int $uniqueId
     * @var string $name
     */
    private $uniqueId;
    private $name;

    /**
     * Demo data.
     * 
     * @var array $data
     */
    private static $data;

    /**
     * Default constructor.
     * 
     * @param int $uniqueId
     * @param string $name
     */
    public function __construct(int $uniqueId, string $name)
    {
        $this->uniqueId = $uniqueId;
        $this->name = $name;
    }

    /**
     * Inicializes demo data.
     */
    public static function initDemoData() : void
    {
        self::$data = array(
            0 => new self(1, 'Spain'),
            1 => new self(2, 'France'),
            2 => new self(3, 'United Kingdom'),
            3 => new self(4, 'Germany'),
            4 => new self(5, 'Italy'),
            5 => new self(6, 'Belgium')
        );
    }

    /**
     * Data repository get all.
     * 
     * @return array
     */
    public static function getAll() : array
    {
        return self::$data;
    }

    /**
     * Checks if a SingleForeignRecord exists by a given id.
     * 
     * @param int $uniqueId
     * 
     * @return bool
     */
    public static function existsById(int $uniqueId) : bool
    {
        return $uniqueId === 23 || $uniqueId === 98;
    }

    /**
     * Retrieves a SingleForeignRecord by a given id.
     * 
     * @requires Id exists.
     * 
     * @param int $uniqueId
     * 
     * @return \PhpAjaxFormDemo\Data\SingleForeignRecord
     */
    public static function getById(int $uniqueId) : self
    {
        switch ($uniqueId) {
            case 1:
                return self::$data[0];
            break;
            case 2:
                return self::$data[1];
            break;
            case 3:
                return self::$data[2];
            break;
            case 4:
                return self::$data[3];
            break;
            case 5:
                return self::$data[4];
            break;
            case 6:
                return self::$data[5];
            break;
        }
    }

    /**
     * JSON serialization.
     */
    public function jsonSerialize()
    {
        return [
            'uniqueId' => $this->getUniqueId(),
            'selectName' => $this->getName(),
            'name' => $this->getName()
        ];
    }

    /**
     * 
     * SingleForeignRecord attribute getters.
     * 
     */

    public function getUniqueId() : int
    {
        return $this->uniqueId;
    }

    public function getName() : string
    {
        return $this->name;
    }
}

?>