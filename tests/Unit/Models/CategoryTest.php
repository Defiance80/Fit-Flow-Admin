<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Category;

class CategoryTest extends TestCase
{
    public function test_category_model_exists()
    {
        $this->assertTrue(class_exists(Category::class));
    }

    public function test_category_has_courses_relationship()
    {
        $category = new Category();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $category->courses());
    }

    public function test_category_fillable_attributes()
    {
        $category = new Category();
        $fillable = $category->getFillable();
        
        $this->assertIsArray($fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('slug', $fillable);
    }
}