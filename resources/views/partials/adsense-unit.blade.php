@if($slotId || $autoFormat)
<div class="ad-slot ad-slot--{{ $name ?? 'unit' }}" id="ad-{{ $name ?? 'unit' }}">
  <p class="ad-label">Quảng cáo</p>
  <ins class="adsbygoogle"
       style="display:block"
       data-ad-client="{{ $clientId }}"
       @if($slotId) data-ad-slot="{{ $slotId }}" @endif
       @if($autoFormat) data-ad-format="auto" data-full-width-responsive="true" @endif
  ></ins>
</div>
@endif
