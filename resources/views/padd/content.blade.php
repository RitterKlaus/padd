@if ($handout)
<h1>{{ $handout->title }}</h1>
{!! $body !!}
@else
<h1>Standby</h1>
<p>No data available.</p>
@endif
