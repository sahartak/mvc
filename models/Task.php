<?php


namespace app\models;


use app\framework\classes\Model;
/**
 * Class task
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $text
 * @property int $status
 */
class Task extends Model
{
    
    CONST MAX_STRING_SIZE = 50;
    
    /**
     * Return name of table in database
     * @return string
     */
    public static function getTableName(): string
    {
        return 'tasks';
    }
    
    /**
     * @inheritDoc
     */
    public function attributes(): array
    {
        return [
            'id', 'name', 'email', 'text', 'status'
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function safeAttributes(): array
    {
        return  [
            'name', 'email', 'text'
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function validate(): bool
    {
        $this->name = strip_tags(trim($this->name));
        $this->validateRequired('name') && $this->validateMaxLength('name', static::MAX_STRING_SIZE);
        
        $this->email = trim($this->email);
        $this->validateRequired('email') && $this->validateEmail('email');
        $this->text = htmlentities(trim($this->text));
        $this->validateRequired('text') && $this->validateMaxLength('text', 2000);
        return $this->getValidationErrors() ? false : true;
    }
    
    
}