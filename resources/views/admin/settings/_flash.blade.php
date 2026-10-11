{{-- resources/views/admin/settings/_flash.blade.php --}}
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: {!! json_encode(session('success')) !!},
                    timer: 3500,
                    timerProgressBar: true,
                    confirmButtonColor: '#28a745'
                });
            }
        });
    </script>
    <noscript>
        <div class="alert alert-success">{{ session('success') }}</div>
    </noscript>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    html: {!! json_encode(session('error')) !!},
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    </script>
    <noscript>
        <div class="alert alert-danger">{{ session('error') }}</div>
    </noscript>
@endif

@if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: {!! json_encode(implode('<br>', $errors->all())) !!},
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    </script>
    <noscript>
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </noscript>
@endif
