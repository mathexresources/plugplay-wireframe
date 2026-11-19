{{-- $p product, $car (optional CarModel), $harder (bool) --}}
@php $car = $car ?? null; $harder = $harder ?? false; @endphp
<div class="pp-product {{ $harder ? 'harder' : '' }}">
  <a href="{{ route('product.show', $p->slug) }}" class="text-reset position-relative">
    @include('partials.img', ['p' => $p])
    @if ($p->own_production)<span class="badge text-bg-dark position-absolute top-0 start-0 m-2">Vlastní výroba</span>@endif
    @if ($p->discount_percent)<span class="badge text-bg-primary position-absolute top-0 end-0 m-2">−{{ $p->discount_percent }} %</span>@endif
  </a>
  <div class="p-3 d-flex flex-column flex-fill">
    @if ($car)
      @if ($harder)
        <div class="pp-fit mb-1 text-secondary"><i class="bi bi-exclamation-triangle"></i> Náročnější, než sis zvolil</div>
      @elseif ($p->fits($car))
        <div class="pp-fit mb-1"><i class="bi bi-patch-check-fill"></i> {{ $p->is_universal ? 'Univerzální – pasuje' : 'Pasuje do tvého auta' }}</div>
      @endif
    @endif
    <a href="{{ route('product.show', $p->slug) }}" class="fw-bold text-reset text-decoration-none">{{ $p->name }}</a>
    <div class="small text-secondary my-1">
      @if ($p->rating)<i class="bi bi-star-fill text-warning"></i> {{ str_replace('.', ',', $p->rating) }} ({{ $p->approvedReviews->count() }}) · @endif
      @if ($p->install_minutes)<i class="bi bi-clock"></i> {{ $p->install_time }} · @include('partials.diff', ['d' => $p->difficulty])@else bez montáže @endif
    </div>
    <div class="small mb-2 {{ $p->in_stock ? 'text-success' : 'text-warning-emphasis' }}">{{ $p->in_stock ? 'Skladem' : 'Do '.$p->restock_days.' dnů' }}</div>
    <div class="mt-auto d-flex justify-content-between align-items-center gap-2">
      <div>@if ($p->old_price)<s class="small text-secondary">{{ czk($p->old_price) }}</s> @endif<b class="fs-5">{{ czk($p->price) }}</b></div>
      @if ($p->variantList())
        <a href="{{ route('product.show', $p->slug) }}" class="btn btn-sm btn-dark">Detail</a>
      @else
        <form method="post" action="{{ route('cart.add') }}">@csrf
          <input type="hidden" name="product_id" value="{{ $p->id }}"><input type="hidden" name="stay" value="1">
          <button class="btn btn-sm {{ $harder ? 'btn-outline-dark' : 'btn-dark' }}" aria-label="Přidat {{ $p->name }} do plánu"><i class="bi bi-plus-lg"></i><i class="bi bi-cart3"></i></button>
        </form>
      @endif
    </div>
  </div>
</div>
