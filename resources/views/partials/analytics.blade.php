@if(config('hanviet.seo.ga_measurement_id'))
<script async src="https://www.googletagmanager.com/gtag/js?id={{ config('hanviet.seo.ga_measurement_id') }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{{ config('hanviet.seo.ga_measurement_id') }}', { anonymize_ip: true });
</script>
@endif
