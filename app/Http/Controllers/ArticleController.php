<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use jcobhams\NewsApi\NewsApi;

class ArticleController extends Controller
{
    use ApiResponder;

    public function index(Request $request)
    {
        try {
            $newsapi = new NewsApi(env('NEWS_API_SECRET_KEY'));
//
////            dd($newsapi->getSources(null, 'en', 'us'));
//        $all_articles = $newsapi->getEverything(null, 'new-york-magazine', null, null, );
//        dd($all_articles);
//
            $top_headlines = $newsapi->getTopHeadlines(null, null, 'us');
            dd($top_headlines);
//
//        $sources = $newsapi->getSources('technology');
//        dd($sources);

        } catch (\Exception $e) {
            return $this->sendError('An unexpected error occurred', [$e->getMessage(), $e->getTraceAsString()], 500);
        }
    }
}
