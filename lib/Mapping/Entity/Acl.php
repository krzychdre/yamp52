<?php

/**
 * $Id$
 */
namespace Entity;
/**
 * Description of Acl
 *
 * @author krzych
 */

/**
 * @Entity
 * @Table(name="acl")
 * @HasLifecycleCallbacks
 */
class Acl {

    /**
     *
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    private $id;

    /**
     * @Column(type="string")
     */
    private $name;

    /**
     * @Column(type="boolean")
     */
    private $allowed;

    /**
     * @PrePersist
     */
    public function setDefaultValues() {
        if($this->allowed == null) {
            $this->allowed = false;
        }
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getAllowed() {
        return $this->allowed;
    }

    public function setAllowed($allowed) {
        $this->allowed = $allowed;
    }

}

?>
