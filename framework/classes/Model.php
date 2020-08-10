<?php


namespace app\framework\classes;


use JasonGrimes\Paginator;

abstract class Model
{
    const ITEMS_PER_PAGE = 3;
    
    protected string $primaryKey = 'id';
    protected array $validationErrors = [];
    
    /**
     * Model constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $attributeNames = $this->attributes();
        $this->setAttributes($attributes, $attributeNames);
    }
    
    /**
     * Adding validation error to attribute
     * @param string $attribute
     * @param string $message
     */
    public function addValidationError(string $attribute, string $message)
    {
        $this->validationErrors[$attribute] = $message;
    }
    
    /**
     * Return array with validation errors
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
    
    /**
     * Setting attributes values to model
     * @param array $attributes
     * @param array $attributeNames
     * @return bool
     */
    public function setAttributes(array $attributes, array $attributeNames): bool
    {
        $loaded = false;
        foreach ($attributeNames as $attributeName) {
            $loaded = true;
            $this->$attributeName = $attributes[$attributeName] ?? null;
        }
        return $loaded;
    }
    
    /**
     * @param array $attributes
     * @return bool
     */
    public function load(array $attributes): bool
    {
        $attributeNames = $this->safeAttributes();
        return $this->setAttributes($attributes, $attributeNames);
    }
    
    /**
     * Return name of table in database
     * @return string
     */
    abstract public static function getTableName(): string;
    
    /**
     * Return array of attributes names in database columns
     * @return array
     */
    abstract public function attributes(): array;
    
    /**
     * Return array of attributes names in database columns which should be loaded from request
     * @return array
     */
    abstract public function safeAttributes(): array;
    
    /**
     * Returns query object
     * @return \Opis\Database\SQL\Query
     */
    public static function find()
    {
        return static::getDb()->from(static::getTableName());
    }
    
    /**
     * @return \Opis\Database\Database
     */
    public static function getDb()
    {
        return Database::getInstance()->getDb();
    }
    
    
    /**
     * Return array of results with keys items, pagination, fieldsSortUrls
     * @param Model $model
     * @param int $currentPage
     * @param string|null $sort
     * @param string|null $sortType
     * @return array
     */
    public static function getPaginatedResults(self $model, int $currentPage, ?string $sort, ?string $sortType): array
    {
        $totalItems = static::find()->count();
        $itemsPerPage = static::ITEMS_PER_PAGE;
        $currentPage = intval($_GET['page'] ?? 1);
        $offset = ($currentPage - 1) * $itemsPerPage;
        
        $query =  static::find();
        
        $sort = $_GET['sort'] ?? null;
        $sortType = $_GET['sortType'] ?? null;
        $attributes = $model->attributes();
        $params = [];
        if ($sort && in_array($sort, $attributes)) {
            $params['sort'] = $sort;
            $query->orderBy($sort, $sortType == 'desc' ? 'DESC' : 'ASC');
        }
        
        $appUrl = Route::getAppUrl();
        $fieldsSortUrls = [];
        foreach ($attributes as $attribute) {
            $fieldsSortUrls[$attribute] = $appUrl.'?sort='.$attribute;
            if ($sort == $attribute && ($sortType == 'asc' || !$sortType)) {
                $fieldsSortUrls[$attribute].= '&sortType=desc';
            }
        }
        
        if ($sortType && in_array($sortType, ['asc', 'desc'])) {
            $params['sortType'] = $sortType;
        }
        
        $params['page'] = '';
        
        $url = $appUrl.'?'.http_build_query($params).'(:num)';
        $pagination = new Paginator($totalItems, $itemsPerPage, $currentPage, $url);
        
        
        $items = $query
            ->offset($offset)
            ->limit($itemsPerPage)
            ->select()
            ->all();
        
        return compact('items', 'pagination', 'fieldsSortUrls');
    }
    
    /**
     * Check if model attributes have valid values
     * @return bool
     */
    public abstract function validate(): bool;
    
    /**
     * @param $attribute
     * @return bool
     */
    public function validateRequired($attribute): bool
    {
        if (empty($this->$attribute)) {
            $this->addValidationError($attribute, $attribute.' is required');
            return false;
        }
        return true;
    }
    
    /**
     * @param string $attribute
     * @param int $length
     * @return bool
     */
    public function validateMaxLength(string $attribute, int $length): bool
    {
        if (mb_strlen($this->$attribute) > $length) {
            $this->addValidationError($attribute, 'Max length for '.$attribute.' is '.$length.' characters');
            return false;
        }
        return true;
    }
    
    
    /**
     * @param string $attribute
     * @return bool
     */
    public function validateEmail(string $attribute): bool
    {
        return filter_var($this->$attribute, FILTER_VALIDATE_EMAIL);
    }
}