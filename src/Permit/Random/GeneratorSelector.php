<?php namespace Permit\Random;

use UnderflowException;

/**
 * This Generator chooses the best generator of all assigned
 **/
class GeneratorSelector implements GeneratorInterface
{

    /**
     * @var array
     **/
    protected $generators = [];

    /**
     * @var \Permit\Random\GeneratorInterface
     **/
    protected $strongestGenerator;

    /**
     * Add a new generator
     *
     * @param \Permit\Random\GeneratorInterface $generator
     * @return self
     **/
    public function add(GeneratorInterface $generator)
    {
        $this->generators[] = $generator;
        return $this;
    }

    /**
     * Remove a  generator
     *
     * @param \Permit\Random\GeneratorInterface $generator
     * @return self
     **/
    public function remove(GeneratorInterface $generator)
    {
        $this->generators = 
            array_filter($this->generators, function($added) use ($generator) {
                return !($generator == $added);
            });
        return $this;
    }

    /**
     * Return all added generators
     *
     * @return array
     **/
    public function getGenerators()
    {
        return array_values($this->generators);
    }

    /**
     * Returns the strongest generator supported by the system
     *
     * @return \Permit\Random\GeneratorInterface
     **/
    public function getStrongestGenerator()
    {
        if(!$this->strongestGenerator){
            $this->strongestGenerator = $this->findStrongestGenerator();
        }
        return $this->strongestGenerator;
    }

    /**
     * {@inheritdoc}
     * @return int
     **/
    public function getStrength()
    {
        return $this->getStrongestGenerator()->getStrength();
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     **/
    public function isSupported()
    {
        return ($this->getStrongestGenerator() instanceof GeneratorInterface);
    }

    /**
     * {@inheritdoc}
     *
     * @param int $length
     * @param bool $asciiOnly
     * @return string
     **/
    public function generate($length=42, $asciiOnly=True)
    {
        return $this->getStrongestGenerator()->generate($length, $asciiOnly);
    }

    /**
     * Returns a supported generator with the most strength
     *
     * @return \Permit\Random\GeneratorInterface
     **/
    protected function findStrongestGenerator()
    {

        if(!count($this->generators)){
            throw new UnderflowException("Assign Generators to Registry");
        }

        $strongest = null;
        $maxStrength = 0;

        foreach ($this->generators as $generator) {

            if (!$generator->isSupported()) {
                continue;
            }

            $strength = $generator->getStrength();
            if ($strength > $maxStrength){
                $maxStrength = $strength;
                $strongest = $generator;
            }

        }

        if (!$strongest) {
            throw new UnderflowException("No supported Generator found");
        }

        return $strongest;

    }

}