@if (session('success') || session('warning') || $errors->any())
    @php
        $flashPayload = [
            'success' => session('success'),
            'warning' => session('warning'),
            'errors' => $errors->all(),
        ];
    @endphp
    <script type="application/json" data-swal-flash>
        {!! json_encode($flashPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>
@endif
