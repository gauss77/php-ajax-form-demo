<?php

namespace PhpAjaxFormDemo\Data;

use JsonSerializable;

/**
 * Multi foreign record (hobby) demo mockup. Used for demonstating 
 * single-value foreign attribute and HTML multiple 'select' tag population and 
 * selection.
 * 
 * @package ajax-form-demo
 * 
 * @author Juan Carrión
 * 
 * @version 0.0.1
 */

class MultiForeignRecord
    implements JsonSerializable
{

    /**
     * MultiForeignRecord attributes.
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
            1 => new self(1, 'Sports'),
            2 => new self(2, 'Music'),
            3 => new self(3, 'Literature'),
            4 => new self(4, 'Nature'),
            5 => new self(5, 'Traveling'),
            6 => new self(6, 'Socializing'),
            7 => new self(7, 'Painting'),
            8 => new self(8, 'Dancing'),
            9 => new self(9, 'Reading'),
            10 => new self(10, 'Writing')
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
     * Checks if a MultiForeignRecord exists by a given id.
     * 
     * @param int $uniqueId
     * 
     * @return bool
     */
    public static function existsById(int $uniqueId) : bool
    {
        return array_key_exists($uniqueId, self::$data);
    }

    /**
     * Retrieves a MultiForeignRecord by a given id.
     * 
     * @requires Id exists.
     * 
     * @param int $uniqueId
     * 
     * @return \PhpAjaxFormDemo\Data\MultiForeignRecord
     */
    public static function getById(int $uniqueId) : self
    {
        return self::$data[$uniqueId];
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
     * MultiForeignRecord attribute getters.
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