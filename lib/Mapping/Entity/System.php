<?php
namespace Entity;
/**
 * Description of System
 *
 * @author krzych
 */

/**
 * @Entity
 * @Table(name="system")
 */

class System {

    /**
     * @Id
     * @Column(type="integer")
     */
    private $id;

    /**
     *
     * @Column(type="boolean")
     */
    private $isInstalled;
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function isInstalled() {
        return $this->isInstalled;
    }

    public function setInstalled($isInstalled) {
        $this->isInstalled = $isInstalled;
    }

    
}

?>
