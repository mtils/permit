<?php namespace Permit\Support\Laravel\Permission;

use Illuminate\Translation\Translator;
use Permit\Permission\RepositoryInterface;
use Permit\Permission\CategoryInterface;
use Permit\Permission\Holder\HolderInterface;
use OutOfBoundsException;
use Permit\Permission\Permission;


class TranslatorRepository implements RepositoryInterface
{

    protected $translator;

    protected $codeLookup = [];

    protected $permissionsByCode = [];

    protected $translationRoot = 'permissions';

    public function __construct(Translator $translator){
        $this->translator = $translator;
    }

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
        throw new OutOfBoundsException("Code $code does not exist.");
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

            $titleKey = $this->getTranslationKey($code);

            if($this->translator->has($titleKey)){
                $permission->setTitle($this->translator->choice($titleKey,1));
            }

            $descriptionKey = $this->getTranslationKey($code, 'description');
            if($this->translator->has($descriptionKey)){
                $permission->setDescription($this->translator->choice($descriptionKey,1));
            }

            $this->permissionsByCode[$code] = $permission;
        }

        return $this->permissionsByCode[$code];

    }

    /**
     * @brief Return the category with id $id
     *
     * @param string $id
     **/
    public function getCategory($id){
    
    }

    /**
     * @brief Returns all permissions
     *
     * @return \Traversable
     **/
    public function all(){
        $all = [];
        foreach(array_keys($this->codeLookup) as $code){
            $all[] = $this->get($code);
        }
        return $all;
    }

    /**
     * @brief Returns a filtered Traversable of permissions
     *
     * @param Permit\Permission\Holder\HolderInterface $holder (optional)
     * @param Permit\Permission\CategoryInterface $category
     * @return Traversable
     **/
    public function filtered(HolderInterface $holder=NULL, CategoryInterface $category=NULL){
    
    }

    /**
     * @brief Return all permissions of $holder
     *
     * @param Permit\Permission\Holder\HolderInterface $holder (optional)
     * @return Traversable of categories
     **/
    public function categories(HolderInterface $holder=NULL){
    
    }

    public function getTranslationRoot()
    {
        return $this->translationRoot;
    }

    public function setTranslationRoot($root)
    {
        $this->translationRoot = $root;
        return $this;
    }

    protected function getTranslationKey($code, $append='title'){

        $key = $this->translationRoot.'.'.str_replace('.','-',$code);

        if($append != 'title'){
            $key .= "-$append";
        }

        return $key;
    }

}
