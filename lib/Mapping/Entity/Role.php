<?php

/*
 *  $Id$
 */

namespace Entity;

use \Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of Role
 *
 * @author krzych
 */

/**
 * @Entity
 * @Table(name="role")
 */
class Role {

    public function __construct() {
        $this->acls = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->securedObjects = new ArrayCollection();
    }


    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    private $id;
    /**
     * @Column(type="string", length="255")
     */
    private $name;
    /** @ManyToMany(targetEntity="User") */
    private $users;
    /** @ManyToMany(targetEntity="SecuredObject") */
    private $securedObjects;
    /** @ManyToMany(targetEntity="Acl") */
    private $acls;


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

    public function getUsers() {
        return $this->users;
    }

    public function setUsers($users) {
        $this->users = $users;
    }

    public function getSecuredObjects() {
        return $this->securedObjects;
    }

    public function setSecuredObjects($securedObjects) {
        $this->securedObjects = $securedObjects;
    }

    public function addSecuredObject($securedObject) {
        $this->securedObjects->add($securedObject);
    }

    public function getAcls() {
        return $this->acls;
    }

    public function setAcls($acls) {
        $this->acls = $acls;
    }

    public function addAcl($acl) {
        $this->acls->add($acl);
    }

}

?>
