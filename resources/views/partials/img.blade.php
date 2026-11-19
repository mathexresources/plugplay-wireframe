{{-- $p product, $class, $style --}}
<div class="pp-img {{ $class ?? '' }}" style="{{ $style ?? '' }}">
  @if ($p->image_url)<img src="{{ $p->image_url }}" alt="{{ $p->name }}" loading="lazy">@else<i class="bi {{ $p->category->icon ?? 'bi-box' }}" aria-hidden="true"></i>@endif
  {{ $slot ?? '' }}
</div>
