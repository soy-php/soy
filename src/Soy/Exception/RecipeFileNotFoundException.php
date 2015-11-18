<?php

namespace Soy\Exception;

class RecipeFileNotFoundException extends \Exception
{
    /**
     * @var string
     */
    private $recipeFile;

    /**
     * @param string $message
     * @param string $recipeFile
     */
    public function __construct($message, $recipeFile)
    {
        parent::__construct($message);
        $this->recipeFile = $recipeFile;
    }

    /**
     * @return string
     */
    public function getRecipeFile()
    {
        return $this->recipeFile;
    }
}
