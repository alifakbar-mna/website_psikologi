<h1>Case Management System</h1>

<p>
    Status laporan
    <strong>{{ $reportTitle }}</strong>:
    <strong>{{ $decisionStatus }}</strong>.
</p>

@if($reviewNote)
    <p>Catatan reviewer:</p>

    <p style="white-space: pre-wrap;">{{ $reviewNote }}</p>
@endif

<p>
    Masuk ke aplikasi dan buka Laporan Saya untuk melihat
    perkembangan atau mengirim revisi.
</p>

<p>
    <a href="{{ url('/reports/mine') }}">
        Buka Laporan Saya
    </a>
</p>