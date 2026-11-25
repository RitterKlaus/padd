@if ($handout)
<h1>{{ $handout->title }}</h1>
@foreach (preg_split('/\R{2,}/', trim($handout->body)) as $paragraph)
<p>{!! nl2br(e($paragraph)) !!}</p>
@endforeach
@else
<h1>Standby</h1>
<p>No data available.</p>
@endif
