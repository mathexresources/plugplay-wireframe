<span class="pp-diff" title="Náročnost: {{ \App\Models\Product::DIFFICULTY[$d] ?? '' }}">@for ($i = 1; $i <= 3; $i++)<i class="bi bi-wrench {{ $i <= $d ? 'on' : '' }}"></i>@endfor</span>
