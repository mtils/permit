<?php namespace Permit\Permission;

use Permit\Holder\HolderInterface;

class ManualRepository implements RepositoryInterface{

    protected $codeLookup = [];
    protected $permissionsByCode = [];
    protected $categoryById=[];

    /**
     * @brief Add a permission code
     *
     * @return void
     **/
    public function addCode($code){
        $this->codeLookup[$code] = true;
    }

    /**
     * @brief Checks if a code exists
     *
     * @return bool
     **/
    public function codeExists($code){
        return isset($this->codeLookup[$code]);
    }

    /**
     * @brief Checks the code and returns it. If code does not exist it throws
     *        a OutOfBoundsException.
     *        This is handy if you build forms with permissions and don't know
     *        exactly its name. So this prevents typos.
     *
     * @param string $code
     * @return string The Code in return
     * @throws \OutOfBoundsException If code does not exist
     **/
    public function checkAndReturn($code){

        if($this->codeExists($code)){
            return $code;
        }
        throw new OutOfBoundsException("Code $code does not exist");
    }

    /**
     * @brief Returns a Permission with code $code
     *
     * @param string $code The permission code
     * @return Permit\Permission
     **/
    public function get($code){

        if(!isset($this->permissionsByCode[$code])){
            $this->checkAndReturn($code);
            $permission = new Permission();
            $permission->setCode($code);
            $this->permissionsByCode[$code] = $permission;
        }
        return $this->permissionsByCode[$code];
    }

    public function add(PermissionInterface $permission){

        $this->permissionsByCode[$permission->getCode()] = $permission;
        $this->addCode($permission->getCode());

        return $this;

    }

    /**
     * @brief Return the category with id $id
     *
     * @param string $id
     **/
    public function getCategory($id){

        if(!isset($this->categoryById[$id])){
            $category = new Category();
            $category->setId($id);
            $this->categoryById[$id] = $category;
        }

        return $this->categoryById[$id];
    }

    /**
     * @brief Returns all permissions
     *
     * @return \Traversable
     **/
    public function all(){
        return array_values($this->permissionsByCode);
    }

    /**
     * @brief Returns a filtered Traversable of permissions
     *
     * @param Permit\Holder\HolderInterface $holder (optional)
     * @param Permit\Permission\CategoryInterface $category
     * @return Traversable
     **/
    public function filtered(HolderInterface $holder=NULL, CategoryInterface $category=NULL){
        return $this->all();
    }

    /**
     * @brief Return all permissions of $holder
     *
     * @param Permit\Holder\HolderInterface $holder (optional)
     * @return Traversable of categories
     **/
    public function categories(HolderInterface $holder=NULL){
        return array_values($this->categories);
    }

}