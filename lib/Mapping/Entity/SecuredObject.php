<?php
/* 
 *  $Id$
 */
namespace Entity;
/**
 * Description of SecuredObject
 *
 * @author krzych
 */
/**
 * @Entity
 * @Table(name="secured_object")
 * @HasLifecycleCallbacks
 */
class SecuredObject {

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    private $id;

    /**
     * @Column(type="string",length="255")
     */
    private $class;

    /**
     * @Column(type="string",length="255")
     */
    private $method;

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

    public function getClass() {
        return $this->class;
    }

    public function setClass($class) {
        $this->class = $class;
    }

    public function getMethod() {
        return $this->method;
    }

    public function setMethod($method) {
        $this->method = $method;
    }

    public function getAllowed() {
        return $this->allowed;
    }

    public function setAllowed($allowed) {
        $this->allowed = $allowed;
    }

}
?>
