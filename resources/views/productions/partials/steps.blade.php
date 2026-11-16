@php
    $steps = [
        1 => ['Submit purity & impurity', 'Weigh clean output and NC'],
        2 => ['Push purity to FG', 'Clean output into finished goods'],
        3 => ['Rework the NC', 'Recovered → FG, final NC → NC stock'],
    ];
@endphp
<ol class="steps">
    @foreach($steps as $number => [$title, $text])
    <li class="step {{ $number < $current ? 'done' : ($number === $current ? 'current' : '') }} {{ ($skipped ?? null) === $number ? 'skipped' : '' }}">
        <span class="step-dot">{{ $number < $current ? '✓' : $number }}</span>
        <span><strong>{{ $title }}</strong><small>{{ ($skipped ?? null) === $number ? 'Not needed — no NC' : $text }}</small></span>
    </li>
    @endforeach
</ol>
