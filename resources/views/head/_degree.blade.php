{{-- Degree lines for the Name column. Expects $degree (Degree or null). --}}
<div class="mt-1">
    <small class="text-muted d-block">Degree Name:</small>
    @if($degree?->degree_name)
        <span class="badge badge-secondary" style="white-space:normal; text-align:left;">{{ $degree->degree_name }}</span>
    @else
        <span class="text-muted">—</span>
    @endif
</div>
