<?php

namespace PhpAjaxFormDemo\Data;

use JsonSerializable;
use PhpAjaxFormDemo\Data\SingleForeignRecord;

/**
 * Record (person) demo mockup.
 * 
 * @package ajax-form-demo
 * 
 * @author Juan Carrión
 * 
 * @version 0.0.1
 */

class Record
    implements JsonSerializable
{

    /**
     * Record attributes.
     * 
     * @var int $uniqueId
     * @var string $name
     * @var string $surname
     * @var \PhpAjaxFormDemo\Data\SingleForeignRecord $nationality
     */
    private $uniqueId;
    private $name;
    private $surname;
    private $nationality;

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
     * @param string $surname
     */
    public function __construct(int $uniqueId, string $name, string $surname, SingleForeignRecord $nationality)
    {
        $this->uniqueId = $uniqueId;
        $this->name = $name;
        $this->surname = $surname;
        $this->nationality = $nationality;
    }

    /**
     * Inicializes demo data.
     */
    public static function initDemoData() : void
    {
        $nationalityFrance = SingleForeignRecord::getById(2);
        $nationalityItaly = SingleForeignRecord::getById(5);

        self::$data = array(
            0 => new self(23, 'Pedro', 'Martínez Fernández', $nationalityFrance),
            1 => new self(98, 'Sandra', 'Alarcón Molina', $nationalityItaly)
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
     * Checks if a Record exists by a given id.
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
     * Retrieves a Record by a given id.
     * 
     * @requires Id exists.
     * 
     * @param int $uniqueId
     * 
     * @return \PhpAjaxFormDemo\Data\Record
     */
    public static function getById(int $uniqueId) : self
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

    /**
     * JSON serialization.
     */
    public function jsonSerialize()
    {
        return [
            'uniqueId' => $this->getUniqueId(),
            'selectName' => $this->getFullName(),
            'name' => $this->getName(),
            'surname' => $this->getSurname(),
            'nationality' => $this->getNationality()->getUniqueId()
        ];
    }

    /**
     * 
     * Record attribute getters.
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

    public function getSurname() : string
    {
        return $this->surname;
    }

    public function getFullName() : string
    {
        return $this->name . ' ' . $this->surname;
    }

    public function getNationality() : SingleForeignRecord
    {
        return $this->nationality;
    }
}

?>