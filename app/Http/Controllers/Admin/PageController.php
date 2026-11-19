<?php

namespace App\Http\Controllers\Admin;

use App\Models\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class PageController extends CrudController
{
    protected string $model = Page::class;
    protected string $route = 'admin.pages';
    protected string $title = 'Stránky';
    protected string $singular = 'stránka';
    protected array $search = ['title'];
    protected string $orderBy = 'sort';
    protected string $orderDir = 'asc';
    protected ?string $slugFrom = 'title';

    protected function columns(): array
    {
        return [
            'Stránka' => fn ($p) => new HtmlString('<b>'.e($p->title).'</b> <a class="small" target="_blank" href="'.route('page', $p->slug).'">/info/'.e($p->slug).'</a>'),
            'Sekce v patičce' => fn ($p) => $p->group === 'nakup' ? 'Nákup' : 'Poradna',
            'Stav' => fn ($p) => new HtmlString($p->is_published ? '<span class="badge text-bg-success">publikovaná</span>' : '<span class="badge text-bg-secondary">skrytá</span>'),
        ];
    }

    protected function fields(?Model $item): array
    {
        return [
            ['name' => 'title', 'label' => 'Název', 'rules' => 'required|string|max:120', 'col' => 6],
            ['name' => 'slug', 'label' => 'URL (slug)', 'rules' => 'required|alpha_dash|max:120', 'unique' => true, 'col' => 6],
            ['name' => 'group', 'label' => 'Sekce v patičce', 'type' => 'select', 'rules' => 'required|in:nakup,poradna', 'options' => ['nakup' => 'Nákup', 'poradna' => 'Poradna'], 'col' => 4],
            ['name' => 'sort', 'label' => 'Pořadí', 'type' => 'number', 'rules' => 'required|integer|min:0', 'col' => 4],
            ['name' => 'is_published', 'label' => 'Publikovat', 'type' => 'checkbox', 'default' => true, 'col' => 4],
            ['name' => 'body', 'label' => 'Obsah (HTML)', 'type' => 'html', 'rules' => 'required|string', 'col' => 12],
        ];
    }
}
