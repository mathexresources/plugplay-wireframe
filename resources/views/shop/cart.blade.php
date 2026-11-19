@extends('layouts.app')
@section('step', '4')
@section('title', $car ? 'Tvůj plán úprav' : 'Košík')
@section('content')
<div class="container py-4">
  @if ($items->isEmpty())
    <div class="text-center py-5">
      <i class="bi bi-list-check display-3 text-secondary"></i>
      <h1 class="mt-2">{{ $car ? 'Plán úprav je zatím prázdný' : 'Košík je prázdný' }}</h1>
      <p class="text-secondary">{{ $car ? 'Vyber, co chceš na '.$car->short_name.' vylepšit.' : 'Zadej svoje auto a ukážeme ti, co do něj pasuje.' }}</p>
      <a href="{{ $car ? route('car.show', $car->slug) : route('home').'#vyber-auta' }}" class="btn btn-dark">{{ $car ? 'Co vylepšit na '.$car->short_name : 'Vybrat auto' }}</a>
      <a href="{{ route('catalog') }}" class="btn btn-outline-dark">Procházet katalog</a>
    </div>
  @else
    <p class="text-uppercase small fw-bold text-secondary mb-1">{{ $car ? 'Tvůj plán úprav' : 'Košík' }}</p>
    <h1 class="mb-1">{{ $car ? $car->short_name.': ' : '' }}{{ $summary->count }} {{ plural($summary->count, 'položka', 'položky', 'položek') }}@if ($summary->minutes), zhruba {{ \App\Models\Product::minutes($summary->minutes) }} práce @endif</h1>
    <p class="text-secondary">Seřadili jsme je v pořadí, ve kterém je doporučujeme montovat.</p>

    <div class="row g-4 mt-1">
      <div class="col-lg-8">
        <div class="pp-timeline">
          @foreach ($items as $i)
            <div class="step mb-4" data-n="{{ $loop->iteration }}">
              <div class="pp-card p-3"><div class="d-flex gap-3">
                @if ($i->type === 'bundle')
                  <div class="pp-img sm flex-shrink-0" style="width:96px;height:96px;min-height:0"><i class="bi bi-box2-heart"></i></div>
                @else
                  <a href="{{ route('product.show', $i->model->slug) }}">@include('partials.img', ['p' => $i->model, 'class' => 'sm flex-shrink-0', 'style' => 'width:96px;height:96px;min-height:0'])</a>
                @endif
                <div class="flex-fill">
                  <div class="d-flex justify-content-between gap-2">
                    <b>@if ($i->type === 'bundle')Sada: {{ $i->model->name }}@else<a class="text-reset" href="{{ route('product.show', $i->model->slug) }}">{{ $i->model->name }}</a>@endif</b>
                    <b class="text-nowrap">{{ czk($i->total) }}</b>
                  </div>
                  <div class="small text-secondary">
                    @if ($i->type === 'bundle'){{ $i->model->products->pluck('name')->implode(' · ') }}
                    @else{{ $i->variant ? $i->variant.' · ' : '' }}{{ $i->model->short_description }}@endif
                  </div>
                  @if ($i->type === 'product' && $i->model->install_minutes)
                    <div class="small mt-2"><i class="bi bi-clock"></i> {{ $i->model->install_time }} · @include('partials.diff', ['d' => $i->model->difficulty])@if ($i->model->location_in_car) · <i class="bi bi-geo-alt"></i> {{ $i->model->location_in_car }}@endif</div>
                  @endif
                  @if ($i->type === 'product' && $i->model->install_tip)
                    <div class="alert alert-light border small py-1 px-2 mt-2 mb-0"><i class="bi bi-lightbulb"></i> {{ $i->model->install_tip }}</div>
                  @endif
                  <form method="post" action="{{ route('cart.update') }}" class="d-flex gap-2 mt-2 small align-items-center">@csrf
                    <input type="hidden" name="key" value="{{ $i->key }}">
                    <input type="number" name="qty" min="0" max="99" class="form-control form-control-sm" style="width:70px" value="{{ $i->qty }}" aria-label="Počet kusů" onchange="this.form.submit()">
                    <span class="text-secondary">× {{ czk($i->price) }}</span>
                    <button name="qty" value="0" class="btn btn-link btn-sm link-secondary p-0 ms-2">Odebrat</button>
                  </form>
                </div>
              </div></div>
            </div>
          @endforeach
          <div class="step" data-n="+">
            <a href="{{ $car ? route('car.show', $car->slug) : route('catalog') }}" class="pp-box d-block p-3 text-center text-reset text-decoration-none"><i class="bi bi-plus-circle"></i> Přidat další upgrade{{ $car ? ' pro '.$car->short_name : '' }}</a>
          </div>
        </div>

        @if ($summary->tools->isNotEmpty())
          <form method="post" action="{{ route('cart.tools') }}" class="pp-box p-3 mt-4">@csrf
            <h2 class="h5"><i class="bi bi-tools"></i> Nářadí pro celý plán</h2>
            <p class="small text-secondary mb-2">Odškrtni, co doma máš. Zbytek ti můžeme přibalit.</p>
            <div class="row small">
              @foreach ($summary->tools as $t)
                @php $inCart = $t->product_id && $items->contains(fn ($i) => $i->type === 'product' && $i->model->id === $t->product_id); @endphp
                <div class="col-sm-6"><div class="form-check">
                  @if ($t->product_id && ! $inCart)
                    <input class="form-check-input tool-missing" type="checkbox" id="t{{ $t->id }}" name="tools[]" value="{{ $t->id }}" data-price="{{ $t->product->price }}">
                    <label class="form-check-label" for="t{{ $t->id }}">{{ $t->name }} – <b>nemám</b> (+{{ czk($t->product->price) }})</label>
                  @else
                    <input class="form-check-input" type="checkbox" checked disabled id="t{{ $t->id }}">
                    <label class="form-check-label" for="t{{ $t->id }}">{{ $t->name }} {{ $inCart ? '– v plánu' : '– běžně doma' }}</label>
                  @endif
                </div></div>
              @endforeach
            </div>
            <div class="d-flex flex-wrap justify-content-end mt-2"><button class="btn btn-sm btn-outline-dark" id="tools-btn">Přidat zaškrtnuté nářadí <span id="tools-sum"></span></button></div>
          </form>
        @endif

        @if ($addMore->isNotEmpty())
          <h2 class="h5 mt-4">Mohlo by se ti hodit</h2>
          <div class="row row-cols-1 row-cols-md-3 g-3">
            @foreach ($addMore as $p)<div class="col">@include('partials.product-card', ['p' => $p, 'car' => $car])</div>@endforeach
          </div>
        @endif
      </div>

      <div class="col-lg-4">
        <div class="pp-card p-4 position-sticky" style="top:1rem">
          <h2 class="h5">Souhrn</h2>
          <div class="d-flex justify-content-between small"><span>Položky ({{ $summary->count }})</span><span>{{ czk($summary->subtotal) }}</span></div>
          @if ($summary->coupon)
            <div class="d-flex justify-content-between small text-success"><span>Sleva {{ $summary->coupon->code }} ({{ $summary->coupon->label }})</span><span>−{{ czk($summary->discount) }}</span></div>
          @endif
          <div class="d-flex justify-content-between small"><span>Doprava</span><span class="text-secondary">{{ $summary->freeShipping ? 'zdarma' : 'zvolíš v dalším kroku' }}</span></div>
          <hr>
          <div class="d-flex justify-content-between fs-4 fw-bold"><span>Celkem</span><span>{{ czk($summary->total) }}</span></div>
          @if ($summary->freeFrom)
            <div class="progress my-3" style="height:8px" role="progressbar" aria-label="Do dopravy zdarma"><div class="progress-bar bg-dark" style="width:{{ min(100, round($summary->total / $summary->freeFrom * 100)) }}%"></div></div>
            <div class="small text-secondary mb-3">{{ $summary->freeShipping ? 'Máš dopravu zdarma!' : 'Do dopravy zdarma ti chybí '.czk($summary->missingForFree).'.' }}</div>
          @endif
          @if ($summary->coupon)
            <form method="post" action="{{ route('cart.coupon') }}" class="mb-3">@csrf <input type="hidden" name="remove" value="1"><button class="btn btn-link btn-sm p-0 link-secondary">Odebrat slevový kód</button></form>
          @else
            <form method="post" action="{{ route('cart.coupon') }}" class="input-group input-group-sm mb-1">@csrf<input class="form-control" name="code" placeholder="Slevový kód" value="{{ old('code') }}" aria-label="Slevový kód"><button class="btn btn-outline-dark">Uplatnit</button></form>
            @if ($summary->couponError)<div class="small text-danger mb-2">{{ $summary->couponError }}</div>@endif
            <div class="small text-secondary mb-3">Vyzkoušej <code>SKOLA10</code></div>
          @endif
          <a href="{{ route('checkout') }}" class="btn btn-dark btn-lg w-100">Pokračovat k objednávce <i class="bi bi-arrow-right"></i></a>
          <form method="post" action="{{ route('cart.save') }}" class="small text-center text-secondary mt-3">@csrf
            <i class="bi bi-bookmark"></i> <button class="btn btn-link btn-sm p-0 link-secondary align-baseline">Uložit plán na později</button> ·
            <i class="bi bi-share"></i> <button name="share" value="1" class="btn btn-link btn-sm p-0 link-secondary align-baseline">Sdílet</button>
          </form>
          @if (session('share_url'))
            <div class="input-group input-group-sm mt-2"><input class="form-control" value="{{ session('share_url') }}" readonly id="share-url" aria-label="Odkaz na plán"><button class="btn btn-outline-dark" type="button" onclick="navigator.clipboard.writeText(document.getElementById('share-url').value);this.textContent='Zkopírováno'">Kopírovat</button></div>
          @endif
        </div>
      </div>
    </div>
  @endif
</div>
@endsection
@push('scripts')
<script>
  const boxes = document.querySelectorAll('.tool-missing'), sum = document.getElementById('tools-sum');
  const upd = () => { let s = 0; boxes.forEach(b => { if (b.checked) s += +b.dataset.price; }); if (sum) sum.textContent = s ? `(+${s.toLocaleString('cs-CZ')} Kč)` : ''; };
  boxes.forEach(b => b.addEventListener('change', upd));
</script>
@endpush
