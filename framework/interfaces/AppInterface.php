<?php
namespace app\framework\interfaces;


interface AppInterface
{
    
    public function __construct(array $configs);
    
    public function run(): void;
    
}