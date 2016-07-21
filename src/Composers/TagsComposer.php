<?php


namespace BotTelegram\Composers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewT;
use Illuminate\Support\Facades\Blade;

class TagsComposer
{
    /**
     * The user repository implementation.
     *
     * @var UserRepository
     */
    protected $users;
    public static $js_files = [];
    public static $css_files = [];

    /**
     * Create a new profile composer.
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct()
    {
        // Dependencies automatically resolved by service container...
//        $this->users = $users;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        static::$js_files[] = '/assets/tags/js/selectize.js';
        static::$js_files[] = '/assets/tags/js/selectize.jquery.js';
        static::$css_files[] = '/assets/tags/css/selectize.default.css';

        list($css_html, $js_html) = $this->renderAssets();
        $view = ViewT::make('bot-telegram::tags', ['css_html'=>$css_html, 'js_html'=>$js_html]);

        $sections = $view->render();
        view()->share('tags', $sections);
        //$view->with('test', $assets);
    }

    public function renderAssets() {
        $css_html = '';
        $js_html = '';
        $js = static::$js_files;
        $css = static::$css_files;
        foreach ($css as $c) {
            $css_html .= "<link href='$c' rel='stylesheet' />";
        }

        foreach ($js as $j) {
            $js_html .= "<script src='$j'></script>";
        }

        return [$css_html, $js_html];
    }
}