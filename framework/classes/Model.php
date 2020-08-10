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
     * Return array of attributes names in database columns
     * @return array
     */
    abstract public function attributes(): array;
    
    /**
     * Setting attributes values to model
     * @param array $attributes
     * @param array $attributeNames
     * @return bool
     */
    public function setAttributes(array $attributes, array $attributeNames): bool
    {
        $loaded = false;
        if ($attributes) {
            foreach ($attributeNames as $attributeName) {
                $loaded = true;
                $this->$attributeName = $attributes[$attributeName] ?? null;
            }
        }
        return $loaded;
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
        
        $query = static::find();
        
        $sort = $_GET['sort'] ?? null;
        $sortType = $_GET['sortType'] ?? null;
        $attributes = $model->attributes();
        $params = [];
        if ($sort && in_array($sort, $attributes)) {
            $params['sort'] = $sort;
            $query->orderBy($sort, $sortType == 'desc' ? 'DESC' : 'ASC');
        } else {
            $query->orderBy('id', 'DESC');
        }
        
        $appUrl = Route::getAppUrl();
        $fieldsSortUrls = [];
        foreach ($attributes as $attribute) {
            $fieldsSortUrls[$attribute] = $appUrl . '?sort=' . $attribute;
            if ($sort == $attribute && ($sortType == 'asc' || !$sortType)) {
                $fieldsSortUrls[$attribute] .= '&sortType=desc';
            }
        }
        
        if ($sortType && in_array($sortType, ['asc', 'desc'])) {
            $params['sortType'] = $sortType;
        }
        
        $params['page'] = '';
        
        $url = $appUrl . '?' . http_build_query($params) . '(:num)';
        $pagination = new Paginator($totalItems, $itemsPerPage, $currentPage, $url);
        
        
        $items = $query
            ->offset($offset)
            ->limit($itemsPerPage)
            ->select()
            ->all();
        
        return compact('items', 'pagination', 'fieldsSortUrls');
    }


    /**
     * Returns query object
     * @return \Opis\Database\SQL\Query
     */
    public static function find()
    {
        return static::getDb()->from(static::getTableName());
    }
    
    /**
     * Returns object instance found by id
     * @param int $id
     * @return static|null
     */
    public static function findById(int $id): ?self
    {
        $data = static::find()->where('id')->eq($id)->select()->first();
        if (!$data) {
            return null;
        }
        return new static(get_object_vars($data));
    }
    
    /**
     * @return \Opis\Database\Database
     */
    public static function getDb()
    {
        return Database::getInstance()->getDb();
    }
    
    /**
     * Return name of table in database
     * @return string
     */
    abstract public static function getTableName(): string;
    
    /**
     * Return array with validation errors
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
    
    /**
     * @param array $attributes
     * @param array|null $attributeNames
     * @return bool
     */
    public function load(array $attributes, ?array $attributeNames = null): bool
    {
        $attributeNames = $attributeNames ?? $this->safeAttributes();
        return $this->setAttributes($attributes, $attributeNames);
    }
    
    /**
     * Return array of attributes names in database columns which should be loaded from request
     * @return array
     */
    abstract public function safeAttributes(): array;
    
    /**
     * @param $attribute
     * @return bool
     */
    public function validateRequired($attribute): bool
    {
        if (empty($this->$attribute)) {
            $this->addValidationError($attribute, $attribute . ' is required');
            return false;
        }
        return true;
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
     * @param string $attribute
     * @param int $length
     * @return bool
     */
    public function validateMaxLength(string $attribute, int $length): bool
    {
        if (mb_strlen($this->$attribute) > $length) {
            $this->addValidationError($attribute, 'Max length for ' . $attribute . ' is ' . $length . ' characters');
            return false;
        }
        return true;
    }
    
    /**
     * Validate email address
     * @param string $attribute
     * @return bool
     */
    public function validateEmail(string $attribute): bool
    {
        if(!filter_var($this->$attribute, FILTER_VALIDATE_EMAIL)) {
            $this->addValidationError($attribute, $this->$attribute.' is not valid email address');
            return false;
        }
        return true;
    }
    
    /**
     * Save model to database if model is valid
     * @return bool
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }
        $attributes = $this->getAttributes();
        $primaryKey = $this->primaryKey;
        $db = static::getDb();
        if ($this->{$primaryKey}) {
            unset($attributes[$this->primaryKey]);
            $db->update(static::getTableName())
                ->where($this->primaryKey)
                ->is($this->{$primaryKey})
                ->set($attributes);
            return true;
        }
        $result = $db->insert($attributes)->into(static::getTableName());
        if ($result) {
            $this->$primaryKey = Database::getInstance()->getLastInsertId();
            return true;
        }
        
        return false;
        
    }
    
    /**
     * Check if model attributes have valid values
     * @return bool
     */
    public abstract function validate(): bool;
    
    /**
     * Returns array of model attributes
     * @return array
     */
    public function getAttributes(): array
    {
        $attributeNames = $this->attributes();
        $attributes = [];
        foreach ($attributeNames as $attributeName) {
            $attributes[$attributeName] = $this->$attributeName ?? null;
        }
        return $attributes;
    }
    
    /**
     * Returns validation error if exists for given attribute
     * @param string $attribute
     * @return string|null
     */
    public function getError(string $attribute): ?string
    {
        return $this->validationErrors[$attribute] ?? null;
    }
}