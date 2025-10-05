<?php

declare(strict_types=1);

use App\Models\DocumentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

test('document type uses HasFactory trait', function () {
    $documentType = new DocumentType;
    $traits = class_uses(DocumentType::class);

    expect($traits)->toContain(HasFactory::class);
});

test('document type uses SoftDeletes trait', function () {
    $documentType = new DocumentType;
    $traits = class_uses(DocumentType::class);

    expect($traits)->toContain(SoftDeletes::class);
});

test('document type has correct fillable attributes', function () {
    $documentType = new DocumentType;
    $fillable = $documentType->getFillable();

    expect($fillable)
        ->toBeArray()
        ->toContain('name')
        ->toContain('description')
        ->toHaveCount(2);
});

test('document type casts timestamps correctly', function () {
    $documentType = new DocumentType;
    $casts = $documentType->getCasts();

    expect($casts)
        ->toHaveKey('created_at', 'datetime')
        ->toHaveKey('updated_at', 'datetime')
        ->toHaveKey('deleted_at', 'datetime');
});

test('document type factory creates valid instance', function () {
    $documentType = DocumentType::factory()->make();

    expect($documentType)
        ->toBeInstanceOf(DocumentType::class)
        ->name->not->toBeNull()
        ->description->not->toBeNull();
});

test('document type factory withoutDescription state works', function () {
    $documentType = DocumentType::factory()->withoutDescription()->make();

    expect($documentType)
        ->toBeInstanceOf(DocumentType::class)
        ->name->not->toBeNull()
        ->description->toBeNull();
});

test('document type factory deleted state works', function () {
    $documentType = DocumentType::factory()->deleted()->make();

    expect($documentType)
        ->toBeInstanceOf(DocumentType::class)
        ->deleted_at->not->toBeNull();
});
