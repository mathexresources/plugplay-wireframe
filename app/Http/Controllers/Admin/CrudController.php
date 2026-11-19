<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Small declarative CRUD for the admin. Each child describes its fields; views admin.crud.* render them.
 * Field: ['name', 'label', 'type' => text|textarea|html|number|select|checkbox|multiselect|image|date|steps, 'options' => [...], 'rules' => '...', 'col' => 6, 'help' => '']
 */
abstract class CrudController extends Controller
{
    protected string $model;
    protected string $route;      // e.g. admin.products
    protected string $title;      // Produkty
    protected string $singular;   // produkt
    protected array $search = ['name'];
    protected array $with = [];
    protected string $orderBy = 'id';
    protected string $orderDir = 'desc';
    protected ?string $slugFrom = null; // auto slug from this field when empty

    abstract protected function fields(?Model $item): array;
    abstract protected function columns(): array; // ['label' => fn($item) => string|HtmlString]

    protected function query() { return ($this->model)::query()->with($this->with); }

    public function index(Request $request)
    {
        $q = $this->query();
        if ($term = trim((string) $request->query('q'))) {
            $q->where(fn ($w) => collect($this->search)->each(fn ($f) => $w->orWhere($f, 'like', "%$term%")));
        }
        $this->filter($q, $request);
        $items = $q->orderBy($this->orderBy, $this->orderDir)->paginate(25)->withQueryString();
        return view('admin.crud.index', ['items' => $items, 'columns' => $this->columns(), 'c' => $this->meta(), 'filters' => $this->filters()]);
    }

    protected function filter($q, Request $request): void {}
    protected function filters(): array { return []; }

    public function create() { return $this->form(null); }

    public function edit($id) { return $this->form(($this->model)::findOrFail($id)); }

    protected function form(?Model $item)
    {
        return view('admin.crud.form', ['item' => $item, 'fields' => $this->fields($item), 'c' => $this->meta()]);
    }

    public function store(Request $request)
    {
        $item = new ($this->model);
        $this->save($request, $item);
        return redirect()->route($this->route.'.index')->with('ok', ucfirst($this->singular).' je vytvořený.');
    }

    public function update(Request $request, $id)
    {
        $item = ($this->model)::findOrFail($id);
        $this->save($request, $item);
        return redirect()->route($this->route.'.edit', $item->getKey())->with('ok', 'Změny jsou uložené.');
    }

    public function destroy($id)
    {
        $item = ($this->model)::findOrFail($id);
        if ($msg = $this->cannotDelete($item)) return back()->withErrors(['delete' => $msg]);
        foreach ($this->fields($item) as $f) {
            if (($f['type'] ?? '') === 'image' && $item->{$f['name']}) Storage::disk('public')->delete($item->{$f['name']});
        }
        $item->delete();
        return redirect()->route($this->route.'.index')->with('ok', ucfirst($this->singular).' je smazaný.');
    }

    protected function cannotDelete(Model $item): ?string { return null; }

    protected function save(Request $request, Model $item): void
    {
        $fields = $this->fields($item->exists ? $item : null);
        $rules = [];
        foreach ($fields as $f) {
            $type = $f['type'] ?? 'text';
            $rule = $f['rules'] ?? 'nullable';
            if ($type === 'image') $rule = 'nullable|image|max:5120';
            if ($type === 'checkbox') continue;
            if ($type === 'multiselect') { $rules[$f['name']] = 'nullable|array'; continue; }
            if ($type === 'steps') { $rules[$f['name']] = 'required|array|min:1'; $rules[$f['name'].'.*.title'] = 'required|string|max:160'; $rules[$f['name'].'.*.body'] = 'nullable|string|max:3000'; continue; }
            if (isset($f['unique'])) $rule .= '|unique:'.$item->getTable().','.$f['name'].($item->exists ? ','.$item->getKey().','.$item->getKeyName() : '');
            $rules[$f['name']] = $rule;
        }
        if ($this->slugFrom && $request->filled($this->slugFrom) && ! $request->filled('slug')) {
            $request->merge(['slug' => Str::slug($request->input($this->slugFrom))]);
        }
        $data = $request->validate($rules, [], collect($fields)->pluck('label', 'name')->all());

        $sync = [];
        foreach ($fields as $f) {
            $name = $f['name']; $type = $f['type'] ?? 'text';
            if ($type === 'checkbox') { $item->$name = $request->boolean($name); continue; }
            if ($type === 'multiselect') { $sync[$f['relation'] ?? $name] = array_map('intval', $data[$name] ?? []); continue; }
            if ($type === 'image') {
                if ($request->boolean($name.'_remove') && $item->$name) { Storage::disk('public')->delete($item->$name); $item->$name = null; }
                if ($request->hasFile($name)) {
                    if ($item->$name) Storage::disk('public')->delete($item->$name);
                    $item->$name = $request->file($name)->store($f['dir'] ?? 'uploads', 'public');
                }
                continue;
            }
            if ($type === 'steps') { $item->$name = array_values(array_map(fn ($s) => ['title' => $s['title'], 'body' => $s['body'] ?? ''], $data[$name])); continue; }
            if (($f['virtual'] ?? false)) continue;
            $item->$name = ($data[$name] ?? null) === '' ? null : ($data[$name] ?? null);
        }
        $this->beforeSave($request, $item);
        $item->save();
        foreach ($sync as $rel => $ids) $item->$rel()->sync($ids);
        $this->afterSave($request, $item);
    }

    protected function beforeSave(Request $request, Model $item): void {}
    protected function afterSave(Request $request, Model $item): void {}

    protected function meta(): object
    {
        return (object) ['route' => $this->route, 'title' => $this->title, 'singular' => $this->singular];
    }

    protected static function opts($collection, string|\Closure $label = 'name'): array
    {
        return $collection->mapWithKeys(fn ($m) => [$m->id => $label instanceof \Closure ? $label($m) : $m->$label])->all();
    }
}
