<?php
/* 
 *  $Id$
 */
namespace Entity;
use \Doctrine\Common\Collections\ArrayCollection;
/**
 * Description of User
 *
 * @author krzych
 */
/**
 * @Entity
 */

class User {
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    private $id;

    /**
     * @Column(type="string", length=255)
     */
    private $login;

    /**
     * @Column(type="string", length=255)
     */
    private $firstName;
    /**
     * @Column(type="string", length=255)
     */
    private $lastName;
    /**
     * @Column(type="string", length=255)
     */
    private $password;
    /**
     * @Column(type="boolean")
     */
    private $active;

    /** @ManyToMany(targetEntity="Role") */
    private $roles;
    /** @ManyToMany(targetEntity="Acl") */
    private $acls;

    public function __construct() {
        $this->acls = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getLogin() {
        return $this->login;
    }

    public function setLogin($login) {
        $this->login = $login;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function setLastName($lastName) {
        $this->lastName = $lastName;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function isActive() {
        return $this->active;
    }

    public function setActive($active) {
        $this->active = $active;
    }

    public function getRoles() {
        return $this->roles;
    }

    public function setRoles($roles) {
        $this->roles = $roles;
    }

    public function addRole($role) {
        $this->roles->add($role);
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
