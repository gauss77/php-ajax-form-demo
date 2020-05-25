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
     * @var array $hobbies
     */
    private $uniqueId;
    private $name;
    private $surname;
    private $nationality;
    private $hobbies;

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
     * @param array $hobbies
     */
    public function __construct(int $uniqueId, string $name, string $surname, SingleForeignRecord $nationality, array $hobbies)
    {
        $this->uniqueId = $uniqueId;
        $this->name = $name;
        $this->surname = $surname;
        $this->nationality = $nationality;
        $this->hobbies = $hobbies;
    }

    /**
     * Inicializes demo data.
     */
    public static function initDemoData() : void
    {
        $nationality23 = SingleForeignRecord::getById(2);
        $nationality98 = SingleForeignRecord::getById(5);

        $hobbies23 = array(
            MultiForeignRecord::getById(1),
            MultiForeignRecord::getById(4),
            MultiForeignRecord::getById(5),
            MultiForeignRecord::getById(6),
            MultiForeignRecord::getById(7),
            MultiForeignRecord::getById(8),
            MultiForeignRecord::getById(9),
        );
        $hobbies98 = array(
            MultiForeignRecord::getById(2),
            MultiForeignRecord::getById(4),
            MultiForeignRecord::getById(5),
            MultiForeignRecord::getById(8),
            MultiForeignRecord::getById(10)
        );

        self::$data = array(
            23 => new self(23, 'Pedro', 'Martínez Fernández', $nationality23, $hobbies23),
            98 => new self(98, 'Sandra', 'Alarcón Molina', $nationality98, $hobbies98)
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
        return array_key_exists($uniqueId, self::$data);
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
        return self::$data[$uniqueId];
    }

    /**
     * JSON serialization.
     */
    public function jsonSerialize()
    {
        $hobbiesUniqueIds = array();

        foreach ($this->getHobbies() as $hobbie) {
            $hobbiesUniqueIds[] = $hobbie->getUniqueId();
        }

        return [
            'uniqueId' => $this->getUniqueId(),
            'selectName' => $this->getFullName(),
            'checkbox' => $this->getUniqueId(),
            'name' => $this->getName(),
            'surname' => $this->getSurname(),
            'nationality' => $this->getNationality()->getUniqueId(),
            'hobbies' => $hobbiesUniqueIds
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

    public function getHobbies() : array
    {
        return $this->hobbies;
    }
}

?>