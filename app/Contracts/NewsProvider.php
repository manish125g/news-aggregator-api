<?php

namespace App\Contracts;

interface NewsProvider
{
    public function fetchArticles(): array;
}
