<?php

function book_images_dir(): string
{
    return __DIR__ . '/images/books';
}

function book_cover_path(string $bookId): string
{
    return book_images_dir() . '/' . $bookId . '.jpeg';
}

function book_cover_url(string $bookId): string
{
    return './images/books/' . rawurlencode($bookId) . '.jpeg';
}

function book_cover_exists(string $bookId): bool
{
    return is_file(book_cover_path($bookId));
}
