@extends('layouts.app')
@section('title', __('Health Metrics'))
@push('style')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header"><h1><i class="fas fa-heartbeat" style="color:var(--primary)"></i> {{ __('Health Metrics Dashboard') }}</h1></div>
        <div class="section-body">
            <div class="row mb-4">
                <div class="col-12">
                    <form method="GET" class="d-flex align-items-center">
                        <label class="mr-2 mb-0"><strong>Client:</strong></label>
                        <select name="client_id" class="form-control mr-2" style="max-width:300px" onchange="this.form.submit()">
                            <option value="">{{ __('Select a client...') }}</option>
                            @foreach($clients as $c)<option value="{{ $c->id }}" {{ request('client_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach
                        </select>
                        <select name="period" class="form-control mr-2" style="max-width:150px" onchange="this.form.submit()">
                            <option value="7" {{ request('period','7')=='7'?'selected':'' }}>7 Days</option>
                            <option value="14" {{ request('period')=='14'?'selected':'' }}>14 Days</option>
                            <option value="30" {{ request('period')=='30'?'selected':'' }}>30 Days</option>
                            <option value="90" {{ request('period')=='90'?'selected':'' }}>90 Days</option>
                        </select>
                    </form>
                </div>
            </div>

            @if(isset($selectedClient))
            <div class="row">
                <div class="col-md-3"><div class="card card-statistic-3">
                    <div class="card-body"><div class="d-flex justify-content-between">
                        <div><small class="text-muted">Resting HR</small><h4 style="color:var(--primary)">{{ $restingHr ?? '--' }} <small>bpm</small></h4></div>
                        <div><i class="fas fa-heart fa-2x" style="color:var(--primary);opacity:0.3"></i></div>
                    </div></div>
                </div></div>
                <div class="col-md-3"><div class="card card-statistic-3">
                    <div class="card-body"><div class="d-flex justify-content-between">
                        <div><small class="text-muted">HRV</small><h4 style="color:var(--success)">{{ $hrv ?? '--' }} <small>ms</small></h4></div>
                        <div><i class="fas fa-wave-square fa-2x" style="color:var(--success);opacity:0.3"></i></div>
                    </div></div>
                </div></div>
                <div class="col-md-3"><div class="card card-statistic-3">
                    <div class="card-body"><div class="d-flex justify-content-between">
                        <div><small class="text-muted">Avg Sleep</small><h4 style="color:var(--info)">{{ $avgSleep ?? '--' }} <small>hrs</small></h4></div>
                        <div><i class="fas fa-moon fa-2x" style="color:var(--info);opacity:0.3"></i></div>
                    </div></div>
                </div></div>
                <div class="col-md-3"><div class="card card-statistic-3">
                    <div class="card-body"><div class="d-flex justify-content-between">
                        <div><small class="text-muted">Daily Steps</small><h4 style="color:var(--warning)">{{ $avgSteps ? number_format($avgSteps) : '--' }}</h4></div>
                        <div><i class="fas fa-shoe-prints fa-2x" style="color:var(--warning);opacity:0.3"></i></div>
                    </div></div>
                </div></div>
            </div>
            <div class="row">
                <div class="col-md-6"><div class="card"><div class="card-header"><h4>{{ __('Heart Rate Trend') }}</h4></div>
                    <div class="card-body"><canvas id="hrChart" height="200"></canvas></div></div></div>
                <div class="col-md-6"><div class="card"><div class="card-header"><h4>{{ __('Sleep Duration') }}</h4></div>
                    <div class="card-body"><canvas id="sleepChart" height="200"></canvas></div></div></div>
            </div>
            <div class="row">
                <div class="col-md-6"><div class="card"><div class="card-header"><h4>{{ __('HRV Trend') }}</h4></div>
                    <div class="card-body"><canvas id="hrvChart" height="200"></canvas></div></div></div>
                <div class="col-md-6"><div class="card"><div class="card-header"><h4>{{ __('Active Calories') }}</h4></div>
                    <div class="card-body"><canvas id="caloriesChart" height="200"></canvas></div></div></div>
            </div>
            @else
            <div class="row"><div class="col-12"><div class="card"><div class="card-body text-center py-5">
                <i class="fas fa-heartbeat fa-3x text-muted mb-3"></i>
                <h5>{{ __('Select a client to view their health metrics') }}</h5>
                <p class="text-muted">Health data from Apple Watch, Google Fit, Fitbit and other wearables will appear here.</p>
            </div></div></div></div>
            @endif
        </div>
    </section>
</div>
@endsection
@push('scripts')
@if(isset($chartData))
<script>
const chartConfig = (label, data, labels, color) => ({
    type: 'line', data: { labels: labels, datasets: [{ label: label, data: data, borderColor: color, backgroundColor: color+'20', tension: 0.3, fill: true }] },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: false } } }
});
@if(isset($chartData['hr']))new Chart(document.getElementById('hrChart'), chartConfig('Heart Rate', {!! json_encode($chartData['hr']['values']) !!}, {!! json_encode($chartData['hr']['labels']) !!}, '#1E88E5'));@endif
@if(isset($chartData['sleep']))new Chart(document.getElementById('sleepChart'), chartConfig('Sleep (hrs)', {!! json_encode($chartData['sleep']['values']) !!}, {!! json_encode($chartData['sleep']['labels']) !!}, '#42A5F5'));@endif
@if(isset($chartData['hrv']))new Chart(document.getElementById('hrvChart'), chartConfig('HRV (ms)', {!! json_encode($chartData['hrv']['values']) !!}, {!! json_encode($chartData['hrv']['labels']) !!}, '#D4AF37'));@endif
@if(isset($chartData['calories']))new Chart(document.getElementById('caloriesChart'), chartConfig('Calories', {!! json_encode($chartData['calories']['values']) !!}, {!! json_encode($chartData['calories']['labels']) !!}, '#F4811F'));@endif
</script>
@endif
@endpush
